<?php
// 1. A trava de segurança DEVE ser a primeira coisa
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

try {
    $pdo = Conexao::getConexao();

    // Busca apenas os usuários que podem ser responsáveis
    $sqlUsuarios = "SELECT id_usuario, nome, cpf_cnpj FROM usuario ORDER BY nome ASC";
    $usuarios = $pdo->query($sqlUsuarios)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Cadastrar Unidade</title>

    <!-- CSS Externo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <!-- Tom Select (Filtro de busca) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
        }

        /* Identidade Visual Mantida */
        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .form-control,
        .form-select,
        .ts-control {
            border: 1px solid #e0e0e0 !important;
            padding: 0.75rem 1rem !important;
            border-radius: 12px !important;
            box-shadow: none !important;
        }

        /* Estilo específico para o filtro de busca Tom Select */
        .ts-wrapper.focus .ts-control {
            border-color: var(--primary-color) !important;
        }

        .ts-dropdown {
            border-radius: 12px;
            margin-top: 5px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            color: #495057;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .icon-square {
            background: #e6f1fe;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Funcionalidade: Feedback visual no botão */
        .btn-primary {
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
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
            <div class="alert alert-compacto fade show" id="alertaFlutuante">
                <i class="bi bi-check-circle-fill me-2"></i> Unidade cadastrada com sucesso!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-compacto fade show text-danger" id="alertaFlutuante" style="color: #dc3545; border-color: #f8d7da;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($_GET['erro']) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="container mb-5">
        <!-- CABEÇALHO/BREADCRUMB -->
        <div class="container page-header-box mb-4">
            <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Cadastrar Unidade</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-primary">Admin</a></li>
                            <li class="breadcrumb-item"><a href="unidades.php" class="text-decoration-none text-primary">Unidades</a></li>
                            <li class="breadcrumb-item active">Cadastrar</li>
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
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">

                        <form action="../controller/CadastroUnidadeControl.php" method="POST" class="needs-validation" id="formCadastro" novalidate>

                            <div class="d-flex align-items-center mb-4">
                                <div class="icon-square me-3">
                                    <i class="bi bi-building-add text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Informações da Unidade</h5>
                                    <p class="text-muted small mb-0">Associe o proprietário à localização física</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- FUNCIONALIDADE: SELECT COM BUSCA (Filtro) -->
                                <div class="col-md-12">
                                    <label class="form-label">Proprietário / Responsável</label>
                                    <select id="busca-usuario" name="id_usuario" placeholder="Digite o nome ou CPF para buscar..." required>
                                        <option value=""></option>
                                        <?php foreach ($usuarios as $user): ?>
                                            <option value="<?= $user['id_usuario'] ?>">
                                                <?= htmlspecialchars($user['nome']) ?> (<?= htmlspecialchars($user['cpf_cnpj']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Selecione um responsável cadastrado.</div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Endereço (Logradouro)</label>
                                    <input type="text" name="endereco" class="form-control" placeholder="Ex: Av. Central, Lote 12" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Número / Apto</label>
                                    <input type="text" name="numero" class="form-control" placeholder="402" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Bloco / Torre</label>
                                    <!-- FUNCIONALIDADE: Transformação em maiúsculas via CSS/JS -->
                                    <input type="text" name="bloco" class="form-control text-uppercase" placeholder="Ex: B">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Unidade</label>
                                    <div class="input-group">
                                        <select name="complemento" class="form-select border-start-0" style="border-radius: 0 12px 12px 0;" required>
                                            <option value="" selected disabled>Selecione...</option>
                                            <option value="Residencial">Residencial</option>
                                            <option value="Comercial">Comercial</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 opacity-25">

                            <div class="row g-3 pt-2">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm rounded-3 py-3">
                                        Finalizar Cadastro
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="reset" class="btn btn-light btn-lg w-100 rounded-3 py-3">
                                        Limpar Dados
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../view/includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        // 1. Inicializa o Filtro de Busca (Tom Select)
        new TomSelect("#busca-usuario", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        // 2. Validação Bootstrap e Feedback de Carregamento
        (() => {
            'use strict'
            const form = document.getElementById('formCadastro');
            const btn = document.getElementById('btnSalvar');

            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    // Se for válido, mostra o carregamento
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';
                    btn.classList.add('disabled');
                }
                form.classList.add('was-validated');
            }, false);
        })();

        // 3. Funcionalidade: Atalho Ctrl + Enter para salvar
        document.addEventListener('keydown', e => {
            if (e.ctrlKey && e.key === 'Enter') {
                document.getElementById('formCadastro').requestSubmit();
            }
        });
    </script>

    <script>
        // Seleciona o alerta independente de ser sucesso ou erro
        const alerta = document.getElementById('alertaFlutuante');

        if (alerta) {
            // 1. Limpa os parâmetros da URL sem recarregar (Melhora a UX)
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('sucesso');
                url.searchParams.delete('erro');
                window.history.replaceState({}, document.title, url.pathname);
            }

            // 2. Animação para sumir após 3 segundos
            setTimeout(() => {
                alerta.style.transition = "all 0.6s ease";
                alerta.style.opacity = "0";
                alerta.style.transform = "translateX(20px)"; // Efeito suave de saída
                setTimeout(() => alerta.remove(), 600);
            }, 3000);
        }
    </script>
</body>

</html>