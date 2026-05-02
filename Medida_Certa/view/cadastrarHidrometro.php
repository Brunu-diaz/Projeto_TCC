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
    // Busca unidades para o select de vínculo
    $unidades = $pdo->query("SELECT id_unidade, bloco, numero FROM unidade ORDER BY bloco, numero ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro ao carregar cadastro: " . $e->getMessage());
    die("Erro ao processar sua solicitação.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Novo Hidrômetro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
            /* Azul Primary */
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #3b82f6;
            /* Azul Primary */
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
                    <h4 class="fw-bold mb-0 text-dark">Cadastrar Hidrômetro</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-primary">Admin</a></li>
                            <li class="breadcrumb-item"><a href="listarHidrometros.php" class="text-decoration-none text-primary">Hidrômetros</a></li>
                            <li class="breadcrumb-item active">Novo Cadastro</li>
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
                    <form action="../controller/CadastrarHidrometroControl.php" method="POST">

                        <!-- Seção 1: Identificação -->
                        <h6 class="section-title text-uppercase"><i class="bi bi-plus-circle-fill me-2"></i>Informações Básicas</h6>
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-uppercase">Código Serial</label>
                                <input type="text" name="codigo" class="form-control" placeholder="Ex: ABC1234567" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase">Modelo do Aparelho</label>
                                <input type="text" name="modelo" class="form-control" placeholder="Ex: Digital v2.0">
                            </div>
                        </div>

                        <!-- Seção 2: Dados Técnicos -->
                        <h6 class="section-title text-uppercase"><i class="bi bi-speedometer2 me-2"></i>Estado Inicial</h6>
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-uppercase">Leitura de Início (m³)</label>
                                <input type="number" step="0.001" name="leitura_inicial" class="form-control" value="0.000">
                                <small class="text-muted">Valor no visor no momento da instalação.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase">Data de Instalação</label>
                                <input type="date" name="data_instalacao" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <!-- Seção 3: Vínculo e Localização -->
                        <h6 class="section-title text-uppercase"><i class="bi bi-house-gear-fill me-2"></i>Vínculo e Localização</h6>
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label text-uppercase">Unidade Responsável</label>
                                <select name="id_unidade" class="form-select">
                                    <option value="">Selecione a Unidade</option>
                                    <?php foreach ($unidades as $u): ?>
                                        <option value="<?= $u['id_unidade'] ?>">
                                            Bloco <?= $u['bloco'] ?> - Unidade <?= $u['numero'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-uppercase">Observações de Localização</label>
                                <textarea name="observacoes" class="form-control" rows="3" placeholder="Ex: Shaft do 2º andar..."></textarea>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <div class="row mt-5 justify-content-center">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm py-3 fw-bold">
                                    Finalizar Cadastro
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