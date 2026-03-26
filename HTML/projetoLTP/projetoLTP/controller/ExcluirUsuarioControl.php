<?php
// Controller responsável por processar a exclusão
require_once '../Model/dao/UsuarioDAO.php';
// Verifica se o ID foi enviado através do método POST
if (isset($_POST['id'])) {
 $id = $_POST['id'];
 // Instancia o DAO para acessar o método de exclusão
 $usuarioDAO = new UsuarioDAO();
 $resultado = $usuarioDAO->excluirUsuario($id);
 // Verifica se a exclusão funcionou para exibir o alerta correto
 if ($resultado) {
 echo "<script>alert('Usuário excluído com sucesso!');";
 echo "window.location.href =
'../view/listarUsuarios.php';</script>";
 } else {
 echo "<script>alert('Erro ao excluir usuário!');";
 echo "window.location.href =
'../view/listarUsuarios.php';</script>";
 }
}
?>