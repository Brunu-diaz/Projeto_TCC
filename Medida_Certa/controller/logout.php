<?php
// Inicia a sessão para ter acesso aos dados que serão destruídos
session_start();

// Limpa todas as variáveis de sessão da memória
$_SESSION = array();

// Se desejar destruir completamente o cookie de sessão no navegador do usuário
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão no servidor
session_destroy();

// Redireciona para a tela de login com uma mensagem de confirmação
header("Location: ../view/login.php?msg=saiu_com_sucesso");
exit();