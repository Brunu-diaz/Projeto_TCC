<?php
require_once 'model/dao/Conexao.php';
try {
    $conn = Conexao::getConexao();
    $result = $conn->query('DESCRIBE tarifa');
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    echo 'Colunas da tabela tarifa:' . PHP_EOL;
    foreach ($columns as $col) {
        echo $col['Field'] . ' - ' . $col['Type'] . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}
?>