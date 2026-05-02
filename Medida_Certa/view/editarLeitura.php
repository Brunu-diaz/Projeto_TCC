<?php
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Segurança: Apenas ADM
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: login.php");
    exit;
}

// Validação do ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listarleituras.php?msg=erro");
    exit;
}

$id_leitura = $_GET['id'];

try {
    $pdo = Conexao::getConexao();

    // 1. Busca a leitura atual
    $sql = "SELECT l.*, u.bloco, u.numero, usr.nome as responsavel, h.id_hidrometro 
            FROM leitura l
            JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
            JOIN unidade u ON h.id_unidade = u.id_unidade
            JOIN usuario usr ON u.id_usuario = usr.id_usuario
            WHERE l.id_leitura = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id_leitura);
    $stmt->execute();
    $leitura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$leitura) {
        header("Location: listarleituras.php?msg=Leitura não encontrada");
        exit;
    }

    // 2. Busca o valor da leitura ANTERIOR para calcular o consumo em tempo real
    $sqlAnterior = "SELECT valor_medido FROM leitura 
                WHERE id_hidrometro = :id_h 
                AND id_leitura < :id_atual 
                ORDER BY id_leitura DESC LIMIT 1";

    $stmtAnt = $pdo->prepare($sqlAnterior);
    $stmtAnt->bindValue(':id_h', $leitura['id_hidrometro']);
    $stmtAnt->bindValue(':id_atual', $id_leitura);
    $stmtAnt->execute();
    $leituraAnterior = $stmtAnt->fetch(PDO::FETCH_ASSOC);

    // Se não houver leitura anterior, o valor base é 0
    $valorAnterior = $leituraAnterior ? (float)$leituraAnterior['valor_medido'] : 0.00;
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Editar Leitura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        .btn-lg {
            padding: 1rem;
            font-size: 1rem;
            font-weight: bold;
        }

        .form-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .info-unidade-box {
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            border-left: 4px solid #0d6efd;
        }
    </style>
</head>

<body class="bg-light">

    <?php include '../view/includes/header.php'; ?>

    <div class="container">
        <!-- Breadcrumb & Header -->
        <div class="page-header-box mb-4">
            <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Editar Medição</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-primary">Admin</a></li>
                            <li class="breadcrumb-item"><a href="listarleituras.php" class="text-decoration-none text-primary">Leituras</a></li>
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
                <div class="form-card p-4 p-md-5">

                    <!-- Resumo da Unidade (Visual) -->
                    <div class="info-unidade-box mb-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-white p-2 rounded-circle shadow-sm">
                                    <i class="bi bi-house-door-fill text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="col">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Responsável pela Unidade</small>
                                <span class="text-muted"><?= $leitura['responsavel'] ?></span>
                                <span class="text-muted ms-2">| Bloco <?= $leitura['bloco'] ?> - Apt <?= $leitura['numero'] ?></span>
                            </div>
                        </div>
                    </div>

                    <form action="../controller/EditarLeituraControl.php" method="POST">
                        <input type="hidden" name="id_leitura" value="<?= $leitura['id_leitura'] ?>">

                        <div class="row g-4">
                            <!-- Data da Leitura -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Data da Medição</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" name="data_leitura" class="form-control border-start-0 ps-0"
                                        value="<?= $leitura['data_leitura'] ?>" required>
                                </div>
                            </div>

                            <!-- Período de Referência -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Mês/Ano Referência</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-info-circle"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 bg-light"
                                        value="<?= $leitura['mes_referencia'] ?>/<?= $leitura['ano_referencia'] ?>" readonly>
                                </div>
                            </div>

                            <!-- Campo Valor Medido Atual -->
                            <div class="col-md-6">
                                <label class="form-label text-dark">Valor Medido Atual (m³)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white border-0"><i class="bi bi-speedometer2"></i></span>
                                    <!-- Adicionamos oninput para garantir compatibilidade total -->
                                    <input type="number" step="0.01" name="valor_medido" id="valor_medido"
                                        class="form-control form-control-lg"
                                        value="<?= $leitura['valor_medido'] ?>" required>
                                </div>
                                <small class="text-muted">Insira o valor exato que aparece no visor do hidrômetro.</small>
                            </div>

                            <!-- Campo Consumo Calculado -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Consumo Calculado (m³)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-droplet"></i></span>
                                    <input type="number" step="0.01" name="consumo_calculado" id="consumo_calculado"
                                        class="form-control border-start-0 ps-0 bg-light"
                                        value="<?= $leitura['consumo_calculado'] ?>" readonly>
                                </div>
                                <small class="text-muted">Calculado automaticamente com base na leitura anterior (<?= number_format($valorAnterior, 2, ',', '.') ?> m³).</small>
                            </div>

                            <hr class="my-4 opacity-25">

                            <div class="row g-3 pt-2">
                                <div class="col-md-6 mx-auto">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm rounded-3 py-3">
                                        Salvar Alterações
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../view/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script de Cálculo em Tempo Real -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputMedido = document.getElementById('valor_medido');
            const inputConsumo = document.getElementById('consumo_calculado');

            // Injetando o valor anterior do PHP para o JS com segurança
            const valorAnterior = parseFloat("<?= $valorAnterior ?>") || 0;

            function calcular() {
                const valorAtual = parseFloat(inputMedido.value) || 0;

                // O Consumo é: O que eu digitei agora MINUS o que estava no hidrômetro antes
                const resultado = valorAtual - valorAnterior;

                // Exibe o resultado com 2 casas decimais, evitando valores negativos
                inputConsumo.value = (resultado > 0) ? resultado.toFixed(2) : "0.00";
            }

            // Escuta tanto a digitação quanto o clique nas setinhas do campo number
            inputMedido.addEventListener('input', calcular);
            inputMedido.addEventListener('change', calcular);
        });
    </script>
</body>

</html>