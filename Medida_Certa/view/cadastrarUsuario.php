<?php
// 1. Segurança e Trava de Admin
require_once __DIR__ . '/../controller/TravaAdmin.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Cadastrar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
</head>

<body class="bg-light">

    <?php include '../view/includes/header.php'; ?>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Novo Usuário</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item"><a href="usuarios.php" class="text-decoration-none text-muted">Usuários</a></li>
                        <li class="breadcrumb-item active">Cadastrar</li>
                    </ol>
                </nav>
            </div>
            <a href="admin.php" class="btn btn-outline-secondary rounded-3 px-3 shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <main class="main-container container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card p-4 p-md-5 shadow-sm border-0" style="border-radius: 20px;">

                    <?php if (isset($_GET['sucesso'])): ?>
                        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i> Usuário cadastrado com sucesso!
                        </div>
                    <?php endif; ?>

                    <form action="../controller/CadastroUsuarioControl.php" method="POST">

                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="icon-square bg-primary-subtle rounded-3 me-3 p-3">
                                    <i class="bi bi-person-badge text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">Dados Pessoais</h5>
                                    <p class="text-muted small mb-0">Informações básicas do cliente ou administrador.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">Nome Completo</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-person"></i></span>
                                        <input type="text" name="nome" class="form-control form-control-lg rounded-end-3" placeholder="Ex: João Silva" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">E-mail</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control form-control-lg rounded-end-3" placeholder="joao@email.com" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">Telefone / WhatsApp</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-whatsapp"></i></span>
                                        <input type="text" name="telefone" class="form-control form-control-lg rounded-end-3" placeholder="(00) 00000-0000">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">CPF ou CNPJ</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-card-text"></i></span>
                                        <input type="text" name="cpf_cnpj" class="form-control form-control-lg rounded-end-3" placeholder="000.000.000-00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-light rounded-4 border border-dashed mb-5">
                            <h6 class="fw-bold mb-4 text-secondary">
                                <i class="bi bi-shield-lock-fill me-2"></i>Credenciais de Acesso
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">Username (Login)</label>
                                    <input type="text" name="username" class="form-control form-control-lg rounded-3" placeholder="ex: joao.silva" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">Senha Provisória</label>
                                    <div class="input-group">
                                        <input type="password" name="senha_provisoria" id="senha_provisoria" class="form-control form-control-lg rounded-start-3" required>
                                        <button class="btn btn-outline-secondary border-start-0" type="button" id="btnGerarSenha" title="Gerar Senha Aleatória">
                                            <i class="bi bi-magic"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary rounded-end-3" type="button" id="btnVerSenha">
                                            <i class="bi bi-eye" id="iconeOlho"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">Perfil de Acesso</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-layers text-secondary"></i></span>
                                        <select name="perfil" class="form-select form-control-lg rounded-end-3">
                                            <option value="Morador" selected>Cliente</option>
                                            <option value="Administrador">Administrador</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 d-flex justify-content-center border-top pt-4">
                            <button type="submit" class="btn btn-primary rounded-3 px-5 shadow-sm fw-bold py-2">
                                <i class="bi bi-person-plus-fill me-2"></i>Finalizar Cadastro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const inputSenha = document.getElementById('senha_provisoria');
        const btnGerar = document.getElementById('btnGerarSenha');
        const btnVer = document.getElementById('btnVerSenha');
        const iconeOlho = document.getElementById('iconeOlho');

        // Função para Gerar Senha Aleatória
        btnGerar.addEventListener('click', () => {
            const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#%';
            let senha = '';
            for (let i = 0; i < 8; i++) {
                senha += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
            }
            inputSenha.value = senha;
            // Muda para tipo 'text' automaticamente para o admin ver o que foi gerado
            inputSenha.type = 'text';
            iconeOlho.classList.replace('bi-eye', 'bi-eye-slash');
        });

        // Função para Alternar Visualização
        btnVer.addEventListener('click', () => {
            if (inputSenha.type === 'password') {
                inputSenha.type = 'text';
                iconeOlho.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                inputSenha.type = 'password';
                iconeOlho.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>
</body>

</html>