<?php
require_once 'model/dao/Conexao.php';

try {
    $conn = Conexao::getConexao();
    
    echo "<h2>Testando Tabela Configuracoes</h2>";
    
    // Verifica se tabela existe
    $result = $conn->query("SHOW TABLES LIKE 'configuracoes'");
    echo "<p><strong>Tabela existe?</strong> " . ($result->rowCount() > 0 ? "✅ Sim" : "❌ Não") . "</p>";
    
    // Mostra dados
    echo "<h3>Dados na tabela:</h3>";
    $data = $conn->query("SELECT chave, valor FROM configuracoes");
    $configs = $data->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($configs) > 0) {
        echo "<pre>";
        print_r($configs);
        echo "</pre>";
    } else {
        echo "<p style='color:red;'>⚠️ Tabela vazia!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Erro: " . $e->getMessage() . "</p>";
}
?>
