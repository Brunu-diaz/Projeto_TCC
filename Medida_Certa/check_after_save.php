<?php
require_once 'model/dao/Conexao.php';
try {
    $conn = Conexao::getConexao();
    $tarifa = $conn->query('SELECT * FROM tarifa WHERE id_tarifa = 1')->fetch(PDO::FETCH_ASSOC);
    echo 'Tarifa após update:' . PHP_EOL;
    print_r($tarifa);
    $faixas = $conn->query('SELECT * FROM tarifa_faixas WHERE id_tarifa = 1')->fetchAll(PDO::FETCH_ASSOC);
    echo 'Faixas após update:' . PHP_EOL;
    print_r($faixas);
} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}
?>