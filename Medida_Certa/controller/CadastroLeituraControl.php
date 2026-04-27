<?php
session_start();
require_once __DIR__ . '/../model/dao/Conexao.php';

function hasColumn(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE :column");
    $stmt->execute([':column' => $column]);
    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/admin.php');
    exit;
}

$id_hidrometro = filter_input(INPUT_POST, 'id_hidrometro', FILTER_VALIDATE_INT);
$valor_medido = filter_input(INPUT_POST, 'valor_medido', FILTER_VALIDATE_FLOAT);
$mes_referencia = filter_input(INPUT_POST, 'mes_referencia', FILTER_VALIDATE_INT);
$ano_referencia = filter_input(INPUT_POST, 'ano_referencia', FILTER_VALIDATE_INT);
$data_leitura = date('Y-m-d');
$id_funcionario = $_SESSION['id_usuario'] ?? null;

if (!$id_hidrometro || $valor_medido === false || $valor_medido === null || !$mes_referencia || !$ano_referencia) {
    header('Location: ../view/cadastrarLeitura.php?erro=campos_vazios');
    exit;
}

if ($valor_medido < 0) {
    header('Location: ../view/cadastrarLeitura.php?erro=valor_invalido');
    exit;
}

try {
    $pdo = Conexao::getConexao();

    // 1. BUSCA LEITURA ANTERIOR PARA CALCULAR CONSUMO
    $sqlBuscaAnterior = "SELECT valor_medido FROM leitura
                         WHERE id_hidrometro = :id_hidrometro
                         ORDER BY ano_referencia DESC, mes_referencia DESC
                         LIMIT 1";
    $stmtAnt = $pdo->prepare($sqlBuscaAnterior);
    $stmtAnt->execute([':id_hidrometro' => $id_hidrometro]);
    $leituraAnterior = $stmtAnt->fetchColumn();

    if ($leituraAnterior !== false && $valor_medido < $leituraAnterior) {
        header('Location: ../view/cadastrarLeitura.php?erro=leitura_menor');
        exit;
    }

    $consumo_calculado = ($leituraAnterior !== false) ? ($valor_medido - $leituraAnterior) : $valor_medido;

    // 2. INSERE A LEITURA
    $hasFuncionarioCol = hasColumn($pdo, 'leitura', 'id_funcionario');
    $insertColumns = 'id_hidrometro, valor_medido, consumo_calculado, data_leitura, mes_referencia, ano_referencia';
    $insertValues = ':id_hidrometro, :valor_medido, :consumo_calculado, :data_leitura, :mes_referencia, :ano_referencia';
    $params = [
        ':id_hidrometro'     => $id_hidrometro,
        ':valor_medido'      => $valor_medido,
        ':consumo_calculado' => $consumo_calculado,
        ':data_leitura'      => $data_leitura,
        ':mes_referencia'    => $mes_referencia,
        ':ano_referencia'    => $ano_referencia,
    ];

    if ($hasFuncionarioCol) {
        $insertColumns .= ', id_funcionario';
        $insertValues .= ', :id_funcionario';
        $params[':id_funcionario'] = $id_funcionario ?? 1;
    }

    $sql = "INSERT INTO leitura ($insertColumns) VALUES ($insertValues)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $id_leitura_atual = $pdo->lastInsertId();

    // 3. LÓGICA DE ANOMALIA (Se consumo > 50% da média das últimas 3)
    $sqlMedia = "SELECT AVG(consumo_calculado) as media
                 FROM (SELECT consumo_calculado FROM leitura
                       WHERE id_hidrometro = :id_hidrometro
                       AND id_leitura != :id_atual
                       ORDER BY ano_referencia DESC, mes_referencia DESC LIMIT 3) as ultimas";
    $stmtMedia = $pdo->prepare($sqlMedia);
    $stmtMedia->execute([':id_hidrometro' => $id_hidrometro, ':id_atual' => $id_leitura_atual]);
    $resMedia = $stmtMedia->fetch(PDO::FETCH_ASSOC);
    $mediaHistorica = $resMedia['media'] ?? 0;

    if ($mediaHistorica > 0 && $consumo_calculado > ($mediaHistorica * 1.5)) {
        $sqlAnomalia = "INSERT INTO anomalia (id_leitura, tipo, descricao, nivel, data_registro)
                        VALUES (:id_leitura, 'Consumo Elevado', :desc, 'Alto', NOW())";
        $stmtAnomalia = $pdo->prepare($sqlAnomalia);
        $desc = 'O sistema detectou um consumo de ' . $consumo_calculado . ' m³, que está acima da média de ' . round($mediaHistorica, 2) . ' m³.';
        $stmtAnomalia->execute([
            ':id_leitura' => $id_leitura_atual,
            ':desc'       => $desc
        ]);
    }

    // 4. NOVA LÓGICA: GERAÇÃO AUTOMÁTICA DA FATURA
    $sqlTarifa = "SELECT id_tarifa, valor_m3, taxa_esgoto FROM tarifa ORDER BY data_vigencia DESC LIMIT 1";
    $dadosTarifa = $pdo->query($sqlTarifa)->fetch(PDO::FETCH_ASSOC);

    $valor_m3 = $dadosTarifa['valor_m3'] ?? 8.26;
    $taxa_esgoto = $dadosTarifa['taxa_esgoto'] ?? 44.72;
    $id_tarifa = $dadosTarifa['id_tarifa'] ?? null;

    // Não cria fatura duplicada para a mesma leitura
    $stmtVerifica = $pdo->prepare("SELECT COUNT(*) FROM fatura WHERE id_leitura = :id_leitura");
    $stmtVerifica->execute([':id_leitura' => $id_leitura_atual]);
    $faturaExiste = $stmtVerifica->fetchColumn() > 0;

    if (!$faturaExiste) {
        $valor_total = ($consumo_calculado * $valor_m3) + $taxa_esgoto;
        $data_emissao = date('Y-m-d');
        $data_vencimento = date('Y-m-d', strtotime('+15 days'));

        $insertCols = ['id_leitura', 'consumo_m3', 'valor_total', 'data_emissao', 'data_vencimento'];
        $insertVals = [':id_leitura', ':consumo_m3', ':valor_total', ':data_emissao', ':data_vencimento'];
        $params = [
            ':id_leitura'  => $id_leitura_atual,
            ':consumo_m3'  => $consumo_calculado,
            ':valor_total' => $valor_total,
            ':data_emissao' => $data_emissao,
            ':data_vencimento' => $data_vencimento,
        ];

        if ($id_tarifa !== null && hasColumn($pdo, 'fatura', 'id_tarifa')) {
            $insertCols[] = 'id_tarifa';
            $insertVals[] = ':id_tarifa';
            $params[':id_tarifa'] = $id_tarifa;
        }

        if (hasColumn($pdo, 'fatura', 'status_pagamento')) {
            $insertCols[] = 'status_pagamento';
            $insertVals[] = ':status_pagamento';
            $params[':status_pagamento'] = 'Pendente';
        }

        if (hasColumn($pdo, 'fatura', 'data_cadastro')) {
            $insertCols[] = 'data_cadastro';
            $insertVals[] = 'NOW()';
        }

        $sqlFatura = "INSERT INTO fatura (" . implode(', ', $insertCols) . ") VALUES (" . implode(', ', $insertVals) . ")";
        $stmtFatura = $pdo->prepare($sqlFatura);
        $stmtFatura->execute($params);
    }

    header('Location: ../view/cadastrarLeitura.php?sucesso=1');
    exit;
} catch (PDOException $e) {
    error_log('Erro no MedidaCerta (Cadastro de Leitura/Fatura): ' . $e->getMessage());
    header('Location: ../view/cadastrarLeitura.php?erro=db_error');
    exit;
}