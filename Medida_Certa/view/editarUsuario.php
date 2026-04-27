<?php
// 1. Segurança e Importações
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';

// 2. Verificação do ID e Busca de Dados
$usuarioDAO = new UsuarioDAO();
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id) {
    header("Location: listarusuarios.php?erro=ID+invalido");
    exit;
}

$u = $usuarioDAO->buscarUsuarioPorId($id);

if (!$u) {
    header("Location: listarusuarios.php?erro=Usuario+nao+encontrado");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Editar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        /* Padronização visual conforme image_b62442.png */
        .card-arredondado {
            border-radius: 15px !important;
            border: 1px solid #f0f0f0;
        }

        /* Cabeçalhos de Seção com Ícones */
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .icon-box {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .icon-user {
            background-color: #e7f1ff;
            color: #0d6efd;
        }

        .icon-lock {
            background-color: #fff9e6;
            color: #ffc107;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        /* Inputs limpos e modernos */
        .form-control,
        .form-select {
            border-radius: 8px !important;
            border: 1px solid #dee2e6;
            padding: 10px 15px;
            background-color: #fff;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: none;
        }

        /* Grupos de entrada (Username e Senha) */
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #adb5bd;
            border-radius: 8px 0 0 8px !important;
        }

        .group-end {
            border-radius: 0 8px 8px 0 !important;
        }
    </style>
</head>

<body class="bg-light">

    <?php include '../view/includes/header.php'; ?>

    <main class="container py-4">

        <div class="page-header-box mb-4">
            <div class="bg-white border-bottom py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px;">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Edição de Usuário</h4>
                    <p class="text-muted small mb-0">Atualize as informações de acesso e permissões.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="listarUsuarios.php" class="btn btn-outline-secondary rounded-3 px-3 shadow-sm">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">

                <div class="card border-0 shadow-sm card-arredondado">
                    <div class="card-body p-5">
                        <form action="../controller/EditarUsuarioControl.php" method="POST">
                            <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">

                            <div class="section-header">
                                <div class="icon-box icon-user"><i class="bi bi-person-plus-fill"></i></div>
                                <div>
                                    <h5 class="fw-bold mb-0">Dados do Cliente</h5>
                                    <small class="text-muted">Informações básicas de identificação</small>
                                </div>
                            </div>

                            <div class="row g-3 mb-5">
                                <div class="col-md-8">
                                    <label class="form-label">Nome Completo / Razão Social</label>
                                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($u['nome']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">CPF / CNPJ</label>
                                    <input type="text" name="cpf_cnpj" class="form-control" value="<?= htmlspecialchars($u['cpf_cnpj']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">E-mail</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Telefone / WhatsApp</label>
                                    <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($u['telefone']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nível de Permissão</label>
                                    <select name="perfil" class="form-select" required>
                                        <option value="2" <?= $u['id_perfil'] == 2 ? 'selected' : '' ?>>Cliente</option>
                                        <option value="1" <?= $u['id_perfil'] == 1 ? 'selected' : '' ?>>Administrador</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-5 opacity-25">

                            <div class="section-header">
                                <div class="icon-box icon-lock"><i class="bi bi-shield-lock-fill"></i></div>
                                <div>
                                    <h5 class="fw-bold mb-0">Segurança e Acesso</h5>
                                    <small class="text-muted">Credenciais para login no sistema</small>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nome de Usuário (Login)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">@</span>
                                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($u['username']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nova Senha (deixe em branco para manter)</label>
                                    <div class="input-group">
                                        <input type="password" name="nova_senha" id="inputSenha" class="form-control" placeholder="••••••••">
                                        <button class="btn btn-outline-secondary group-end" type="button" onclick="gerarSenhaAleatoria()"><i class="bi bi-magic"></i></button>
                                        <button class="btn btn-outline-secondary group-end" type="button" onclick="toggleSenha()"><i class="bi bi-eye" id="iconSenha"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 d-flex justify-content-center border-top pt-4">
                                <button type="submit" class="btn btn-primary rounded-3 px-5 shadow-sm fw-bold py-2">
                                    Salvar Alterações <i class="bi bi-check2-all ms-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSenha() {
            const input = document.getElementById('inputSenha');
            const icon = document.getElementById('iconSenha');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        function gerarSenhaAleatoria() {
            const input = document.getElementById('inputSenha');
            const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
            let pass = "";
            for (let i = 0; i < 10; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
            input.value = pass;
            input.type = 'text';
            document.getElementById('iconSenha').classList.replace('bi-eye', 'bi-eye-slash');
        }
    </script>
</body>

</html>