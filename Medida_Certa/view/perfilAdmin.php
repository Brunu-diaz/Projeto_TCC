<?php
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    header('Location: login.php');
    exit;
}

try {
    $usuarioDAO = new UsuarioDAO();
    $dadosAdmin = $usuarioDAO->buscarUsuarioPorId($id_usuario);

    $nomeAdmin = $dadosAdmin['nome'] ?? 'Administrador';
    $emailAdmin = $dadosAdmin['email'] ?? '';
    $cpf_cnpj = $dadosAdmin['cpf_cnpj'] ?? '';
    $telefone = $dadosAdmin['telefone'] ?? '';
    $foto_db = $dadosAdmin['foto'] ?? '';

    // Lógica de Iniciais
    $nome_limpo = trim($nomeAdmin);
    $partes_nome = explode(' ', $nome_limpo);
    $primeira_inicial = mb_substr($partes_nome[0], 0, 1);
    $ultima_inicial = (count($partes_nome) > 1) ? mb_substr(end($partes_nome), 0, 1) : '';
    $iniciais = strtoupper($primeira_inicial . $ultima_inicial);

    // 5. Lógica da Foto (Sincronizada com o que o Header espera)
    $tem_foto = false;
    $foto_perfil_url = '';
    $foto_url_header = '';
    $tem_foto_header = false;

    if (!empty($foto_db)) {
        if (strpos($foto_db, 'data:image') === 0) {
            $foto_perfil_url = $foto_db;
            $foto_url_header = $foto_db;
            $tem_foto = true;
            $tem_foto_header = true;
        } else {
            $imagemPath = __DIR__ . '/../assets/img/perfil/' . $foto_db;
            if (file_exists($imagemPath)) {
                $foto_perfil_url = '../assets/img/perfil/' . rawurlencode($foto_db);
                $foto_url_header = $foto_perfil_url;
                $tem_foto = true;
                $tem_foto_header = true;
            } else {
                $base64 = base64_encode($foto_db);
                if ($base64) {
                    $foto_perfil_url = 'data:image/jpeg;base64,' . $base64;
                    $foto_url_header = $foto_perfil_url;
                    $tem_foto = true;
                    $tem_foto_header = true;
                }
            }
        }
    }

    // Variáveis para a View
    $iniciais_header = $iniciais;
} catch (Exception $e) {
    error_log($e->getMessage());
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil | MedidaCerta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }

        .profile-container {
            max-width: 1000px;
            margin: 50px auto;
        }

        .card-profile {
            border: none;
            border-radius: 24px;
            overflow: hidden;
        }

        .card-profile .card-body {
            padding: 2rem;
        }

        .profile-upload-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto;
        }

        .profile-upload-container img,
        .profile-upload-container .profile-initials-large {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;

        }

        .profile-upload-container img {
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            display: block;
        }

        .profile-initials-large {
            display: flex !important;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d6efd, #004085);
            color: white;
            font-size: 3rem;
            font-weight: 700;
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            text-transform: uppercase;
            line-height: 1;
            box-sizing: border-box;
            text-align: center;
        }

        /* 4. Ajuste para a classe d-none do Bootstrap não conflitar */
        .profile-initials-large.d-none {
            display: none !important;
        }

        .profile-upload-container .btn-camera {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 3px solid #fff;
            background: #0d6efd;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .profile-upload-container .btn-camera:hover {
            background: #0b5ed7;
        }

        .btn-remove-photo {
            position: absolute;
            top: 0;
            right: -10px;
            background: #dc3545;
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.2s;
            z-index: 15;
        }

        .btn-remove-photo:hover {
            background: #a71d2a;
            transform: scale(1.1);
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #495057;
            text-transform: uppercase;
            margin-bottom: 0.6rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 14px 16px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .15);
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Perfil do Administrador</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item active">Perfil</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <main class="main-container container mb-5">
        <div class="profile-container">
            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Perfil atualizado com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Erro ao processar solicitação.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card card-profile shadow-sm">
                        <div class="card-body">
                            <form action="../controller/PerfilControl.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($id_usuario, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="remover_foto" id="removerFotoInput" value="0">
                                <input type="hidden" name="redirect" value="perfilAdmin.php">

                                <div class="row gy-4">
                                    <div class="col-md-4 text-center border-end-md">
                                        <div class="profile-upload-container mb-3">
                                            <div id="avatarContainer" class="position-relative d-inline-block">
                                                <?php if ($tem_foto): ?>
                                                    <img id="imgPreview" src="<?= htmlspecialchars($foto_perfil_url, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de Perfil">
                                                    <div id="initialsCircle" class="profile-initials-large d-none"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
                                                    <button type="button" id="btnRemovePhoto" class="btn-remove-photo shadow-sm" onclick="removeImage()">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <img id="imgPreview" src="" alt="Foto de Perfil" style="display: none;">
                                                    <div id="initialsCircle" class="profile-initials-large"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
                                                    <button type="button" id="btnRemovePhoto" class="btn-remove-photo shadow-sm d-none" onclick="removeImage()">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                            <label for="fotoInput" class="btn-camera shadow-sm">
                                                <i class="bi bi-camera-fill"></i>
                                            </label>
                                            <input type="file" name="foto" id="fotoInput" accept="image/*" class="d-none" onchange="previewImage(this)">
                                        </div>

                                        <h5 class="mt-3 fw-bold mb-1"><?= htmlspecialchars($nomeAdmin, ENT_QUOTES, 'UTF-8') ?></h5>
                                        <p class="text-muted small">Administrador MedidaCerta</p>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="row gy-3">
                                            <div class="col-12">
                                                <label class="form-label">Nome Completo</label>
                                                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($nomeAdmin, ENT_QUOTES, 'UTF-8') ?>" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">E-mail</label>
                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($emailAdmin, ENT_QUOTES, 'UTF-8') ?>" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Telefone</label>
                                                <input type="text" name="telefone" id="telefone" class="form-control" value="<?= htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8') ?>" placeholder="(00) 00000-0000">
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label">CPF / CNPJ</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
                                                    <input type="text" class="form-control border-start-0 ps-0" value="<?= htmlspecialchars($cpf_cnpj, ENT_QUOTES, 'UTF-8') ?>" readonly>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="p-3 rounded-4 bg-light border">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1 fw-bold">Segurança da Conta</h6>
                                                            <p class="small text-muted mb-0">Altere sua senha sempre que necessário.</p>
                                                        </div>
                                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalSenha">
                                                            Alterar Senha
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column flex-md-row justify-content-end gap-2 mt-4">
                                            <a href="admin.php" class="btn btn-light rounded-pill px-4">Voltar</a>
                                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Salvar Alterações</button>
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

    <div class="modal fade" id="modalSenha" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 15px;">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">Alterar Senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../controller/SenhaControl.php" method="POST">
                    <div class="modal-body px-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label class="form-label">Senha Atual</label>
                            <input type="password" name="senha_atual" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nova Senha</label>
                            <input type="password" name="nova_senha" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nova Senha</label>
                            <input type="password" name="confirma_senha" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar Nova Senha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            const file = input.files[0];
            const imgPreview = document.getElementById('imgPreview');
            const initialsCircle = document.getElementById('initialsCircle');
            const btnRemove = document.getElementById('btnRemovePhoto');
            const removerInput = document.getElementById('removerFotoInput');

            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('A imagem é muito grande (Máx 2MB).');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = e => {
                    if (imgPreview) {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                        imgPreview.classList.remove('d-none');
                    }
                    if (initialsCircle) {
                        initialsCircle.classList.add('d-none');
                    }
                    if (btnRemove) {
                        btnRemove.classList.remove('d-none');
                    }
                    if (removerInput) {
                        removerInput.value = '0';
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            const imgPreview = document.getElementById('imgPreview');
            const initialsCircle = document.getElementById('initialsCircle');
            const btnRemove = document.getElementById('btnRemovePhoto');
            const fotoInput = document.getElementById('fotoInput');
            const removerInput = document.getElementById('removerFotoInput');

            if (imgPreview) {
                imgPreview.src = '';
                imgPreview.style.display = 'none';
                imgPreview.classList.add('d-none');
            }
            if (fotoInput) {
                fotoInput.value = '';
            }
            if (initialsCircle) {
                initialsCircle.classList.remove('d-none');
            }
            if (btnRemove) {
                btnRemove.classList.add('d-none');
            }
            if (removerInput) {
                removerInput.value = '1';
            }
        }

        const tel = document.getElementById('telefone');
        if (tel) {
            tel.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.slice(0, 11);
                let formatted = '';
                if (value.length > 0) formatted += '(' + value.slice(0, 2);
                if (value.length > 2) formatted += ') ' + value.slice(2, 7);
                if (value.length > 7) formatted += '-' + value.slice(7, 11);
                e.target.value = formatted;
            });
        }
    </script>
</body>

</html>