    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MedidaCerta - Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../assets/css/login.css">
        
    </head>
    <body>
        
        <!-- Header Padrão -->
        <header>
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
                <div class="container">
                    <!-- Logo com o ícone de gota -->
                    <a class="navbar-brand d-flex align-items-center fw-bold" href="../index.php">
                        <i class="bi bi-droplet-fill me-2" aria-hidden="true"></i> <!-- Ícone de gota -->
                            MedidaCerta
                    </a>
                        <!-- Botão para celular (hamburger) -->
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <!-- Links do Menu -->
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ms-auto text-white">
                            <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="quemsomos.php">Quem Somos</a></li>
                            <li class="nav-item"><a class="nav-link" href="../index.php#contato">Contato</a></li>
                            <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link active fw-bold" href="login.php">Login <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i></a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        
        <!-- Container que centraliza o card -->
        <div class="main-container bg-light">
            <div class="card login-card p-4 bg-white">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <a href="../medida_certa/index.php" class="text-decoration-none">
                            <i class="bi bi-droplet-fill logo-login" aria-hidden="true"></i>
                            <h2 class="fw-bold text-dark mt-2">MedidaCerta</h2>
                        </a>
                        <p class="text-muted">Acesse seu painel de consumo</p>
                    </div>

                    <form action="dashboard.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small" for="iusuario">E-mail ou Usuário</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-1"><i class="bi bi-person text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-1" id="iusuario" placeholder="exemplo@email.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small" for="isenha">Senha</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-1"><i class="bi bi-lock text-muted" aria-hidden="true"></i></span>
                                <input type="password" name="senha" class="form-control border-1" placeholder="••••••••" required id="isenha">
                                <button class="btn btn-outline-light text-muted" type="button" id="btn-senha" aria-label="Mostrar ou esconder senha" style="background-color: #f8f9fa;">
                                    <i class="bi bi-eye" id="icon-senha" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-4 small">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label text-muted" for="remember">Lembrar-me</label>
                            </div>
                            <a href="#" class="text-primary text-decoration-none">Esqueceu a senha?</a>
                        </div>

                            <button type="submit" class="btn btn-primary w-100 btn-login shadow-sm">
                                Entrar <i class="bi bi-box-arrow-in-right ms-2" aria-hidden="true"></i>
                            </button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted small">Ainda não tem acesso? <br> 
                        <a href="cadastro.php" class="fw-bold text-primary text-decoration-none">Criar Cadastro</a></p>
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
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

            <script>
        const btnSenha = document.getElementById('btn-senha');
        const inputSenha = document.getElementById('isenha');
        const iconSenha = document.getElementById('icon-senha');

        btnSenha.addEventListener('click', () => {
            // Alterna o tipo do input
            const type = inputSenha.getAttribute('type') === 'password' ? 'text' : 'password';
            inputSenha.setAttribute('type', type);
            
            // Alterna o ícone (bi-eye / bi-eye-slash)
            iconSenha.classList.toggle('bi-eye');
            iconSenha.classList.toggle('bi-eye-slash');
        });
    </script>
    </body>
    </html>