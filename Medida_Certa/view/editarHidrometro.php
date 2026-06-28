<?php
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Segurança: Apenas ADM
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: listarHidrometros.php");
    exit;
}

try {
    $pdo = Conexao::getConexao();

    // 1. Busca os dados atuais (incluindo os novos campos)
    $stmt = $pdo->prepare("SELECT * FROM hidrometro WHERE id_hidrometro = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $hidrometro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hidrometro) {
        header("Location: listarHidrometros.php?msg=nao_encontrado");
        exit;
    }

    // 2. Busca unidades para o select
    $unidades = $pdo->query("SELECT id_unidade, bloco, numero FROM unidade ORDER BY bloco, numero ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao carregar edição: " . $e->getMessage());
    die("Erro ao processar sua solicitação.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Detalhes do Hidrômetro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        .edit-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .form-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 0.6rem 1rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #3b82f6;
            border-bottom: 2px solid #eff6ff;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-light">

    <?php include '../view/includes/header.php'; ?>

    <main class="main-container container mb-5">

        <div class="page-header-box mb-4">
            <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Configurações do Hidrômetro</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-primary">Admin</a></li>
                            <li class="breadcrumb-item"><a href="listarHidrometros.php" class="text-decoration-none text-primary">Hidrômetros</a></li>
                            <li class="breadcrumb-item active">Editar</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary rounded-3 px-4 shadow-sm">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="edit-card p-4 p-md-5">
                    <form action="../controller/EditarHidrometroControl.php" method="POST">
                        <input type="hidden" name="id_hidrometro" value="<?= $hidrometro['id_hidrometro'] ?>">

                        <!-- Seção 1: Identificação -->
                        <h6 class="section-title text-uppercase"><i class="bi bi-tag-fill me-2"></i>Identificação e Modelo</h6>
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-uppercase">Código Serial</label>
                                <input type="text" name="codigo" class="form-control" value="<?= htmlspecialchars($hidrometro['codigo']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase">Modelo do Aparelho</label>
                                <input type="text" name="modelo" class="form-control" value="<?= htmlspecialchars($hidrometro['modelo'] ?? '') ?>" placeholder="Ex: Woltmann, Digital v2">
                            </div>
                        </div>

                        <!-- Seção 2: Dados Técnicos -->
                        <h6 class="section-title text-uppercase"><i class="bi bi-tools me-2"></i>Dados Técnicos e Instalação</h6>
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-uppercase">Leitura Inicial (m³)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-speedometer2"></i></span>
                                    <input type="number" step="0.001" name="leitura_inicial" class="form-control border-start-0"
                                        value="<?= htmlspecialchars($hidrometro['leitura_inicial'] ?? '0.000') ?>" style="border-radius: 0 12px 12px 0;">
                                </div>
                                <small class="text-muted">Valor registrado no momento da instalação.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase">Data de Instalação</label>
                                <input type="date" name="data_instalacao" class="form-control" value="<?= $hidrometro['data_instalacao'] ?? '' ?>">
                            </div>
                        </div>

                        <!-- Seção 3: Localização -->
                        <h6 class="section-title text-uppercase"><i class="bi bi-geo-alt-fill me-2"></i>Localização e Vínculo</h6>
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label text-uppercase">Unidade (Apartamento / Bloco)</label>
                                <select name="id_unidade" class="form-select" required>
                                    <option value="">Selecione a Unidade</option>
                                    <?php foreach ($unidades as $u): ?>
                                        <option value="<?= $u['id_unidade'] ?>" <?= ($u['id_unidade'] == $hidrometro['id_unidade']) ? 'selected' : '' ?>>
                                            Bloco <?= $u['bloco'] ?> - Unidade <?= $u['numero'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-uppercase">Notas de Localização ou Observações</label>
                                <textarea name="observacoes" class="form-control" rows="3" placeholder="Ex: Localizado no shaft do corredor, altura 1.20m."><?= htmlspecialchars($hidrometro['observacoes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="row mt-5 justify-content-center">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm py-3 fw-bold">
                                    Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>