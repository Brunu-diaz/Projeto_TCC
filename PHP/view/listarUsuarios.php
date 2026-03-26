<?php
// Importa o DAO para buscar os usuários no banco
require_once '../Model/dao/UsuarioDAO.php';
// Instancia o DAO e busca a lista de usuários
$usuarioDAO = new UsuarioDAO();
$usuarios = $usuarioDAO->listarUsuarios();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Listar Usuários</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
    <h1>Lista de Usuários</h1>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?php echo $usuario['id']; ?></td>
            <td><?php echo $usuario['nome']; ?></td>
            <td><?php echo $usuario['email']; ?></td>
            <td><?php echo ($usuario['status'] ?? 0) ? 'Ativo' : 'Inativo'; ?></td>
            <td>
                <div style="display: flex; gap: 10px;">

                    <form action="../controller/EditarUsuarioControl.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                        <button type="submit">Editar</button>
                    </form>

                    <?php if (($usuario['status'] ?? 0) == 1): ?>
                        <form action="../controller/StatusUsuarioControl.php" method="post" 
                              onsubmit="return confirm('Deseja realmente desativar este usuário?');">
                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                            <input type="hidden" name="status" value="0">
                            <button type="submit" style="background-color: orange;">Desativar</button>
                        </form>
                    <?php else: ?>
                        <form action="../controller/StatusUsuarioControl.php" method="post">
                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                            <input type="hidden" name="status" value="1">
                            <button type="submit" style="background-color: green; color: white;">Ativar</button>
                        </form>
                    <?php endif; ?>

                    <form action="../controller/ExcluirUsuarioControl.php" method="post" 
                          onsubmit="return confirm('CUIDADO: Deseja apagar permanentemente?');">
                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                        <button type="submit" style="color: red;">Excluir</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <a href="../index.php">Voltar</a>
</body>
</html>