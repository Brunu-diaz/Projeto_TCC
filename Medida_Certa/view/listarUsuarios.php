<?php
// 1. Segurança e Importações
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';

// Segurança: Apenas ADM acessa
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: login.php");
    exit;
}

// 2. Busca de Dados Inicial (Traz todos, o JS filtra depois)
$usuarioDAO = new UsuarioDAO();
$listaUsuarios = $usuarioDAO->listarUsuarios();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Gestão de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        /* --- ESTILIZAÇÃO DO FILTRO (IGUAL UNIDADES.PHP) --- */
        .search-wrapper {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 15px;
            border: 1px solid #edf2f7;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }

        .search-input-group {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }

        .search-input-group .form-control {
            border-radius: 12px;
            padding-left: 45px;
            height: 45px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }

        .search-input-group .form-control:focus {
            background-color: #fff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .search-input-group .bi-search {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            color: #a0aec0;
            font-size: 1.1rem;
        }

        .avatar-sm {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .x-small {
            font-size: 0.75rem;
        }

        /* Container que centraliza o alerta no topo da tela */
        .alert-floating-container {
            position: fixed;
            top: 25px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1060;
            width: auto;
        }

        /* Estilo "Pílula" igual ao do vídeo */
        .alert-compacto {
            background: #ffffff;
            border: none;
            border-left: 4px solid #198754;
            /* Verde para sucesso */
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 50px;
            /* Faz o formato arredondado */
            padding: 10px 25px;
            font-weight: 500;
            color: #198754;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .alert-compacto-erro {
            border-left-color: #dc3545;
            color: #dc3545;
        }
    </style>
</head>

<body class="bg-light">

    <div class="alert-floating-container">
    <?php if (isset($_GET['msg']) && $_GET['msg'] !== 'erro'): ?>
        <div class="alert alert-compacto fade show" id="alertaFlutuante">
            <i class="bi bi-check-circle-fill me-2"></i> 
            <?= htmlspecialchars($_GET['msg'] == 'sucesso' ? 'Operação realizada!' : $_GET['msg']) ?>
        </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'erro'): ?>
        <div class="alert alert-compacto alert-compacto-erro fade show" id="alertaFlutuante">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Erro ao processar solicitação.
        </div>
    <?php endif; ?>
</div>

    <?php include '../view/includes/header.php'; ?>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Gestão de Usuários</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item active">Usuários</li>
                    </ol>
                </nav>
            </div>

            <a href="editarUsuario.php" class="btn btn-primary rounded-3 px-4 shadow-sm d-flex align-items-center fw-bold" style="height: 42px; font-size: 0.9rem;">
                <i class="bi bi-person-plus-fill me-2"></i>Novo Usuário
            </a>
        </div>
    </div>

    <main class="main-container container mb-5">

        <div class="search-wrapper">
            <div class="search-input-group">
                <i class="bi bi-search"></i>
                <input type="text" id="inputPesquisa" class="form-control"
                    placeholder="Pesquisar por nome, e-mail ou perfil...">
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1px solid #f1f5f9 !important;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th class="ps-4 py-3 text-muted">Usuário</th>
                            <th class="py-3 text-muted">Contato</th>
                            <th class="py-3 text-muted">Perfil</th>
                            <th class="py-3 text-muted">Status</th>
                            <th class="text-center pe-4 py-3 text-muted">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaUsuarios">
                        <?php if (empty($listaUsuarios)): ?>
                            <tr class="sem-dados">
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    Nenhum usuário cadastrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaUsuarios as $u): ?>
                                <tr class="usuario-row <?= $u['ativo'] == 0 ? 'opacity-75' : '' ?>">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle text-primary rounded-circle me-3">
                                                <i class="bi bi-person-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark nome-alvo"><?= htmlspecialchars($u['nome']) ?></div>
                                                <div class="text-muted x-small">Desde: <?= $u['data_formatada'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small email-alvo"><i class="bi bi-envelope me-1 text-muted"></i> <?= htmlspecialchars($u['email']) ?></div>
                                        <div class="small text-muted"><i class="bi bi-whatsapp me-1"></i> <?= htmlspecialchars($u['telefone']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill perfil-alvo">
                                            <?= htmlspecialchars($u['nome_perfil']) ?>
                                        </span>
                                    </td>
                                    <td>
    <?php if ($u['bloqueado'] == 1): ?>
        <span class="badge bg-danger text-white rounded-pill px-3">
            <i class="bi bi-lock-fill me-1"></i> Suspenso
        </span>
    <?php elseif ($u['ativo'] == 0): ?>
        <span class="badge bg-warning text-dark rounded-pill px-3">
            <i class="bi bi-clock-history me-1"></i> Pendente
        </span>
    <?php else: ?>
        <span class="badge bg-success-subtle text-success rounded-pill px-3">
            <i class="bi bi-check-circle me-1"></i> Ativo
        </span>
    <?php endif; ?>
</td>
                                    <td class="text-center pe-4">
    <form action="../controller/StatusUsuarioControl.php" method="POST" id="formStatus<?= $u['id_usuario'] ?>" style="display:inline;">
        <input type="hidden" name="id" value="<?= $u['id_usuario'] ?>">
        <input type="hidden" name="status" value="<?= $u['bloqueado'] == 1 ? '0' : '1' ?>">
        
        <button type="button" 
            class="btn btn-sm <?= $u['bloqueado'] == 1 ? 'btn-danger' : 'btn-outline-danger' ?> rounded-circle shadow-sm"
            onclick="confirmarTrocaStatus(<?= $u['id_usuario'] ?>, '<?= addslashes($u['nome']) ?>', '<?= $u['bloqueado'] == 1 ? 'ativar' : 'desativar' ?>')"
            title="<?= $u['bloqueado'] == 1 ? 'Desbloquear Usuário' : 'Suspender Usuário' ?>">
            <i class="bi <?= $u['bloqueado'] == 1 ? 'bi-unlock-fill' : 'bi-person-x-fill' ?>"></i>
        </button>
    </form>

    <a href="editarUsuario.php?id=<?= $u['id_usuario'] ?>&modo=editar"
        class="btn btn-sm btn-light rounded-circle shadow-sm ms-1 border" title="Editar Dados">
        <i class="bi bi-pencil-square text-primary"></i>
    </a>
</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <tr id="avisoVazio" class="d-none">
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-search fs-2 text-muted d-block mb-2"></i>
                                <span class="text-muted">Nenhum usuário encontrado para esta busca.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('inputPesquisa');
            const rows = document.querySelectorAll('.usuario-row');
            const emptyNotice = document.getElementById('avisoVazio');

            // LÓGICA DE FILTRO IGUAL UNIDADES (TEMPO REAL)
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                let hasResults = false;

                rows.forEach(row => {
                    const textoLinha = row.textContent.toLowerCase();
                    if (textoLinha.includes(query)) {
                        row.classList.remove('d-none');
                        hasResults = true;
                    } else {
                        row.classList.add('d-none');
                    }
                });

                // Mostrar aviso se não encontrar nada
                if (!hasResults && query !== "") {
                    emptyNotice.classList.remove('d-none');
                } else {
                    emptyNotice.classList.add('d-none');
                }
            });
        });

        // Modal de Confirmação
        function confirmarTrocaStatus(id, nome, acao) {
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
    const titulo = document.getElementById('modalTitulo');
    const mensagem = document.getElementById('modalMensagem');
    const btnConfirmar = document.getElementById('btnConfirmarModal');

    if (acao === 'desativar') {
        titulo.innerText = "Confirmar Suspensão";
        mensagem.innerHTML = `Deseja realmente <strong>suspender</strong> o acesso de <strong>${nome}</strong>? O usuário não conseguirá logar no MedidaCerta.`;
        btnConfirmar.className = "btn btn-danger rounded-3 px-4";
        btnConfirmar.innerText = "Confirmar Bloqueio";
    } else {
        titulo.innerText = "Liberar Acesso";
        mensagem.innerHTML = `Deseja <strong>restaurar</strong> o acesso de <strong>${nome}</strong>?`;
        btnConfirmar.className = "btn btn-success rounded-3 px-4";
        btnConfirmar.innerText = "Confirmar Liberação";
    }

    btnConfirmar.onclick = () => document.getElementById('formStatus' + id).submit();
    modal.show();
}
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerta = document.getElementById('alertaFlutuante');

            if (alerta) {
                // 1. Limpa o parâmetro 'msg' da URL sem recarregar a página
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('msg');
                    window.history.replaceState({}, document.title, url.pathname);
                }

                // 2. Aguarda 3 segundos e faz o alerta sumir suavemente
                setTimeout(() => {
                    alerta.style.transition = "opacity 0.6s ease, transform 0.6s ease";
                    alerta.style.opacity = "0";
                    alerta.style.transform = "translateY(-20px)"; // Efeito de subir ao sumir

                    setTimeout(() => alerta.remove(), 600);
                }, 3000);
            }
        });
    </script>

    <div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" id="modalTitulo">Confirmar Ação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4">
                    <p id="modalMensagem" class="text-muted"></p>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnConfirmarModal" class="btn btn-danger rounded-3 px-4 shadow-sm">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>