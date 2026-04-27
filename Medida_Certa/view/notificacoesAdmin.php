<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

try {
    $conn = Conexao::getConexao();
    
    $sql = "SELECT a.*, u.numero, u.bloco, COALESCE(l.data_leitura, a.data_registro) AS data_exibicao, usu.nome as morador 
            FROM anomalia a 
            LEFT JOIN leitura l ON a.id_leitura = l.id_leitura 
            LEFT JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro 
            LEFT JOIN unidade u ON h.id_unidade = u.id_unidade 
            LEFT JOIN usuario usu ON u.id_usuario = usu.id_usuario 
            ORDER BY COALESCE(l.data_leitura, a.data_registro) DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_SESSION['admin_notificacoes_vistas'] = true;
} catch (PDOException $e) {
    error_log("Erro ao buscar notificações admin: " . $e->getMessage());
    $notificacoes = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Notificações Administrativas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; color: #1f2937; }
        .page-header-box { border-radius: 16px; margin-top: -45px; position: relative; z-index: 10; }
        .breadcrumb-item a { color: #0d6efd; text-decoration: none; }
        .breadcrumb-item.active { color: #6b7280; }
        .search-wrapper { background-color: #ffffff; border-radius: 16px; padding: 18px; border: 1px solid #e5e7eb; margin-bottom: 1.75rem; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04); }
        .search-input-group { position: relative; max-width: 600px; margin: 0 auto; }
        .search-input-group .form-control { border-radius: 12px; padding-left: 48px; height: 50px; border: 1px solid #d1d5db; background-color: #f8fafc; transition: all 0.2s ease; }
        .search-input-group .form-control:focus { background-color: #ffffff; border-color: #0d6efd; box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); }
        .search-input-group .bi-search { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 5; color: #94a3b8; font-size: 1.1rem; }
        .card-notif { border-radius: 18px; border: 1px solid #e5e7eb; transition: transform .2s ease, box-shadow .2s ease; background: #ffffff; }
        .card-notif:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08); }
        .badge-unidade { background-color: #f1f5f9; color: #334155; font-weight: 600; }
        .icon-box { width: 52px; height: 52px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; }
        .bg-danger-soft { background-color: #fee2e2; color: #b91c1c; }
        .bg-warning-soft { background-color: #fef3c7; color: #92400e; }
        .table-notif th, .table-notif td { vertical-align: middle; }
    </style>
</head>
<body class="bg-light">

    <?php include_once __DIR__ . '/includes/header.php'; ?>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Notificações</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item active">Notificações</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-primary rounded-pill py-2 px-3">Total de alertas: <?= count($notificacoes) ?></span>
                <a href="admin.php" class="btn btn-outline-secondary rounded-pill">Voltar ao Painel</a>
            </div>
        </div>
    </div>

    <main class="main-container container mb-5">
                </div>
            </div>
        </div>

        <div class="search-wrapper">
            <div class="search-input-group mx-auto">
                <i class="bi bi-search"></i>
                <input type="text" id="inputPesquisa" class="form-control" placeholder="Pesquisar por tipo, bloco, unidade, morador, nível ou descrição...">
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1px solid #e5e7eb;">
            <div class="table-responsive">
                <table class="table table-hover table-notif align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase text-muted small">
                            <th class="ps-4 py-3">Data</th>
                            <th class="py-3">Unidade</th>
                            <th class="py-3">Morador</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3">Nível</th>
                            <th class="py-3">Descrição</th>
                            <th class="text-center pe-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaNotificacoes">
                        <?php if (empty($notificacoes)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                                    Nenhuma notificação encontrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notificacoes as $notif): 
                                $isCritico = strtolower($notif['nivel']) === 'crítico' || strtolower($notif['nivel']) === 'critico';
                                $badgeClass = $isCritico ? 'bg-danger text-white' : 'bg-warning text-dark';
                                $searchText = implode(' ', [
                                    $notif['tipo'], $notif['descricao'], $notif['bloco'] ?? '', $notif['numero'] ?? '', $notif['morador'] ?? '', $notif['nivel']
                                ]);
                                $dataExibicao = $notif['data_exibicao'] ?? $notif['data_registro'] ?? null;
                            ?>
                                <tr class="notificacao-row" data-search="<?= htmlspecialchars(strtolower($searchText), ENT_QUOTES, 'UTF-8') ?>">
                                    <td class="ps-4"><?= $dataExibicao ? date('d/m/Y', strtotime($dataExibicao)) : '-' ?></td>
                                    <td>Bloco <?= htmlspecialchars($notif['bloco'] ?? '-') ?> · <?= htmlspecialchars($notif['numero'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($notif['morador'] ?? 'Desconhecido') ?></td>
                                    <td><?= htmlspecialchars($notif['tipo']) ?></td>
                                    <td><span class="badge <?= $badgeClass ?> rounded-pill px-3 py-2" style="font-size:0.8rem;"><?= htmlspecialchars($notif['nivel']) ?></span></td>
                                    <td class="text-truncate" style="max-width: 380px;"><?= htmlspecialchars($notif['descricao']) ?></td>
                                    <td class="text-center pe-4">
                                        <a href="exibir_detalhes_alerta.php?id=<?= $notif['id_anomalia'] ?>" class="btn btn-sm btn-outline-primary rounded-pill me-1">
                                            <i class="bi bi-eye me-1"></i> Ver
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="arquivarNotificacao(this)">
                                            <i class="bi bi-archive me-1"></i> Arquivar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr id="avisoVazio" class="d-none">
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-search fs-2 text-muted d-block mb-2"></i>
                                <span class="text-muted">Nenhuma notificação encontrada para esta busca.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('inputPesquisa');
            const rows = document.querySelectorAll('.notificacao-row');
            const emptyNotice = document.getElementById('avisoVazio');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                let hasResults = false;

                rows.forEach(row => {
                    const textoLinha = row.dataset.search;
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

        function arquivarNotificacao(button) {
            button.classList.add('disabled');
            button.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Arquivado';
        }
    </script>
</body>
</html>