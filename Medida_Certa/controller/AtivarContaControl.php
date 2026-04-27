<?php
session_start();
require_once '../model/dao/UsuarioDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/ativar_conta.php');
    exit;
}

// 1. Captura os dados do formulário
$novo_usuario    = trim($_POST['novo_usuario'] ?? '');
$senha           = trim($_POST['senha'] ?? '');
$confirmar_senha = trim($_POST['confirmar_senha'] ?? '');

// 2. Validações básicas
if (!$novo_usuario || !$senha) {
    header('Location: ../view/ativar_conta.php?erro=' . urlencode('Preencha todos os campos.'));
    exit;
}

if ($senha !== $confirmar_senha) {
    header('Location: ../view/ativar_conta.php?erro=' . urlencode('As senhas não coincidem.'));
    exit;
}

// 3. Verifica se a sessão do usuário ainda é válida
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../view/login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$usuarioDAO = new UsuarioDAO();

/**
 * CORREÇÃO AQUI: 
 * Passamos o $id, o $novo_usuario (para atualizar o login) e a $senha (que o DAO vai hashear).
 */
$sucesso = $usuarioDAO->ativarConta($id_usuario, $novo_usuario, $senha);

if ($sucesso) {
    // Atualiza o nome na sessão para refletir no Dashboard imediatamente
    $_SESSION['nome_usuario'] = $novo_usuario;
    header("Location: ../view/dashboard.php?sucesso=" . urlencode("Conta ativada com sucesso!"));
    exit;
} else {
    header('Location: ../view/ativar_conta.php?erro=' . urlencode('Erro ao ativar conta. Tente novamente.'));
    exit;
}