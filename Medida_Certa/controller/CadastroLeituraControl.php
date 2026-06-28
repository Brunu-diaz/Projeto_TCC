<?php
session_start();
require_once __DIR__ . '/../model/dao/Conexao.php';

function hasColumn(PDO $pdo, string $table, string $column): bool
{
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

// Validações básicas
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

    // 1. BUSCA LEITURA INICIAL (CADASTRO) E ÚLTIMA LEITURA (MENSAL)
    $sqlBase = "SELECT 
                    h.leitura_inicial,
                    (SELECT l.valor_medido FROM leitura l 
                     WHERE l.id_hidrometro = h.id_hidrometro 
                     ORDER BY l.ano_referencia DESC, l.mes_referencia DESC LIMIT 1) as ultima_leitura
                FROM hidrometro h 
                WHERE h.id_hidrometro = :id_hidrometro";

    $stmtBase = $pdo->prepare($sqlBase);
    $stmtBase->execute([':id_hidrometro' => $id_hidrometro]);
    $dadosBase = $stmtBase->fetch(PDO::FETCH_ASSOC);

    $leituraInicialHidrometro = $dadosBase['leitura_inicial'] ?? 0;
    $ultimaLeituraMensal = $dadosBase['ultima_leitura'];

    // Define o ponto de referência: prioriza a última leitura mensal, 
    // se não houver, usa a leitura inicial de cadastro.
    $pontoReferencia = ($ultimaLeituraMensal !== null) ? $ultimaLeituraMensal : $leituraInicialHidrometro;

    // VALIDAÇÃO: Impede leitura menor que o histórico/inicial
    if ($valor_medido < $pontoReferencia) {
        header('Location: ../view/cadastrarLeitura.php?erro=leitura_menor');
        exit;
    }

    // Cálculo do consumo real do período
    $consumo_calculado = $valor_medido - $pontoReferencia;

    // 2. INSERE A LEITURA
    $hasFuncionarioCol = hasColumn($pdo, 'leitura', 'id_funcionario');
    $insertColumns = 'id_hidrometro, valor_medido, consumo_calculado, data_leitura, mes_referencia, ano_referencia';
    $insertValues = ':id_hidrometro, :valor_medido, :consumo_calculado, :data_leitura, :mes_referencia, :ano_referencia';
    $paramsLeitura = [
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
        $paramsLeitura[':id_funcionario'] = $id_funcionario ?? 1;
    }

    $sql = "INSERT INTO leitura ($insertColumns) VALUES ($insertValues)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($paramsLeitura);

    $id_leitura_atual = $pdo->lastInsertId();

    // 3. LÓGICA DE ANOMALIA
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
        $desc = 'Consumo de ' . $consumo_calculado . ' m³ detectado, acima da média de ' . round($mediaHistorica, 2) . ' m³.';
        $stmtAnomalia->execute([
            ':id_leitura' => $id_leitura_atual,
            ':desc'       => $desc
        ]);
    }

    // 4. GERAÇÃO AUTOMÁTICA DA FATURA
    $sqlTarifa = "SELECT id_tarifa, valor_m3, taxa_esgoto FROM tarifa ORDER BY data_vigencia DESC LIMIT 1";
    $dadosTarifa = $pdo->query($sqlTarifa)->fetch(PDO::FETCH_ASSOC);

    $valor_m3 = $dadosTarifa['valor_m3'] ?? 8.26;
    $taxa_esgoto = $dadosTarifa['taxa_esgoto'] ?? 44.72;
    $id_tarifa = $dadosTarifa['id_tarifa'] ?? null;

    $stmtVerifica = $pdo->prepare("SELECT COUNT(*) FROM fatura WHERE id_leitura = :id_leitura");
    $stmtVerifica->execute([':id_leitura' => $id_leitura_atual]);

    if ($stmtVerifica->fetchColumn() == 0) {
        $valor_total = ($consumo_calculado * $valor_m3) + $taxa_esgoto;
        $data_emissao = date('Y-m-d');
        $data_vencimento = date('Y-m-d', strtotime('+15 days'));

        $insertColsFatura = ['id_leitura', 'consumo_m3', 'valor_total', 'data_emissao', 'data_vencimento'];
        $insertValsFatura = [':id_leitura', ':consumo_m3', ':valor_total', ':data_emissao', ':data_vencimento'];
        $paramsFatura = [
            ':id_leitura'      => $id_leitura_atual,
            ':consumo_m3'      => $consumo_calculado,
            ':valor_total'     => $valor_total,
            ':data_emissao'    => $data_emissao,
            ':data_vencimento' => $data_vencimento,
        ];

        if ($id_tarifa !== null && hasColumn($pdo, 'fatura', 'id_tarifa')) {
            $insertColsFatura[] = 'id_tarifa';
            $insertValsFatura[] = ':id_tarifa';
            $paramsFatura[':id_tarifa'] = $id_tarifa;
        }

        if (hasColumn($pdo, 'fatura', 'status_pagamento')) {
            $insertColsFatura[] = 'status_pagamento';
            $insertValsFatura[] = ':status_pagamento';
            $paramsFatura[':status_pagamento'] = 'Pendente';
        }

        $sqlFatura = "INSERT INTO fatura (" . implode(', ', $insertColsFatura) . ") VALUES (" . implode(', ', $insertValsFatura) . ")";
        $stmtFatura = $pdo->prepare($sqlFatura);
        $stmtFatura->execute($paramsFatura);
    }

    header('Location: ../view/cadastrarLeitura.php?sucesso=leitura_cadastrada');
    exit;
} catch (PDOException $e) {
    error_log('Erro no MedidaCerta: ' . $e->getMessage());
    header('Location: ../view/cadastrarLeitura.php?erro=db_error');
    exit;
}
