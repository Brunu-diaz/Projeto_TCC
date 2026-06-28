<?php
// Pega apenas o nome do arquivo (ex: unidades.php)
$pagina_atual = basename($_SERVER['PHP_SELF']);

// Lógica para foto do administrador (similar ao headerCliente.php)
if (!isset($foto_url_header)) {
    $foto_url_header = '';
}
if (!isset($tem_foto_header)) {
    $tem_foto_header = false;
}
if (!isset($iniciais_header)) {
    $iniciais_header = 'AD'; // Default para admin
}

if (!$tem_foto_header && isset($_SESSION['id_usuario'])) {
    // Fallback: buscar foto do admin no banco
    require_once __DIR__ . '/../../model/dao/Conexao.php';
    $conn_h = Conexao::getConexao();
    $sql_h = "SELECT nome, foto FROM usuario WHERE id_usuario = :id";
    $stmt_h = $conn_h->prepare($sql_h);
    $stmt_h->bindParam(':id', $_SESSION['id_usuario'], PDO::PARAM_INT);
    $stmt_h->execute();
    $usuario_h = $stmt_h->fetch(PDO::FETCH_ASSOC);

    if ($usuario_h) {
        $nome_limpo_h = trim($usuario_h['nome']);
        $partes_nome_h = explode(' ', $nome_limpo_h);
        $p_inicial_h = mb_substr($partes_nome_h[0], 0, 1);
        $u_inicial_h = (count($partes_nome_h) > 1) ? mb_substr(end($partes_nome_h), 0, 1) : "";
        $iniciais_header = strtoupper($p_inicial_h . $u_inicial_h);

        $foto_db_header = $usuario_h['foto'] ?? '';
        if (!empty($foto_db_header)) {
            if (strpos($foto_db_header, 'data:image') === 0) {
                $foto_url_header = $foto_db_header;
                $tem_foto_header = true;
            } else {
                $imagemPath = __DIR__ . '/../../assets/img/perfil/' . $foto_db_header;
                if (file_exists($imagemPath)) {
                    $foto_url_header = '../assets/img/perfil/' . rawurlencode($foto_db_header);
                    $tem_foto_header = true;
                }
            }
        }
    }
}

$listaNotificacoesAdmin = [];
$totalNotificacoesAdmin = 0;

try {
    if (!isset($conn_h) || !($conn_h instanceof PDO)) {
        require_once __DIR__ . '/../../model/dao/Conexao.php';
        $conn_h = Conexao::getConexao();
    }

    if (isset($conn_h) && $conn_h instanceof PDO) {
        $sqlNotificacoes = "SELECT a.*, u.numero, u.bloco, COALESCE(l.data_leitura, a.data_registro) AS data_exibicao, usu.nome as morador 
                             FROM anomalia a 
                             LEFT JOIN leitura l ON a.id_leitura = l.id_leitura 
                             LEFT JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro 
                             LEFT JOIN unidade u ON h.id_unidade = u.id_unidade 
                             LEFT JOIN usuario usu ON u.id_usuario = usu.id_usuario 
                             ORDER BY COALESCE(l.data_leitura, a.data_registro) DESC 
                             LIMIT 5";
        $stmtNotificacoes = $conn_h->prepare($sqlNotificacoes);
        $stmtNotificacoes->execute();
        $listaNotificacoesAdmin = $stmtNotificacoes->fetchAll(PDO::FETCH_ASSOC);

        $sqlTotal = "SELECT COUNT(*) as total FROM anomalia";
        $totalNotificacoesAdmin = (int)$conn_h->query($sqlTotal)->fetch(PDO::FETCH_ASSOC)['total'];

        $notificacoesVisualizadas = $_SESSION['admin_notificacoes_vistas'] ?? false;
        if ($notificacoesVisualizadas) {
            $totalNotificacoesAdmin = 0;
        }
    }
} catch (PDOException $e) {
    error_log("Erro ao carregar notificações do header admin: " . $e->getMessage());
}
?>
<style>
    /* Container do Círculo de Perfil */
    .profile-circle-wrapper {
        position: relative;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease;
    }

    .profile-circle-wrapper:hover {
        transform: scale(1.05);
    }

    /* Círculo com as Iniciais */
    .profile-circle {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #0d6efd, #004085);
        border: 2px solid #ffffff;
        border-radius: 50%;
        color: #fff;
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* Imagem de Perfil (se houver) */
    .profile-circle-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.8);
    }
    .dropdown-menu {
        border-radius: 12px !important;
        padding: 0.5rem;
        min-width: 200px;
        border: none;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
        margin-top: 10px !important;
    }

    .dropdown-item {
        border-radius: 8px;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }

    .dropdown-item i {
        font-size: 1.1rem;
    }
</style>

