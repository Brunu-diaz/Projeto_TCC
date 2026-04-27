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
    $sql = "SELECT a.*, u.numero, u.bloco, l.data_leitura FROM anomalia a JOIN leitura l ON a.id_leitura = l.id_leitura JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro JOIN unidade u ON h.id_unidade = u.id_unidade WHERE u.id_usuario = :id_usuario ORDER BY l.data_leitura DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $notificacoes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Notificações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/perfil.css">
</head>

<body>
    <?php include_once __DIR__ . '/includes/headerCliente.php'; ?>

    <main class="container py-5">
        <div class="row mb-4">
            <div class="col-12 text-center text-md-start">
                <h4 class="fw-bold text-dark">Notificações</h4>
                <p class="text-muted">Aqui você encontra alertas de anomalias e informações importantes sobre seu consumo.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 p-3 p-md-4" style="border-radius: 16px;">
                    <div class="card-body">
                        <?php if (!empty($notificacoes)): ?>
                            <div class="list-group">
                                <?php foreach ($notificacoes as $notif): ?>
                                    <div class="list-group-item list-group-item-action mb-3 rounded-4 border">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold text-danger"><?= htmlspecialchars($notif['tipo'] ?? 'Alerta') ?></h6>
                                                        <p class="mb-1 text-muted small">Unidade <?= htmlspecialchars($notif['numero'] ?? '-') ?> - <?= htmlspecialchars($notif['bloco'] ?? '-') ?></p>
                                                    </div>
                                                    <small class="text-muted text-nowrap"><?= htmlspecialchars(date('d/m/Y', strtotime($notif['data_leitura'] ?? date('Y-m-d')))) ?></small>
                                                </div>
                                                <?php if (!empty($notif['descricao'])): ?>
                                                    <p class="mb-0 text-muted small"><?= htmlspecialchars($notif['descricao']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-bell-slash fs-1 text-muted mb-3"></i>
                                <p class="text-muted mb-0">Nenhuma notificação no momento. Seu consumo está sob controle.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/includes/footerCliente.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
