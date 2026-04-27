<?php
// 0. Início da Sessão (Obrigatório para acessar $_SESSION)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Dependências e Segurança
require_once __DIR__ . '/../controller/TravaCliente.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Identificação do Usuário
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    header("Location: ../login.php");
    exit;
}

// 2. Busca de dados no Banco de Dados
try {
    $conn = Conexao::getConexao();
    $sql = "SELECT nome, email, telefone, cpf_cnpj, foto FROM usuario WHERE id_usuario = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header("Location: ../controller/logout.php");
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// 3. Processamento de Variáveis para a View
$nome = $usuario['nome'] ?? 'Cliente';
$email = $usuario['email'] ?? '';
$telefone = $usuario['telefone'] ?? '';
$cpf_cnpj = $usuario['cpf_cnpj'] ?? '';

// 4. Lógica de Iniciais (Sincronizada com o Header)
$nome_limpo = trim($nome);
$partes_nome = explode(' ', $nome_limpo);
$primeira_inicial = mb_substr($partes_nome[0], 0, 1);
$ultima_inicial = (count($partes_nome) > 1) ? mb_substr(end($partes_nome), 0, 1) : "";
$iniciais = strtoupper($primeira_inicial . $ultima_inicial);

// 5. Lógica da Foto (Sincronizada com o que o Header espera)
$foto_db = $usuario['foto'] ?? '';
$foto_url_header = "";    // Variável que o headerCliente.php usa
$tem_foto_header = false; // Variável que o headerCliente.php usa

if (!empty($foto_db)) {
    if (is_string($foto_db) && strpos($foto_db, 'data:image') === 0) {
        $foto_url_header = $foto_db;
        $tem_foto_header = true;
    } elseif (is_string($foto_db)) {
        $imagemPath = __DIR__ . '/../assets/img/perfil/' . $foto_db;
        if (file_exists($imagemPath)) {
            $foto_url_header = '../assets/img/perfil/' . rawurlencode($foto_db);
            $tem_foto_header = true;
        } else {
            $base64 = base64_encode($foto_db);
            if ($base64) {
                $foto_url_header = 'data:image/jpeg;base64,' . $base64;
                $tem_foto_header = true;
            }
        }
    }
}

// Variáveis de compatibilidade para o corpo do perfil.php
$foto_perfil_url = $foto_url_header;
$tem_foto = $tem_foto_header;
$iniciais_header = $iniciais; // Enviando as iniciais para o círculo do header

// 6. Segurança CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 7. Fim da lógica - Abaixo segue o HTML
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/perfil.css">
    <style>
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
    </style>
</head>

<body>

    <?php
    // Como perfil.php já está na pasta 'view', o caminho para o include é este:
    include_once __DIR__ . '/includes/headerCliente.php';
    ?>

    <main class="container py-5">
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

        <div class="row mb-4">
            <div class="col-12 text-center text-md-start">
                <h4 class="fw-bold text-dark">Meu Perfil</h4>
                <p class="text-muted">Gerencie suas informações pessoais e de contato.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card card-profile p-2 p-md-4">
                    <div class="card-body">
                        <form action="../controller/PerfilControl.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $id_usuario ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                            <div class="row gy-4">
                                <div class="col-md-4 text-center border-end-md">
                                    <div class="profile-upload-container">
                                        <div id="avatarContainer" class="position-relative d-inline-block">
                                            <?php if ($tem_foto): ?>
                                                <img id="imgPreview" src="<?= htmlspecialchars($foto_perfil_url) ?>" alt="Foto de Perfil">
                                                <div id="initialsCircle" class="profile-initials-large d-none"><?= htmlspecialchars($iniciais) ?></div>
                                                <button type="button" id="btnRemovePhoto" class="btn-remove-photo shadow-sm" onclick="removeImage()">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            <?php else: ?>
                                                <img id="imgPreview" src="" alt="Foto de Perfil" style="display: none;">
                                                <div id="initialsCircle" class="profile-initials-large"><?= $iniciais ?></div>
                                                <button type="button" id="btnRemovePhoto" class="btn-remove-photo shadow-sm d-none" onclick="removeImage()">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <label for="fotoInput" class="btn-camera shadow-sm">
                                            <i class="bi bi-camera-fill"></i>
                                        </label>
                                        <input type="file" name="foto" id="fotoInput" accept="image/*" class="d-none" onchange="previewImage(this)">

                                        <input type="hidden" name="remover_foto" id="removerFotoInput" value="0">
                                    </div>
                                    <h5 class="mt-3 fw-bold mb-1"><?= htmlspecialchars($nome) ?></h5>
                                    <p class="text-muted small">Membro desde 2026</p>
                                </div>

                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-12 mb-4">
                                            <label class="form-label">Nome Completo</label>
                                            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($nome) ?>" required>
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label class="form-label">E-mail de Acesso</label>
                                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label class="form-label">Telefone / WhatsApp</label>
                                            <input type="text" name="telefone" id="telefone" class="form-control" value="<?= htmlspecialchars($telefone) ?>" placeholder="(61) 99999-9999">
                                        </div>

                                        <div class="col-12 mb-4">
                                            <label class="form-label">Documento (CPF/CNPJ)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0" data-bs-toggle="tooltip" title="Bloqueado para edição.">
                                                    <i class="bi bi-lock-fill text-muted"></i>
                                                </span>
                                                <input type="text" class="form-control bg-light border-start-0 ps-0" value="<?= htmlspecialchars($cpf_cnpj) ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="col-12 mb-4">
                                            <div class="p-3 border rounded d-flex justify-content-between align-items-center bg-light">
                                                <div>
                                                    <h6 class="mb-1 fw-bold">Segurança da Conta</h6>
                                                    <p class="small text-muted mb-0">Proteja sua conta alterando sua senha regularmente.</p>
                                                </div>
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalSenha">
                                                    Alterar Senha
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column flex-md-row justify-content-end gap-2 mt-2">
                                        <a href="dashboard.php" class="btn btn-light rounded-pill px-4">Voltar</a>
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                            Atualizar Perfil
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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

    <?php include '../view/includes/footerCliente.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

        function previewImage(input) {
            const file = input.files[0];
            const imgPreview = document.getElementById('imgPreview');
            const initialsCircle = document.getElementById('initialsCircle');
            const btnRemove = document.getElementById('btnRemovePhoto');
            const removerInput = document.getElementById('removerFotoInput');

            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert("A imagem é muito grande (Máx 2MB).");
                    input.value = "";
                    return;
                }
                const reader = new FileReader();
                reader.onload = e => {
                    if (imgPreview) {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                    }
                    if (initialsCircle) {
                        initialsCircle.classList.add('d-none');
                    }
                    if (btnRemove) {
                        btnRemove.classList.remove('d-none');
                    }
                    if (removerInput) {
                        removerInput.value = "0";
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
                imgPreview.src = "";
                imgPreview.style.display = 'none';
            }
            if (fotoInput) {
                fotoInput.value = "";
            }
            if (initialsCircle) {
                initialsCircle.classList.remove('d-none');
            }
            if (btnRemove) {
                btnRemove.classList.add('d-none');
            }
            if (removerInput) {
                removerInput.value = "1";
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