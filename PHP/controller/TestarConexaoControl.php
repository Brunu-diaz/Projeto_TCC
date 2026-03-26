<?php
/**
 * Controller de Teste de Conexão
 * Caminho: controller/TestarConexaoControl.php
 */

require_once '../model/dao/Conexao.php';

try {
    // Chama o método estático do seu DAO
    $conexao = Conexao::getConexao();

    if ($conexao instanceof PDO) {
        echo "<div style='color: green; font-weight: bold; padding: 10px; border: 1px solid green; background-color: #e6fffa; border-radius: 5px; font-family: sans-serif;'>";
        echo "✅ Conexão estabelecida com sucesso!<br>";
        echo "Base de dados: sistema_agua";
        echo "</div>";
    } else {
        echo "<div style='color: red; font-weight: bold; padding: 10px; border: 1px solid red; background-color: #fff5f5; border-radius: 5px; font-family: sans-serif;'>";
        echo "❌ A conexão retornou nulo. Verifique as configurações no arquivo Conexao.php.";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold; padding: 10px; border: 1px solid red; background-color: #fff5f5; border-radius: 5px; font-family: sans-serif;'>";
    echo "⚠️ Erro crítico ao tentar conectar: " . $e->getMessage();
    echo "</div>";
}

// Link para voltar ao menu
echo "<br><a href='../index.php' style='text-decoration: none; color: #3498db; font-family: sans-serif;'>← Voltar para o Início</a>";