<?php
require_once __DIR__ . '/../model/dao/Conexao.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = Conexao::getConexao();

        // 1. Coleta os dados
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS);
        $modelo = filter_input(INPUT_POST, 'modelo', FILTER_SANITIZE_SPECIAL_CHARS);
        $leitura = filter_input(INPUT_POST, 'leitura_inicial', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $data_inst = !empty($_POST['data_instalacao']) ? $_POST['data_instalacao'] : date('Y-m-d');
        $obs = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS);
        
        // 2. Lógica para Unidade Opcional
        // Se id_unidade estiver vazio ou não for um número, definimos como NULL
        $id_unidade = filter_input(INPUT_POST, 'id_unidade', FILTER_VALIDATE_INT);
        $id_unidade_valor = ($id_unidade) ? $id_unidade : null;

        $status = 'Ativo'; 

        // 3. SQL (conforme a estrutura da imagem image_b99bf3.png)
        $sql = "INSERT INTO hidrometro (
                    codigo, 
                    modelo, 
                    leitura_inicial, 
                    data_instalacao, 
                    status, 
                    id_unidade, 
                    observacoes
                ) VALUES (
                    :codigo, 
                    :modelo, 
                    :leitura, 
                    :data_inst, 
                    :status, 
                    :id_unidade, 
                    :obs
                )";

        $stmt = $pdo->prepare($sql);
        
        // 4. Execução garantindo o envio de tipos corretos
        $stmt->bindValue(':codigo', $codigo);
        $stmt->bindValue(':modelo', $modelo);
        $stmt->bindValue(':leitura', $leitura ?: 0);
        $stmt->bindValue(':data_inst', $data_inst);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id_unidade', $id_unidade_valor, $id_unidade_valor === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':obs', $obs);

        if ($stmt->execute()) {
            header("Location: ../view/listarHidrometros.php?msg=sucesso");
        } else {
            header("Location: ../view/listarHidrometros.php?msg=erro_cadastro");
        }
        exit;

    } catch (PDOException $e) {
        // Se ainda der erro, o die() vai mostrar se é falta de permissão de NULL no banco
        die("ERRO DE BANCO: " . $e->getMessage());
    }
}