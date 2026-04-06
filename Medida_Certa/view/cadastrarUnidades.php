<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Cadastrar Unidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unidades.css">
</head>
<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="../index.php">
                    <i class="bi bi-droplet-fill me-2"></i> MedidaCerta
                </a>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="unidades.php">Voltar para Unidades</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-container bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card about-card p-4 p-md-5 bg-white border-0 shadow-sm">
                        <div class="card-body">
                            
                            <div class="text-center mb-5">
                                <div class="icon-square bg-primary-subtle rounded-circle mx-auto mb-3">
                                    <i class="bi bi-plus-circle-fill text-primary fs-2"></i>
                                </div>
                                <h2 class="fw-bold text-dark section-title">Cadastrar Nova Unidade</h2>
                                <p class="text-muted">Preencha os dados abaixo para vincular um novo hidrômetro.</p>
                            </div>

                            <form action="processa_cadastro_unidade.php" method="POST">
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <label class="form-label fw-600">Identificação da Unidade</label>
                                        <input type="text" name="nome_unidade" class="form-control form-control-lg rounded-pill px-4" placeholder="Ex: Apartamento 402 ou Loja 05" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Condomínio</label>
                                        <select name="condominio" class="form-select form-control-lg rounded-pill px-4" required>
                                            <option value="" selected disabled>Selecione o condomínio</option>
                                            <option value="1">Solar das Águas</option>
                                            <option value="2">Residencial Brasília</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Bloco / Torre</label>
                                        <input type="text" name="bloco" class="form-control form-control-lg rounded-pill px-4" placeholder="Ex: Bloco B">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-600">Número de Série do Hidrômetro</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light rounded-start-pill px-3"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" name="hidrometro_serial" class="form-control form-control-lg rounded-end-pill px-4" placeholder="Verifique no visor do seu aparelho" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-5">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm py-3">
                                                    Finalizar Cadastro
                                                </button>
                                            </div>
                                            <div class="col-md-6">
                                                <a href="unidades.php" class="btn btn-outline-secondary btn-lg w-100 rounded-pill py-3">
                                                    Cancelar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-4 text-center text-white">
    <div class="container">
        <p class="mb-0">&copy; 2026 MedidaCerta - Sistema de Gestão de Água Condominial. Todos os direitos reservados.</p>
        <p class="mb-0">CNPJ: 00.000.000/0001-00</p>
        <p class="mb-0">Brasília-DF</p>
    </div>
</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>