<?php
// 1. A trava de segurança DEVE ser a primeira coisa
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Filtros de busca (Mês e Ano selecionados)
$mesSelecionado = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 12]
]) ?? intval(date('m'));
$anoSelecionado = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 2000, 'max_range' => intval(date('Y')) + 1]
]) ?? intval(date('Y'));
$mesSelecionado = intval($mesSelecionado);
$anoSelecionado = intval($anoSelecionado);

$dadosRelatorio = [];
$resGrafico = [];
$mediaHistorica = 0;
$totalFaturamentoMes = 0;
$anosDisponiveis = [];
$mesesGrafico = [
    1 => 'Jan',
    2 => 'Fev',
    3 => 'Mar',
    4 => 'Abr',
    5 => 'Mai',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Ago',
    9 => 'Set',
    10 => 'Out',
    11 => 'Nov',
    12 => 'Dez'
];
$unidadeEconomica = ['numero' => '--', 'consumo' => 0];

try {
    $pdo = Conexao::getConexao();
    if (!$pdo) {
        throw new Exception('Falha ao conectar com o banco de dados.');
    }

    // 0. BUSCAR ANOS PARA O FILTRO (Dinâmico)
    $sqlAnos = "SELECT DISTINCT ano_referencia FROM leitura ORDER BY ano_referencia DESC";
    $anosDisponiveis = $pdo->query($sqlAnos)->fetchAll(PDO::FETCH_COLUMN);
    if (empty($anosDisponiveis)) {
        $anosDisponiveis = [intval(date('Y'))];
    }
    if (!in_array($anoSelecionado, $anosDisponiveis, true)) {
        $anoSelecionado = intval($anosDisponiveis[0]);
    }

    // 1. MÉDIA HISTÓRICA (Auditoria Geral)
    $sqlMedia = "SELECT AVG(total_mes) as media_anual FROM (
                    SELECT SUM(COALESCE(consumo_calculado, 0)) as total_mes
                    FROM leitura
                    GROUP BY mes_referencia, ano_referencia
                 ) as subconsulta";
    $mediaHistorica = (float) ($pdo->query($sqlMedia)->fetch(PDO::FETCH_ASSOC)['media_anual'] ?? 0);

    // 2. RELATÓRIO DE UNIDADES (Consumo Real por unidade)
    $sqlRelatorio = "SELECT 
                        u.id_unidade,
                        u.numero,
                        u.bloco,
                        us.nome as proprietario,
                        COALESCE(l_atual.consumo_calculado, 0) as consumo,
                        l_atual.data_leitura as ultima_leitura
                     FROM unidade u
                     INNER JOIN usuario us ON u.id_usuario = us.id_usuario
                     INNER JOIN hidrometro h ON u.id_unidade = h.id_unidade
                     LEFT JOIN (
                         SELECT id_hidrometro, consumo_calculado, data_leitura
                         FROM leitura
                         WHERE mes_referencia = :mes AND ano_referencia = :ano
                     ) l_atual ON h.id_hidrometro = l_atual.id_hidrometro
                     ORDER BY consumo DESC, u.bloco, u.numero";
    $stmtRel = $pdo->prepare($sqlRelatorio);
    $stmtRel->execute([':mes' => $mesSelecionado, ':ano' => $anoSelecionado]);
    $dadosRelatorio = $stmtRel->fetchAll(PDO::FETCH_ASSOC);

    // 3. DADOS DO GRÁFICO (Evolução do Ano Selecionado)
    $sqlGrafico = "SELECT mes_referencia, SUM(COALESCE(consumo_calculado, 0)) as total
                   FROM leitura
                   WHERE ano_referencia = :ano
                   GROUP BY mes_referencia
                   ORDER BY mes_referencia ASC";
    $stmtGraf = $pdo->prepare($sqlGrafico);
    $stmtGraf->execute([':ano' => $anoSelecionado]);
    $graficoRows = $stmtGraf->fetchAll(PDO::FETCH_ASSOC);

    $resGrafico = array_fill(1, 12, 0.0);
    foreach ($graficoRows as $g) {
        $resGrafico[intval($g['mes_referencia'])] = (float) $g['total'];
    }

    // Descobre a unidade mais econômica com consumo positivo
    foreach ($dadosRelatorio as $item) {
        $consumoItem = (float) $item['consumo'];
        if ($consumoItem > 0 && ($unidadeEconomica['consumo'] === 0 || $consumoItem < $unidadeEconomica['consumo'])) {
            $unidadeEconomica = ['numero' => $item['numero'], 'consumo' => $consumoItem];
        }
    }

    $totalFaturamentoMes = 0;
    foreach ($dadosRelatorio as $d) {
        $totalFaturamentoMes += calcularTarifa((float) $d['consumo']);
    }
} catch (Exception $e) {
    error_log("Erro MedidaCerta: " . $e->getMessage());
}

