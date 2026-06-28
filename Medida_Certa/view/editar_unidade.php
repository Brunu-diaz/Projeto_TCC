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

    // 1. Busca Unidade + Hidrômetro + Dados de Leitura
    $sqlUnidade = "SELECT u.*, h.modelo, h.status as h_status, h.codigo as h_serial, h.leitura_inicial,
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

    // 2. Busca Usuários (Com CPF para o filtro)
    $usuarios = $pdo->query("SELECT id_usuario, nome, cpf_cnpj FROM usuario ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
        }

        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        /* Melhoria nos inputs para ficarem iguais à página de cadastro */
        .form-control,
        .form-select {
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem !important;
            /* Tamanho exato da outra página */
            transition: all 0.2s ease;
            font-size: 0.95rem !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        .form-label {
            color: #495057;
            margin-bottom: 0.5rem;
            letter-spacing: -0.2px;
            font-size: 0.875rem;
        }

        .icon-square {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-lg {
            padding: 1rem;
            font-size: 1rem;
            font-weight: bold;
        }

        .alert-floating-container {
            position: fixed;
            top: 25px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1060;
        }

        .alert-compacto {
            background: #ffffff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 500;
            color: #198754;
        }
    </style>
</head>

<body class="bg-light">

    <?php include '../view/includes/header.php'; ?>

    <div class="alert-floating-container">
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-compacto fade show" id="sucessoAlert">
                <i class="bi bi-check-circle-fill me-2"></i> Unidade atualizada com sucesso!
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-compacto fade show text-danger" id="alertaFlutuante" style="color: #dc3545; border-color: #f8d7da;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($_GET['erro']) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Editar Cadastro</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-primary">Admin</a></li>
                        <li class="breadcrumb-item"><a href="unidades.php" class="text-decoration-none text-primary">Unidades</a></li>
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

    <main class="main-container container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-4 p-md-5">

                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="icon-square me-3" style="background: #e6f1fe; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-pencil-square text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Informações da Unidade</h5>
                                    <p class="text-muted small mb-0">Unidade <?= $unidade['numero'] ?> - Bloco <?= $unidade['bloco'] ?></p>
                                </div>
                            </div>

                            <div class="text-end">
                                <span class="badge <?= $unidade['ultima_comunicacao'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> rounded-pill px-3 py-2" style="font-weight: 500;">
                                    <i class="bi bi-broadcast me-1"></i>
                                    <?= $unidade['ultima_comunicacao'] ? 'Última Leitura: ' . date('d/m/Y H:i', strtotime($unidade['ultima_comunicacao'])) : 'Sem leituras' ?>
                                </span>
                            </div>
                        </div>

                        <form id="formEditarUnidade" action="../controller/EdicaoUnidadeControl.php" method="POST" class="needs-validation" novalidate autocomplete="off">
                            <input type="hidden" name="id_unidade" value="<?= $id_unidade ?>">
                            <input type="hidden" id="morador_original" value="<?= $unidade['id_usuario'] ?>">

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small">Endereço Completo</label>
                                    <input type="text" name="endereco" class="form-control rounded-3 py-2" value="<?= htmlspecialchars($unidade['endereco']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small">Número / Apto</label>
                                    <input type="text" name="numero" class="form-control rounded-3 py-2" value="<?= htmlspecialchars($unidade['numero']) ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small">Bloco / Torre</label>
                                    <input type="text" name="bloco" class="form-control rounded-3 py-2" value="<?= htmlspecialchars($unidade['bloco']) ?>">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small">Complemento</label>
                                    <input type="text" name="complemento" class="form-control rounded-3 py-2" value="<?= htmlspecialchars($unidade['complemento']) ?>">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small">Proprietário Responsável</label>
                                    <select id="busca-usuario" name="id_usuario" placeholder="Digite o nome ou CPF para buscar..." required>
                                        <option value=""></option>
                                        <?php foreach ($usuarios as $user): ?>
                                            <option value="<?= $user['id_usuario'] ?>" <?= (isset($unidade) && $user['id_usuario'] == $unidade['id_usuario']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($user['nome']) ?> (<?= htmlspecialchars($user['cpf_cnpj']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Selecione um responsável cadastrado.</div>

                                    <!-- Alerta de alteração (opcional, mantido do seu código anterior) -->
                                    <div id="avisoMorador" class="alert alert-warning mt-3 d-none border-0 shadow-sm" style="border-radius: 12px; font-size: 0.85rem;">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        Atenção: Alterar o proprietário afetará as faturas futuras.
                                    </div>
                                </div>
                            </div>

                            <hr class="my-5 opacity-25">

                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="icon-square me-3" style="background: #fffaf0; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-gear-fill text-warning fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">Configuração do Dispositivo</h5>
                                        <p class="text-muted small mb-0">Dados técnicos do hidrômetro vinculado</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label small">Código Serial</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" name="codigo_hidrometro" class="form-control rounded-end-3 py-2 text-uppercase" value="<?= htmlspecialchars($unidade['h_serial'] ?? '') ?>" placeholder="Ex: MC-2026-XXXX" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">Modelo</label>
                                        <input list="listaModelos" name="modelo_hidrometro" class="form-control rounded-3 py-2" value="<?= htmlspecialchars($unidade['modelo'] ?? 'Digital v2.0') ?>">
                                        <datalist id="listaModelos">
                                            <?php foreach ($modelosCadastrados as $mod): ?>
                                                <option value="<?= htmlspecialchars($mod['modelo']) ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">Status de Operação</label>
                                        <select name="status_hidrometro" class="form-select rounded-3 py-2">
                                            <option value="Ativo" <?= ($unidade['h_status'] == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                                            <option value="Inativo" <?= ($unidade['h_status'] == 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 opacity-25">

                            <div class="row g-3 pt-2">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm py-3">
                                        Salvar Alterações
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-danger btn-lg w-100 rounded-3 py-3" data-bs-toggle="modal" data-bs-target="#modalExcluir">
                                        Excluir Unidade
                                    </button>
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
                    <i class="bi bi-exclamation-triangle text-danger fs-1 mb-3"></i>
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

    <!-- Dependência JS do TomSelect -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Inicializa o TomSelect para o usuário
            const tsUsuario = new TomSelect("#busca-usuario", {
                create: false,
                sortField: {
                    field: "text",
                    order: "asc"
                },
                render: {
                    no_results: function(data, escape) {
                        return '<div class="no-results py-2 px-3 text-muted">Nenhum morador encontrado...</div>';
                    },
                    option: function(data, escape) {
                        return '<div class="py-2 px-3">' + escape(data.text) + '</div>';
                    }
                }
            });

            // Lógica para mostrar o aviso caso o usuário seja alterado
            const idOriginal = "<?= $unidade['id_usuario'] ?? '' ?>";
            tsUsuario.on('change', function(value) {
                const aviso = document.getElementById('avisoMorador');
                if (aviso) {
                    if (value !== idOriginal && idOriginal !== "") {
                        aviso.classList.remove('d-none');
                    } else {
                        aviso.classList.add('d-none');
                    }
                }
            });
        });
    </script>

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
    <script>
        const sucessoAlert = document.getElementById('sucessoAlert');
        const alertToRemove = sucessoAlert;

        if (alertToRemove) {
            // 1. Limpa o parâmetro da URL sem recarregar a página
            // Isso impede que o F5 mostre a mensagem de novo
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('sucesso');
                url.searchParams.delete('erro');
                window.history.replaceState({}, document.title, url.pathname);
            }

            // 2. Animação de sumir o alerta após 3 segundos
            setTimeout(() => {
                alertToRemove.style.transition = "opacity 0.6s ease";
                alertToRemove.style.opacity = "0";
                setTimeout(() => alertToRemove.remove(), 600);
            }, 3000);
        }
    </script>
</body>

</html>