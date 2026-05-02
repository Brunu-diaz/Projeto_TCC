<?php
/**
 * Controller para confirmar o pagamento de uma fatura
 * Localização: controller/confirmarpagamento.php
 */

// 1. Configuração de Sessão Segura e Global
session_set_cookie_params(['path' => '/']); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/dao/Conexao.php';

// 2. Trava de Segurança Básica
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../view/login.php?erro=sessao_expirada");
    exit;
}

// 3. Processamento do Pagamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_fatura']) && !empty($_POST['id_fatura'])) {
    
    $id_fatura = $_POST['id_fatura'];

    try {
        $pdo = Conexao::getConexao();

        // SQL para atualizar o status e a data do pagamento
        $sql = "UPDATE fatura SET 
                status_pagamento = 'Pago', 
                data_pagamento = NOW() 
                WHERE id_fatura = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id_fatura, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // REDIRECIONAMENTO COM SUCESSO
            // Passamos o id_fatura de volta para que a página saiba qual fatura carregar
            header("Location: ../view/faturapdf.php?id_fatura=" . $id_fatura . "&pagamento=sucesso");
            exit;
        } else {
            throw new Exception("Falha ao executar a atualização no banco de dados.");
        }

    } catch (Exception $e) {
        // Em caso de erro técnico, interrompe e exibe a mensagem
        die("Erro crítico ao processar pagamento: " . $e->getMessage());
    }

} else {
    /** * PROTEÇÃO CONTRA O ERRO DE PARÂMETROS AUSENTES:
     * Se o ID não veio pelo POST, não podemos mandar para faturapdf.php,
     * pois lá o script daria o erro que você viu na imagem.
     */
    header("Location: ../view/dashboard.php?erro=id_fatura_nao_recebido");
    exit;
}