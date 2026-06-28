<?php
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Segurança: Apenas ADM
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: login.php");
    exit;
}

try {
    $pdo = Conexao::getConexao();

    // Ajustado para 'codigo' e 'status' conforme a imagem do seu banco
    $sql = "SELECT 
            h.id_hidrometro, 
            h.codigo, 
            h.modelo, 
            h.status, 
            u.bloco, 
            u.numero as num_unidade, 
            usr.nome as nome_usuario 
        FROM hidrometro h
        LEFT JOIN unidade u ON h.id_unidade = u.id_unidade
        LEFT JOIN usuario usr ON u.id_usuario = usr.id_usuario
        ORDER BY 
            CASE WHEN u.bloco IS NULL THEN 1 ELSE 0 END, -- Joga os sem unidade para o fim da lista
            u.bloco, 
            u.numero ASC";

    $listaHidrometros = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao carregar hidrômetros: " . $e->getMessage());
    die("Erro ao processar sua solicitação.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Gestão de Hidrômetros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        /* Estilos originais mantidos */
        .list-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

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

        .search-input-group .bi-search {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            color: #a0aec0;
            font-size: 1.1rem;
        }

        .serial-badge {
            font-family: 'Monaco', 'Consolas', monospace;
            letter-spacing: 1px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
        }

        .alert-floating-container {
            position: fixed;
            top: 25px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1060;
            width: auto;
        }

        .alert-compacto {
            background: #ffffff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 500;
            color: #198754;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .alert-compacto-erro {
            color: #dc3545;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Envolvemos todo o conteúdo superior em uma div para o Flexbox funcionar -->
    <?php include '../view/includes/header.php'; ?>

    <!-- Alertas Flutuantes -->
    <div class="alert-floating-container">
        <?php if (isset($_GET['msg'])): ?>
            <?php
            $msg = $_GET['msg'];
            $isErro = (strpos($msg, 'erro') !== false);

            // Mapeamento de mensagens amigáveis
            $textoExibir = "Operação realizada!";
            if ($msg == 'sucesso_update') $textoExibir = "Hidrômetro atualizado com sucesso!";
            if ($msg == 'excluido') $textoExibir = "Hidrômetro removido com sucesso!";
            if ($isErro) $textoExibir = "Erro ao processar solicitação.";
            if ($msg == 'erro_dependencia') $textoExibir = "Não é possível excluir: existem leituras vinculadas a este aparelho.";
            ?>

            <div class="alert alert-compacto <?= $isErro ? 'alert-compacto-erro' : '' ?> fade show" id="alertaFlutuante">
                <i class="bi <?= $isErro ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' ?> me-2"></i>
                <?= $textoExibir ?>
            </div>
        <?php endif; ?>
    </div>

    <main class="main-container container mb-5">
        <!-- Header da Página -->
        <div class="page-header-box mb-4">
            <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Gestão de Hidrômetros</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-primary">Admin</a></li>
                            <li class="breadcrumb-item active">Hidrômetros</li>
                        </ol>
                    </nav>
                </div>
                <a href="cadastrarHidrometro.php" class="btn btn-primary rounded-3 px-4 shadow-sm d-flex align-items-center fw-bold" style="height: 42px; font-size: 0.9rem;">
                    <i class="bi bi-plus-lg me-2"></i> Novo Hidrômetro
                </a>
            </div>
        </div>

        <!-- Barra de Busca -->
        <div class="search-wrapper">
            <div class="search-input-group">
                <i class="bi bi-search"></i>
                <input type="text" id="inputPesquisa" class="form-control"
                    placeholder="Pesquisar por serial, bloco ou proprietário...">
            </div>
        </div>

        <!-- Tabela Estilizada -->
        <div class="list-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr style="font-size: 0.85rem;" class="text-muted text-uppercase">
                            <th class="ps-4 py-3">Código Serial</th>
                            <th class="py-3">Modelo</th>
                            <th class="py-3">Unidade / Bloco</th>
                            <th class="py-3">Proprietário</th>
                            <th class="py-3">Status</th>
                            <th class="text-center pe-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaCorpo">
                        <?php if (empty($listaHidrometros)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">Nenhum hidrômetro cadastrado.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($listaHidrometros as $h): ?>
                            <tr class="hidrometro-item">
                                <td class="ps-4">
                                    <span class="badge serial-badge px-3 py-2 rounded-pill">
                                        <i class="bi bi-upc-scan me-2 text-primary"></i><?= htmlspecialchars($h['codigo']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-dark small fw-medium">
                                        <?= htmlspecialchars($h['modelo'] ?? 'N/A') ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">Apt: <?= $h['num_unidade'] ?></div>
                                    <div class="text-muted small">Bloco: <?= $h['bloco'] ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($h['nome_usuario'])): ?>
                                            <!-- Caso tenha usuário -->
                                            <div class="bg-primary-subtle text-primary rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-person-fill small"></i>
                                            </div>
                                            <span class="fw-bold text-dark nome-alvo">
                                                <?= htmlspecialchars($h['nome_usuario']) ?>
                                            </span>
                                        <?php else: ?>
                                            <!-- Caso NÃO tenha usuário -->
                                            <div class="bg-light text-muted rounded-circle p-2 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px dashed #ccc;">
                                                <i class="bi bi-person-dash small"></i>
                                            </div>
                                            <span class="text-muted fst-italic">
                                                Não vinculado
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <!-- Lógica para o ENUM 'status' -->
                                    <?php if ($h['status'] === 'Ativo'): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3">
                                            <i class="bi bi-check-circle me-1"></i> Ativo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3">
                                            <i class="bi bi-x-circle me-1"></i> Inativo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center ps-4">
                                    <form action="../controller/StatusHidrometroControl.php" method="POST" id="formStatus<?= $h['id_hidrometro'] ?>" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $h['id_hidrometro'] ?>">
                                        <!-- Inverte o status atual -->
                                        <input type="hidden" name="novo_status" value="<?= $h['status'] === 'Ativo' ? 'Inativo' : 'Ativo' ?>">

                                        <button type="button"
                                            class="btn btn-sm <?= $h['status'] === 'Ativo' ? 'btn-outline-warning' : 'btn-outline-success' ?> rounded-circle shadow-sm"
                                            onclick="confirmarTrocaStatus(<?= $h['id_hidrometro'] ?>, '<?= $h['codigo'] ?>', '<?= $h['status'] === 'Ativo' ? 'desativar' : 'ativar' ?>')">
                                            <i class="bi <?= $h['status'] === 'Ativo' ? 'bi-power' : 'bi-play-fill' ?>"></i>
                                        </button>
                                    </form>
                                    <a href="editarHidrometro.php?id=<?= $h['id_hidrometro'] ?>"
                                        class="btn btn-sm btn-light rounded-circle shadow-sm border"
                                        title="Editar Hidrômetro">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger rounded-circle shadow-sm ms-1"
                                        onclick="confirmarExclusao(<?= $h['id_hidrometro'] ?>, '<?= $h['codigo'] ?>')"
                                        title="Excluir Hidrômetro">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </main>
    <?php include '../view/includes/footer.php'; ?>

    <!-- Modal de Exclusão Único -->
    <div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px;">
                <div class="modal-body p-4 text-center">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-octagon-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold">Confirmar Exclusão</h5>
                    <p class="text-secondary">
                        Deseja excluir o hidrômetro <strong id="codigoHidrometroModal" class="text-dark"></strong>?
                    </p>

                    <form action="../controller/ExcluirHidrometroControl.php" method="POST" class="mt-4">
                        <!-- O JS vai preencher este valor -->
                        <input type="hidden" name="id_hidrometro" id="idHidrometroModal">

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light w-100 rounded-3" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger w-100 rounded-3">Excluir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Status (Específico) -->
    <div class="modal fade" id="modalConfirmacaoStatus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <div class="modal-body p-4 text-center">
                    <h5 class="fw-bold mb-2" id="modalTituloStatus">Mudar Status</h5>
                    <p class="text-muted mb-4" id="modalMensagemStatus"></p>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-3 flex-grow-1" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="btnConfirmarStatus" class="btn btn-primary rounded-3 flex-grow-1">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('inputPesquisa').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let linhas = document.querySelectorAll('.hidrometro-item');

            linhas.forEach(linha => {
                let texto = linha.innerText.toLowerCase();
                linha.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });

        function confirmarExclusao(id, codigo) {
            // 1. Preenche o ID no input oculto do formulário
            document.getElementById('idHidrometroModal').value = id;

            // 2. Coloca o código do hidrômetro no texto para o usuário ver
            document.getElementById('codigoHidrometroModal').innerText = codigo;

            // 3. Abre o modal manualmente via Bootstrap
            const meuModal = new bootstrap.Modal(document.getElementById('modalExcluir'));
            meuModal.show();
        }

        function confirmarTrocaStatus(id, serial, acao) {
            // Usando o novo ID 'modalConfirmacaoStatus'
            const modalElement = document.getElementById('modalConfirmacaoStatus');
            const modal = new bootstrap.Modal(modalElement);

            const titulo = document.getElementById('modalTituloStatus');
            const mensagem = document.getElementById('modalMensagemStatus');
            const btnConfirmar = document.getElementById('btnConfirmarStatus');

            if (acao === 'desativar') {
                titulo.innerText = "Desativar Hidrômetro";
                mensagem.innerHTML = `Deseja realmente <strong>desativar</strong> o hidrômetro <strong>${serial}</strong>?`;
                btnConfirmar.className = "btn btn-warning rounded-3 flex-grow-1 fw-bold";
            } else {
                titulo.innerText = "Ativar Hidrômetro";
                mensagem.innerHTML = `Deseja <strong>reativar</strong> o hidrômetro <strong>${serial}</strong>?`;
                btnConfirmar.className = "btn btn-success rounded-3 flex-grow-1 fw-bold";
            }

            // O segredo está aqui: submeter o form correto
            btnConfirmar.onclick = function() {
                document.getElementById('formStatus' + id).submit();
            };

            modal.show();
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerta = document.getElementById('alertaFlutuante');
            if (alerta) {
                // Limpa a URL sem recarregar a página
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('msg');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                }

                // Efeito de saída (sobe e some)
                setTimeout(() => {
                    alerta.style.transition = "opacity 0.6s ease, transform 0.6s ease";
                    alerta.style.opacity = "0";
                    alerta.style.transform = "translateY(-20px)";
                    setTimeout(() => alerta.remove(), 600);
                }, 3000);
            }
        });
    </script>

</body>

</html>