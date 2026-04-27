<?php

/**
 * PROJETO: MedidaCerta
 * MELHORIA: Lógica de preenchimento de dias vazios no gráfico
 */

// 1. A trava de segurança DEVE ser a primeira coisa
require_once __DIR__ . '/../controller/TravaAdmin.php';

// 2. Conexão com o Banco de Dados
require_once __DIR__ . '/../model/dao/Conexao.php';

// Inicializa variáveis
$consumoAtualGeral = 0;
$totalUnidadesAtivas = 0;
$totalAlertasHoje = 0;
$dadosGrafico = [];
$labelsGrafico = [];
$dadosGraficoIncidentes = [];
$labelsGraficoIncidentes = [];

try {
    $pdo = Conexao::getConexao();

    if (!$pdo) {
        throw new Exception("Falha ao estabelecer conexão com o banco.");
    }

    // A. Busca Consumo Real do Mês utilizando a coluna consumo_calculado
    $sqlConsumoTotal = "SELECT SUM(consumo_calculado) as total FROM leitura 
                        WHERE mes_referencia = MONTH(CURRENT_DATE()) 
                        AND ano_referencia = YEAR(CURRENT_DATE())";
    $resConsumo = $pdo->query($sqlConsumoTotal)->fetch(PDO::FETCH_ASSOC);
    $consumoAtualGeral = $resConsumo['total'] ?? 0;

    // B. Busca Total de Unidades (Gerenciamento)
    $sqlUnidades = "SELECT COUNT(*) as total FROM unidade";
    $totalUnidadesAtivas = $pdo->query($sqlUnidades)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // C. Busca Alertas/Anomalias do dia pelo momento em que o alerta foi registrado
    $sqlAlertas = "SELECT COUNT(*) as total FROM anomalia WHERE DATE(data_registro) = CURRENT_DATE()";
    $totalAlertasHoje = $pdo->query($sqlAlertas)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // C2. Busca tendência de incidentes nos últimos 7 dias
    $sqlIncidentes = "SELECT DATE_FORMAT(data_registro, '%d/%m') as dia, COUNT(*) as total_dia 
                      FROM anomalia 
                      WHERE DATE(data_registro) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
                        AND DATE(data_registro) <= CURDATE() 
                      GROUP BY DATE(data_registro) 
                      ORDER BY DATE(data_registro) ASC";
    $resIncidentes = $pdo->query($sqlIncidentes)->fetchAll(PDO::FETCH_ASSOC);

    $dadosTemporariosIncidentes = [];
    foreach ($resIncidentes as $row) {
        $dadosTemporariosIncidentes[$row['dia']] = (int)$row['total_dia'];
    }

    // D. BUSCA DADOS PARA O GRÁFICO (Melhorado: Preenchimento de lacunas)
    $sqlGrafico = "SELECT DATE_FORMAT(data_leitura, '%d/%m') as dia, SUM(consumo_calculado) as total_dia 
                   FROM leitura 
                   WHERE DATE(data_leitura) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                     AND DATE(data_leitura) <= CURDATE()
                   GROUP BY DATE(data_leitura) 
                   ORDER BY DATE(data_leitura) ASC";
    $resGrafico = $pdo->query($sqlGrafico)->fetchAll(PDO::FETCH_ASSOC);

    // Mapeia os resultados vindos do banco em um array temporário [ 'dia/mes' => valor ]
    $dadosTemporarios = [];
    foreach ($resGrafico as $row) {
        $dadosTemporarios[$row['dia']] = (float)$row['total_dia'];
    }

    // Gera os últimos 7 dias dinamicamente para garantir que o gráfico não tenha "buracos"
    for ($i = 6; $i >= 0; $i--) {
        $dataRotulo = date('d/m', strtotime("-$i days"));
        $labelsGrafico[] = $dataRotulo;

        // Se a data existe no banco, usa o valor. Se não, define como 0.
        $dadosGrafico[] = $dadosTemporarios[$dataRotulo] ?? 0;
        $dadosGraficoIncidentes[] = $dadosTemporariosIncidentes[$dataRotulo] ?? 0;
        $labelsGraficoIncidentes[] = $dataRotulo;
    }
} catch (Exception $e) {
    error_log("Erro no Admin MedidaCerta: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container main-content flex-grow-1">

        <div class="row g-3 mb-4 justify-content-center">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100 border-0 border-start border-4 border-success">
                    <div class="card-body">
                        <small class="text-success fw-bold text-uppercase">Consumo Total do Mês</small>
                        <h3 class="fw-bold mt-1 mb-0">
                            <span class="counter"><?= number_format($consumoAtualGeral, 2, ',', '.') ?></span> m³
                        </h3>
                        <div class="mt-2 text-success small">
                            <i class="bi bi-water"></i> Consumo real apurado
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100 border-0 border-start border-4 border-warning">
                    <div class="card-body">
                        <small class="text-warning fw-bold text-uppercase">Unidades Ativas</small>
                        <h3 class="fw-bold mt-1 mb-0">
                            <span class="counter"><?= $totalUnidadesAtivas ?></span>
                        </h3>
                        <div class="mt-2 small text-muted">Cadastradas no sistema</div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100 border-0 border-start border-4 border-danger">
                    <div class="card-body">
                        <small class="text-danger fw-bold text-uppercase">Alertas Registrados Hoje</small>
                        <h3 class="fw-bold mt-1 mb-0">
                            <span class="counter"><?= $totalAlertasHoje ?></span>
                        </h3>
                        <div class="mt-2 small text-muted">Incidentes detectados</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">Tendência de Consumo (m³)</h6>
                        <span class="badge bg-light text-dark border">Dados em tempo real</span>
                    </div>
                    <div style="height: 225px;">
                        <canvas id="graficoConsumo"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card actions-card mb-4 shadow-sm p-4">
                    <h6 class="fw-bold mb-3">Controle Operacional</h6>
                    <div class="d-grid gap-2">
                        <a href="cadastrarLeitura.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Nova Leitura
                        </a>
                        <a href="cadastrarUsuario.php" class="btn btn-outline-primary shadow-sm">
                            <i class="bi bi-person-plus-fill me-1"></i> Cadastrar Usuário
                        </a>
                        <a href="cadastrarUnidades.php" class="btn btn-outline-primary">
                            <i class="bi bi-building-add me-1"></i> Cadastrar Unidade
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Configuração do Gráfico (Chart.js)
            const canvas = document.getElementById('graficoConsumo');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($labelsGrafico) ?>,
                        datasets: [{
                            label: 'Consumo Real (m³)',
                            data: <?= json_encode($dadosGrafico) ?>,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#0d6efd',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            } // Oculta legenda para um visual cleaner
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f0f0f0'
                                },
                                ticks: {
                                    callback: (value) => value + ' m³'
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
            }

            const canvasIncidentes = document.getElementById('graficoIncidentes');
            if (canvasIncidentes) {
                const ctxIncidentes = canvasIncidentes.getContext('2d');
                new Chart(ctxIncidentes, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($labelsGraficoIncidentes) ?>,
                        datasets: [{
                            label: 'Incidentes',
                            data: <?= json_encode($dadosGraficoIncidentes) ?>,
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.12)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#dc3545',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    stepSize: 1
                                },
                                grid: {
                                    color: '#f0f0f0'
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
            }

            // 2. Animação dos contadores (Melhoria do Passo 4)
            document.querySelectorAll('.counter').forEach(el => {
                // Limpa o texto original para pegar o número puro
                const rawText = el.innerText.replace(/\./g, '').replace(',', '.');
                const target = parseFloat(rawText);

                if (isNaN(target)) return;

                const duration = 1500; // Duração da animação em ms
                const startTime = performance.now();

                const updateCount = (currentTime) => {
                    const elapsedTime = currentTime - startTime;
                    const progress = Math.min(elapsedTime / duration, 1);

                    // Cálculo do valor atual baseado no progresso (easing)
                    const currentCount = progress * target;

                    // Formata o número conforme o padrão brasileiro (Passo 4)
                    el.innerText = currentCount.toLocaleString('pt-BR', {
                        minimumFractionDigits: target % 1 !== 0 ? 2 : 0,
                        maximumFractionDigits: target % 1 !== 0 ? 2 : 0
                    });

                    if (progress < 1) {
                        requestAnimationFrame(updateCount);
                    } else {
                        // Garante que o valor final seja exatamente o alvo
                        el.innerText = target.toLocaleString('pt-BR', {
                            minimumFractionDigits: target % 1 !== 0 ? 2 : 0,
                            maximumFractionDigits: target % 1 !== 0 ? 2 : 0
                        });
                    }
                };

                requestAnimationFrame(updateCount);
            });
        });
    </script>
</body>

</html>