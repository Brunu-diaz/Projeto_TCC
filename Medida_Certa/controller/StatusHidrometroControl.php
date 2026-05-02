<?php
require_once __DIR__ . '/../model/dao/Conexao.php';
session_start();

// Segurança: Apenas Administrador pode alterar status
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: ../view/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $novoStatus = $_POST['novo_status'] ?? null;

    // Validação básica: o status deve ser 'Ativo' ou 'Inativo' conforme seu ENUM
    if ($id && ($novoStatus === 'Ativo' || $novoStatus === 'Inativo')) {
        try {
            $pdo = Conexao::getConexao();
            
            // Query para atualizar o status no banco de dados
            $sql = "UPDATE hidrometro SET status = :status WHERE id_hidrometro = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':status', $novoStatus);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Redireciona com mensagem de sucesso
                $msg = ($novoStatus === 'Ativo') ? "Hidrômetro ativado com sucesso!" : "Hidrômetro desativado com sucesso!";
                header("Location: ../view/listarHidrometros.php?msg=" . urlencode($msg));
                exit;
            }
        } catch (Exception $e) {
            error_log("Erro ao mudar status: " . $e->getMessage());
            header("Location: ../view/listarHidrometros.php?msg=erro");
            exit;
        }
    }
}

// Se algo der errado ou acesso direto, volta para a lista
header("Location: ../view/listarHidrometros.php");
exit;