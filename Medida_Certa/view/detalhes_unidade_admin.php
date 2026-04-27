<?php
// 1. A trava de segurança DEVE ser a primeira coisa
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Captura o ID da unidade
$id_unidade = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Captura os parâmetros de filtro (enviados via JS ou Form)
$mes_ref = filter_input(INPUT_GET, 'mes_ref', FILTER_VALIDATE_INT);
$ano_ref = filter_input(INPUT_GET, 'ano_ref', FILTER_VALIDATE_INT);

if (!$id_unidade) {
    header("Location: unidades.php");
    exit;
}

try {
    $pdo = Conexao::getConexao();

    // 1. Informações da Unidade (Baseado na tabela 'unidade', 'usuario' e 'hidrometro')
    $sqlUnidade = "SELECT u.*, us.nome as morador_nome, h.codigo as h_serial, h.modelo, h.status as h_status,
               ts.status_beneficio as ts_status, ts.tipo_beneficio
               FROM unidade u
               LEFT JOIN usuario us ON u.id_usuario = us.id_usuario
               LEFT JOIN hidrometro h ON u.id_unidade = h.id_unidade
               LEFT JOIN tarifa_social ts ON us.id_usuario = ts.id_usuario
               WHERE u.id_unidade = :id";
    $stmtU = $pdo->prepare($sqlUnidade);
    $stmtU->execute([':id' => $id_unidade]);
    $unidade = $stmtU->fetch(PDO::FETCH_ASSOC);

    // 2. Log de Leituras com Filtro Dinâmico
    // Localize esta linha no seu código e ajuste:
    $sqlLeituras = "SELECT l.id_leitura, l.data_leitura, l.valor_medido, l.consumo_calculado, l.mes_referencia, l.ano_referencia, f.id_fatura, f.status_pagamento 
                FROM leitura l
                INNER JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
                LEFT JOIN fatura f ON l.id_leitura = f.id_leitura
                WHERE h.id_unidade = :id";

    if ($mes_ref && $ano_ref) {
        $sqlLeituras .= " AND l.mes_referencia = :mes AND l.ano_referencia = :ano";
    }

    $sqlLeituras .= " ORDER BY l.data_leitura DESC";

    $stmtL = $pdo->prepare($sqlLeituras);
    $paramsLeitura = [':id' => $id_unidade];

    if ($mes_ref && $ano_ref) {
        $paramsLeitura[':mes'] = $mes_ref;
        $paramsLeitura[':ano'] = $ano_ref;
    }

    $stmtL->execute($paramsLeitura);
    $leituras = $stmtL->fetchAll(PDO::FETCH_ASSOC);

    // --- CÁLCULOS E MÉTRICAS ---

    // Dados iniciais para os cards
    $leituraAtual = $leituras[0] ?? ['valor_medido' => 0, 'data_leitura' => date('Y-m-d')];
    // --- LÓGICA PARA PEGAR INICIAIS (NOME E SOBRENOME) ---
    $iniciais = '??';
    if (!empty($unidade['morador_nome'])) {
        $nomeCompleto = trim($unidade['morador_nome']);
        $partesNome = explode(' ', $nomeCompleto);

        $primeiraLetra = mb_substr($partesNome[0], 0, 1);

        // Se tiver sobrenome, pega a primeira letra do último nome, senão pega a segunda letra do primeiro nome
        if (count($partesNome) > 1) {
            $ultimaLetra = mb_substr(end($partesNome), 0, 1);
        } else {
            $ultimaLetra = mb_substr($partesNome[0], 1, 1);
        }

        $iniciais = strtoupper($primeiraLetra . $ultimaLetra);
    }

    // 3. Busca histórico para média (independente do filtro para não distorcer)
    $sqlMedia = "SELECT valor_medido FROM leitura l 
                 INNER JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro 
                 WHERE h.id_unidade = :id ORDER BY data_leitura DESC LIMIT 7";
    $stmtM = $pdo->prepare($sqlMedia);
    $stmtM->execute([':id' => $id_unidade]);
    $dadosParaMedia = $stmtM->fetchAll(PDO::FETCH_ASSOC);

    // Cálculo da média de consumo dos últimos meses
    $consumosParaMedia = [];
    if (count($dadosParaMedia) > 1) {
        for ($i = 0; $i < count($dadosParaMedia) - 1; $i++) {
            $dif = $dadosParaMedia[$i]['valor_medido'] - $dadosParaMedia[$i + 1]['valor_medido'];
            if ($dif >= 0) $consumosParaMedia[] = $dif;
            if (count($consumosParaMedia) == 6) break;
        }
    }
    $mediaConsumo = count($consumosParaMedia) > 0 ? array_sum($consumosParaMedia) / count($consumosParaMedia) : 0;

    // 4. CÁLCULO DO CONSUMO DO ÚLTIMO MÊS (Essencial para o Status abaixo)
    $consumoUltimoMes = (count($dadosParaMedia) > 1) ? ($dadosParaMedia[0]['valor_medido'] - $dadosParaMedia[1]['valor_medido']) : 0;

    // 5. LÓGICA DE STATUS DINÂMICO (Agora a variável $consumoUltimoMes já existe!)
    $statusTexto = "Normal";
    $statusClasse = "text-success";

    if ($consumoUltimoMes > ($mediaConsumo * 1.3)) { // 30% acima da média
        $statusTexto = "Alto";
        $statusClasse = "text-danger";
    } elseif ($consumoUltimoMes > 0 && $consumoUltimoMes < ($mediaConsumo * 0.5)) {
        $statusTexto = "Baixo";
        $statusClasse = "text-info";
    }
} catch (Exception $e) {
    die("Erro ao processar dados da unidade: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Detalhes Técnicos da Unidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        @media print {

            /* Esconde tudo exceto a tabela e informações essenciais */
            .header,
            .btn,
            .dropdown,
            form,
            .page-header-box .d-flex.gap-2 {
                display: none !important;
            }

            body {
                padding-top: 0;
                background: white;
            }

            .card {
                border: none !important;
                shadow: none !important;
            }

            .container {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

    <?php include '../view/includes/header.php'; ?>

    <main class="container">
        <div class="page-header-box mb-4">
            <div class="bg-white border py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px;">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Unidade: <?= htmlspecialchars($unidade['bloco'] . " - " . $unidade['numero']) ?></h4>

                    <?php
                    // Define a cor e o texto baseado no status real
                    $statusHidrometro = $unidade['h_status'] ?? 'Inativo'; // Se for nulo, assume inativo
                    $badgeCor = ($statusHidrometro === 'Ativo') ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle';
                    ?>

                    <span class="badge <?= $badgeCor ?> border">
                        <i class="bi bi-broadcast me-1"></i>
                        Hidrômetro <?= $statusHidrometro ?>
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="editar_unidade.php?id=<?= $id_unidade ?>" class="btn btn-primary rounded-3 shadow-sm d-flex align-items-center">
                        <i class="bi bi-pencil me-2"></i>Editar
                    </a>
                    <a href="unidades.php" class="btn btn-outline-secondary rounded-3 px-4 d-flex align-items-center">Voltar</a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100 p-4 shadow-sm border-0" style="border-radius: 16px;">
                    <h6 class="fw-bold text-primary mb-4 border-bottom pb-2 text-uppercase" style="letter-spacing: 1px;">Informações de Cadastro</h6>

                    <div class="mb-4">
                        <label class="text-muted small d-block">Proprietário Responsável</label>
                        <div class="d-flex align-items-center mt-1">
                            <?php if (!empty($unidade['foto_perfil'])): ?>
                                <img src="../assets/img/perfis/<?= $unidade['foto_perfil'] ?>"
                                    class="rounded-circle me-2 border"
                                    style="width: 38px; height: 38px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-2 shadow-sm"
                                    style="width: 38px; height: 38px; font-weight: bold; font-size: 0.9rem; border: 2px solid #fff;">
                                    <?= $iniciais ?>
                                </div>
                            <?php endif; ?>

                            <span class="fw-bold text-dark"><?= htmlspecialchars($unidade['morador_nome'] ?? 'Não vinculado') ?></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small d-block">Endereço Completo</label>
                        <p class="mb-0 fw-semibold text-dark"><?= htmlspecialchars($unidade['endereco']) ?></p>
                        <p class="text-muted small">Bloco: <?= htmlspecialchars($unidade['bloco']) ?> | Número: <?= htmlspecialchars($unidade['numero']) ?></p>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small d-block">Dados do Dispositivo</label>
                        <p class="mb-0 fw-semibold text-dark">
                            <i class="bi bi-upc-scan me-2"></i>Serial: <?= htmlspecialchars($unidade['h_serial'] ?? 'Não cadastrado') ?>
                        </p>
                        <span class="badge bg-light text-dark border mt-1 font-monospace">
                            Modelo: <?= htmlspecialchars($unidade['modelo'] ?? 'Padrão') ?>
                        </span>
                    </div>

                    <div class="mb-0">
                        <label class="text-muted small d-block">Benefício Tarifa Social</label>
                        <div class="d-flex align-items-center mt-1">
                            <?php
                            $beneficioAtivo = (isset($unidade['ts_status']) && $unidade['ts_status'] === 'Ativo');
                            $corTexto = $beneficioAtivo ? 'text-primary' : 'text-muted';
                            $icone = $beneficioAtivo ? 'bi-patch-check-fill' : 'bi-x-circle';
                            ?>
                            <i class="bi <?= $icone ?> <?= $corTexto ?> me-2"></i>
                            <p class="mb-0 fw-semibold <?= $corTexto ?>">
                                <?= $beneficioAtivo ? htmlspecialchars($unidade['tipo_beneficio']) : 'Nenhum Benefício Ativo' ?>
                            </p>
                        </div>

                        <?php if ($beneficioAtivo): ?>
                            <div class="alert alert-primary border-0 mt-2 p-2 mb-0" style="background-color: #e7f1ff; border-radius: 8px;">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-info-circle-fill me-2" style="font-size: 0.85rem; margin-top: 2px;"></i>
                                    <small style="font-size: 0.75rem; line-height: 1.2;">
                                        Esta unidade possui desconto ativo vinculado ao CPF/CNPJ do morador.
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card p-3 border-start border-primary border-4 shadow-sm border-0" style="border-radius: 12px;">
                            <label class="text-muted small fw-bold">ÚLTIMA LEITURA</label>
                            <h3 class="fw-bold mb-0"><?= number_format($leituraAtual['valor_medido'], 2, ',', '.') ?> <small class="fs-6 text-muted">m³</small></h3>
                            <p class="text-muted small mb-0 mt-2">Registrado em: <?= date('d/m/Y', strtotime($leituraAtual['data_leitura'])) ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card p-3 border-start border-info border-4 shadow-sm border-0" style="border-radius: 12px;">
                            <label class="text-muted small fw-bold">MÉDIA MENSAL (6m)</label>
                            <h3 class="fw-bold mb-0 text-info"><?= number_format($mediaConsumo, 2, ',', '.') ?> <small class="fs-6 text-muted">m³</small></h3>
                            <p class="text-muted small mb-0 mt-2"><i class="bi bi-graph-up me-1"></i> Baseado nas últimas leituras</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card p-3 border-start border-success border-4 shadow-sm border-0" style="border-radius: 12px;">
                            <label class="text-muted small fw-bold">STATUS DO CONSUMO</label>
                            <h3 class="fw-bold mb-0 <?= $statusClasse ?>"><?= $statusTexto ?></h3>
                            <p class="text-muted small mb-0 mt-2">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                <?= $statusTexto === 'Alto' ? 'Consumo acima da média' : 'Dentro do esperado' ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card p-4 shadow-sm border-0" style="border-radius: 16px;">
                            <h6 class="fw-bold text-dark mb-4"><i class="bi bi-clock-history me-2 text-primary"></i>Histórico Detalhado</h6>

                            <div class="d-flex justify-content-between align-items-center mb-4 p-2 bg-light rounded-3 no-print">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-filter text-primary"></i>
                                        <span class="small fw-bold text-muted">Filtrar:</span>
                                    </div>

                                    <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="d-flex gap-2 align-items-center mb-0">
                                        <input type="hidden" name="id" value="<?= $id_unidade ?>">

                                        <select name="mes_ref" class="form-select form-select-sm border-2" style="width: 130px; border-radius: 8px;">
                                            <option value="">Mês</option>
                                            <?php
                                            $meses = [1 => "Janeiro", 2 => "Fevereiro", 3 => "Março", 4 => "Abril", 5 => "Maio", 6 => "Junho", 7 => "Julho", 8 => "Agosto", 9 => "Setembro", 10 => "Outubro", 11 => "Novembro", 12 => "Dezembro"];
                                            foreach ($meses as $num => $nome): ?>
                                                <option value="<?= $num ?>" <?= (isset($_GET['mes_ref']) && $_GET['mes_ref'] == $num) ? 'selected' : '' ?>><?= $nome ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                        <select name="ano_ref" class="form-select form-select-sm border-2" style="width: 100px; border-radius: 8px;">
                                            <option value="">Ano</option>
                                            <?php
                                            $anoAtual = date('Y');
                                            for ($i = $anoAtual; $i >= $anoAtual - 5; $i--): ?>
                                                <option value="<?= $i ?>" <?= (isset($_GET['ano_ref']) && $_GET['ano_ref'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-dark px-3 fw-bold" style="border-radius: 8px;">OK</button>

                                        <?php if (!empty($_GET['mes_ref']) || !empty($_GET['ano_ref'])): ?>
                                            <a href="<?= $_SERVER['PHP_SELF'] ?>?id=<?= $id_unidade ?>" class="btn btn-sm btn-outline-danger border-2 fw-bold" style="border-radius: 8px; text-decoration: none;">
                                                <i class="bi bi-x-lg"></i> Limpar
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                </div>

                                <div class="d-flex gap-2">
                                    <button onclick="exportarTabela('csv')" class="btn btn-sm btn-dark shadow-sm fw-bold rounded-3 px-3">
                                        <i class="bi bi-file-earmarked-excel me-1 text-success"></i> Excel
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="small text-muted border-0">DATA LEITURA</th>
                                            <th class="small text-muted border-0">LEITURA (m³)</th>
                                            <th class="small text-muted border-0">CONS. MENSAL</th>
                                            <th class="small text-muted border-0">STATUS</th>
                                            <th class="small text-muted border-0 text-center">FATURA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($leituras)): ?>
                                            <?php foreach ($leituras as $l):
                                                // Pegando os valores diretamente das colunas do banco de dados
                                                $valorLeitura = $l['valor_medido'] ?? 0;
                                                $consumoMes = $l['consumo_calculado'] ?? 0;

                                                // Status de Pagamento e estilização do Badge
                                                $statusPagto = $l['status_pagamento'] ?? 'Pendente';
                                                $badgeClass = ($statusPagto === 'Pago') ? 'bg-success-subtle text-success' : (($statusPagto === 'Atrasado') ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning');
                                            ?>
                                                <tr>
                                                    <td class="small fw-semibold">
                                                        <?= date('d/m/Y', strtotime($l['data_leitura'])) ?>
                                                    </td>

                                                    <td class="fw-bold text-primary">
                                                        <?= number_format($valorLeitura, 2, ',', '.') ?>
                                                    </td>

                                                    <td class="small">
                                                        <span class="fw-medium"><?= number_format($consumoMes, 2, ',', '.') ?> m³</span>
                                                        <?php if ($consumoMes > 15): ?>
                                                            <i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="Consumo elevado"></i>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                        <span class="badge <?= $badgeClass ?> rounded-pill px-3">
                                                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>
                                                            <?= $statusPagto ?>
                                                        </span>
                                                    </td>

                                                    <td class="text-center">
                                                        <?php if (!empty($l['id_fatura'])): ?>
                                                            <a href="faturaPDF.php?id=<?= $l['id_fatura'] ?>" target="_blank" class="btn btn-sm btn-light border shadow-sm rounded-3" title="Visualizar Fatura">
                                                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                                                <span class="small fw-bold">PDF</span>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted small">--</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    <i class="bi bi-info-circle me-2"></i>Nenhum registro encontrado.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function exportarTabela(tipo) {
            const tabela = document.querySelector("table");
            let csv = [];
            const linhas = tabela.querySelectorAll("tr");

            for (let i = 0; i < linhas.length; i++) {
                const linha = [];
                const colunas = linhas[i].querySelectorAll("td, th");

                for (let j = 0; j < colunas.length; j++) {
                    // Remove espaços extras e limpa o texto (ignora a coluna de botões PDF)
                    let texto = colunas[j].innerText.replace(/(\r\n|\n|\r)/gm, "").trim();
                    // Evita problemas com a vírgula do decimal no CSV
                    texto = texto.replace('"', '""');
                    linha.push('"' + texto + '"');
                }

                // Remove a última coluna (Ações/Fatura) se você não quiser exportar o botão "PDF"
                linha.pop();
                csv.push(linha.join(";")); // Usamos ";" para o Excel brasileiro entender as colunas
            }

            const csvContent = "\uFEFF" + csv.join("\n"); // Adiciona BOM para caracteres especiais (acentos)
            const blob = new Blob([csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement("a");

            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "historico_consumo_unidade.csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        function executarFiltroBasico() {
            const mes = document.getElementById('filtro_mes').value;
            const ano = document.getElementById('filtro_ano').value;
            const id = new URLSearchParams(window.location.search).get('id');

            if (!mes || !ano) {
                alert("Selecione o mês e o ano.");
                return;
            }

            window.location.href = `detalhes_unidade.php?id=${id}&mes_ref=${mes}&ano_ref=${ano}`;
        }
    </script>
</body>

</html>