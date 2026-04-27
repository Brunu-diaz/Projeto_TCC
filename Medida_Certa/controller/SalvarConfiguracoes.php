<?php
session_start();
require_once __DIR__ . '/../model/dao/Conexao.php';

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Validação de Segurança CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token de segurança inválido. Tente atualizar a página.");
    }

    $conn = Conexao::getConexao();

    // 2. Mapeamento dos Dados (Sincronizado com o Banco de Dados)
    // Usamos os nomes das chaves conforme sua tabela no BD
    $configuracoes = [
        'tarifa_minima_valor'     => $_POST['valor_m3'] ?? '0.00',
        'taxa_esgoto_percentual'  => $_POST['taxa_esgoto'] ?? '0',
        'dia_vencimento'          => $_POST['dia_vencimento'] ?? '10',
        'alerta_vazamento'        => isset($_POST['alerta_vazamento']) ? '1' : '0',
        'alerta_inadimplencia'    => isset($_POST['alerta_inadimplencia']) ? '1' : '0', // Adicionado
        'modo_manutencao'         => isset($_POST['modo_manutencao']) ? '1' : '0',
        'nome_condominio'         => $_POST['nome_condominio'] ?? 'Residencial MedidaCerta'
    ];

    try {
        $conn->beginTransaction();

        // Prepara a Query uma única vez para performance
        $stmt = $conn->prepare("UPDATE configuracoes SET valor = :valor, updated_at = NOW() WHERE chave = :chave");

        // 3. Executa o Loop para cada configuração
        foreach ($configuracoes as $chave => $valor) {
            $stmt->execute([
                ':valor' => $valor,
                ':chave' => $chave
            ]);
        }

        $conn->commit();
        
        // 4. Redirecionamento de Sucesso (O exit é crucial aqui)
        header("Location: ../view/configuracoesAdmin.php?sucesso=1");
        exit;

    } catch (Exception $e) {
        // Se algo der errado, desfaz as alterações no banco
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        error_log("Erro ao salvar configurações MedidaCerta: " . $e->getMessage());
        header("Location: ../view/configuracoesAdmin.php?erro=1");
        exit;
    }
} else {
    // Se tentarem acessar o controller direto pela URL, redireciona
    header("Location: ../view/configuracoesAdmin.php");
    exit;
}