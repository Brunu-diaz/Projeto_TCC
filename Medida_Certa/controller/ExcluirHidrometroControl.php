<?php
require_once __DIR__ . '/../model/dao/Conexao.php';
session_start();

// Segurança: Verifica se é Admin
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: ../view/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id_hidrometro', FILTER_SANITIZE_NUMBER_INT);

    if ($id) {
        try {
            $pdo = Conexao::getConexao();
            
            // Prepara a exclusão
            $stmt = $pdo->prepare("DELETE FROM hidrometro WHERE id_hidrometro = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Redireciona com a tag que o seu alerta já mapeia como "removido com sucesso"
                header("Location: ../view/listarHidrometros.php?msg=excluido");
            } else {
                header("Location: ../view/listarHidrometros.php?msg=erro_excluir");
            }
            exit;

        } catch (PDOException $e) {
            // Caso existam chaves estrangeiras (ex: leituras vinculadas), o banco impedirá a exclusão
            error_log("Erro ao excluir hidrômetro: " . $e->getMessage());
            header("Location: ../view/listarHidrometros.php?msg=erro_dependencia");
            exit;
        }
    }
}

// Se tentar acessar o arquivo sem POST ou sem ID
header("Location: ../view/listarHidrometros.php");
exit;