<?php
require_once 'model/dao/Conexao.php';
try {
    $conn = Conexao::getConexao();
    echo 'Tabela tarifa existe: ' . ($conn->query("SHOW TABLES LIKE 'tarifa'")->rowCount() > 0 ? 'Sim' : 'Não') . PHP_EOL;
    echo 'Tabela tarifa_faixas existe: ' . ($conn->query("SHOW TABLES LIKE 'tarifa_faixas'")->rowCount() > 0 ? 'Sim' : 'Não') . PHP_EOL;
    $tarifa = $conn->query('SELECT * FROM tarifa')->fetchAll(PDO::FETCH_ASSOC);
    echo 'Dados tarifa: ' . count($tarifa) . ' registros' . PHP_EOL;
    if (count($tarifa) > 0) print_r($tarifa[0]);
    $faixas = $conn->query('SELECT * FROM tarifa_faixas')->fetchAll(PDO::FETCH_ASSOC);
    echo 'Dados tarifa_faixas: ' . count($faixas) . ' registros' . PHP_EOL;
    if (count($faixas) > 0) print_r(array_slice($faixas, 0, 3));
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}
?>