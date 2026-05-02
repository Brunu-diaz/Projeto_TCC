<?php
// 1. Início de sessão e trava de segurança
require_once __DIR__ . '/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Verifica se a requisição é POST para processar os dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. Coleta de dados vindos do formulário
    $id_leitura        = $_POST['id_leitura'] ?? null;
    $valor_medido      = $_POST['valor_medido'] ?? null;
    $consumo_calculado = $_POST['consumo_calculado'] ?? null;
    $data_leitura      = $_POST['data_leitura'] ?? null;
    $id_funcionario    = $_SESSION['id_usuario'] ?? null; // ID do admin que está editando

    // Validação básica de segurança
    if (!$id_leitura || !$valor_medido || !$data_leitura) {
        header("Location: ../view/listarleituras.php?msg=Erro: Dados incompletos");
        exit;
    }

    try {
        $pdo = Conexao::getConexao();

        // 3. Execução do UPDATE no Banco de Dados
        // Atualizamos o valor medido, o consumo calculado automaticamente pelo JS e quem editou
        $sql = "UPDATE leitura SET 
                valor_medido = :valor, 
                consumo_calculado = :consumo, 
                data_leitura = :data,
                id_funcionario = :func
                WHERE id_leitura = :id";

        $stmt = $pdo->prepare($sql);
        
        $stmt->bindValue(':valor', $valor_medido);
        $stmt->bindValue(':consumo', $consumo_calculado);
        $stmt->bindValue(':data', $data_leitura);
        $stmt->bindValue(':func', $id_funcionario);
        $stmt->bindValue(':id', $id_leitura);

        if ($stmt->execute()) {
            // 4. Redirecionamento original: Volta para a lista com mensagem de sucesso
            header("Location: ../view/listarleituras.php?msg=Leitura atualizada com sucesso!");
            exit;
        } else {
            header("Location: ../view/listarleituras.php?msg=erro");
            exit;
        }

    } catch (PDOException $e) {
        // Log de erro silencioso para o sistema e aviso para o usuário
        error_log("Erro ao editar leitura: " . $e->getMessage());
        header("Location: ../view/listarleituras.php?msg=Erro técnico ao salvar");
        exit;
    }
} else {
    // Se tentarem acessar o arquivo via URL, manda para a lista
    header("Location: ../view/listarleituras.php");
    exit;
}