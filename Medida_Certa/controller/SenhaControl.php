<?php
session_start();
require_once __DIR__ . '/../model/dao/Conexao.php';

// Define para onde voltar com base no perfil (Segurança e UX)
$rota_retorno = ($_SESSION['perfil'] === 'Administrador') ? '../view/perfilAdmin.php' : '../view/perfil.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $rota_retorno");
    exit;
}

// Validação do Token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: $rota_retorno?erro=sessao_invalida");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$senha_atual = $_POST['senha_atual'];
$nova_senha = $_POST['nova_senha'];
$confirma_senha = $_POST['confirma_senha'];

if (empty($senha_atual) || empty($nova_senha) || empty($confirma_senha)) {
    header("Location: $rota_retorno?erro=campos_vazios");
    exit;
}

if ($nova_senha !== $confirma_senha) {
    header("Location: $rota_retorno?erro=senhas_diferentes");
    exit;
}

try {
    $pdo = Conexao::getConexao();

    // Busca a senha na tabela 'login'
    $sql = "SELECT senha_hash FROM login WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_usuario' => $id_usuario]);
    $login = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se a senha atual bate
    if (!$login || !password_verify($senha_atual, $login['senha_hash'])) {
        header("Location: $rota_retorno?erro=senha_atual_incorreta");
        exit;
    }

    // Hash da nova senha
    $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    $sql_update = "UPDATE login SET senha_hash = :novo_hash WHERE id_usuario = :id_usuario";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([
        ':novo_hash' => $novo_hash,
        ':id_usuario' => $id_usuario
    ]);

    header("Location: $rota_retorno?sucesso=senha_alterada");
    exit;

} catch (PDOException $e) {
    header("Location: $rota_retorno?erro=db_error");
    exit;
}