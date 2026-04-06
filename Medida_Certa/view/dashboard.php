<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MedidaCerta</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ícones do Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

    <!-- Header Padrão MedidaCerta -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="../index.php">
                    <i class="bi bi-droplet-fill me-2"></i> MedidaCerta
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="login.php">Sair <i class="bi bi-box-arrow-right"></i></a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container py-5">
        <!-- Título e Boas-vindas -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h4 class="fw-bold text-dark">Bem-Vindo, Bruno Dias</h4>
                <p class="text-muted">Unidade: Bloco A - Apto 402.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-white text-primary shadow-sm p-2 px-3 rounded-pill border">
                    <i class="bi bi-calendar3 me-2"></i> Período: Março 2026
                </span>
            </div>
        </div>

        <!-- Cards de Resumo Rápido -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card card-custom p-3 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape bg-primary text-white me-3 shadow-sm">
                            <i class="bi bi-droplet"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small text-uppercase fw-bold">Consumo Atual</p>
                            <h3 class="fw-bold mb-0">8.4 <small class="fs-6 text-muted">m³</small></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-custom p-3 h-100 border-start border-success border-4">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape bg-success text-white me-3 shadow-sm">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small text-uppercase fw-bold">Previsão Fatura</p>
                            <h3 class="fw-bold mb-0 text-success">R$ 94,20</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-custom p-3 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape bg-info text-white me-3 shadow-sm">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small text-uppercase fw-bold">Status do Sistema</p>
                            <h5 class="fw-bold mb-0 text-info">Rede Estável</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico e Histórico -->
        <div class="row g-4">
            <!-- Gráfico de Consumo -->
            <div class="col-lg-7">
                <div class="card card-custom p-4 shadow-sm h-100">
                    <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-graph-up me-2 text-primary"></i>Histórico de Consumo (m³)</h5>
                    <div id="chartWrapper">
                        <canvas id="graficoConsumo"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabela de Histórico -->
            <div class="col-lg-5">
                <div class="table-container shadow-sm h-100">
                    <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Últimas Leituras</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr class="small text-uppercase">
                                    <th>Mês</th>
                                    <th>Consumo</th>
                                    <th>Fatura</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold">Jan/26</td>
                                    <td>8.4 m³</td>
                                    <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalFatura"><i class="bi bi-file-earmark-pdf"></i></button></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Dez/25</td>
                                    <td>9.0 m³</td>
                                    <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalFatura"><i class="bi bi-file-earmark-pdf"></i></button></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Nov/25</td>
                                    <td>9.2 m³</td>
                                    <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalFatura"><i class="bi bi-file-earmark-pdf"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Fatura (Simulação) -->
    <div class="modal fade" id="modalFatura" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 20px;">
                <div class="modal-header bg-primary text-white border-0" style="border-radius: 20px 20px 0 0;">
                    <h5 class="modal-title fw-bold">Fatura Detalhada</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-file-earmark-check display-4 text-success"></i>
                        <p class="text-muted mt-2">Leitura processada com sucesso via Telemetria.</p>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><span>Consumo Registrado:</span> <strong>8.4 m³</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Valor da Tarifa:</span> <strong>R$ 11,21 / m³</strong></li>
                        <li class="list-group-item d-flex justify-content-between bg-light"><span>Total a Pagar:</span> <strong class="text-primary fs-5">R$ 94,20</strong></li>
                    </ul>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" onclick="window.print()">Baixar PDF</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 text-center text-white">
    <div class="container">
        <p class="mb-0">&copy; 2026 MedidaCerta - Sistema de Gestão de Água Condominial. Todos os direitos reservados.</p>
        <p class="mb-0">CNPJ: 00.000.000/0001-00</p>
        <p class="mb-0">Brasília-DF</p>
    </div>
</footer>

    <!-- Scripts: Bootstrap + Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Configuração do Gráfico
        const ctx = document.getElementById('graficoConsumo').getContext('2d');
        
        // Criando o degradê idêntico ao header para as barras
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#0d6efd');
        gradient.addColorStop(1, '#0056b3');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ago/25', 'Set/25', 'Out/25', 'Nov/25', 'Dez/25', 'Jan/26'],
                datasets: [{
                    label: 'Consumo (m³)',
                    data: [10.5, 9.2, 11.0, 9.2, 9.0, 8.4],
                    backgroundColor: gradient,
                    borderRadius: 10,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: '#f0f0f0', drawBorder: false }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>