<?php

// 1. A trava de segurança DEVE ser a primeira coisa
require_once __DIR__ . '/../controller/TravaAdmin.php';

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Cadastrar Unidade e Cliente</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

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

        /* Estilização dos Cards e Seções */
        .card {
            border-radius: 20px;
            border: none;
        }

        .page-header-box div[style*="border-radius: 16px"] {
            border: none !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        /* Melhoria nos inputs */
        .form-control {
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            background-color: #fff;
        }

        .form-label {
            color: #495057;
            margin-bottom: 0.5rem;
            letter-spacing: -0.2px;
        }

        /* Estilização do Grupo de Botões (Tipo de Unidade) */
        .btn-check:checked+.btn-outline-primary {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }

        .btn-outline-primary {
            border-color: #e0e0e0;
            color: #6c757d;
            background: #fff;
        }

        .btn-outline-primary:hover {
            background-color: #f1f7ff;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* Ícones decorativos */
        .icon-square {
            transition: transform 0.3s ease;
        }

        .card:hover .icon-square {
            transform: scale(1.05);
        }

        /* Botões de Ação */
        .btn-lg {
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(13, 110, 253, 0.25);
        }

        /* --- REMOÇÃO DAS BORDAS AZUIS NOS BOTÕES DE GRUPO --- */
        .input-group .btn:focus,
        .input-group .btn:active,
        .input-group .btn:focus-visible {
            box-shadow: none !important;
            outline: none !important;
            border-color: #e0e0e0 !important;
            background-color: #fff !important;
        }

        .input-group .btn-outline-secondary {
            border-color: #e0e0e0;
            background-color: #fff;
            color: #6c757d;
        }

        .input-group:focus-within .btn-outline-secondary {
            border-color: var(--primary-color) !important;
        }

        /* Ajuste específico para o campo de senha não quebrar a borda */
        #senha {
            border-right: none;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem !important;
            }
        }

        /* Faz com que a borda de validação envolva os botões adjacentes */
        .was-validated .input-group:has(#senha:invalid) .btn-outline-secondary {
            border-color: #dc3545 !important;
        }

        .was-validated .input-group:has(#senha:valid) .btn-outline-secondary {
            border-color: #198754 !important;
        }

        /* Garante que o ícone de exclamação do Bootstrap não sobreponha o texto */
        .was-validated #senha.form-control {
            padding-right: 2rem;
            background-position: right 0.5rem center;
        }
    </style>
</head>

