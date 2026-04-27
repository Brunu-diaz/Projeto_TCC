<?php
// 1. Segurança: Somente Admins podem excluir
require_once __DIR__ . '/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// 2. Captura o ID da unidade
$id_unidade = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id_unidade) {
    header("Location: ../view/unidades.php?erro=" . urlencode("ID inválido para exclusão."));
    exit;
}

try {
    $pdo = Conexao::getConexao();

    /* DICA DE ARQUITETURA:
       Se o seu banco de dados estiver com ON DELETE CASCADE na FK do Hidrômetro,
       basta excluir a unidade. Caso contrário, excluímos o hidrômetro antes.
    */
    
    $pdo->beginTransaction();

    // 1º: Remove o hidrômetro vinculado a esta unidade
    $sqlHidro = "DELETE FROM hidrometro WHERE id_unidade = :id";
    $stmtH = $pdo->prepare($sqlHidro);
    $stmtH->execute([':id' => $id_unidade]);

    // 2º: Remove a unidade
    $sqlUnidade = "DELETE FROM unidade WHERE id_unidade = :id";
    $stmtU = $pdo->prepare($sqlUnidade);
    $stmtU->execute([':id' => $id_unidade]);

    $pdo->commit();

    // Redireciona com mensagem de sucesso
    header("Location: ../view/unidades.php?sucesso=" . urlencode("Unidade removida com sucesso do sistema."));
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Se houver erro (ex: unidade com faturas vinculadas sem CASCADE), o PHP avisará aqui
    header("Location: ../view/unidades.php?erro=" . urlencode("Erro ao excluir: Verifique se existem leituras ou faturas vinculadas."));
    exit;
}