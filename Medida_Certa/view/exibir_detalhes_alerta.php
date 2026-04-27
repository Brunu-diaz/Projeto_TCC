<?php
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

$idAnomalia = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$idAnomalia) {
    header("Location: notificacoesadmin.php");
    exit;
}

try {
    $conn = Conexao::getConexao();
    
    // SQL utilizando os nomes de colunas confirmados (id_anomalia e consumo_calculado)
    $sql = "SELECT a.*, l.consumo_calculado, l.data_leitura, u.numero, u.bloco, usu.nome as morador, usu.email
            FROM anomalia a 
            INNER JOIN leitura l ON a.id_leitura = l.id_leitura 
            INNER JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro 
            INNER JOIN unidade u ON h.id_unidade = u.id_unidade 
            INNER JOIN usuario usu ON u.id_usuario = usu.id_usuario 
            WHERE a.id_anomalia = :id";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $idAnomalia);
    $stmt->execute();
    $detalhe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$detalhe) {
        die("Nenhuma anomalia encontrada para o ID: " . htmlspecialchars($idAnomalia));
    }
} catch (PDOException $e) {
    die("Erro no Banco de Dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Visualizar Alerta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
    :root {
        --mc-primary: #0d6efd;
        --mc-dark: #0f172a;
        --mc-gray: #64748b;
        --mc-bg: #f8fafc;
    }

    /* Botão Principal Estilizado */
    .btn-novo-usuario {
        background: var(--mc-primary);
        color: white;
        border-radius: 12px;
        padding: 10px 24px;
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
        transition: all 0.3s ease;
        border: none;
    }
    .btn-novo-usuario:hover {
        background: #0b5ed7;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13, 110, 253, 0.35);
    }

    /* Card de Detalhes Premium */
    .card-premium {
        border: 1px solid rgba(241, 245, 249, 1);
        border-radius: 20px !important;
        background: #ffffff;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.03) !important;
    }

    /* Ícones com Background Suave */
    .info-icon-wrapper {
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.4rem;
        transition: transform 0.3s ease;
    }
    .card-premium:hover .info-icon-wrapper { transform: scale(1.05); }

    .bg-soft-primary { background: #e0e7ff; color: #4338ca; }
    .bg-soft-danger { background: #fee2e2; color: #b91c1c; }
    .bg-soft-success { background: #dcfce7; color: #15803d; }

    /* Label de Dados */
    .data-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--mc-gray);
        margin-bottom: 4px;
    }
    /* Estilo para a caixa branca flutuante */
    .page-header-box { border-radius: 16px; margin-top: -45px; position: relative; z-index: 10; }
    
    /* Garante que o container principal tenha espaço */
    main.container {
        padding-top: 0 !important;
    }
</style>
</head>
<body class="bg-light">
    <?php include_once __DIR__ . '/includes/header.php'; ?>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Detalhes da Anomalia</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item"><a href="notificacoesadmin.php" class="text-decoration-none text-muted">Notificações</a></li>
                        <li class="breadcrumb-item active">Detalhes</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex gap-2">
                <a href="notificacoesadmin.php" class="btn btn-outline-secondary rounded-3 px-4 shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <main class="main-container container mb-5">

    <div class="card card-premium overflow-hidden">
        <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between" 
             style="background: linear-gradient(to right, #f8fafc, #ffffff);">
            <div class="d-flex align-items-center">
                <span class="badge rounded-circle p-1 me-2 <?= (strpos(strtolower($detalhe['nivel'] ?? ''), 'crít') !== false) ? 'bg-danger' : 'bg-warning' ?>"></span>
                <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 1px;">Status: Verificação Pendente</span>
            </div>
            <span class="badge bg-dark-subtle text-dark rounded-pill px-3">ID #<?= $detalhe['id_anomalia'] ?></span>
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="row g-5">
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex align-items-start">
                        <div class="info-icon-wrapper bg-soft-primary me-3">
                            <i class="bi bi-house-door"></i>
                        </div>
                        <div>
                            <div class="data-label">Localização</div>
                            <div class="fw-bold fs-4 text-dark">Bloco <?= htmlspecialchars($detalhe['bloco']) ?></div>
                            <div class="text-muted fw-semibold">Apartamento <?= htmlspecialchars($detalhe['numero']) ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 border-start-md">
                    <div class="d-flex align-items-start">
                        <div class="info-icon-wrapper bg-soft-danger me-3">
                            <i class="bi bi-droplet-half"></i>
                        </div>
                        <div>
                            <div class="data-label">Consumo Medido</div>
                            <div class="fw-bold fs-4 text-dark"><?= number_format($detalhe['consumo_calculado'], 2, ',', '.') ?> <small class="fs-6 fw-normal text-muted">m³</small></div>
                            <div class="text-muted small">Data: <?= date('d/m/Y', strtotime($detalhe['data_leitura'])) ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-lg-4 border-start-lg">
                    <div class="d-flex align-items-start">
                        <div class="info-icon-wrapper bg-soft-success me-3">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div>
                            <div class="data-label">Responsável</div>
                            <div class="fw-bold fs-5 text-dark"><?= htmlspecialchars($detalhe['morador']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($detalhe['email']) ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-5">
                    <div class="position-relative p-4 rounded-4" style="background: #f1f5f9; border-left: 4px solid var(--mc-primary);">
                        <div class="data-label mb-3 text-primary">Análise da Ocorrência</div>
                        <p class="mb-0 text-dark lh-lg" style="font-size: 1rem; font-style: italic;">
                            "<?= nl2br(htmlspecialchars($detalhe['descricao'])) ?>"
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-5 d-flex flex-column flex-md-row gap-3 justify-content-end">
                <a href="mailto:<?= $detalhe['email'] ?>" class="btn btn-outline-primary rounded-pill px-5 fw-bold">
                    <i class="bi bi-envelope-at me-2"></i>Contatar Via E-mail
                </a>
                <button class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                    <i class="bi bi-check2-all me-2"></i>Finalizar Atendimento
                </button>
            </div>
        </div>
    </div>
</main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>