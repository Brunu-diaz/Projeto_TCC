<?php
require_once '../model/dao/Conexao.php';
$pdo = Conexao::getConexao();
$nova_senha = password_hash('admin123', PASSWORD_DEFAULT);

$sql = "UPDATE login SET senha_hash = ? WHERE username = 'admin'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nova_senha]);

echo "Senha atualizada com sucesso para: admin123";
?>