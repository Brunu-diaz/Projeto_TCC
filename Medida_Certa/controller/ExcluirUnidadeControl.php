<?php
require_once __DIR__ . '/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

$id_unidade = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id_unidade) {
    header("Location: ../view/unidades.php?erro=" . urlencode("ID inválido para exclusão."));
    exit;
}

try {
    $pdo = Conexao::getConexao();
    $pdo->beginTransaction();

    // 1º: Busca os IDs dos hidrômetros vinculados
    $stmtIds = $pdo->prepare("SELECT id_hidrometro FROM hidrometro WHERE id_unidade = :id");
    $stmtIds->execute([':id' => $id_unidade]);
    $ids = $stmtIds->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // 2º: Remove faturas vinculadas às leituras desses hidrômetros
        $pdo->prepare("DELETE FROM fatura WHERE id_leitura IN (SELECT id_leitura FROM leitura WHERE id_hidrometro IN ($placeholders))")
            ->execute($ids);

        // 3º: Remove as leituras
        $pdo->prepare("DELETE FROM leitura WHERE id_hidrometro IN ($placeholders)")
            ->execute($ids);

        // 4º: Remove os hidrômetros
        $pdo->prepare("DELETE FROM hidrometro WHERE id_unidade = ?")
            ->execute([$id_unidade]);
    }

    // 5º: Remove a unidade
    $pdo->prepare("DELETE FROM unidade WHERE id_unidade = ?")
        ->execute([$id_unidade]);

    $pdo->commit();

    header("Location: ../view/unidades.php?sucesso=" . urlencode("Unidade removida com sucesso do sistema."));
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    header("Location: ../view/editar_unidade.php?id={$id_unidade}&erro=" . urlencode("Erro ao excluir: " . $e->getMessage()));
    exit;
}
