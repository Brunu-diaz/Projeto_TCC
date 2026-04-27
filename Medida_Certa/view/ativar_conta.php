<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Ativar Conta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login.css">
    <style>
        .progress { height: 8px; border-radius: 4px; }
        .termo-box { height: 120px; overflow-y: scroll; font-size: 0.8rem; border: 1px solid #dee2e6; padding: 10px; border-radius: 5px; background: #f8f9fa; }
    </style>
</head>

<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="../index.php">
                    <i class="bi bi-droplet-fill me-2" aria-hidden="true"></i>
                    MedidaCerta
                </a>
            </div>
        </nav>
    </header>

    <div class="main-container bg-light">
        <div class="card login-card p-4 bg-white" style="max-width: 450px; margin: auto;">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock-fill logo-login text-primary" style="font-size: 3rem;" aria-hidden="true"></i>
                    <h2 class="fw-bold text-dark mt-2">Ativar Conta</h2>
                    <p class="text-muted small">Defina suas credenciais e aceite os termos para continuar.</p>
                </div>

                <?php if (isset($_GET['erro'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($_GET['erro']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="../controller/AtivarContaControl.php" method="POST" id="form-ativacao">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Novo Usuário (Login)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-1"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="novo_usuario" id="novo_usuario" class="form-control border-1" placeholder="Ex: joao" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small">Nova Senha</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-1"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="senha" id="senha" class="form-control border-1" placeholder="••••••••" required>
                            <button class="btn btn-outline-light text-muted border-1" type="button" onclick="toggleSenha('senha', 'icon1')"
                                style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-left: none;">
                                <i class="bi bi-eye" id="icon1"></i>
                            </button>
                        </div>
                        <div class="progress mt-2 mb-1">
                            <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small id="password-strength-text" class="text-muted" style="font-size: 0.75rem;">Força da senha</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Confirmar Senha</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-1"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control border-1" placeholder="••••••••" required>
                            <button class="btn btn-outline-light text-muted border-1" type="button" onclick="toggleSenha('confirmar_senha', 'icon2')"
                                style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-left: none;">
                                <i class="bi bi-eye" id="icon2"></i>
                            </button>
                        </div>
                        <div id="msg-erro" class="small text-danger mt-1" style="display: none;">
                            <i class="bi bi-exclamation-circle"></i> As senhas não coincidem!
                        </div>
                    </div>

                    <div class="mb-3">
    <div class="form-check d-flex align-items-center">
        <input class="form-check-input me-2" type="checkbox" id="check-lgpd" required>
        <label class="form-check-label small text-muted" for="check-lgpd">
            Li e aceito os <a href="#" class="text-primary fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalTermos">termos de condições e privacidade</a>.
        </label>
    </div>
</div>

<div class="modal fade" id="modalTermos" tabindex="-1" aria-labelledby="modalTermosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalTermosLabel">
                    <i class="bi bi-shield-check me-2"></i>Termos de Uso e Privacidade - MedidaCerta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="font-size: 0.9rem; line-height: 1.6;">
                <h6 class="fw-bold text-primary">1. Finalidade do Tratamento de Dados</h6>
                <p class="text-muted">O sistema <strong>MedidaCerta</strong> coleta e processa seu nome, e-mail e dados de consumo hídrico exclusivamente para viabilizar a medição individualizada e o faturamento do seu imóvel, conforme o Art. 7º, inciso V da LGPD (execução de contrato)</p>
                
                <h6 class="fw-bold text-primary mt-3">2. Transparência e Consumo</h6>
                <p class="text-muted">Os dados de medição de água são utilizados para gerar relatórios, alertas de vazamento e histórico de consumo. Estes dados são visíveis apenas para o usuário da unidade e para o administrador do sistema (síndico/gestor)</p>
                
                <h6 class="fw-bold text-primary mt-3">3. Segurança e Criptografia</h6>
                <p class="text-muted">Em conformidade com o Art. 46 da LGPD, utilizamos medidas técnicas como criptografia de senhas e proteção de banco de dados para garantir que suas informações não sejam acessadas por terceiros não autorizados.</p>

                <h6 class="fw-bold text-primary mt-3">4. Seus Direitos</h6>
                <p class="text-muted">Você possui o direito de acessar, corrigir ou solicitar a exclusão de seus dados pessoais a qualquer momento, desde que não interfira nas obrigações legais de cobrança do condomínio.</p>

                <h6 class="fw-bold text-primary mt-3">5. Responsabilidade do Usuário</h6>
                <p class="text-muted">O usuário compromete-se a fornecer informações verídicas e a manter o sigilo de suas credenciais de acesso, sendo responsável pelo uso de sua conta.</p>
                
                <div class="alert alert-info py-2 small mt-4">
                    <i class="bi bi-info-circle-fill me-2"></i> Ao clicar em "Entendi", você confirma que compreende a forma como seus dados são tratados no ecossistema MedidaCerta. Estes termos podem ser atualizados conforme mudanças na legislação vigente.
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-primary w-100 fw-bold" data-bs-dismiss="modal">Entendi</button>
            </div>
        </div>
    </div>
</div>

                    <button type="submit" id="btn-ativar" class="btn btn-primary w-100 shadow-sm btn-login" disabled>
                        Ativar Conta
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <a href="login.php" class="small text-primary text-decoration-none fw-bold">
                        <i class="bi bi-arrow-left me-1"></i> Voltar ao Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 text-center text-white bg-dark mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2026 MedidaCerta - Sistema de Gestão de Água Condominial. Todos os direitos reservados.</p>
            <p class="mb-0">CNPJ: 00.000.000/0001-00</p>
            <p class="mb-0">Brasília-DF</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const inputSenha = document.getElementById('senha');
        const inputConfirma = document.getElementById('confirmar_senha');
        const checkLgpd = document.getElementById('check-lgpd');
        const btnAtivar = document.getElementById('btn-ativar');
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.getElementById('password-strength-text');
        const inputUsuario = document.getElementById('novo_usuario');

        function toggleSenha(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        }

        // Validação da força da senha
        inputSenha.addEventListener('input', () => {
            const val = inputSenha.value;
            let strength = 0;
            if (val.length >= 6) strength += 25;
            if (val.match(/[A-Z]/)) strength += 25;
            if (val.match(/[0-9]/)) strength += 25;
            if (val.match(/[^A-Za-z0-9]/)) strength += 25;

            strengthBar.style.width = strength + '%';
            
            if (strength <= 25) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.innerText = 'Senha muito fraca';
            } else if (strength <= 50) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.innerText = 'Senha razoável';
            } else if (strength <= 75) {
                strengthBar.className = 'progress-bar bg-info';
                strengthText.innerText = 'Senha boa';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.innerText = 'Senha forte';
            }
            validarFormulario();
        });

        function validarFormulario() {
    const inputSenha = document.getElementById('senha');
    const inputConfirma = document.getElementById('confirmar_senha');
    const inputUsuario = document.getElementById('novo_usuario');
    const checkLgpd = document.getElementById('check-lgpd');
    const btnAtivar = document.getElementById('btn-ativar');

    const senhasIguais = inputSenha.value === inputConfirma.value && inputSenha.value !== '';
    const usuarioPreenchido = inputUsuario.value.trim() !== '';
    const termoAceito = checkLgpd.checked;
    const senhaValida = inputSenha.value.length >= 6;

    // Gerencia a mensagem de erro visual
    document.getElementById('msg-erro').style.display = (inputConfirma.value && !senhasIguais) ? 'block' : 'none';

    // Só habilita se todos os critérios forem atendidos
    btnAtivar.disabled = !(senhasIguais && usuarioPreenchido && termoAceito && senhaValida);
}

// Adicione os ouvintes de evento
document.getElementById('check-lgpd').addEventListener('change', validarFormulario);
document.getElementById('senha').addEventListener('input', validarFormulario);
document.getElementById('confirmar_senha').addEventListener('input', validarFormulario);
document.getElementById('novo_usuario').addEventListener('input', validarFormulario);
</script>
</body>

</html>