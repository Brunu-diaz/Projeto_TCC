<?php
session_start();
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';

// Segurança: só ADM pode resetar
if ($_SESSION['perfil'] !== 'Administrador') {
    header("Location: ../view/login.php");
    exit;
}

$id_usuario = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($id_usuario) {
    $usuarioDAO = new UsuarioDAO();
    if ($usuarioDAO->resetarSenha($id_usuario)) {
        // Sucesso: Volta para o dashboard com mensagem
        header("Location: ../view/dashboard_adm.php?msg=" . urlencode("Senha resetada para 'Medida123'"));
    } else {
        header("Location: ../view/dashboard_adm.php?erro=" . urlencode("Erro ao resetar senha."));
    }
}
exit;