<?php
// Importa o DAO para buscar os usuários no banco
require_once '../Model/dao/UsuarioDAO.php';
// Instancia o DAO e busca todos os usuários
$usuarioDAO = new UsuarioDAO();
$usuarios = $usuarioDAO->listarUsuarios();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
 <meta charset="UTF-8">
 <title>Editar Usuários</title>
 <link rel="stylesheet" href="../assets/estilo.css">
</head>
<body>
 <h1>Editar Usuários</h1>
 <table border="1">
 <tr>
 <th>ID</th>
 <th>Nome</th>
 <th>Email</th>
 <th>Ação</th>
 </tr>
 <?php foreach ($usuarios as $usuario): ?>
 <tr>
 <td><?php echo $usuario['id']; ?></td>
 <td><?php echo $usuario['nome']; ?></td>
 <td><?php echo $usuario['email']; ?></td>
 <!-- Botão que envia o ID via POST para o controller -->
 <td>
 <form action="../controller/EditarUsuarioControl.php"
method="post">
 <input type="hidden" name="id" value="<?php echo
$usuario['id']; ?>">
 <button type="submit">Editar</button>
 </form>
 </td>
 </tr>
 <?php endforeach; ?>
 </table>
 <br>
 <!-- Link para voltar ao menu principal -->
 <a href="../index.php">Voltar</a>
</body>
</html>