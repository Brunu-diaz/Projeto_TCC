<?php
require_once 'Conexao.php';
require_once __DIR__ . '/../dto/UsuarioDTO.php';
class UsuarioDAO {

 // Método para cadastrar um novo usuário no banco de dados
 public function cadastrarUsuario(UsuarioDTO $usuarioDTO) {
 try {
 $pdo = Conexao::getConexao();
 $sql = "INSERT INTO usuario (nome, email, senha) VALUES (?,
?, ?)";
 $stmt = $pdo->prepare($sql);
 $stmt->bindValue(1, $usuarioDTO->getNome());
 $stmt->bindValue(2, $usuarioDTO->getEmail());
 $stmt->bindValue(3, md5($usuarioDTO->getSenha()));
 return $stmt->execute();
 } catch (PDOException $e) {
 echo "Erro ao cadastrar: " . $e->getMessage();
 return false;
 }
 }

 // Método para listar todos os usuários (ativos e inativos)
 public function listarUsuarios() {
 try {
 $pdo = Conexao::getConexao();
 // Buscamos o status para que a View saiba se mostra o botão Ativar ou Desativar
 $sql = "SELECT id, nome, email, status FROM usuario";
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