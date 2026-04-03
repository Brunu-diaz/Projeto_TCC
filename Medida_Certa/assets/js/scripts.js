//index.php
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">

//cadastro.php
    // Função para mostrar/esconder senha (genérica)
function toggleSenha(idInput, idIcone) {
    const input = document.getElementById(idInput);
    const icone = document.getElementById(idIcone);

    if (input.type === "password") {
        input.type = "text";
        icone.classList.replace("bi-eye-slash", "bi-eye");
    } else {
        input.type = "password";
        icone.classList.replace("bi-eye", "bi-eye-slash");
    }
}    

function validarFormulario() {
    const senha = document.getElementById('senha').value;
    const confirma = document.getElementById('confirmar_senha').value;
    const termos = document.getElementById('termos').checked;
    const msgErro = document.getElementById('msg-erro');
    const botao = document.querySelector('button[type="submit"]');

    // Verifica senhas
    const senhasIguais = (senha === confirma && confirma !== "");
    
    if (confirma !== "" && !senhasIguais) {
        msgErro.style.display = 'block';
    } else {
        msgErro.style.display = 'none';
    }

    // O botão só habilita se: Senhas Iguais E Termos Marcados
    if (senhasIguais && termos) {
        botao.disabled = false;
    } else {
        botao.disabled = true;
    }
}

function avaliarForcaSenha() {
    const senha = document.getElementById('senha').value;
    const barra = document.getElementById('barra-forca');
    const texto = document.getElementById('texto-forca');
    let forca = 0;

    if (senha.length >= 8) forca += 25;
    if (senha.match(/[a-z]+/)) forca += 25;
    if (senha.match(/[A-Z]+/)) forca += 25;
    if (senha.match(/[0-9]+/)) forca += 15;
    if (senha.match(/[^a-zA-Z0-9]+/)) forca += 10;

    // Atualiza a barra visualmente
    barra.style.width = forca + '%';

    if (forca < 30) {
        barra.className = "progress-bar progress-bar-striped progress-bar-animated bg-danger";
        texto.innerHTML = "Senha muito fraca";
        texto.className = "text-danger small";
    } else if (forca < 60) {
        barra.className = "progress-bar progress-bar-striped progress-bar-animated bg-warning";
        texto.innerHTML = "Senha razoável";
        texto.className = "text-warning small";
    } else if (forca < 90) {
        barra.className = "progress-bar progress-bar-striped progress-bar-animated bg-info";
        texto.innerHTML = "Senha boa";
        texto.className = "text-info small";
    } else {
        barra.className = "progress-bar progress-bar-striped progress-bar-animated bg-success";
        texto.innerHTML = "Senha forte e segura";
        texto.className = "text-success small";
    }
}
</script>