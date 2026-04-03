<?php
//Classe de Conexão com Banco de Dados
//Local: model/dao/Conexao.php
class Conexao {
 //Método estático para obter a conexão.
 //Pode ser chamado diretamente como Conexao::getConexao()

 public static function getConexao() {
 // Configurações do seu servidor local (XAMPP/WAMP)
 $host = "localhost"; //O padrão é sempre colocar localhost, porém como mudei a porta, tem que especificar
 $dbname = "sistema_agua"; // Nome do banco definido no seu banco.sql
 $usuario = "root"; // Usuário padrão do XAMPP
 $senha = "123456"; // Senha padrão do XAMPP (vazia)
 try {
 // Cria a instância do PDO
 $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8",
$usuario, $senha);

 // Configura o PDO para lançar exceções em caso de erro de SQL
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 return $pdo;
 } catch (PDOException $e) {
 // Caso falhe, exibe o erro na tela
 echo "Erro na conexão: " . $e->getMessage();
 return null;
 }
 }
}
?>