<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verifica se o usuário está logado e se o perfil é de CLIENTE (ID 2)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 2) {
    // Destrói qualquer resíduo de sessão e manda para o login
    session_destroy();
    header("Location: ../view/login.php?erro=Acesso restrito. Faça login.");
    exit();
}

// 2. Verifica se o sistema está em MODO DE MANUTENÇÃO
require_once __DIR__ . '/../model/dao/Conexao.php';

try {
    $conn = Conexao::getConexao();
    
    // Busca o status do modo de manutenção na tabela de configurações
    $sql = "SELECT valor FROM configuracoes WHERE chave = 'modo_manutencao' LIMIT 1";
    $stmt = $conn->query($sql);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se o valor for '1', redireciona o cliente para a página de aviso
    if ($config && $config['valor'] == '1') {
        header("Location: ../view/manutencao.php");
        exit();
    }
} catch (PDOException $e) {
    // Caso a tabela ainda não exista ou ocorra erro no banco, 
    // permitimos o acesso para não travar o sistema por erro técnico.
    error_log("Erro ao verificar modo de manutenção: " . $e->getMessage());
}
?>