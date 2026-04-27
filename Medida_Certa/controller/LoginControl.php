<?php
session_start();

// Caminhos para a estrutura do projeto MedidaCerta
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /**
     * Captura e sanitização dos dados de entrada
     */
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
    $senha = trim($_POST['senha']);

    // Captura o checkbox "Lembrar-me"
    $lembrar = isset($_POST['remember']);

    $usuarioDAO = new UsuarioDAO();

    /**
     * O método autenticar realiza o password_verify internamente 
     * e retorna os dados do usuário + nome do perfil.
     */
    $dadosUsuario = $usuarioDAO->autenticar($username, $senha);

    if ($dadosUsuario) {
        // --- LÓGICA DO "LEMBRAR-ME" (COOKIES) ---
        if ($lembrar) {
            setcookie('lembrar_usuario', $username, time() + (30 * 24 * 60 * 60), "/");
        } else {
            if (isset($_COOKIE['lembrar_usuario'])) {
                setcookie('lembrar_usuario', '', time() - 3600, "/");
            }
        }

        // Salva dados essenciais na sessão
        // Alteração importante: agora salvamos também o ID do perfil para a trava de segurança
        $_SESSION['id_usuario']   = $dadosUsuario['id_usuario'];
        $_SESSION['nome_usuario'] = $dadosUsuario['nome'];
        $_SESSION['id_perfil']    = $dadosUsuario['id_perfil']; // Essencial para trava_admin.php
        $_SESSION['perfil']       = $dadosUsuario['nome_perfil'];

        // --- LÓGICA DE REDIRECIONAMENTO ---

        // 1. CHECAGEM DE BLOQUEIO (Nova regra de ouro)
        // Se o ADM marcou como bloqueado, o acesso é interrompido imediatamente.
        if ($dadosUsuario['bloqueado'] == 1) {
            header("Location: ../view/login.php?erro=" . urlencode("Seu acesso foi suspenso. Procure a administração do condomínio."));
            exit;
        }

        // 2. Verificação de Primeiro Acesso (Status Ativo = 0)
        // Se a conta não estiver ativa, redireciona para ativação independente do perfil
        if ($dadosUsuario['ativo'] == 0) {
            header("Location: ../view/ativar_conta.php");
            exit;
        }

        // 3. Redirecionamento por Perfil (Após a conta estar ativa)
        // Usamos o nome do perfil conforme o seu banco de dados
        if ($dadosUsuario['nome_perfil'] === 'Administrador') {
            header("Location: ../view/admin.php");
            exit;
        } else if ($dadosUsuario['nome_perfil'] === 'Cliente') {
            header("Location: ../view/dashboard.php");
            exit;
        }
    } else {
        /**
         * Erro de autenticação: Usuário não encontrado, senha errada 
         * ou perfil não vinculado corretamente no banco (INNER JOIN falhou).
         */
        header("Location: ../view/login.php?erro=" . urlencode("Usuário ou senha incorretos."));
        exit;
    }
} else {
    // Proteção contra acesso direto ao script via URL
    header("Location: ../view/login.php");
    exit;
}