<body class="bg-light">

    <?php include '../view/includes/header.php'; ?>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Cadastrar Nova Unidade</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item"><a href="unidades.php" class="text-decoration-none text-muted">Unidades</a></li>
                        <li class="breadcrumb-item active">Cadastrar</li>
                    </ol>
                </nav>
            </div>
            <a href="unidades.php" class="btn btn-outline-secondary rounded-3 px-4 shadow-sm">
                <i class="bi bi-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <main class="main-container container mb-5">

        <?php if (isset($_GET['sucesso'])): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">
                <strong>Cadastro realizado com sucesso!</strong>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">
                <strong>Erro:</strong> <?php echo htmlspecialchars($_GET['erro']); ?>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <form action="../controller/CadastroUnidadeControl.php" method="POST" id="formCadastro" class="needs-validation" novalidate autocomplete="off">

                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="icon-square me-3" style="background: #e6f1fe; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-person-plus-fill text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">Dados do Cliente</h5>
                                        <p class="text-muted small mb-0">Informações para a tabela `usuario`</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold small">Nome Completo / Razão Social</label>
                                        <input type="text" name="nome" class="form-control rounded-3 py-2" placeholder="Ex: João ou Loja de Conveniência" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">CPF / CNPJ</label>
                                        <input type="text" id="cpf_cnpj" name="cpf_cnpj" class="form-control rounded-3 py-2" placeholder="000.000.000-00" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">E-mail</label>
                                        <input type="email" name="email" class="form-control rounded-3 py-2" placeholder="cliente@email.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Telefone / WhatsApp</label>
                                        <input type="text" id="telefone" name="telefone" class="form-control rounded-3 py-2" placeholder="(61) 99999-9999" required>
                                    </div>
                                    <div class="col-md-6">
                                    <label class="form-label fw-bold small" for="id_perfil">Tipo de Usuário</label>
                                    <select class="form-control rounded-3 py-2" name="id_perfil" required>
                                        <option value="">Selecione o perfil...</option>
                                        <option value="1">Administrador</option>
                                        <option value="2">Cliente</option>
                                    </select>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-5 opacity-25">

                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="icon-square me-3" style="background: #fffaf0; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-shield-lock-fill text-warning fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">Credenciais Provisórias</h5>
                                        <p class="text-muted small mb-0">Dados para a tabela `login` (Alteração obrigatória no 1º acesso)</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Username (Login)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-at"></i></span>
                                            <input type="text" id="username" name="username" class="form-control rounded-end-3 py-2" placeholder="ex: bruno.dias" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Senha Temporária</label>
                                        <div class="input-group">
                                            <input type="password" id="senha" name="senha_provisoria" class="form-control py-2" required>
                                            <button class="btn btn-outline-secondary border-start-0" type="button" onclick="gerarSenha()" title="Gerar Senha">
                                                <i class="bi bi-magic"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary border-start-0" type="button" onclick="toggleSenha()">
                                                <i class="bi bi-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-5 opacity-25">

                            <div class="mb-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="icon-square me-3" style="background: #e6fffa; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-building-up text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">Configuração da Unidade</h5>
                                        <p class="text-muted small mb-0">Endereço e Hidrômetro vinculado</p>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label fw-bold small d-block">Tipo de Unidade</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="tipo_unidade" id="residencial" value="Residencial" checked>
                                            <label class="btn btn-outline-primary py-2" for="residencial"><i class="bi bi-house-door me-2"></i>Apartamento</label>

                                            <input type="radio" class="btn-check" name="tipo_unidade" id="comercial" value="Comercial">
                                            <label class="btn btn-outline-primary py-2" for="comercial"><i class="bi bi-shop me-2"></i>Loja Comercial</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold small">Endereço da Unidade</label>
                                        <input type="text" name="endereco" class="form-control rounded-3 py-2" placeholder="Ex: Av. Central, Lote 12" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Número / Apto</label>
                                        <input type="text" name="numero" class="form-control rounded-3 py-2" placeholder="402" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Bloco / Torre</label>
                                        <input type="text" name="bloco" class="form-control rounded-3 py-2" placeholder="Bloco B" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Serial do Hidrômetro</label>
                                        <input type="text" name="codigo_hidrometro" class="form-control rounded-3 py-2 fw-bold" placeholder="00000000" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 pt-3">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm py-3 fw-bold">
                                        <i class="bi bi-cloud-check me-2"></i>Finalizar Cadastro
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="unidades.php" class="btn btn-light btn-lg w-100 rounded-3 py-3 text-muted border">
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-masker/1.2.0/vanilla-masker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Seleção de elementos
        const inputTel = document.querySelector("#telefone");
        const inputCpfCnpj = document.querySelector("#cpf_cnpj");
        const inputUser = document.querySelector("#username");
        const inputSenha = document.querySelector("#senha");

        // 1. Máscaras (Telefone e CPF/CNPJ)
        inputTel.addEventListener('input', () => {
            const value = inputTel.value.replace(/\D/g, "");
            const mask = value.length > 10 ? "(99) 99999-9999" : "(99) 9999-9999";
            VMasker(inputTel).maskPattern(mask);
        });

        inputCpfCnpj.addEventListener('input', () => {
            const value = inputCpfCnpj.value.replace(/\D/g, "");
            const mask = value.length <= 11 ? "999.999.999-99" : "99.999.999/9999-99";
            VMasker(inputCpfCnpj).maskPattern(mask);
        });

        // 2. Tratamento de Username
        inputUser.addEventListener('input', function(e) {
            e.target.value = e.target.value.toLowerCase().replace(/\s/g, '');
        });

        // 3. Funções de Senha
        function toggleSenha() {
            const icon = document.getElementById('toggleIcon');
            if (inputSenha.type === 'password') {
                inputSenha.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                inputSenha.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        function gerarSenha() {
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*";
            let novaSenha = "";
            for (let i = 0; i < 10; i++) {
                novaSenha += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            inputSenha.value = novaSenha;
            inputSenha.type = 'text';
            document.getElementById('toggleIcon').classList.replace('bi-eye', 'bi-eye-slash');

            // Feedback visual
            inputSenha.style.backgroundColor = "#e6fffa";
            setTimeout(() => inputSenha.style.backgroundColor = "#fff", 500);
        }

        // 4. Validação Bootstrap
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

    <script>
        // Seleciona todos os alertas e faz eles sumirem após 4 segundos
        setTimeout(function() {
            let alertas = document.querySelectorAll('div[style*="padding: 15px"]');
            alertas.forEach(a => a.style.display = 'none');
        }, 4000);
    </script>
</body>

</html>