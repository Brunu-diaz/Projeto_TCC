<!DOCTYPE html>
<html lang="pt-br">
<head>
 <meta charset="UTF-8">
 <title>Sistema de Usuários</title>
 <!-- Link para o CSS centralizado na pasta assets -->
 <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
 <h1>Sistema de Cadastro de Usuários</h1>

 <!-- Seção administrativa para verificar a saúde do banco de dados
-->
 <fieldset>
 <legend>Administração</legend>
 <form action="controller/TestarConexaoControl.php"
method="post">
 <button type="submit">Testar Conexão com o Banco</button>
 </form>
 </fieldset>

 <hr>

 <!-- Seção principal de entrada de dados -->
 <h2>Cadastrar Novo Usuário</h2>
 <form action="controller/CadastroUsuarioControl.php" method="post">
 <label>Nome:</label><br>
 <input type="text" name="nome" placeholder="Digite o nome
completo" required><br><br>

 <label>Email:</label><br>
 <input type="email" name="email"
placeholder="exemplo@email.com" required><br><br>

 <label>Senha:</label><br>
 <input type="password" name="senha" required><br><br>

 <button type="submit">Finalizar Cadastro</button>
 <button type="reset">Limpar Campos</button>
 </form><br><br>

  <!-- Seção principal de entrada de dados -->
 <h2>Cadastrar Novo Produto</h2>
 <form action="controller/CadastroProdutoControl.php" method="post">
 <label>Nome:</label><br>
 <input type="text" name="nome" placeholder="Digite o nome
completo" required><br><br>

 <label>Quantidade:</label><br>
 <input type="number" name="quantidade"
placeholder="Somente Números" required><br><br>

 <button type="submit">Finalizar Cadastro</button>
 <button type="reset">Limpar Campos</button>
 </form><br>

 <hr>

 <!-- Seção de acesso ao gerenciamento (Listagem, Edição e Exclusão)
-->
 <h2>Gerenciar Usuários</h2>
 <p>Acesse a lista para visualizar, desativar ou excluir registros
permanentemente:</p>

 <a href="View/listarUsuarios.php">
 <button type="button" style="padding: 10px 25px; cursor:
pointer; font-weight: bold;">
 Visualizar Lista de Usuários
 </button>
 </a>
</body>
</html>