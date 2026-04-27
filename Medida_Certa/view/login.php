<?php
// Verifica se existe o cookie de "Lembrar-me" para preencher o campo automaticamente
$usuario_salvo = $_COOKIE['lembrar_usuario'] ?? '';
?>
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

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="../index.php">
                    <i class="bi bi-droplet-fill me-2" aria-hidden="true"></i>
                    MedidaCerta
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto text-white">
                        <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="quemsomos.php">Quem Somos</a></li>
                        <li class="nav-item"><a class="nav-link" href="../index.php#contato">Contato</a></li>
                        <li class="nav-item"><a class="nav-link active fw-bold" href="login.php">Login <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i></a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="main-container bg-light">
        <div class="card login-card p-4 bg-white">
            <div class="card-body">
                <div class="text-center mb-4">
                    <a href="../index.php" class="text-decoration-none">
                        <i class="bi bi-droplet-fill logo-login" aria-hidden="true"></i>
                        <h2 class="fw-bold text-dark mt-2">MedidaCerta</h2>
                    </a>
                    <p class="text-muted small">Acesse seu painel de consumo</p>
                </div>

                <?php if (isset($_GET['erro'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo htmlspecialchars($_GET['erro']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="../controller/LoginControl.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold small" for="iusuario">E-mail ou Usuário</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-1"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="username" class="form-control border-1" id="iusuario"
                                placeholder="Ex: joao" required value="<?= htmlspecialchars($usuario_salvo) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small" for="isenha">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-1"><i class="bi bi-lock text-muted" aria-hidden="true"></i></span>
                            <input type="password" name="senha" class="form-control border-1" placeholder="••••••••" required id="isenha">
                            <button class="btn btn-outline-light text-muted border-1" type="button" id="btn-senha"
                                aria-label="Mostrar ou esconder senha"
                                style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-left: none;">
                                <i class="bi bi-eye" id="icon-senha" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-4 small">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember" <?= !empty($usuario_salvo) ? 'checked' : '' ?>>
                            <label class="form-check-label text-muted" for="remember">Lembrar-me</label>
                        </div>
                        <a href="#" class="text-primary text-decoration-none" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Esqueceu a senha?</a>

                        <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2"></i>Recuperar Acesso</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center p-4">
                                        <i class="bi bi-building-exclamation text-primary display-4 mb-3"></i>
                                        <p class="text-muted">Por questões de segurança condominial, a redefinição de senha deve ser solicitada diretamente à <strong>Administração</strong>.</p>
                                        <div class="alert alert-secondary small border-0">
                                            O síndico irá gerar uma nova senha provisória para o seu usuário.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Entendi</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-login shadow-sm">
                        Entrar <i class="bi bi-box-arrow-in-right ms-2" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <div class="alert alert-info border-0 bg-light small text-start d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill fs-5 me-3 text-primary"></i>
                        <div>
                            Recebeu sua <strong class="text-dark fw-bold">senha provisória</strong>? Use-a para ativar sua conta.
                            Para novos acessos, procure a <span class="text-dark fw-bold">Administração do seu Condomínio</span>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 text-center">
        <div class="container">
            <p class="mb-1 text-white"><strong>&copy; 2026 MedidaCerta</strong> Sistema de Gestão de Água Condominial. Todos os direitos reservados.</p>
            <p class="mb-0 text-white small">CNPJ: 00.000.000/0001-00</p>
            <p class="mb-0 text-white small">Brasília-DF</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const btnSenha = document.getElementById('btn-senha');
        const inputSenha = document.getElementById('isenha');
        const iconSenha = document.getElementById('icon-senha');

        btnSenha.addEventListener('click', () => {
            const type = inputSenha.getAttribute('type') === 'password' ? 'text' : 'password';
            inputSenha.setAttribute('type', type);
            iconSenha.classList.toggle('bi-eye');
            iconSenha.classList.toggle('bi-eye-slash');
        });
    </script>

</body>

</html>