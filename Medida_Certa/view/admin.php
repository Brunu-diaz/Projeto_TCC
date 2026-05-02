<?php
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

$consumoAtualGeral = 0;
$totalUnidadesAtivas = 0;
$totalAlertasHoje = 0;
$labelsGrafico = [];
$dadosGrafico = [];
$dadosMediaCondominio = [];

try {
    $pdo = Conexao::getConexao();

    // 1. Consumo Total do Mês
    $sqlConsumo = "SELECT SUM(consumo_calculado) as total FROM leitura 
               WHERE mes_referencia = MONTH(CURRENT_DATE()) 
               AND ano_referencia = YEAR(CURRENT_DATE())";
$resConsumo = $pdo->query($sqlConsumo)->fetch(PDO::FETCH_ASSOC);
$consumoAtualGeral = (float)($resConsumo['total'] ?? 0);

    // 2. Média Diária
    $diaAtual = (int)date('d');
$mediaDiaria = ($consumoAtualGeral > 0) ? ($consumoAtualGeral / $diaAtual) : 0;

    // 3. Total de Unidades
    $totalUnidadesAtivas = $pdo->query("SELECT COUNT(*) FROM unidade")->fetchColumn() ?: 0;

    // 4. Alertas de Hoje
    $totalAlertasHoje = $pdo->query("SELECT COUNT(*) FROM anomalia WHERE DATE(data_registro) = CURRENT_DATE()")->fetchColumn() ?: 0;

    // 5. Dados para o Gráfico
    $sqlGrafico = "SELECT DATE_FORMAT(MIN(data_leitura), '%d/%m') as dia, SUM(consumo_calculado) as total_dia 
               FROM leitura 
               WHERE DATE(data_leitura) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               GROUP BY DATE(data_leitura) 
               ORDER BY DATE(data_leitura) ASC";
$resGrafico = $pdo->query($sqlGrafico)->fetchAll(PDO::FETCH_ASSOC);

    $dadosTemp = [];
foreach ($resGrafico as $row) {
    $dadosTemp[$row['dia']] = (float)$row['total_dia'];
}
$labelsGrafico = [];
$dadosGrafico = [];
$dadosMediaCondominio = [];

    for ($i = 6; $i >= 0; $i--) {
    $rotulo = date('d/m', strtotime("-$i days"));
    $labelsGrafico[] = $rotulo;
    $dadosGrafico[] = $dadosTemp[$rotulo] ?? 0; // Se não houver dado, coloca 0
    $dadosMediaCondominio[] = round($mediaDiaria, 2);
}

} catch (Exception $e) {
    error_log("Erro no Admin: " . $e->getMessage());
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
                        <div class="mt-2 text-success small"><i class="bi bi-water"></i> Consumo real apurado</div>
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
                        <small class="text-danger fw-bold text-uppercase">Alertas Hoje</small>
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
                        <a href="cadastrarLeitura.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Nova Leitura</a>
                        <a href="cadastrarUsuario.php" class="btn btn-outline-primary"><i class="bi bi-person-plus-fill me-1"></i> Cadastrar Usuário</a>
                        <a href="cadastrarUnidades.php" class="btn btn-outline-primary"><i class="bi bi-building-add me-1"></i> Cadastrar Unidade</a>
                        <a href="relatorios.php" class="btn btn-outline-primary"><i class="bi bi-bar-chart-line me-1"></i>Relatórios</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Configuração do Gráfico
            const ctx = document.getElementById('graficoConsumo').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labelsGrafico) ?>,
        datasets: [
            {
                label: 'Consumo Real (m³)',
                data: <?= json_encode($dadosGrafico) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Média Condomínio (m³)',
                data: <?= json_encode($dadosMediaCondominio) ?>,
                borderColor: '#ffc107',
                borderDash: [5, 5],
                fill: false,
                pointRadius: 0
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => v + ' m³' } }
        }
    }
}); // CORREÇÃO 2: Removido o fechamento extra que matava o script aqui

            // 2. Animação dos contadores
            document.querySelectorAll('.counter').forEach(el => {
                const rawText = el.innerText.replace(/\./g, '').replace(',', '.');
                const target = parseFloat(rawText);
                if (isNaN(target)) return;

                const duration = 1500;
                const startTime = performance.now();

                const updateCount = (currentTime) => {
                    const elapsedTime = currentTime - startTime;
                    const progress = Math.min(elapsedTime / duration, 1);
                    const currentCount = progress * target;

                    el.innerText = currentCount.toLocaleString('pt-BR', {
                        minimumFractionDigits: target % 1 !== 0 ? 2 : 0,
                        maximumFractionDigits: target % 1 !== 0 ? 2 : 0
                    });

                    if (progress < 1) {
                        requestAnimationFrame(updateCount);
                    } else {
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