<?php
function calcularPrecoCaesb($consumo)
{
    $valorTotal = 0;
    if ($consumo <= 7) {
        $valorTotal = 40.00;
    } elseif ($consumo <= 13) {
        $valorTotal = 40.00 + ($consumo - 7) * 6.50;
    } else {
        $valorTotal = 40.00 + (6 * 6.50) + ($consumo - 13) * 12.00;
    }
    return number_format($valorTotal, 2, ',', '.');
}

// ... restante da lógica de meta ...
?>

<?php
// Configurações de Meta para o TCC
$metaConsumoGeral = 250;
$consumoAtualGeral = 350; // Simulação de economia

// Valor da fatura com o consumo atual
$faturaAtualTexto = calcularPrecoCaesb($consumoAtualGeral);
$valorFaturaTotal = floatval(str_replace(',', '.', str_replace('.', '', $faturaAtualTexto)));

// Valor da fatura se estivesse na meta exata
$faturaMetaTexto = calcularPrecoCaesb($metaConsumoGeral);
$valorFaturaMeta = floatval(str_replace(',', '.', str_replace('.', '', $faturaMetaTexto)));

// Diferença financeira (Positivo = Desperdício / Negativo = Economia)
$diferencaFinanceira = $valorFaturaTotal - $valorFaturaMeta;
$diferencaFormatada = number_format(abs($diferencaFinanceira), 2, ',', '.');
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

    <header class="header-gradient pb-5">
        <nav class="navbar navbar-expand-lg navbar-dark pt-3">
            <div class="container">
                <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                    <i class="bi bi-droplet-fill me-2 text-primary"></i>
                    <span class="fs-5">MedidaCerta</span>
                    <span class="badge bg-primary rounded-pill text-white ms-2 text-uppercase fw-bold"
                        style="font-size: 0.55rem; padding: 0.35em 0.7em; letter-spacing: 0.5px;">
                        ADMIN
                    </span>
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipal">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navPrincipal">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                        <li class="nav-item me-2">
                            <a class="nav-link active d-flex align-items-center" href="index.php">
                                <i class="bi bi-speedometer2 me-2"></i> Visão Geral
                            </a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link d-flex align-items-center" href="unidades.php">
                                <i class="bi bi-building me-2"></i> Unidades
                            </a>
                        </li>

                        <li class="nav-item dropdown list-unstyled">
                            <a class="nav-link position-relative d-inline-flex align-items-center p-2" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5 text-white"></i>

                                <span class="custom-notification-badge">
                                    2
                                </span>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-0 mt-2" style="width: 320px; overflow: hidden;">
                                <li class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                                    <span class="fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Notificações</span>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size: 0.6rem;">2 NOVAS</span>
                                </li>

                                <div style="max-height: 300px; overflow-y: auto;">
                                    <li>
                                        <a class="dropdown-item p-3 border-bottom d-flex align-items-start gap-3" href="#">
                                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; flex-shrink: 0;">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 small fw-bold text-danger">Vazamento Detectado!</p>
                                                <p class="mb-1 text-muted small" style="font-size: 0.8rem;">Unidade 402 - Bloco B apresenta consumo atípico.</p>
                                                <small class="text-uppercase fw-bold opacity-50" style="font-size: 0.6rem;">há 5 min</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item p-3 border-bottom d-flex align-items-start gap-3" href="#">
                                            <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; flex-shrink: 0;">
                                                <i class="bi bi-droplet-half"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 small fw-bold">Meta de Consumo</p>
                                                <p class="mb-1 text-muted small" style="font-size: 0.8rem;">O condomínio atingiu 80% da meta mensal.</p>
                                                <small class="text-uppercase fw-bold opacity-50" style="font-size: 0.6rem;">há 2 horas</small>
                                            </div>
                                        </a>
                                    </li>
                                </div>

                                <li>
                                    <a class="dropdown-item small text-center p-2 fw-bold text-primary bg-light" href="#">
                                        Ver todas as notificações
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown ms-lg-3">
                            <a class="d-flex align-items-center text-decoration-none text-white dropdown-toggle px-2" href="#" data-bs-toggle="dropdown">
                                <div class="profile-circle me-2">BD</div>
                                <span class="small d-none d-md-inline">Bruno Dias</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                <li><a class="dropdown-item" href="perfil.php">Meu Perfil</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="login.php">Sair</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container main-content flex-grow-1">

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100 border-0 border-start border-4 border-primary">
                    <div class="card-body">
                        <small class="fw-bold text-primary text-uppercase">Fatura Estimada</small>
                        <h3 class="fw-bold mt-1 mb-0">
                            R$ <span class="counter-money" data-target="<?= $valorFaturaTotal ?>">0,00</span>
                        </h3>

                        <div class="mt-2">
                            <?php if ($diferencaFinanceira > 0): ?>
                                <span class="small text-danger">
                                    Aumento de: R$ <?= $diferencaFormatada ?>
                                </span>
                            <?php elseif ($diferencaFinanceira < 0): ?>
                                <span class="small text-success">
                                    Economia de: R$ <?= $diferencaFormatada ?>
                                </span>
                            <?php else: ?>
                                <span class="small text-black">
                                    <i class="bi bi-check-circle me-1"></i> Na Meta
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100 border-0 border-start border-4 border-success">
                    <div class="card-body">
                        <small class="text-success fw-bold text-uppercase">Consumo Total</small>
                        <h3 class="fw-bold mt-1 mb-0"><span class="counter">1250</span> m³</h3>
                        <div class="mt-2">
                            <span class="small text-success"><i class="bi bi-arrow-down"></i> 4% vs mês anterior</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100 border-0 border-start border-4 border-warning">
                    <div class="card-body">
                        <small class="text-warning fw-bold text-uppercase">Unidades Ativas</small>
                        <h3 class="fw-bold mt-1 mb-0"><span class="counter">142</span></h3>
                        <div class="mt-2">
                            <span class="small text-black">Monitoradas hoje</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100 border-0 border-start border-4 border-danger">
                    <div class="card-body">
                        <small class="text-danger fw-bold text-uppercase">Alertas Hoje</small>
                        <h3 class="fw-bold mt-1 mb-0"><span class="counter">2</span></h3>
                        <div class="mt-2">
                            <span class="small text-black">Ação necessária</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">Tendência de Consumo (m³)</h6>
                        <span class="badge bg-light text-dark border">Últimos 7 dias</span>
                    </div>
                    <div style="height: 250px;">
                        <canvas id="graficoConsumo"></canvas>
                    </div>
                </div>

                <div class="card shadow-sm p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
                        <h6 class="fw-bold mb-0">Monitoramento de Unidades</h6>
                        <div class="d-flex gap-2">
                            <select id="filtroStatus" class="form-select form-select-sm rounded-pill" style="width: 120px;">
                                <option value="">Todos</option>
                                <option value="Normal">Normal</option>
                                <option value="Vazamento">Vazamento</option>
                            </select>
                            <input type="text" id="inputBusca" class="form-control form-control-sm rounded-pill" placeholder="Buscar unidade..." style="width: 180px;">
                            <button onclick="window.print()" class="btn btn-light btn-sm rounded-pill border"><i class="bi bi-printer"></i></button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>UNIDADE</th>
                                    <th>CONDOMÍNIO</th>
                                    <th class="text-center">CONSUMO</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-end">AÇÕES</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaCorpo">
                                <tr>
                                    <td>
                                        <div class="fw-bold">Apt 402 - B</div><small class="text-muted">João Silva</small>
                                    </td>
                                    <td>Solar das Águas</td>
                                    <td class="text-center">0.45 m³</td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success rounded-pill">Normal</span></td>
                                    <td class="text-end"><button class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-danger">Loja 05 - T</div><small class="text-muted">Maria Souza</small>
                                    </td>
                                    <td>Centro Brasília</td>
                                    <td class="text-center fw-bold text-danger">2.10 m³</td>
                                    <td class="text-center"><span class="badge bg-danger-subtle text-danger rounded-pill">Vazamento</span></td>
                                    <td class="text-end"><button class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card actions-card mb-4">
                    <h6>Ações Rápidas</h6>
                    <div class="d-grid">
                        <a href="nova_leitura.php" class="btn btn-nova-leitura">
                            <i class="bi bi-plus-circle"></i> Nova Leitura
                        </a>
                        <a href="unidades.php" class="btn btn-cadastrar-unidade">
                            <i class="bi bi-building-add"></i> Cadastrar Unidade
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm p-4">
                    <h6 class="fw-bold mb-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Alertas Prioritários</h6>
                    <div class="alert-critical p-3 mb-2 text-center rounded-3">
                        <small class="d-block fw-bold mb-1">Apt 402 - Bloco B</small>
                        <p class="small mb-2">Desvio de 300% detectado!</p>
                        <a href="https://wa.me/5500000000000" class="btn btn-sm btn-danger w-100 rounded-pill">
                            <i class="bi bi-whatsapp me-1"></i> Notificar
                        </a>
                    </div>
                    <div class="mt-3">
                        <a href="notificacoes.php" class="small text-decoration-none text-muted">Ver todos os 2 incidentes...</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1 text-white">
                <strong>&copy; 2026 MedidaCerta</strong> - Sistema de Gestão de Água Condominial.
            </p>
            <p class="mb-0 text-white small">CNPJ: 00.000.000/0001-00</p>
            <p class="mb-0 text-white small">Brasília-DF</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. CONFIGURAÇÃO DO GRÁFICO (Com Gradiente)
            const canvas = document.getElementById('graficoConsumo');
            if (canvas) {
                const ctx = canvas.getContext('2d');

                // Criação do gradiente azul
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(13, 110, 253, 0.3)');
                gradient.addColorStop(1, 'rgba(13, 110, 253, 0.0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                        datasets: [{
                                label: 'Consumo Real',
                                data: [120, 150, 140, 180, 170, 210, 190],
                                borderColor: '#0d6efd',
                                backgroundColor: gradient, // Usa o gradiente aqui
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#0d6efd'
                            },
                            {
                                label: 'Ideal (Meta)',
                                data: [100, 100, 100, 100, 100, 100, 100],
                                borderColor: '#198754',
                                borderDash: [5, 5],
                                fill: false,
                                tension: 0,
                                borderWidth: 1.5,
                                pointRadius: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: {
                                    usePointStyle: true
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f1f1f1'
                                }
                            }
                        }
                    }
                });
            }

            // 2. FUNÇÃO DE ANIMAÇÃO UNIFICADA (Counters)
            const animateValue = (el, start, end, duration, isMoney = false) => {
                if (start === end) return;
                const range = end - start;
                let current = start;
                const increment = end > start ? Math.ceil(range / (duration / 20)) : -1;
                const stepTime = 20;

                const timer = setInterval(() => {
                    current += (range / (duration / stepTime));

                    // Verifica se ultrapassou o alvo
                    if ((range > 0 && current >= end) || (range < 0 && current <= end)) {
                        current = end;
                        clearInterval(timer);
                    }

                    const formatConfig = isMoney ? {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    } : {
                        maximumFractionDigits: 0
                    };

                    el.innerText = current.toLocaleString('pt-BR', formatConfig);
                }, stepTime);
            };

            // Aplica a animação aos números simples
            document.querySelectorAll('.counter').forEach(el => {
                const target = parseFloat(el.innerText.replace('.', '').replace(',', '.'));
                el.innerText = "0"; // Começa do zero
                animateValue(el, 0, target, 1000, false);
            });

            // Aplica a animação aos valores em dinheiro
            document.querySelectorAll('.counter-money').forEach(el => {
                const target = parseFloat(el.getAttribute('data-target')) || 0;
                animateValue(el, 0, target, 1000, true);
            });
        });
    </script>
</body>

</html>