<?php
// 1. A trava de segurança DEVE ser a primeira coisa
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Busca a tarifa mais recente para mostrar como sugestão nos campos
try {
    $pdo = Conexao::getConexao();
    $sqlAtual = "SELECT * FROM tarifa ORDER BY id_tarifa DESC LIMIT 1";
    $tarifaAtual = $pdo->query($sqlAtual)->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $tarifaAtual = null;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Cadastrar Tarifa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">

    <style>
        :root { --primary-color: #0d6efd; --bg-light: #f8f9fa; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); }
        .card { border-radius: 20px; border: none; }
        .form-control { border: 1px solid #e0e0e0; padding: 0.75rem 1rem; transition: all 0.2s ease; font-size: 0.95rem; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); }
        .icon-square { transition: transform 0.3s ease; }
        .card:hover .icon-square { transform: scale(1.05); }
        .btn-lg { padding: 1rem; font-size: 1rem; transition: all 0.3s ease; border-radius: 12px; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(13, 110, 253, 0.25); }
    </style>
</head>

<body class="bg-light">

    <?php include '../view/includes/header.php'; ?>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Configurar Nova Tarifa</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item active">Tarifas</li>
                    </ol>
                </nav>
            </div>
            <a href="admin.php" class="btn btn-outline-secondary rounded-3 px-4 shadow-sm">
                <i class="bi bi-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <main class="main-container container mb-5">

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div><strong>Sucesso!</strong> A nova tarifa foi configurada e será aplicada aos próximos cálculos.</div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><strong>Erro!</strong> Não foi possível salvar a tarifa. Verifique os dados.</div>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <form action="../controller/CadastroTarifaControl.php" method="POST" class="needs-validation" novalidate>

                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="icon-square me-3" style="background: #fff4e6; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-currency-dollar text-warning fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">Valores da Tarifa</h5>
                                        <p class="text-muted small mb-0">Defina os custos vigentes para o cálculo das faturas</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold small">Nome/Identificação da Tarifa</label>
                                        <input type="text" name="nome" class="form-control rounded-3 py-2" 
                                               placeholder="Ex: Tarifa Residencial 2026" 
                                               value="<?= $tarifaAtual['nome'] ?? '' ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Valor por m³ (R$)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">R$</span>
                                            <input type="number" step="0.01" name="valor_m3" class="form-control rounded-end-3 py-2" 
                                                   placeholder="0,00" value="<?= $tarifaAtual['valor_m3'] ?? '' ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Taxa de Esgoto (%)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">%</span>
                                            <input type="number" step="0.01" name="taxa_esgoto" class="form-control rounded-end-3 py-2" 
                                                   placeholder="100.00" value="<?= $tarifaAtual['taxa_esgoto'] ?? '100.00' ?>" required>
                                        </div>
                                        <div class="form-text small">Geralmente 100% sobre o consumo.</div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold small">Data de Início da Vigência</label>
                                        <input type="date" name="data_vigencia" class="form-control rounded-3 py-2" 
                                               value="<?= date('Y-m-d') ?>" required>
                                        <div class="form-text mt-2">Os cálculos usarão esta tarifa para leituras a partir desta data.</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 opacity-25">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold">
                                        <i class="bi bi-check-circle me-2"></i>Salvar Tarifa
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="admin.php" class="btn btn-light btn-lg w-100 text-muted border">
                                        Cancelar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if ($tarifaAtual): ?>
                <div class="mt-4 p-3 bg-white border rounded-4 shadow-sm">
                    <h6 class="fw-bold text-muted small mb-2"><i class="bi bi-info-circle me-2"></i>Tarifa em Vigência Atualmente:</h6>
                    <div class="d-flex justify-content-between">
                        <span><strong><?= $tarifaAtual['nome'] ?>:</strong> R$ <?= number_format($tarifaAtual['valor_m3'], 2, ',', '.') ?>/m³</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Ativa</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>