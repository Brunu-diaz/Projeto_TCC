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
            border-radius: 20px !important;
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
            color: #495057;
            margin-bottom: 0.5rem;
            letter-spacing: -0.2px;
            font-size: 0.875rem;
        }

        /* Inputs limpos e modernos */
        .form-control,
        .form-select {
            border: 1px solid #e0e0e0 !important;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
            font-size: 0.95rem !important;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            background-color: #fff;
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

        .btn-lg {
            padding: 1rem;
            font-size: 1rem;
            font-weight: bold;
        }

        .alert-floating-container {
            position: fixed;
            top: 25px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1060;
        }

        .alert-compacto {
            background: #ffffff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 500;
            color: #198754;
        }
    </style>
</head>

<body class="bg-light">

    <?php include '../view/includes/header.php'; ?>

    <div class="alert-floating-container">
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-compacto fade show" id="sucessoAlert">
                <i class="bi bi-check-circle-fill me-2"></i> Usuário atualizado com sucesso!
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-compacto fade show text-danger" id="alertaFlutuante" style="color: #dc3545; border-color: #f8d7da;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($_GET['erro']) ?>
            </div>
        <?php endif; ?>
    </div>

    <main class="main-container container mb-5">

        <div class="page-header-box mb-4">
            <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Editar Cadastro</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-primary">Admin</a></li>
                            <li class="breadcrumb-item"><a href="listarUsuarios.php" class="text-decoration-none text-primary">Usuários</a></li>
                            <li class="breadcrumb-item active">Editar</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary rounded-3 px-4 shadow-sm">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">

                <div class="card border-0 shadow-sm card-arredondado">
                    <div class="card-body p-4 p-md-5">
                        <form action="../controller/EditarUsuarioControl.php" method="POST">
                            <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">

                            <div class="section-header">
                                <div class="icon-box icon-user"><i class="bi bi-person-plus-fill"></i></div>
                                <div>
                                    <h5 class="fw-bold mb-0">Dados do Cliente</h5>
                                    <small class="text-muted">Informações básicas de identificação</small>
                                </div>
                            </div>

                            <div class="row g-3">
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

                            <hr class="my-4 opacity-25">

                            <div class="row g-3 pt-2">
                                <div class="col-md-6 mx-auto">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm py-3 fw-bold">
                                        Salvar Alterações
                                    </button>
                                </div>
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

    <script>
        const sucessoAlert = document.getElementById('sucessoAlert');
        const alertToRemove = sucessoAlert;

        if (alertToRemove) {
            // 1. Limpa o parâmetro da URL sem recarregar a página
            // Isso impede que o F5 mostre a mensagem de novo
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('sucesso');
                url.searchParams.delete('erro');
                window.history.replaceState({}, document.title, url.pathname);
            }

            // 2. Animação de sumir o alerta após 3 segundos
            setTimeout(() => {
                alertToRemove.style.transition = "opacity 0.6s ease";
                alertToRemove.style.opacity = "0";
                setTimeout(() => alertToRemove.remove(), 600);
            }, 3000);
        }
    </script>
</body>

</html>