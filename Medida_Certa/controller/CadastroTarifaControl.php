<?php
session_start();
require_once __DIR__ . '/../model/dao/Conexao.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Captura e sanitiza os dados do formulário
    $nome          = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $valor_m3      = filter_input(INPUT_POST, 'valor_m3', FILTER_VALIDATE_FLOAT);
    $taxa_esgoto   = filter_input(INPUT_POST, 'taxa_esgoto', FILTER_VALIDATE_FLOAT);
    $data_vigencia = filter_input(INPUT_POST, 'data_vigencia', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validação básica
    if ($nome && $valor_m3 !== false && $taxa_esgoto !== false && $data_vigencia) {
        
        try {
            $pdo = Conexao::getConexao();
            $pdo->beginTransaction(); // Inicia transação para garantir que tudo seja salvo ou nada

            // 1. Inserção na tabela 'tarifa' conforme o diagrama
            $sqlTarifa = "INSERT INTO tarifa (nome, valor_m3, taxa_esgoto, data_vigencia) 
                          VALUES (:nome, :valor_m3, :taxa_esgoto, :data_vigencia)";
            
            $stmt = $pdo->prepare($sqlTarifa);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':valor_m3', $valor_m3);
            $stmt->bindValue(':taxa_esgoto', $taxa_esgoto);
            $stmt->bindValue(':data_vigencia', $data_vigencia);
            $stmt->execute();

            // 2. Sincronização com a tabela de 'configuracoes' (Melhoria de arquitetura)
            // Isso permite que o sistema recupere o valor atual de forma mais simples
            $sqlConfig = "INSERT INTO configuracoes (chave, valor) 
                          VALUES ('tarifa_atual_m3', :valor_m3), ('taxa_esgoto_atual', :taxa_esgoto)
                          ON DUPLICATE KEY UPDATE valor = VALUES(valor)";
            
            $stmtConfig = $pdo->prepare($sqlConfig);
            $stmtConfig->execute([':valor_m3' => $valor_m3, ':taxa_esgoto' => $taxa_esgoto]);

            $pdo->commit(); // Confirma as alterações

            // Redireciona de volta com sucesso
            header("Location: ../view/cadastrarTarifa.php?sucesso=1");
            exit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack(); // Cancela se algo der errado
            }
            error_log("Erro no MedidaCerta (Cadastro de Tarifa): " . $e->getMessage());
            header("Location: ../view/cadastrarTarifa.php?erro=db_error");
            exit();
        }

    } else {
        header("Location: ../view/cadastrarTarifa.php?erro=campos_invalidos");
        exit();
    }
} else {
    // Se alguém tentar acessar o arquivo diretamente sem POST
    header("Location: ../view/admin.php");
    exit();
}