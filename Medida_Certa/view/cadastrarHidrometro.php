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
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
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

        /* Faz o Tom Select respeitar o visual do Bootstrap 5 e seu layout */
        .ts-control {
            border-radius: 0.375rem !important;
            /* Padrão Bootstrap ou ajuste para seu rounded-3 */
            padding: 0.5rem 0.75rem !important;
        }

        .ts-dropdown {
            border-radius: 0.375rem !important;
            margin-top: 5px !important;
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
                                <!-- Adicionado o ID para o filtro e mantido a classe form-select -->
                                <select id="select-unidade-responsavel" name="id_unidade" class="form-select">
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
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Inicializa o filtro para a Unidade Responsável
            new TomSelect("#select-unidade-responsavel", {
                create: false,
                sortField: {
                    field: "text",
                    order: "asc"
                },
                render: {
                    // Mensagem personalizada quando não encontra resultados
                    no_results: function(data, escape) {
                        return '<div class="no-results py-2 px-3 text-muted">Nenhuma unidade encontrada...</div>';
                    },
                    // Mantém o padding e estilo visual nas opções
                    option: function(data, escape) {
                        return '<div class="py-2 px-3">' + escape(data.text) + '</div>';
                    }
                }
            });
        });
    </script>
</body>

</html>