<header class="header-gradient pb-5">
    <nav class="navbar navbar-expand-lg navbar-dark pt-3">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="admin.php">
                <i class="bi bi-droplet-fill me-2 text-primary"></i>
                <span class="fs-5">MedidaCerta</span>
                <span class="badge bg-primary rounded-pill text-white ms-2 text-uppercase fw-bold" style="font-size: 0.55rem; padding: 0.35em 0.7em; letter-spacing: 0.5px;">
                    ADMIN
                </span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navPrincipal">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-1">

                    <li class="nav-item me-2">
                        <a class="nav-link d-flex align-items-center px-3 <?php echo ($pagina_atual == 'admin.php') ? 'active' : ''; ?>" href="admin.php">
                            <i class="bi bi-house-door-fill me-1"></i> Visão Geral
                        </a>
                    </li>

                    <li class="nav-item me-2">
                        <a class="nav-link d-flex align-items-center <?php echo ($pagina_atual == 'listarUsuarios.php') ? 'active' : ''; ?>" href="listarUsuarios.php">
                            <i class="bi bi-person-fill me-1"></i> Usuários
                        </a>
                    </li>

                    <li class="nav-item me-2">
                        <a class="nav-link d-flex align-items-center <?php echo ($pagina_atual == 'unidades.php') ? 'active' : ''; ?>" href="unidades.php">
                            <i class="bi bi-building me-1"></i> Unidades
                        </a>
                    </li>

                    <li class="nav-item me-2">
                        <a class="nav-link d-flex align-items-center <?php echo ($pagina_atual == 'listarHidrometros.php') ? 'active' : ''; ?>" href="listarHidrometros.php">
                            <i class="bi bi-speedometer2 me-1"></i> Hidrômetros
                        </a>
                    </li>

                    <li class="nav-item me-2">
                        <a class="nav-link d-flex align-items-center <?php echo ($pagina_atual == 'listarLeituras.php') ? 'active' : ''; ?>" href="listarLeituras.php">
                            <i class="bi bi-clock-history me-1"></i> Leituras
                        </a>
                    </li>

                    <li class="nav-item dropdown list-unstyled ms-lg-2">
                        <a class="nav-link position-relative d-inline-flex align-items-center text-white" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($totalNotificacoesAdmin > 0): ?>
                                <span class="custom-notification-badge"><?= $totalNotificacoesAdmin ?></span>
                            <?php endif; ?>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-0 mt-2" style="width: 320px; overflow: hidden;">
                            <li class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                                <span class="fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Notificações</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size: 0.6rem;">
                                    <?= $totalNotificacoesAdmin ?> NOVAS
                                </span>
                            </li>

                            <?php if (!empty($listaNotificacoesAdmin)): ?>
                                <?php foreach ($listaNotificacoesAdmin as $notif):
                                    $titulo = htmlspecialchars($notif['tipo'] ?? $notif['descricao'] ?? 'Alerta');
                                    $descricao = htmlspecialchars($notif['descricao'] ?? '');
                                    $unidadeTexto = 'Unidade ' . htmlspecialchars($notif['numero'] ?? '-') . ' - Bloco ' . htmlspecialchars($notif['bloco'] ?? '-');
                                    $dataHora = htmlspecialchars(date('d/m/Y H:i', strtotime($notif['data_exibicao'] ?? $notif['data_registro'] ?? 'now')));
                                ?>
                                    <li>
                                        <a class="dropdown-item p-3 border-bottom d-flex align-items-start gap-3" href="notificacoesAdmin.php">
                                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; flex-shrink: 0;">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 small fw-bold text-danger"><?= $titulo ?></p>
                                                <p class="mb-1 text-muted small" style="font-size: 0.8rem;"><?= $unidadeTexto ?></p>
                                                <small class="text-uppercase fw-bold opacity-50" style="font-size: 0.6rem;"><?= $dataHora ?></small>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="p-4 text-center text-muted small">
                                    Nenhuma notificação encontrada.
                                </li>
                            <?php endif; ?>

                            <li>
                                <a class="dropdown-item small text-center p-2 fw-bold text-primary bg-light" href="notificacoesAdmin.php">Ver todas</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown ms-lg-3">
                        <a class="d-flex align-items-center text-decoration-none text-white dropdown-toggle px-2" href="#" data-bs-toggle="dropdown">
                            <div class="profile-circle-wrapper me-2">
                                <?php if ($tem_foto_header): ?>
                                    <img src="<?= htmlspecialchars($foto_url_header, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de Perfil" class="profile-circle-img">
                                <?php else: ?>
                                    <div class="profile-circle"><?= htmlspecialchars($iniciais_header, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 animate slideIn" style="border-radius: 12px;">
                            <li class="px-3 py-2 d-md-none border-bottom mb-2">
                                <span class="fw-bold text-primary"><?= htmlspecialchars($nome_header) ?></span>
                            </li>

                            <li>
                                <a class="dropdown-item" href="perfilAdmin.php">
                                    <i class="bi bi-person-vcard me-3 text-muted"></i>Meu Perfil
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="admin_beneficios.php">
                                    <i class="bi bi-patch-check me-3 text-muted"></i>Benefícios
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="configuracoesAdmin.php">
                                    <i class="bi bi-gear me-3 text-muted"></i>Configurações
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="suporte.php">
                                    <i class="bi bi-headset me-3 text-muted"></i>Suporte Técnico
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider opacity-50">
                            </li>

                            <li>
                                <a class="dropdown-item text-danger" href="../controller/logout.php">
                                    <i class="bi bi-box-arrow-right me-3"></i>Sair
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>