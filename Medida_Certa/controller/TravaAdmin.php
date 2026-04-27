<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e se o perfil é de administrador (ID 1 no seu BD)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 1) {
    // Destrói qualquer resíduo de sessão e manda para o login
    session_destroy();
    header("Location: ../view/login.php?erro=Acesso restrito. Faça login.");
    exit();
}
?>