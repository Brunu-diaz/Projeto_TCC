<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Evita erros se a página principal esquecer de carregar o usuário
if (!isset($usuario) && isset($_SESSION['id_usuario'])) {
    // Fallback: Se a variável não existe, tentamos buscar no banco rapidamente
    // Isso evita que o header quebre em páginas novas
    require_once __DIR__ . '/../../model/dao/Conexao.php';
    $conn_h = Conexao::getConexao();
    $sql_h = "SELECT nome, foto FROM usuario WHERE id_usuario = :id";
    $stmt_h = $conn_h->prepare($sql_h);
    $stmt_h->bindParam(':id', $_SESSION['id_usuario'], PDO::PARAM_INT);
    $stmt_h->execute();
    $usuario = $stmt_h->fetch(PDO::FETCH_ASSOC);
}

$nome_header = $usuario['nome'] ?? 'Cliente';
$foto_db_header = $usuario['foto'] ?? '';

// 1. Lógica de iniciais
$nome_limpo_h = trim($nome_header);
$partes_nome_h = explode(' ', $nome_limpo_h);
$p_inicial_h = mb_substr($partes_nome_h[0], 0, 1);
$u_inicial_h = (count($partes_nome_h) > 1) ? mb_substr(end($partes_nome_h), 0, 1) : "";
$iniciais_header = strtoupper($p_inicial_h . $u_inicial_h);

// 2. Lógica de Foto
if (!isset($foto_url_header)) {
    $foto_url_header = '';
}
if (!isset($tem_foto_header)) {
    $tem_foto_header = false;
}

if (!empty($foto_url_header) && $tem_foto_header) {
    // Usado quando a página já preparou as variáveis antes do include
} elseif (!empty($foto_db_header)) {
    if (is_string($foto_db_header) && strpos($foto_db_header, 'data:image') === 0) {
        $foto_url_header = $foto_db_header;
        $tem_foto_header = true;
    } else {
        $imagemPath = __DIR__ . '/../../assets/img/perfil/' . $foto_db_header;
        if (file_exists($imagemPath)) {
            $foto_url_header = '../assets/img/perfil/' . rawurlencode($foto_db_header);
            $tem_foto_header = true;
        } else {
            $base64 = base64_encode($foto_db_header);
            if ($base64) {
                $foto_url_header = 'data:image/jpeg;base64,' . $base64;
                $tem_foto_header = true;
            }
        }
    }
}

