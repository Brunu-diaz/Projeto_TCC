<?php
require_once __DIR__ . '/../model/dao/Conexao.php';
session_start();

// 1. Verificação de Segurança (Apenas Administradores)
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: ../view/login.php");
    exit;
}

// 2. Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 3. Higienização e recebimento dos dados (Incluindo os novos campos)
    $id_hidrometro   = filter_input(INPUT_POST, 'id_hidrometro', FILTER_SANITIZE_NUMBER_INT);
    $codigo          = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS);
    $modelo          = filter_input(INPUT_POST, 'modelo', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_unidade      = filter_input(INPUT_POST, 'id_unidade', FILTER_SANITIZE_NUMBER_INT);
    
    // Novos campos baseados na sugestão anterior:
    $leitura_inicial = filter_input(INPUT_POST, 'leitura_inicial', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $data_instalacao = filter_input(INPUT_POST, 'data_instalacao', FILTER_SANITIZE_SPECIAL_CHARS);
    $observacoes     = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS);

    // Validação básica de campos obrigatórios
    if (!$id_hidrometro || !$codigo || !$id_unidade) {
        header("Location: ../view/listarHidrometros.php?msg=erro_dados_incompletos");
        exit;
    }

    try {
        $pdo = Conexao::getConexao();

        // 4. Preparação da SQL de Update (Atualizada com os novos campos e sem o status)
        $sql = "UPDATE hidrometro SET 
                    codigo = :codigo, 
                    modelo = :modelo, 
                    id_unidade = :id_unidade,
                    leitura_inicial = :leitura_inicial,
                    data_instalacao = :data_instalacao,
                    observacoes = :observacoes
                WHERE id_hidrometro = :id";

        $stmt = $pdo->prepare($sql);

        // Bind dos parâmetros
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':id_unidade', $id_unidade, PDO::PARAM_INT);
        $stmt->bindParam(':leitura_inicial', $leitura_inicial);
        $stmt->bindParam(':data_instalacao', $data_instalacao);
        $stmt->bindParam(':observacoes', $observacoes);
        $stmt->bindParam(':id', $id_hidrometro, PDO::PARAM_INT);

        // 5. Execução e Redirecionamento
        if ($stmt->execute()) {
            header("Location: ../view/listarHidrometros.php?msg=sucesso_update");
        } else {
            header("Location: ../view/listarHidrometros.php?msg=erro_update");
        }
        exit;

    } catch (PDOException $e) {
        error_log("Erro ao atualizar hidrômetro ID $id_hidrometro: " . $e->getMessage());
        header("Location: ../view/listarHidrometros.php?msg=erro_db");
        exit;
    }
} else {
    header("Location: ../view/listarHidrometros.php");
    exit;
}