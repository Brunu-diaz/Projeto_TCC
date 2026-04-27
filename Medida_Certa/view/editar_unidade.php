<?php
// 1. Segurança e Trava de Admin
require_once __DIR__ . '/../controller/TravaAdmin.php';

if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../model/dao/Conexao.php';

$id_unidade = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id_unidade) {
    header("Location: unidades.php?erro=" . urlencode("Unidade não encontrada."));
    exit;
}

try {
    $pdo = Conexao::getConexao();

    // 1. Busca Unidade + Hidrômetro + Última Leitura (Item 2)
    $sqlUnidade = "SELECT u.*, h.modelo, h.status as h_status, h.codigo as h_serial,
                   (SELECT MAX(data_leitura) FROM leitura l WHERE l.id_hidrometro = h.id_hidrometro) as ultima_comunicacao
                   FROM unidade u 
                   LEFT JOIN hidrometro h ON u.id_unidade = h.id_unidade 
                   WHERE u.id_unidade = :id";

    $stmt = $pdo->prepare($sqlUnidade);
    $stmt->execute([':id' => $id_unidade]);
    $unidade = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$unidade) {
        header("Location: unidades.php?erro=" . urlencode("Registro inexistente."));
        exit;
    }

    // 2. Busca Usuários
    $usuarios = $pdo->query("SELECT id_usuario, nome FROM usuario ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Busca Modelos Únicos (Datalist)
    $modelosCadastrados = $pdo->query("SELECT DISTINCT modelo FROM hidrometro WHERE modelo IS NOT NULL ORDER BY modelo ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Editar Unidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
</head>

<body class="bg-light">

    <?php include '../view/includes/header.php'; ?>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Editar Cadastro</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item"><a href="unidades.php" class="text-decoration-none text-muted">Unidades</a></li>
                        <li class="breadcrumb-item active">Editar ID #<?= $unidade['id_unidade'] ?></li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="unidades.php" class="btn btn-outline-secondary rounded-3 px-3 shadow-sm">
                    <i class="bi bi-x-lg me-1"></i> Cancelar
                </a>
            </div>
        </div>
    </div>

    <main class="main-container container mb-5">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-10 col-xl-8">
                <div class="card p-4 p-md-5 shadow-sm border-0" style="border-radius: 20px;">
                    <div class="card-body p-0">

                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="icon-square bg-primary-subtle rounded-3 me-3 p-3">
                                    <i class="bi bi-pencil-square text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Informações Gerais</h5>
                                    <p class="text-muted small mb-0">Unidade <?= $unidade['numero'] ?> - Bloco <?= $unidade['bloco'] ?></p>
                                </div>
                            </div>

                            <div class="text-end">
                                <span class="badge <?= $unidade['ultima_comunicacao'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> rounded-pill px-3">
                                    <i class="bi bi-broadcast me-1"></i>
                                    <?= $unidade['ultima_comunicacao'] ? 'Última Leitura: ' . date('d/m/Y H:i', strtotime($unidade['ultima_comunicacao'])) : 'Sem leituras registradas' ?>
                                </span>
                            </div>
                        </div>

                        <form id="formEditarUnidade" action="../controller/EdicaoUnidadeControl.php" method="POST">
                            <input type="hidden" name="id_unidade" value="<?= $id_unidade ?>">
                            <input type="hidden" id="morador_original" value="<?= $unidade['id_usuario'] ?>">

                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">Endereço Completo</label>
                                    <input type="text" name="endereco" class="form-control form-control-lg rounded-3" value="<?= htmlspecialchars($unidade['endereco']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">Número / Apto</label>
                                    <input type="text" name="numero" class="form-control form-control-lg rounded-3" value="<?= htmlspecialchars($unidade['numero']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">Bloco / Torre</label>
                                    <input type="text" name="bloco" class="form-control form-control-lg rounded-3" value="<?= htmlspecialchars($unidade['bloco']) ?>">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark small">Complemento</label>
                                    <input type="text" name="complemento" class="form-control form-control-lg rounded-3" value="<?= htmlspecialchars($unidade['complemento']) ?>">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">Proprietário Responsável</label>
                                    <select name="id_usuario" id="id_usuario" class="form-select form-control-lg rounded-3" required>
                                        <option value="">Selecione um morador...</option>
                                        <?php foreach ($usuarios as $user): ?>
                                            <option value="<?= $user['id_usuario'] ?>" <?= ($user['id_usuario'] == $unidade['id_usuario']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($user['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div id="avisoMorador" class="alert alert-warning mt-2 d-none" style="font-size: 0.8rem;">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        Atenção: Alterar o proprietário moverá as faturas futuras para este novo usuário.
                                    </div>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <div class="p-4 bg-light rounded-4 border border-dashed">
                                        <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-gear-fill me-2"></i>Configuração do Dispositivo</h6>
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold text-dark small">Código Serial (Hidrômetro)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-upc-scan"></i></span>
                                                    <input type="text" name="codigo_hidrometro" class="form-control form-control-lg rounded-end-3 text-uppercase" value="<?= htmlspecialchars($unidade['h_serial'] ?? '') ?>" placeholder="Ex: MC-2026-XXXX" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-dark small">Modelo do Dispositivo</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-cpu"></i></span>
                                                    <input list="listaModelos" name="modelo_hidrometro" class="form-control form-control-lg rounded-end-3" value="<?= htmlspecialchars($unidade['modelo'] ?? 'Digital v2.0') ?>" placeholder="Selecione ou digite...">
                                                </div>
                                                <datalist id="listaModelos">
                                                    <?php foreach ($modelosCadastrados as $mod): ?>
                                                        <option value="<?= htmlspecialchars($mod['modelo']) ?>">
                                                        <?php endforeach; ?>
                                                </datalist>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-dark small">Status de Operação</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0 rounded-start-3">
                                                        <i class="bi bi-activity text-secondary"></i>
                                                    </span>
                                                    <select name="status_hidrometro" class="form-select form-control-lg rounded-end-3">
                                                        <option value="Ativo" <?= ($unidade['h_status'] == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                                                        <option value="Inativo" <?= ($unidade['h_status'] == 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-5">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm py-3 fw-bold">
                                                <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-outline-danger btn-lg w-100 rounded-3 py-3" data-bs-toggle="modal" data-bs-target="#modalExcluir">
                                                <i class="bi bi-trash me-2"></i>Excluir
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body p-4 text-center">
                    <div class="bg-danger-subtle text-danger rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-exclamation-octagon fs-2"></i>
                    </div>
                    <h5 class="fw-bold">Confirmar Exclusão?</h5>
                    <p class="text-muted small">Você está prestes a remover a unidade <strong><?= $unidade['numero'] ?></strong>. Esta ação não poderá ser desfeita.</p>
                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-light w-100 rounded-3 border" data-bs-dismiss="modal">Cancelar</button>
                        <a href="../controller/ExcluirUnidadeControl.php?id=<?= $id_unidade ?>" class="btn btn-danger w-100 rounded-3">Sim, Excluir</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Item 1: Lógica de Auditoria e Aviso de Troca de Morador
        const selectMorador = document.getElementById('id_usuario');
        const moradorOriginal = document.getElementById('morador_original').value;
        const avisoMorador = document.getElementById('avisoMorador');

        selectMorador.addEventListener('change', function() {
            if (this.value !== moradorOriginal && moradorOriginal !== "") {
                avisoMorador.classList.remove('d-none');
            } else {
                avisoMorador.classList.add('d-none');
            }
        });

        document.getElementById('formEditarUnidade').addEventListener('submit', function(e) {
            if (selectMorador.value !== moradorOriginal && moradorOriginal !== "") {
                const confirmar = confirm("Atenção: Você está alterando o proprietário desta unidade. Confirmar alteração de responsabilidade financeira?");
                if (!confirmar) e.preventDefault();
            }
        });

        // Padronização de Input (Caps Lock automático)
        document.querySelector('input[name="codigo_hidrometro"]').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>

</html>