$listaNotificacoes = [];
$totalNovas = 0;
$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!empty($id_usuario)) {
    try {
        require_once __DIR__ . '/../../model/dao/Conexao.php';
        $pdo = Conexao::getConexao();

        $sql_notificacoes = "SELECT a.*, u.numero, u.bloco, l.data_leitura,
                                  COALESCE(a.tipo, a.descricao, 'Anomalia detectada') AS titulo
                           FROM anomalia a
                           JOIN leitura l ON a.id_leitura = l.id_leitura
                           JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
                           JOIN unidade u ON h.id_unidade = u.id_unidade
                           WHERE u.id_usuario = :id_usuario
                           ORDER BY l.data_leitura DESC
                           LIMIT 5";

        $stmt_notif = $pdo->prepare($sql_notificacoes);
        $stmt_notif->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt_notif->execute();

        $listaNotificacoes = $stmt_notif->fetchAll(PDO::FETCH_ASSOC);
        $totalNovas = count($listaNotificacoes);
    } catch (PDOException $e) {
        $listaNotificacoes = [];
        $totalNovas = 0;
    }
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
    .profile-initials-min {
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
    .profile-img-min {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.8);
    }

    /* Badge Amarela de Edição */
    .edit-badge {
        position: absolute;
        bottom: -1px;
        right: -1px;
        background: #ffc107;
        /* Cor amarela do Bootstrap */
        color: #212529;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
        border: 1.5px solid #0d6efd;
        /* Borda da mesma cor do header para "cortar" visualmente */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Ajustes de Alinhamento da Navbar */
    .navbar-dark .navbar-nav .nav-link {
        color: rgba(255, 255, 255, 0.9);
    }

    /* Alinhamento da lista de navegação para evitar saltos */
    .navbar-nav {
        align-items: center !important;
    }

    /* Links da Navbar - Unificação de altura e padding */
    .navbar-nav .nav-link {
        color: rgba(255, 255, 255, 0.8) !important;
        font-weight: 500;
        font-size: 0.95rem;
        padding: 0.5rem 1rem !important;
        /* Padding lateral fixo */
        transition: all 0.3s ease;
        border-radius: 8px;
    }

    .navbar-nav .nav-link:hover {
        color: #fff !important;
        background: rgba(255, 255, 255, 0.1) !important;
    }

    .navbar-nav .nav-link.active {
        color: #fff !important;
        background: rgba(255, 255, 255, 0.15) !important;
        border-radius: 8px !important;
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

    /* A Badge (Bolinha Vermelha) */
    .custom-notification-badge {
        position: absolute;
        top: 5px;
        right: 5px;

        background-color: #dc3545;
        /* Vermelho do Bootstrap */
        color: #ffffff;

        width: 15px;
        height: 15px;

        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 10px;
        font-weight: 800;
    }

    .dropdown-menu {
        border-radius: 12px !important;
        padding: 0.5rem;
        min-width: 200px;
        border: none;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
        margin-top: 10px !important;
    }
</style>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold" href="dashboard.php">
                <i class="bi bi-droplet-fill me-2"></i> MedidaCerta
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarDashboard">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarDashboard">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item dropdown list-unstyled ms-lg-2">
                        <a class="nav-link position-relative d-inline-flex align-items-center text-white"
                            href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($totalNovas > 0): ?>
                                <span class="custom-notification-badge"><?php echo $totalNovas; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-0 mt-2" style="width: 320px; overflow: hidden;">
                            <li class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                                <span class="fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Notificações</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size: 0.6rem;">
                                    <?php echo $totalNovas; ?> NOVAS
                                </span>
                            </li>
                            <?php if ($totalNovas > 0): ?>
                                <?php foreach ($listaNotificacoes as $notif): ?>
                                    <li>
                                        <a class="dropdown-item p-3 border-bottom d-flex align-items-start gap-3" href="unidadesCliente.php">
                                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; flex-shrink: 0;">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 small fw-bold text-danger"><?= htmlspecialchars($notif['titulo'] ?? ($notif['tipo'] ?? 'Alerta')) ?></p>
                                                <p class="mb-1 text-muted small" style="font-size: 0.8rem;">
                                                    Unidade <?= htmlspecialchars($notif['numero'] ?? '-') ?> - <?= htmlspecialchars($notif['bloco'] ?? '-') ?>
                                                </p>
                                                <small class="text-uppercase fw-bold opacity-50" style="font-size: 0.6rem;">
                                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($notif['data_leitura']))) ?>
                                                </small>
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
                                <a class="dropdown-item small text-center p-2 fw-bold text-primary bg-light" href="notificacoes.php">Ver todas</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="d-flex align-items-center text-decoration-none text-white dropdown-toggle px-2"
                            href="#" id="perfilDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">

                            <div class="profile-circle-wrapper me-2">
                                <?php if (!empty($foto_url_header) && isset($tem_foto_header) && $tem_foto_header): ?>
                                    <img src="<?= htmlspecialchars($foto_url_header) ?>" class="profile-img-min" alt="Perfil">
                                <?php else: ?>
                                    <div class="profile-initials-min"><?= htmlspecialchars($iniciais_header ?? '??') ?></div>
                                <?php endif; ?>
                            </div>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 animate slideIn">
                            <li class="px-3 py-2 d-md-none border-bottom mb-2">
                                <span class="fw-bold text-primary"><?= htmlspecialchars($nome_header) ?></span>
                            </li>
                            <li>
                                <a class="dropdown-item" href="perfil.php">
                                    <i class="bi bi-person-vcard me-3 text-muted"></i>Meu Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="unidadesCliente.php">
                                    <i class="bi bi-building me-3 text-muted"></i>Minhas Unidades
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="configuracoes.php">
                                    <i class="bi bi-gear me-3 text-muted"></i>Configurações
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="ajuda.php">
                                    <i class="bi bi-question-circle me-3"></i>Central de Ajuda
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