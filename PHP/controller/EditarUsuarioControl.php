<?php
require_once '../Model/dto/UsuarioDTO.php';
require_once '../Model/dao/UsuarioDAO.php';

$usuarioDAO = new UsuarioDAO();

// PARTE 1: Processar a atualização (Salvar)
if (isset($_POST['nome']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Criamos o DTO com os novos dados do formulário
    $usuarioDTO = new UsuarioDTO();
    $usuarioDTO->setNome($_POST['nome']);
    $usuarioDTO->setEmail($_POST['email']);
    $usuarioDTO->setSenha($_POST['senha']);

    // Lógica simples: Removemos o antigo e cadastramos o novo com os dados atualizados
    // Já que o seu DAO possui os métodos excluirUsuario e cadastrarUsuario
    $usuarioDAO->excluirUsuario($id);
    $usuarioDAO->cadastrarUsuario($usuarioDTO);

    header("Location: ../view/listarUsuarios.php");
    exit();

// PARTE 2: Exibir o formulário (Carregar dados)
} else if (isset($_POST['id'])) {
    $idBuscado = $_POST['id'];
    $lista = $usuarioDAO->listarUsuarios();
    $usuario = null;

    foreach ($lista as $u) {
        if ($u['id'] == $idBuscado) {
            $usuario = $u;
            break;
        }
    }

    if ($usuario) {
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body>
    <h1>Editar Usuário</h1>
    <form action="EditarUsuarioControl.php" method="post">
        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
        
        <label>Nome:</label>
        <input type="text" name="nome" value="<?php echo $usuario['nome']; ?>" required>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $usuario['email']; ?>" required>
        
        <label>Senha:</label>
        <input type="password" name="senha" required>
        
        <button type="submit">Salvar Alterações</button>
    </form>
    <br>
    <a href="../view/listarUsuarios.php">Voltar</a>
</body>
</html>
<?php 
    }
} 
?>