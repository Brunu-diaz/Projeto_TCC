<!DOCTYPE html>
<html lang="pt-br">
<head>
 <meta charset="UTF-8">
 <title>Cadastrar Produto</title>
 <link rel="stylesheet" href="../assets/estilo.css">
</head>
<body>
 <div>
 <h1>Cadastrar Produto</h1>
 <form action="../controller/CadastroProdutoControl.php"
method="post">
 <label>Nome:</label>
 <input type="text" name="nome" required>
 <label>Quantidade:</label>
 <input type="number" name="quantidade" required>
 <button type="submit">Cadastrar</button>
 </form>
 <br>
 <a href="../index.php"><button type="button">Voltar</button></a>
 </div>
</body>
</html>