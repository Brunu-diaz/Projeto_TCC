<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../dto/UsuarioDTO.php';
class UsuarioDAO {

// Método para cadastrar um novo usuário no banco de dados
public function cadastrarUsuario(UsuarioDTO $usuarioDTO) {
    try {
    $pdo = Conexao::getConexao();
    $pdo->beginTransaction();

// Inserir na tabela usuario
    $sqlUser = "INSERT INTO usuario (nome, email, ativo) VALUES (?, ?, ?)";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->bindValue(1, $usuarioDTO->getNome());
    $stmtUser->bindValue(2, $usuarioDTO->getEmail());
    $stmtUser->bindValue(3, 1); // Ativo por padrão
    $stmtUser->execute();

// Pega o ID gerado para o usuário
    $idUsuario = $pdo->lastInsertId();

// Inserir na tabela login
    $sqlLogin = "INSERT INTO login (id_usuario, username, senha_hash) VALUES (?, ?, ?)";
    $stmtLogin = $pdo->prepare($sqlLogin);
    $stmtLogin->bindValue(1, $idUsuario);
    $stmtLogin->bindValue(2, $usuarioDTO->getEmail()); // Usando email como username
    $stmtLogin->bindValue(3, password_hash($usuarioDTO->getSenha(), PASSWORD_DEFAULT));       
    $stmtLogin->execute();

// Salva tudo no banco
    $pdo->commit();
 return true
 } catch (PDOException $e) {
    $pdo->rollBack(); // Desfaz se algo der errado
    echo "Erro ao cadastrar: " . $e->getMessage();
 return false;
 }
 }

 // Método para listar todos os usuários (ativos e inativos)
 public function listarUsuarios() {
 try {
 $pdo = Conexao::getConexao();
 // Buscamos o status para que a View saiba se mostra o botão Ativar ou Desativar
 $sql = "SELECT id_usuario, nome, email, ativo FROM usuario";
 $stmt = $pdo->prepare($sql);
 $stmt->execute();
 return $stmt->fetchAll(PDO::FETCH_ASSOC);
 } catch (PDOException $e) {
 echo "Erro ao listar: " . $e->getMessage();
 return [];
 }
 }

 // Método único para Ativar (1) ou Desativar (0) - Exclusão Lógica
 public function alterarStatus($id, $novoStatus) {
 try {
 $pdo = Conexao::getConexao();
 $sql = "UPDATE usuario SET status = ? WHERE id = ?";
 $stmt = $pdo->prepare($sql);
 $stmt->bindValue(1, $novoStatus);
 $stmt->bindValue(2, $id);
 return $stmt->execute();
 } catch (PDOException $e) {
 echo "Erro ao alterar status: " . $e->getMessage();
 return false;
 }
 }

 // Método para excluir um usuário permanentemente (Exclusão Física)
 public function excluirUsuario($id) {
 try {
 $pdo = Conexao::getConexao();
 $sql = "DELETE FROM usuario WHERE id = ?";
 $stmt = $pdo->prepare($sql);
 $stmt->bindValue(1, $id);
 return $stmt->execute();
 } catch (PDOException $e) {
 echo "Erro ao excluir: " . $e->getMessage();
 return false;
 }
 }
} // Fim da classe UsuarioDAO
?>