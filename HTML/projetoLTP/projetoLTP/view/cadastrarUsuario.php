<!DOCTYPE html>
<html lang="pt-br">
<head>
 <meta charset="UTF-8">
 <title>Cadastrar Usuário</title>
 <link rel="stylesheet" href="../assets/estilo.css">
</head>
<body>
 <div>
 <h1>Cadastrar Usuário</h1>
 <form action="../controller/CadastroUsuarioControl.php"
method="post">
 <label>Nome:</label>
 <input type="text" name="nome" required>
 <label>Email:</label>
 <input type="email" name="email" required>
 <label>Senha:</label>
 <input type="password" name="senha" required>
 <button type="submit">Cadastrar</button>
 </form>
 <br>
 <a href="../index.php"><button type="button">Voltar</button></a>
 </div>
</body>
</html>