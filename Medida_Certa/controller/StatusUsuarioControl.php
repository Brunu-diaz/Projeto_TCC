<?php
/**
 * Controller: StatusUsuarioControl.php
 * Finalidade: Bloquear ou Desbloquear o acesso de um usuário (Solução Definitiva)
 */

require_once __DIR__ . '/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';

if (isset($_POST['id']) && isset($_POST['status'])) {

    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    // Agora o status 1 significa BLOQUEADO e 0 significa LIBERADO
    $novoStatusBloqueio = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);

    $usuarioDAO = new UsuarioDAO();

    // ALTERAÇÃO AQUI: Chamando o método que criamos para a nova coluna 'bloqueado'
    $resultado = $usuarioDAO->alternarBloqueio($id, $novoStatusBloqueio);

    // Lógica de Mensagem baseada na coluna 'bloqueado'
    if ($novoStatusBloqueio == 1) {
        $msg = "Acesso do usuário suspenso!";
    } else {
        $msg = "Acesso do usuário liberado!";
    }

    if ($resultado) {
        // Sugestão: Passar a mensagem via URL ou Sessão para o alert aparecer na lista
        header("Location: ../view/listarUsuarios.php?msg=" . urlencode($msg));
        exit;
    } else {
        header("Location: ../view/listarUsuarios.php?msg=erro");
        exit;
    }

} else {
    header("Location: ../view/listarUsuarios.php");
    exit();
}