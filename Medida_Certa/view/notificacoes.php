<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controller/TravaCliente.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    header('Location: login.php');
    exit;
}

try {
    $conn = Conexao::getConexao();

    // SQL adaptado para buscar apenas as anomalias das unidades do cliente logado
    $sql = "SELECT a.*, u.numero, u.bloco, COALESCE(l.data_leitura, a.data_registro) AS data_exibicao
            FROM anomalia a 
            LEFT JOIN leitura l ON a.id_leitura = l.id_leitura 
            LEFT JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro 
            LEFT JOIN unidade u ON h.id_unidade = u.id_unidade 
            WHERE u.id_usuario = :id_usuario
            ORDER BY COALESCE(l.data_leitura, a.data_registro) DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar notificações cliente: " . $e->getMessage());
    $notificacoes = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Minhas Notificações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/perfil.css">
    <style>
        /* Layout Sticky Footer sem esticar lateral */
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            /* Impede a rolagem horizontal indesejada */
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1 0 auto;
            width: 100%;
        }

        footer {
            flex-shrink: 0;
            width: 100%;
        }

        /* Ajuste do Header (Padrão cliente) */
        .page-header-box {
            margin-top: -30px;
            position: relative;
            z-index: 10;
        }

        /* Impede que a tabela force a largura da página */
        .table-responsive {
            border: none;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .search-wrapper .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25 row rgba(13, 110, 253, 0.1);
        }

        .search-wrapper {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 18px;
            border: 1px solid #e5e7eb;
            margin-bottom: 1.75rem;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        }

        .search-input-group {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-input-group .form-control {
            border-radius: 12px;
            padding-left: 48px;
            height: 50px;
            border: 1px solid #d1d5db;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }

        .search-input-group .bi-search {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .badge-nivel {
            font-size: 0.8rem;
            font-weight: 600;
        }

        .bg-danger-soft {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .bg-warning-soft {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>

<body class="bg-light">

    <?php include_once __DIR__ . '/includes/headerCliente.php'; ?>

    <main class="main-container container mb-5">

        <div class="row py-5">
            <div class="col-12 text-center text-md-start">
                <h4 class="fw-bold text-dark">Notificações</h4>
                <p class="text-muted">Aqui você encontra alertas de anomalias e informações importantes sobre seu consumo.</p>
            </div>
        </div>

        <div class="search-wrapper">
            <div class="search-input-group mx-auto">
                <i class="bi bi-search"></i>
                <input type="text" id="inputPesquisa" class="form-control" placeholder="Pesquisar por tipo, unidade, nível ou descrição...">
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1px solid #e5e7eb;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase text-muted small">
                            <th class="ps-4 py-3">Data</th>
                            <th class="py-3">Unidade</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3">Nível</th>
                            <th class="py-3">Descrição</th>
                            <th class="text-center pe-4 py-3">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaNotificacoes">
                        <?php if (empty($notificacoes)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                                    Nenhuma notificação encontrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notificacoes as $notif):
                                $nivel = strtolower($notif['nivel']);
                                $isCritico = $nivel === 'crítico' || $nivel === 'critico' || $nivel === 'alto';
                                $badgeClass = $isCritico ? 'bg-danger text-white' : 'bg-warning text-dark';

                                // Dados para o filtro de pesquisa
                                $searchText = implode(' ', [
                                    $notif['tipo'],
                                    $notif['descricao'],
                                    $notif['bloco'] ?? '',
                                    $notif['numero'] ?? '',
                                    $notif['nivel']
                                ]);
                                $dataExibicao = $notif['data_exibicao'];
                            ?>
                                <tr class="notificacao-row" data-search="<?= htmlspecialchars(strtolower($searchText), ENT_QUOTES, 'UTF-8') ?>">
                                    <td class="ps-4"><?= date('d/m/Y', strtotime($dataExibicao)) ?></td>
                                    <td>
                                        <span class="fw-bold text-dark">Bloco <?= htmlspecialchars($notif['bloco'] ?? '-') ?></span>
                                        <small class="text-muted">· Un. <?= htmlspecialchars($notif['numero'] ?? '-') ?></small>
                                    </td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($notif['tipo']) ?></td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-2 badge-nivel">
                                            <?= htmlspecialchars($notif['nivel']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small" style="max-width: 300px;"><?= htmlspecialchars($notif['descricao']) ?></td>
                                    <td class="text-center pe-4">
                                        <a href="detalhes_anomalia.php?id=<?= $notif['id_anomalia'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="bi bi-info-circle me-1"></i> Detalhes
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <tr id="avisoVazio" class="d-none">
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-search fs-2 text-muted d-block mb-2"></i>
                                <span class="text-muted">Nenhuma notificação encontrada para esta busca.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footerCliente.php'; ?>

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
    </script>
</body>

</html>