function calcularTarifa($consumo)
{
    $consumo = (float) $consumo;
    if ($consumo <= 0) {
        return 0.00;
    }
    if ($consumo <= 7) {
        return 40.00;
    }
    if ($consumo <= 13) {
        return 40.00 + ($consumo - 7) * 6.50;
    }
    return 40.00 + (6 * 6.50) + ($consumo - 13) * 12.00;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Auditoria e Relatórios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --glass-border: 1px solid #f1f5f9;
            --card-radius: 16px;
            --mc-primary: #0d6efd;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        /* Fundo escuro para permitir a sobreposição */
        .header-bg-dark {
            background-color: #1e293b;
            /* Mesma cor do seu menu superior */
            height: 100px;
            width: 100%;
            position: absolute;
            top: 0;
            z-index: -1;
        }

        /* Caixa do cabeçalho flutuante */
        .page-header-box {
            background-color: #ffffff;
            border-radius: var(--card-radius);
            border: var(--glass-border);
            margin-top: -69px;
            /* Faz a caixa subir sobre o fundo escuro */
            position: relative;
            z-index: 10;

        }

        .stat-card {
            border-radius: var(--card-radius);
            border: var(--glass-border);
            transition: transform 0.2s;
        }

        .table-responsive {
            border-radius: 12px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <?php include '../view/includes/header.php'; ?>

    <div class="header-bg-dark no-print"></div>

    <main class="container py-4">

        <div class="container page-header-box mb-4 no-print shadow-sm">
            <div class="bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Painel de Auditoria</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                            <li class="breadcrumb-item active">Relatórios</li>
                        </ol>
                    </nav>
                </div>

                <div class="d-flex gap-2">
                    <button onclick="exportarCSV()" class="btn btn-light border rounded-3 px-3 fw-bold d-flex align-items-center" style="height: 42px; font-size: 0.9rem;">
                        <i class="bi bi-download me-2"></i>Exportar CSV
                    </button>
                    <button onclick="window.print()" class="btn btn-light border rounded-3 px-3 fw-bold d-flex align-items-center" style="height: 42px; font-size: 0.9rem;">
                        <i class="bi bi-printer me-2"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-4 no-print">
            <div class="col-12">
                <form method="GET" class="bg-white p-3 shadow-sm d-flex gap-3 align-items-end" style="border-radius: 16px; border: 1px solid #f1f5f9;">
                    <div style="min-width: 150px;">
                        <label class="small fw-bold text-muted mb-1">Mês de Referência</label>
                        <select name="mes" class="form-select border-0 bg-light">
                            <?php
                            $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                            foreach ($meses as $num => $nome): ?>
                                <option value="<?= $num ?>" <?= $mesSelecionado == $num ? 'selected' : '' ?>><?= $nome ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="small fw-bold text-muted mb-1">Ano</label>
                        <select name="ano" class="form-select border-0 bg-light">
                            <?php foreach ($anosDisponiveis as $ano): ?>
                                <option value="<?= $ano ?>" <?= $anoSelecionado == $ano ? 'selected' : '' ?>><?= $ano ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-dark rounded-3 px-4 fw-bold" style="height: 38px;">Atualizar</button>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card shadow-sm p-4 h-100 bg-white border-primary">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Média Geral</small>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($mediaHistorica, 1, ',', '.') ?> m³</h3>
                    <div class="text-primary mt-2 small"><i class="bi bi-info-circle me-1"></i>Histórico total</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm p-4 h-100 bg-white border-info">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Total do Mês</small>
                    <?php $consumoTotalMes = array_sum(array_column($dadosRelatorio, 'consumo')); ?>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($consumoTotalMes, 1, ',', '.') ?> m³</h3>
                    <div class="text-info mt-2 small"><i class="bi bi-reception-4 me-1"></i>Soma das leituras</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm p-4 h-100 bg-white border-success">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Faturamento Est.</small>
                    <h3 class="fw-bold mb-0 mt-1">R$ <?= number_format($totalFaturamentoMes, 2, ',', '.') ?></h3>
                    <div class="text-success mt-2 small">Referente ao mês <?= $mesSelecionado ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm p-4 h-100 bg-white border-warning">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Unidade Econômica</small>
                    <h3 class="fw-bold  mb-0 mt-1"><?= number_format($unidadeEconomica['consumo'], 1, ',', '.') ?> m³</h3>
                    <div class="mt-2 small fw-bold text-warning">Apt <?= htmlspecialchars($unidadeEconomica['numero']) ?></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px;">
                    <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-graph-up-arrow me-2"></i>Evolução de Consumo em <?= $anoSelecionado ?></h6>
                    <div style="height: 350px;">
                        <canvas id="graficoComparativo"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; overflow: hidden;">
                    <div class="bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-primary">Detalhamento por Unidade</h6>
                        <span class="badge bg-light text-dark"><?= count($dadosRelatorio) ?> Aptos</span>
                    </div>
                    <div class="table-responsive" style="max-height: 450px;">
                        <table class="table table-hover align-middle mb-0" id="tabelaDados">
                            <thead class="bg-light sticky-top">
                                <tr style="font-size: 0.7rem;" class="text-muted text-uppercase">
                                    <th class="ps-4">Unidade</th>
                                    <th class="text-center">Consumo</th>
                                    <th class="text-end pe-4">Valor Estimado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($dadosRelatorio): foreach ($dadosRelatorio as $row):
                                        $alertaVazamento = ($row['consumo'] > $mediaHistorica * 1.6 && $row['consumo'] > 0);
                                ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.7rem;">
                                                        <?= $row['numero'] ?>
                                                    </div>
                                                    <div>
                                                        <span class="fw-bold text-dark d-block">Bloco <?= $row['bloco'] ?></span>
                                                        <small class="text-muted"><?= explode(' ', $row['proprietario'])[0] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold <?= $alertaVazamento ? 'text-danger' : 'text-dark' ?>">
                                                    <?= number_format($row['consumo'], 1) ?> m³
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <span class="fw-bold text-success">
                                                    R$ <?= number_format(calcularTarifa($row['consumo']), 2, ',', '.') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">Sem dados para este período.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function exportarCSV() {
            let csv = [];
            const rows = document.querySelectorAll("#tabelaDados tr");
            for (let i = 0; i < rows.length; i++) {
                const row = [],
                    cols = rows[i].querySelectorAll("td, th");
                for (let j = 0; j < cols.length; j++) row.push(cols[j].innerText.trim());
                csv.push(row.join(";"));
            }
            const blob = new Blob(["\ufeff" + csv.join("\n")], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.setAttribute("download", "relatorio_medidacerta.csv");
            link.click();
        }

        const ctx = document.getElementById('graficoComparativo').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php foreach ($resGrafico as $mes => $valor) echo "'" . $mesesGrafico[$mes] . "',"; ?>],
                datasets: [{
                    label: 'Consumo Total (m³)',
                    data: [<?php foreach ($resGrafico as $valor) echo $valor . ","; ?>],
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderRadius: 8,
                    barThickness: 25
                }, {
                    label: 'Média do Condomínio',
                    type: 'line',
                    data: [<?php foreach ($resGrafico as $g) echo $mediaHistorica . ","; ?>],
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>