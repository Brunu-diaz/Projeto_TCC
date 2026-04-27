<?php
// Altere estas linhas no topo do seu dashboard.php
require_once __DIR__ . '/../controller/TravaCliente.php';
require_once __DIR__ . '/../model/dao/Conexao.php'; // Chame a conexão ANTES do DAO
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';

function getNomeMes($mes)
{
    $meses = [
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
    return $meses[(int)$mes] ?? 'Indef';
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

try {
    $pdo = Conexao::getConexao();

    $usuarioDAO = new UsuarioDAO();
    $dadosUsuario = $usuarioDAO->buscarUsuarioPorId($id_usuario);
    $nome = $dadosUsuario['nome'] ?? 'Cliente';

    // 1. BUSCA AS ÚLTIMAS 4 LEITURAS (A atual + 3 para média de anomalia)
    $sqlAnalise = "SELECT l.id_leitura, l.consumo_calculado, l.mes_referencia, l.ano_referencia, l.data_leitura
                   FROM leitura l
                   JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
                   JOIN unidade u ON h.id_unidade = u.id_unidade
                   WHERE u.id_usuario = :id_usuario
                   ORDER BY l.ano_referencia DESC, l.mes_referencia DESC LIMIT 4";

    $stmtAnalise = $pdo->prepare($sqlAnalise);
    $stmtAnalise->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmtAnalise->execute();
    $leiturasRecentes = $stmtAnalise->fetchAll(PDO::FETCH_ASSOC);

    // --- DEFINIÇÃO DE VARIÁVEIS BASE PARA EVITAR ERROS ---
    $dadosUltimaLeitura = $leiturasRecentes[0] ?? null;
    $ultimoConsumo = isset($dadosUltimaLeitura['consumo_calculado']) ? (float)$dadosUltimaLeitura['consumo_calculado'] : 0;
    $consumoExibir = $ultimoConsumo;
    $mesRef = isset($dadosUltimaLeitura['mes_referencia']) ? (int)$dadosUltimaLeitura['mes_referencia'] : (int)date('n');
    $anoAtual = $dadosUltimaLeitura['ano_referencia'] ?? date('Y'); // Corrige linha 64
    $anoRef = $anoAtual;

    $mesesPt = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $periodoReferencia = $mesesPt[$mesRef - 1] . ' / ' . $anoRef;

    $sqlDivida = "SELECT COUNT(*) FROM fatura f
                  JOIN leitura l ON f.id_leitura = l.id_leitura
                  JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
                  JOIN unidade u ON h.id_unidade = u.id_unidade
                  WHERE u.id_usuario = :id 
                  AND f.status_pagamento IN ('Pendente', 'Atrasado')
                  AND f.data_vencimento < CURDATE()";
    $stmtD = $pdo->prepare($sqlDivida);
    $stmtD->execute([':id' => $id_usuario]);
    $isInadimplente = $stmtD->fetchColumn() > 0;

    // 1. Verificar se o usuário já possui benefício ATIVO
    $sqlAtivo = "SELECT * FROM tarifa_social WHERE id_usuario = :id AND status_beneficio = 'Ativo'";
    $stmtAtivo = $pdo->prepare($sqlAtivo);
    $stmtAtivo->execute([':id' => $id_usuario]);
    $beneficioAtual = $stmtAtivo->fetch(PDO::FETCH_ASSOC);

    // 2. Lógica para o Informativo de Aptidão
    $aptoTarifaSocial = (!$isInadimplente && $ultimoConsumo <= 30);

    // 3. Busca consumo do ano anterior para comparar o Bônus de 20%
    $anoAnterior = $anoAtual - 1;
    $sqlAnt = "SELECT consumo_calculado FROM leitura l 
               JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
               JOIN unidade u ON h.id_unidade = u.id_unidade
               WHERE u.id_usuario = :id AND l.mes_referencia = :mes AND l.ano_referencia = :ano_ant";

    $stmtAnt = $pdo->prepare($sqlAnt);
    $stmtAnt->execute([
        ':id' => $id_usuario, 
        ':mes' => $mesRef, 
        ':ano_ant' => $anoAnterior
    ]);
    $consumoAnoPassado = $stmtAnt->fetchColumn() ?: 0; // Corrige linha 71

    // Apto se houve consumo ano passado e o atual é menor
    $aptoBonus = (!$isInadimplente && $consumoAnoPassado > 0 && $ultimoConsumo < $consumoAnoPassado);

    // 2. LÓGICA DE DETECÇÃO DE ANOMALIA E STATUS (Mantida original)
    $statusSistema = 'Rede Estável';
    $statusClass = 'text-info';
    $temNotificacao = false;

    if ($dadosUltimaLeitura) {
        $consumoAtual = (float)$dadosUltimaLeitura['consumo_calculado'];
        $leiturasAnteriores = array_slice($leiturasRecentes, 1);

        if (count($leiturasAnteriores) > 0) {
            $somaAnteriores = array_sum(array_column($leiturasAnteriores, 'consumo_calculado'));
            $mediaAnteriores = $somaAnteriores / count($leiturasAnteriores);

            if ($mediaAnteriores > 0 && $consumoAtual > ($mediaAnteriores * 1.5)) {
                $stmtCheck = $pdo->prepare("SELECT id_anomalia FROM anomalia WHERE id_leitura = :id_leitura");
                $stmtCheck->execute([':id_leitura' => $dadosUltimaLeitura['id_leitura']]);

                if (!$stmtCheck->fetch()) {
                    $sqlInsereAnomalia = "INSERT INTO anomalia (id_leitura, tipo, descricao, nivel) 
                                          VALUES (:id_leitura, 'Consumo Elevado', 'Consumo atual 50% acima da média das últimas 3 leituras.', 'Alto')";
                    $stmtInsere = $pdo->prepare($sqlInsereAnomalia);
                    $stmtInsere->execute([':id_leitura' => $dadosUltimaLeitura['id_leitura']]);
                }
                $statusSistema = 'Anomalia Detectada';
                $statusClass = 'text-danger';
                $temNotificacao = true;
            }
        }

        if ($statusSistema === 'Rede Estável' && strtotime($dadosUltimaLeitura['data_leitura']) < strtotime('-45 days')) {
            $statusSistema = 'Leitura Pendente';
            $statusClass = 'text-warning';
        }
    }

    // 3. CÁLCULO DA FATURA REAL (Mantida original)
    $tarifa = $pdo->query("SELECT valor_m3, taxa_esgoto FROM tarifa ORDER BY data_vigencia DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $valor_m3 = $tarifa['valor_m3'] ?? 8.26;
    $taxa_esgoto = $tarifa['taxa_esgoto'] ?? 0;
    $valorFatura = ($consumoExibir * $valor_m3) + $taxa_esgoto;

    // 4. HISTÓRICO COMPLETO PARA A TABELA (Mantida original)
    $sqlHistorico = "SELECT l.id_leitura, l.mes_referencia, l.ano_referencia, l.consumo_calculado, 
                        f.id_fatura, f.valor_total, f.status_pagamento, f.data_vencimento
                 FROM leitura l
                 JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
                 JOIN unidade u ON h.id_unidade = u.id_unidade
                 LEFT JOIN fatura f ON l.id_leitura = f.id_leitura
                 WHERE u.id_usuario = :id_usuario
                 ORDER BY l.ano_referencia DESC, l.mes_referencia DESC";

    $stmtLeituras = $pdo->prepare($sqlHistorico);
    $stmtLeituras->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmtLeituras->execute();
    $historicoLeituras = $stmtLeituras->fetchAll(PDO::FETCH_ASSOC);

    // 5. DADOS DO GRÁFICO (Mantida original)
    $sqlGrafico = "SELECT mes_referencia, ano_referencia, SUM(consumo_calculado) as total 
                   FROM (SELECT l.mes_referencia, l.ano_referencia, l.consumo_calculado 
                         FROM leitura l
                         JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
                         JOIN unidade u ON h.id_unidade = u.id_unidade
                         WHERE u.id_usuario = :id_usuario
                         ORDER BY l.ano_referencia DESC, l.mes_referencia DESC LIMIT 6) sub
                   GROUP BY ano_referencia, mes_referencia
                   ORDER BY ano_referencia ASC, mes_referencia ASC";

    $stmtGrafico = $pdo->prepare($sqlGrafico);
    $stmtGrafico->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmtGrafico->execute();
    $resGrafico = $stmtGrafico->fetchAll(PDO::FETCH_ASSOC);

    $graficoLabels = array_map(fn($i) => getNomeMes($i['mes_referencia']) . '/' . substr($i['ano_referencia'], -2), $resGrafico);
    $graficoValues = array_map(fn($i) => (float)$i['total'], $resGrafico);

    $graficoMedia = [];
    foreach ($graficoValues as $index => $valor) {
        $janela = array_slice($graficoValues, max(0, $index - 2), ($index < 2 ? $index + 1 : 3));
        $graficoMedia[] = array_sum($janela) / count($janela);
    }
} catch (Exception $e) {
    $consumoExibir = 0;
    $ultimoConsumo = 0;
    $anoAtual = date('Y');
    $consumoAnoPassado = 0;
    $valorFatura = 0;
    $statusSistema = 'Erro de Conexão';
    $statusClass = 'text-muted';
    $historicoLeituras = [];
    $temNotificacao = false;
    $aptoBonus = false;
    $aptoTarifaSocial = false;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Meu Painel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Container do Círculo de Perfil */
        .profile-circle-wrapper {
            position: relative;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }

        .profile-circle-wrapper:hover {
            transform: scale(1.05);
        }

        /* Círculo com as Iniciais */
        .profile-initials-min {
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Imagem de Perfil (se houver) */
        .profile-img-min {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        /* Badge Amarela de Edição */
        .edit-badge {
            position: absolute;
            bottom: -1px;
            right: -1px;
            background: #ffc107;
            /* Cor amarela do Bootstrap */
            color: #212529;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            border: 1.5px solid #0d6efd;
            /* Borda da mesma cor do header para "cortar" visualmente */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Ajustes de Alinhamento da Navbar */
        .navbar-dark .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9);
        }

        /* Alinhamento da lista de navegação para evitar saltos */
        .navbar-nav {
            align-items: center !important;
        }

        /* Links da Navbar - Unificação de altura e padding */
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem !important;
            /* Padding lateral fixo */
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .navbar-nav .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .navbar-nav .nav-link.active {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.15) !important;
            border-radius: 8px !important;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }

        .dropdown-item i {
            font-size: 1.1rem;
        }

        /* A Badge (Bolinha Vermelha) */
        .custom-notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;

            background-color: #dc3545;
            /* Vermelho do Bootstrap */
            color: #ffffff;

            width: 15px;
            height: 15px;

            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 10px;
            font-weight: 800;
        }

        .dropdown-menu {
            border-radius: 12px !important;
            padding: 0.5rem;
            min-width: 200px;
            border: none;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
            margin-top: 10px !important;
        }

        .bg-light-success {
            background-color: #e8f5e9;
        }

        .bg-light-warning {
            background-color: #fffde7;
        }

        .bg-light-secondary {
            background-color: #f8f9fa;
        }

        /* Estilos para os Badges de Status */
        .status-badge {
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 0.85rem;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-apto {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .status-inapto {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        .status-alerta {
            background-color: #fff3cd;
            color: #664d03;
            border: 1px solid #ffecb5;
        }
    </style>
</head>

<body>

    <?php include_once __DIR__ . '/includes/headerCliente.php'; ?>

    <?php if (isset($_GET['pagamento']) && $_GET['pagamento'] === 'sucesso'): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-left: 5px solid #198754 !important;">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Pagamento Confirmado!</h6>
                        <small>A fatura foi baixada e o status da unidade atualizado.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <main class="container py-5">

        <!-- Cards -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h4 class="fw-bold text-dark">Bem-vindo, <?= htmlspecialchars($nome) ?></h4>
                <p class="text-muted">Dados baseados no último fechamento realizado.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-white text-primary shadow-sm p-2 px-3 rounded-pill border">
                    <i class="bi bi-calendar3 me-2"></i> Ref: <?= $periodoReferencia ?>
                </span>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card card-custom p-3 h-100 border-start border-primary shadow-sm border-4">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape bg-primary text-white me-3 shadow-sm">
                            <i class="bi bi-droplet"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small text-uppercase fw-bold">Último Consumo</p>
                            <h3 class="fw-bold mb-0 text-primary"><?= number_format($consumoExibir, 1, ',', '.') ?> <small class="fs-6 text-primary">m³</small></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-custom p-3 h-100 border-start border-success border-4 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape bg-success text-white me-3 shadow-sm">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small text-uppercase fw-bold">Última Fatura</p>
                            <h3 class="fw-bold mb-0 text-success">R$ <?= number_format($valorFatura, 2, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-custom p-3 h-100 border-start border-danger border-4 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape bg-danger text-white me-3 shadow-sm rounded-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-exclamation-triangle fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small text-uppercase fw-bold">Pendências</p>
                            <h3 class="fw-bold mb-0 text-danger">
                                <?php
                                $pendentes = array_filter($historicoLeituras, function ($item) {
                                    return ($item['status_pagamento'] === 'Pendente' || $item['status_pagamento'] === 'Atrasado');
                                });
                                echo count($pendentes);
                                ?>
                                <small class="fs-6">faturas</small>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-custom p-3 h-100 border-start border-warning shadow-sm border-4">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape bg-warning text-white me-3 shadow-sm">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 small text-uppercase fw-bold">Status da Unidade</p>
                            <h5 class="fw-bold mb-0 <?php echo $statusClass; ?>"><?php echo $statusSistema; ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico e Histórico -->
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card card-custom p-4 shadow-sm h-100">
                        <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-graph-up me-2 text-primary"></i>Histórico de Consumo (m³)</h5>
                        <div id="chartWrapper">
                            <canvas id="graficoConsumo"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="table-container shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Últimas Leituras</h5>
                            <?php if (count($historicoLeituras) > 6): ?>
                                <a href="historico_completo.php" class="btn btn-sm btn-link text-decoration-none">Ver tudo</a>
                            <?php endif; ?>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr class="small text-uppercase">
                                        <th>Mês</th>
                                        <th>Consumo</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Fatura</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($historicoLeituras) > 0):
                                        // Mostra apenas as primeiras 6 leituras
                                        $exibirLeituras = array_slice($historicoLeituras, 0, 6);
                                        foreach ($exibirLeituras as $item): ?>
                                            <tr>
                                                <td class="fw-bold">
                                                    <?php echo getNomeMes($item['mes_referencia']) . '/' . substr($item['ano_referencia'], -2); ?>
                                                </td>
                                                <td><?php echo number_format($item['consumo_calculado'], 1, ',', '.'); ?> m³</td>

                                                <td class="text-nowrap">
                                                    <?php echo $item['valor_total'] ? 'R$ ' . number_format($item['valor_total'], 2, ',', '.') : '---'; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item['id_fatura']): ?>
                                                        <?php
                                                        $status = $item['status_pagamento'] ?? 'Pendente';
                                                        $statusClass = 'bg-secondary text-white';
                                                        if ($status === 'Pago') {
                                                            $statusClass = 'bg-success';
                                                        } elseif ($status === 'Atrasado') {
                                                            $statusClass = 'bg-danger';
                                                        } elseif ($status === 'Pendente') {
                                                            $statusClass = 'bg-warning text-dark';
                                                        }
                                                        ?>
                                                        <span class="badge <?= $statusClass ?>"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">Não Emitida</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item['id_fatura']): ?>
                                                        <a class="btn btn-sm btn-outline-primary" href="./gerar_pdf.php?id=<?php echo htmlspecialchars($item['id_fatura'], ENT_QUOTES, 'UTF-8'); ?>&download=1" target="_blank" rel="noopener noreferrer" title="Abrir fatura em nova aba e gerar impressão/download">
                                                            <i class="bi bi-file-earmark-pdf"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <form method="POST" action="../controller/GerarFaturaControl.php" target="_blank" style="display: inline;">
                                                            <input type="hidden" name="id_leitura" value="<?php echo htmlspecialchars($item['id_leitura'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Gerar fatura e acionar impressão/download">
                                                                <i class="bi bi-currency-dollar"></i> Gerar
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Nenhuma leitura encontrada.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px; background: #ffffff;">
                        <div class="card-body p-4">

                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">
                                        <i class="bi bi-calculator text-primary me-2"></i>Consultoria de Rateio Interno
                                    </h6>
                                    <p class="text-muted small mb-0">Baseado nas metas de consumo do condomínio</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-soft-info text-info px-3 py-2" style="background-color: #e0f7fa; font-size: 0.75rem; letter-spacing: 0.5px;">
                                        REGRAS DO CONDOMÍNIO
                                    </span>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <?php
                                    // Define cores e ícones baseados no status (Prioridade para Inadimplência)
                                    $corSocial = '#fffbeb'; // Amarelo (Aviso)
                                    $iconSocial = 'bi-exclamation-triangle-fill text-warning';

                                    if ($isInadimplente) {
                                        $corSocial = '#fef2f2'; // Vermelho claro (Erro)
                                        $iconSocial = 'bi-x-circle-fill text-danger';
                                    } elseif ($aptoTarifaSocial) {
                                        $corSocial = '#f0fdf4'; // Verde claro (Sucesso)
                                        $iconSocial = 'bi-check-circle-fill text-success';
                                    }
                                    ?>
                                    <div class="h-100 p-3 rounded-4 border-0" style="background-color: <?php echo $corSocial; ?>;">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="p-2 rounded-3 bg-white shadow-sm">
                                                    <i class="bi <?php echo $iconSocial; ?> fs-4"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 1px;">Desconto Social (Rateio)</span>
                                                <h6 class="fw-bold my-1">
                                                    <?php
                                                    if ($isInadimplente) echo 'Benefício Suspenso';
                                                    elseif ($aptoTarifaSocial) echo 'Meta de Consumo Atingida';
                                                    else echo 'Acima da Meta';
                                                    ?>
                                                </h6>
                                                <p class="small mb-0 text-secondary">
                                                    <?php
                                                    if ($isInadimplente) echo "Regularize suas faturas em aberto para voltar a usufruir dos descontos.";
                                                    elseif ($aptoTarifaSocial) echo "Parabéns! Seu consumo sob controle garante a menor faixa no rateio.";
                                                    else echo "Consumo acima de 30 m³. Reduza para obter o desconto no próximo mês.";
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <?php
                                    $corBonus = '#f8fafc'; // Cinza (Neutro)
                                    $iconBonus = 'bi-dash-circle text-secondary';

                                    if ($isInadimplente) {
                                        $corBonus = '#fef2f2';
                                        $iconBonus = 'bi-lock-fill text-danger';
                                    } elseif ($aptoBonus) {
                                        $corBonus = '#eff6ff'; // Azul (Info)
                                        $iconBonus = 'bi-star-fill text-primary';
                                    }
                                    ?>
                                    <div class="h-100 p-3 rounded-4 border-0" style="background-color: <?php echo $corBonus; ?>;">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="p-2 rounded-3 bg-white shadow-sm">
                                                    <i class="bi <?php echo $iconBonus; ?> fs-4"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 1px;">Bônus de Economia</span>
                                                <h6 class="fw-bold my-1">
                                                    <?php
                                                    if ($isInadimplente) echo 'Bônus Bloqueado';
                                                    elseif ($aptoBonus) echo 'Apto ao Rateio com Bônus';
                                                    else echo 'Sem Redução Histórica';
                                                    ?>
                                                </h6>
                                                <p class="small mb-0 text-secondary">
                                                    <?php
                                                    if ($isInadimplente) echo "Débitos pendentes impedem a aplicação de bônus por economia.";
                                                    elseif ($aptoBonus) echo "Você contribuiu para a redução global do prédio e terá prioridade no bônus.";
                                                    else echo "Seu consumo não reduziu em relação ao mesmo mês do ano passado ($consumoAnoPassado m³).";
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top" style="border-color: #f1f5f9 !important;">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background-color: #f8fafc;">
                                    <div class="d-flex align-items-center flex-grow-1 me-3">
                                        <i class="bi bi-info-circle-fill text-primary me-3 fs-5"></i>
                                        <p class="mb-0 text-muted" style="font-size: 0.78rem;">
                                            <strong>Nota de Gestão:</strong> Os descontos são aplicados pelo síndico no boleto condominial com base nestes dados do <strong>MedidaCerta</strong>.
                                        </p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalPassoPasso" style="font-size: 0.75rem; min-width: 140px;">
                                        COMO FUNCIONA?
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Fatura -->
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



    <?php include '../view/includes/footerCliente.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('graficoConsumo').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#0d6efd');
        gradient.addColorStop(1, '#0056b3');

        new Chart(ctx, {
            data: {
                labels: <?php echo json_encode($graficoLabels); ?>,
                datasets: [{
                        type: 'line', // Define este dataset como linha
                        label: 'Média (3 meses)',
                        data: <?php echo json_encode($graficoMedia); ?>,
                        borderColor: '#ffc107', // Cor amarela/âmbar para destaque
                        borderWidth: 3,
                        borderDash: [5, 5], // Deixa a linha tracejada
                        pointRadius: 4,
                        pointBackgroundColor: '#ffc107',
                        tension: 0.4, // Suaviza a curva da linha
                        fill: false,
                        order: 1 // Garante que a linha fique na frente das barras
                    },
                    {
                        type: 'bar', // Mantém o consumo em barras
                        label: 'Consumo (m³)',
                        data: <?php echo json_encode($graficoValues); ?>,
                        backgroundColor: gradient,
                        borderRadius: 10,
                        borderSkipped: false,
                        order: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true, // Ativado para o usuário saber o que é a linha
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 20
                        }
                    },
                    tooltip: {
                        mode: 'index', // Mostra os dois valores ao passar o mouse
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2
                                }) + ' m³';
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f0f0f0'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + ' m³';
                            }
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
    <div class="modal fade" id="modalPassoPasso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-building-check text-primary me-2"></i>Gestão de Incentivos e Rateio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-4 h-100 border-0">
                                <h6 class="fw-bold text-success mb-3"><i class="bi bi-people-fill me-2"></i>Incentivo Social (Interno)</h6>
                                <p class="small text-muted">Redução na taxa de rateio para unidades que mantêm consumo de subsistência.</p>
                                <ul class="list-unstyled small mb-3">
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i><strong>Consumo:</strong> Máximo de 30m³ no mês.</li>
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i><strong>Perfil:</strong> Cadastro validado pelo síndico.</li>
                                </ul>
                                <div class="bg-white p-2 rounded-3 shadow-sm">
                                    <strong class="d-block small text-dark">Documentação:</strong>
                                    <span class="text-muted" style="font-size: 0.75rem;">Apresentar comprovante de NIS ou CadÚnico à administração.</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-4 h-100 border-0">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-graph-down-arrow me-2"></i>Bônus de Eficiência</h6>
                                <p class="small text-muted">Repasse proporcional do bônus concedido pela concessionária ao condomínio.</p>
                                <ol class="small text-muted ps-3">
                                    <li class="mb-2">Consumo menor que o mesmo mês do ano anterior.</li>
                                    <li class="mb-2">O desconto de 20% é aplicado sobre o seu consumo individual no rateio.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 px-2">
                        <h6 class="fw-bold mb-4">Como o benefício chega ao seu boleto:</h6>

                        <div class="timeline">
                            <div class="d-flex mb-1">
                                <div class="d-flex flex-column align-items-center me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px; min-width: 28px; font-weight: bold; font-size: 0.85rem;">1</div>
                                    <div class="bg-primary opacity-25" style="width: 2px; height: 40px;"></div>
                                </div>
                                <div class="pt-1">
                                    <strong class="small d-block text-dark">Monitoramento Inteligente</strong>
                                    <p class="small text-muted">O MedidaCerta registra seu consumo em tempo real.</p>
                                </div>
                            </div>

                            <div class="d-flex mb-1">
                                <div class="d-flex flex-column align-items-center me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px; min-width: 28px; font-weight: bold; font-size: 0.85rem;">2</div>
                                    <div class="bg-primary opacity-25" style="width: 2px; height: 40px;"></div>
                                </div>
                                <div class="pt-1">
                                    <strong class="small d-block text-dark">Cálculo de Rateio</strong>
                                    <p class="small text-muted">O síndico utiliza os relatórios do sistema para validar quem atingiu as metas.</p>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="d-flex flex-column align-items-center me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px; min-width: 28px; font-weight: bold; font-size: 0.85rem;">3</div>
                                </div>
                                <div class="pt-1">
                                    <strong class="small d-block text-dark">Abatimento no Condomínio</strong>
                                    <p class="small text-muted mb-0">O desconto aparece detalhado no seu boleto de taxa condominial.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>