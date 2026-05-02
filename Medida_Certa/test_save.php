<?php
// Simular POST para testar SalvarConfiguracoes.php
$_POST = [
    'csrf_token' => 'test',
    'id_tarifa' => '1',
    'nome_condominio' => 'Teste Condominio',
    'valor_fixo' => '15.00',
    'valor_m3' => '5.00',
    'taxa_esgoto' => '80',
    'dia_vencimento' => '15',
    'alerta_vazamento' => '1',
    'alerta_inadimplencia' => '1',
    'modo_manutencao' => '0',
    'faixa_limite' => ['10', '20', '30'],
    'faixa_valor' => ['3.50', '4.50', '6.00']
];

$_SERVER['REQUEST_METHOD'] = 'POST';

session_start();
$_SESSION['csrf_token'] = 'test';

require_once 'controller/SalvarConfiguracoes.php';
?>