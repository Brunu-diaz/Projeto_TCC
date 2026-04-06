<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Gerenciar Unidades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unidades.css">
    <link rel="stylesheet" href="../assets/css/unificado.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="container page-header-box">
        <div class="bg-white border-bottom py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Gerenciar Unidades</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="admin.php">Admin</a></li>
                        <li class="breadcrumb-item active">Unidades</li>
                    </ol>
                </nav>
            </div>
            <a href="cadastrarUnidades.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-2"></i>Nova Unidade
            </a>
        </div>
    </div>

    <main class="main-container container mt-4 mb-5">
        <div class="row g-4">
            <div class="col-md-6 col-xl-4">
                <div class="card unit-card feature-box h-100">
                    <div class="card-body p-4 text-dark">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="icon-square bg-primary-subtle text-primary">
                                <i class="bi bi-house-door-fill fs-4"></i>
                            </div>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3">Ativo</span>
                        </div>
                        <h5 class="fw-bold mb-1">Apartamento 402</h5>
                        <p class="text-muted small">Residencial Solar das Águas - Bloco B</p>
                        
                        <div class="bg-light p-3 rounded-3 my-3">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-muted">Consumo (Mês):</span>
                                <span class="fw-bold">12.4 m³</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" style="width: 65%"></div>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <a href="dashboard.php?id=1" class="btn btn-outline-primary btn-sm w-100 rounded-pill">Ver Detalhes</a>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-light btn-sm w-100 rounded-pill border">Editar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card unit-card feature-box h-100 border-start border-warning border-4">
                    <div class="card-body p-4 text-dark">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="icon-square bg-warning-subtle text-warning">
                                <i class="bi bi-shop fs-4"></i>
                            </div>
                            <span class="badge bg-warning-subtle text-warning rounded-pill px-3">Alerta</span>
                        </div>
                        <h5 class="fw-bold mb-1">Loja Comercial 05</h5>
                        <p class="text-muted small">Shopping Center Brasília - Térreo</p>
                        
                        <div class="bg-warning-subtle p-3 rounded-3 my-3">
                            <div class="d-flex justify-content-between mb-1 small text-warning-emphasis">
                                <span>Consumo (Mês):</span>
                                <span class="fw-bold text-dark">45.2 m³</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 88%"></div>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <a href="dashboard.php?id=2" class="btn btn-outline-primary btn-sm w-100 rounded-pill">Ver Detalhes</a>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-light btn-sm w-100 rounded-pill border">Editar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>