<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="index.html">
                    <i class="bi bi-droplet-fill me-2"></i> MedidaCerta
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto text-white">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="quemsomos.php">Quem Somos</a></li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="login.php">
                                Login <i class="bi bi-box-arrow-in-right"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <div class="main-container bg-light">
        <div class="card login-card p-4 bg-white">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus-fill logo-login"></i>
                    <h2 class="fw-bold text-dark mt-2">Criar Conta</h2>
                    <p class="text-muted">Cadastre-se para gerenciar seu consumo</p>
                </div>

                <form action="../controller/CadastroUsuarioControl.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small" for="nome">Nome Completo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="nome" class="form-control border-0" id="nome" placeholder="Seu nome aqui" autocomplete="name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small" for="email">E-mail</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control border-0" id="email" placeholder="exemplo@email.com" autocomplete="email" required>
                        </div>
                    </div>

                    <div class="mb-3">
    <label class="form-label fw-bold small" for="senha">Senha</label>
    <div class="input-group">
        <span class="input-group-text bg-light border-0"><i class="bi bi-lock text-muted"></i></span>
        <input type="password" name="senha" class="form-control border-0" id="senha" placeholder="••••••••" required autocomplete="new-password" oninput="avaliarForcaSenha(); validarFormulario();">
        <span class="input-group-text bg-light border-0" style="cursor: pointer;" onclick="toggleSenha('senha', 'btn-senha-1')">
            <i class="bi bi-eye-slash text-muted" id="btn-senha-1"></i>
        </span>
    </div>
    <div class="progress mt-2" style="height: 10px; border-radius: 20px;">
    <div id="barra-forca" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; border-radius: 20px;"></div>
</div>
<div class="text-center mt-1">
    <small id="texto-forca" class="form-text" style="font-size: 0.7rem; font-weight: bold;"></small>
</div>
</div>

<div class="mb-4">
    <label class="form-label fw-bold small" for="confirmar_senha">Confirmar Senha</label>
    <div class="input-group">
        <span class="input-group-text bg-light border-0"><i class="bi bi-lock text-muted"></i></span>
        <input type="password" name="confirmar_senha" class="form-control border-0" id="confirmar_senha" placeholder="••••••••" required autocomplete="new-password" oninput="validarFormulario()">
        <span class="input-group-text bg-light border-0" style="cursor: pointer;" onclick="toggleSenha('confirmar_senha', 'btn-senha-2')">
            <i class="bi bi-eye-slash text-muted" id="btn-senha-2"></i>
        </span>
    </div>
    <div id="msg-erro" class="small mt-2" style="display: none; color: #dc3545;">
        <i class="bi bi-exclamation-circle"></i> As senhas não coincidem!
    </div>
</div>

                    <div class="mb-4">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="termos" name="termos" required onchange="validarFormulario()">
        <label class="form-check-label small text-muted" for="termos">
            Eu li e aceito os <a href="#" data-bs-toggle="modal" data-bs-target="#modalTermos" class="text-decoration-none">Termos de Uso e Política de Privacidade</a>.
        </label>
    </div>
</div>
                    <button type="submit" id="btn-finalizar" class="btn btn-primary w-100 btn-custom shadow-sm mb-3" disabled>
                        Finalizar Cadastro <i class="bi bi-check-circle ms-2"></i>
                    </button>

                    <a href="login.php" class="btn btn-outline-secondary w-100 btn-custom border-0">
                        Já tenho conta, quero entrar
                    </a>
                </form>
            </div>
        </div>
    </div>

    <fieldset>
 <legend>Administração</legend>
 <form action="../controller/TestarConexaoControl.php"
method="post">
 <button type="submit">Testar Conexão com o Banco</button>
 </form>
 </fieldset>

    <footer class="py-4 bg-dark text-center text-white">
        <div class="container">
            <p class="mb-0">&copy; 2026 MedidaCerta - Sistema de Gestão de Água Condominial. Todos os direitos reservados.</p>
            <p class="mb-0">CNPJ: 00.000.000/0001-00</p>
            <p class="mb-0">Brasília-DF</p>
        </div>
    </footer>

<div class="modal fade" id="modalTermos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary">Termos de Uso e Privacidade - Medida Certa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body small text-muted">
                <h6 class="fw-bold text-dark">1. Finalidade do Tratamento de Dados</h6>
                <p>O sistema Medida Certa coleta e processa seu nome, e-mail e dados de consumo hídrico exclusivamente para viabilizar a medição individualizada e o faturamento do seu imóvel, conforme o Art. 7º, inciso V da LGPD (execução de contrato).</p>

                <h6 class="fw-bold text-dark">2. Transparência e Consumo</h6>
                <p>Os dados de medição de água são utilizados para gerar relatórios, alertas de vazamento e histórico de consumo. Estes dados são visíveis apenas para o usuário da unidade e para o administrador do sistema.</p>

                <h6 class="fw-bold text-dark">3. Segurança e Criptografia</h6>
                <p>Em conformidade com o Art. 46 da LGPD, utilizamos medidas técnicas como criptografia de senhas e proteção de banco de dados para garantir que suas informações não sejam acessadas por terceiros não autorizados.</p>

                <h6 class="fw-bold text-dark">4. Seus Direitos</h6>
                <p>Você possui o direito de acessar, corrigir ou solicitar a exclusão de seus dados pessoais a qualquer momento, desde que não interfira nas obrigações legais de cobrança do condomínio.</p>

                <h6 class="fw-bold text-dark">5. Responsabilidade do Usuário</h6>
                <p>O usuário compromete-se a fornecer informações verídicas e a manter o sigilo de suas credenciais de acesso, sendo responsável pelo uso de sua conta.</p>

                <div class="alert alert-info mt-3 p-2">
                    Ao clicar em "Entendi", você confirma que compreende a forma como seus dados são tratados no ecossistema MedidaCerta.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendi</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/scripts.js"></script>
</body>
</html>