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

    // ======================================================================
    // NOVA TRAVA DE SEGURANÇA: Impede o bloqueio do último administrador
    // ======================================================================
    if ($novoStatusBloqueio == 1) {
        
        // 1. Buscamos os dados do usuário que está prestes a ser bloqueado
        $dadosUsuario = $usuarioDAO->buscarPorId($id); // Verifique se o nome do método no seu DAO é buscarPorId
        
        // 2. Se ele for um Administrador (id_perfil == 1), precisamos checar se é o único ativo
        if ($dadosUsuario && $dadosUsuario['id_perfil'] == 1) {
            
            // 3. Conta quantos administradores não estão bloqueados no sistema
            // (Você pode criar um método rápido no seu DAO ou fazer a contagem aqui se preferir)
            $totalAdminsAtivos = $usuarioDAO->contarAdminsAtivos(); 
            
            if ($totalAdminsAtivos <= 1) {
                // Se só resta ele ativo, barra a operação e avisa o usuário
                $msgErro = "Operação negada! O sistema precisa de pelo menos um Administrador ativo.";
                header("Location: ../view/listarUsuarios.php?msg=" . urlencode($msgErro));
                exit;
            }
        }
    }

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