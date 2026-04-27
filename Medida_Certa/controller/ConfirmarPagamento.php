<?php
require_once __DIR__ . '/../model/dao/Conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_fatura'])) {
    $id_fatura = $_POST['id_fatura'];

    try {
        $database = new Conexao();
        $pdo = $database->getConexao();

        // SQL para atualizar a fatura
        $sql = "UPDATE fatura SET 
                status_pagamento = 'Pago', 
                data_pagamento = NOW() 
                WHERE id_fatura = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id_fatura, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Redireciona de volta para o painel com uma bandeira de sucesso
            header("Location: ../view/dashboard.php?pagamento=sucesso");
            exit;
        }
    } catch (Exception $e) {
        die("Erro ao processar pagamento: " . $e->getMessage());
    }
} else {
    header("Location: ../view/dashboard.php");
    exit;
}