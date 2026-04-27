<?php
// 1. A trava de segurança DEVE ser a primeira coisa
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

try {
    $pdo = Conexao::getConexao();
    
    // INNER JOIN para buscar os dados da Unidade + o Código do Hidrômetro vinculado
    $sqlUnidades = "SELECT 
                        u.id_unidade, 
                        u.numero, 
                        u.bloco, 
                        h.id_hidrometro,
                        h.codigo AS codigo_hidrometro 
                    FROM unidade u
                    INNER JOIN hidrometro h ON u.id_unidade = h.id_unidade
                    ORDER BY u.bloco, u.numero";
                    
    $listaUnidades = $pdo->query($sqlUnidades)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Para debug: echo "Erro: " . $e->getMessage(); 
    $listaUnidades = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Lançar Leitura de Consumo</title>

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
        .form-control, .form-select { border: 1px solid #e0e0e0; padding: 0.75rem 1rem; transition: all 0.2s ease; font-size: 0.95rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); }
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
                <h4 class="fw-bold mb-0 text-dark">Lançar Nova Leitura</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item"><a href="leituras.php" class="text-decoration-none text-muted">Leituras</a></li>
                        <li class="breadcrumb-item active">Cadastrar</li>
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
            <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Sucesso!</strong> Leitura registrada com sucesso.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4 alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Erro:</strong> 
                <?php 
                    if($_GET['erro'] == 'db_error') echo "Falha ao salvar no banco de dados.";
                    elseif($_GET['erro'] == 'campos_vazios') echo "Preencha todos os campos corretamente.";
                    elseif($_GET['erro'] == 'leitura_menor') echo "O valor informado é menor que a última leitura cadastrada.";
                    else echo "Não foi possível processar a leitura.";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <form action="../controller/CadastroLeituraControl.php" method="POST" class="needs-validation" novalidate>

                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="icon-square me-3" style="background: #e6f1fe; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-water text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">Informações do Hidrômetro</h5>
                                        <p class="text-muted small mb-0">Selecione a unidade e informe o valor medido</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="unidade" class="form-label fw-bold small">Selecione a Unidade / Hidrômetro</label>
                                        <select id="unidade" name="id_hidrometro" class="form-select rounded-3 py-2" required>
                                            <option value="">Escolha a unidade...</option>
                                            <?php foreach ($listaUnidades as $u): ?>
                                                <option value="<?= $u['id_hidrometro'] ?>">
                                                    Bloco <?= $u['bloco'] ?> - Apto <?= $u['numero'] ?> (S/N: <?= $u['codigo_hidrometro'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="mes" class="form-label fw-bold small">Mês de Referência</label>
                                        <select id="mes" name="mes_referencia" class="form-select rounded-3 py-2" required>
                                            <?php
                                            $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                                            foreach ($meses as $num => $nome): ?>
                                                <option value="<?= $num ?>" <?= $num == date('n') ? 'selected' : '' ?>><?= $nome ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="ano" class="form-label fw-bold small">Ano de Referência</label>
                                        <input id="ano" type="number" name="ano_referencia" class="form-control rounded-3 py-2" value="<?= date('Y') ?>" required>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="valor" class="form-label fw-bold small">Valor Medido (m³)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-speedometer2"></i></span>
                                            <input id="valor" type="number" step="0.01" name="valor_medido" class="form-control rounded-end-3 py-2 fw-bold text-primary" placeholder="0.00" style="font-size: 1.2rem;" required>
                                        </div>
                                        <div class="form-text mt-2">Insira exatamente o valor que aparece no visor do hidrômetro.</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 opacity-25">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold">
                                        <i class="bi bi-save me-2"></i>Registrar Leitura
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