<?php
// 1. Segurança e Importações
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Segurança: Apenas ADM acessa
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: login.php");
    exit;
}

// 2. Busca de Dados Inicial
try {
    $pdo = Conexao::getConexao();
    // Query trazendo dados da unidade e serial do hidrômetro para o filtro funcionar bem
    $sql = "SELECT l.*, h.codigo as h_serial, u.bloco, u.numero, usr.nome as responsavel 
            FROM leitura l
            JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
            JOIN unidade u ON h.id_unidade = u.id_unidade
            JOIN usuario usr ON u.id_usuario = usr.id_usuario
            ORDER BY l.data_leitura DESC, l.id_leitura DESC";

    $listaLeituras = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $listaLeituras = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Gestão de Leituras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        /* Reutilizando os estilos idênticos ao listarusuarios.php */
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

        /* Estilo específico para destacar o consumo */
        .consumo-destaque {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0d6efd;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Alertas Flutuantes -->
    <div class="alert-floating-container">
        <?php if (isset($_GET['msg']) && $_GET['msg'] !== 'erro'): ?>
            <div class="alert alert-compacto fade show" id="alertaFlutuante">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($_GET['msg'] == 'sucesso' ? 'Operação realizada!' : $_GET['msg']) ?>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'erro'): ?>
            <div class="alert alert-compacto alert-compacto-erro fade show" id="alertaFlutuante">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Erro ao processar leitura.
            </div>
        <?php endif; ?>
    </div>

    <?php include '../view/includes/header.php'; ?>

    <!-- Cabeçalho da Página -->
    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Histórico de Leituras</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-primary">Admin</a></li>
                        <li class="breadcrumb-item active">Leituras</li>
                    </ol>
                </nav>
            </div>

            <a href="cadastrarLeitura.php" class="btn btn-primary rounded-3 px-4 shadow-sm d-flex align-items-center fw-bold" style="height: 42px; font-size: 0.9rem;">
                <i class="bi bi-plus-lg me-2"></i>Nova Leitura
            </a>
        </div>
    </div>

    <main class="main-container container mb-5">

        <!-- Barra de Pesquisa -->
        <div class="search-wrapper">
            <div class="search-input-group">
                <i class="bi bi-search"></i>
                <input type="text" id="inputPesquisa" class="form-control"
                    placeholder="Pesquisar por endereço, bloco, apartamento ou serial...">
            </div>
        </div>

        <!-- Tabela de Leituras -->
        <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1px solid #f1f5f9 !important;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th class="ps-4 py-3 text-muted">Unidade / Imóvel</th>
                            <th class="py-3 text-muted">Hidrômetro</th>
                            <th class="py-3 text-muted">Período</th>
                            <th class="py-3 text-muted">Consumo (m³)</th>
                            <th class="text-center pe-4 py-3 text-muted">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaLeituras">
                        <?php if (empty($listaLeituras)): ?>
                            <tr class="sem-dados">
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                    Nenhuma leitura registrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaLeituras as $l): ?>
                                <tr class="leitura-row">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary-subtle text-primary p-2 rounded-3 me-3" style="width: 40px; height: 40px; display:flex; align-items:center; justify-content:center;">
                                                <i class="bi bi-house-door-fill"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark nome-alvo"><?= htmlspecialchars($l['responsavel']) ?></div>
                                                <div class="text-muted x-small">Bloco: <?= $l['bloco'] ?> | Apt: <?= $l['numero'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill">
                                            <i class="bi bi-upc-scan me-1"></i> <?= htmlspecialchars($l['h_serial']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?= date('d/m/Y', strtotime($l['data_leitura'])) ?></div>
                                        <div class="text-muted x-small"><?= $l['mes_referencia'] ?>/<?= $l['ano_referencia'] ?></div>
                                    </td>
                                    <td>
                                        <div class="consumo-destaque">+ <?= number_format($l['consumo_calculado'], 2, ',', '.') ?></div>
                                        <div class="text-muted x-small">Leitura: <?= number_format($l['valor_medido'], 2, ',', '.') ?> m³</div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="editarLeitura.php?id=<?= $l['id_leitura'] ?>"
                                            class="btn btn-sm btn-light rounded-circle shadow-sm border" title="Editar Leitura">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>

                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger rounded-circle shadow-sm ms-1"
                                            onclick="confirmarExclusao(<?= $l['id_leitura'] ?>, '<?= $l['bloco'] ?> - <?= $l['numero'] ?>')"
                                            title="Excluir Leitura">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <tr id="avisoVazio" class="d-none">
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-search fs-2 text-muted d-block mb-2"></i>
                                <span class="text-muted">Nenhuma leitura encontrada para esta busca.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Modal de Confirmação de Exclusão (O QUE ESTAVA FALTANDO) -->
        <div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-labelledby="modalConfirmacaoLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="modalConfirmacaoLabel">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div id="modalMensagem">
                            <!-- O JavaScript vai injetar o texto aqui -->
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="button" class="btn btn-light flex-grow-1 rounded-3 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" id="btnConfirmarModal" class="btn btn-danger flex-grow-1 rounded-3 fw-bold">Sim, Excluir</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('inputPesquisa');
            const rows = document.querySelectorAll('.leitura-row');
            const emptyNotice = document.getElementById('avisoVazio');

            // Filtro em tempo real igual ao de usuários
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

                if (!hasResults && query !== "") {
                    emptyNotice.classList.remove('d-none');
                } else {
                    emptyNotice.classList.add('d-none');
                }
            });
        });

        // Lógica do Alerta (Sobe e some)
        document.addEventListener('DOMContentLoaded', function() {
            const alerta = document.getElementById('alertaFlutuante');
            if (alerta) {
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('msg');
                    window.history.replaceState({}, document.title, url.pathname);
                }
                setTimeout(() => {
                    alerta.style.transition = "opacity 0.6s ease, transform 0.6s ease";
                    alerta.style.opacity = "0";
                    alerta.style.transform = "translateY(-20px)";
                    setTimeout(() => alerta.remove(), 600);
                }, 3000);
            }
        });

        function confirmarExclusao(id, unidade) {
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));

            // Texto de alerta mais enfático
            document.getElementById('modalMensagem').innerHTML = `
        <div class="text-center">
            <i class="bi bi-exclamation-triangle text-danger fs-1 mb-3"></i>
            <p>Deseja realmente excluir a leitura da unidade <strong>${unidade}</strong>?</p>
            <div class="alert alert-warning py-2 mb-0" style="font-size: 0.85rem;">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Atenção:</strong> A fatura e anomalias relacionadas a esta medição também serão <strong>removidas permanentemente</strong>.
            </div>
        </div>
    `;

            const btnConfirmar = document.getElementById('btnConfirmarModal');
            btnConfirmar.classList.replace('btn-primary', 'btn-danger'); // Deixa o botão vermelho
            btnConfirmar.innerText = "Sim, Excluir";

            btnConfirmar.onclick = () => {
                window.location.href = `../controller/ExcluirLeituraControl.php?id=${id}`;
            };

            modal.show();
        }
    </script>
</body>

</html>