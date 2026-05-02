<?php
require_once __DIR__ . '/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: ../view/login.php");
    exit;
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_leitura = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id_leitura === false || $id_leitura === null) {
        header("Location: ../view/listarLeituras.php?msg=erro");
        exit;
    }

    try {
        $pdo = Conexao::getConexao();
        $pdo->beginTransaction();

        // Exclui quaisquer anomalias vinculadas à leitura antes de remover a leitura.
        $sqlAnomalia = "DELETE FROM anomalia WHERE id_leitura = :id";
        $stmtAnomalia = $pdo->prepare($sqlAnomalia);
        $stmtAnomalia->bindValue(':id', $id_leitura, PDO::PARAM_INT);
        $stmtAnomalia->execute();

        // Exclui a leitura. A fatura será removida automaticamente pela FK CASCADE.
        $sqlLeitura = "DELETE FROM leitura WHERE id_leitura = :id";
        $stmtLeitura = $pdo->prepare($sqlLeitura);
        $stmtLeitura->bindValue(':id', $id_leitura, PDO::PARAM_INT);
        $stmtLeitura->execute();

        $pdo->commit();
        header("Location: ../view/listarLeituras.php?msg=sucesso");
        exit;
    } catch (Exception $e) {
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Erro ao excluir leitura: ' . $e->getMessage());
        header("Location: ../view/listarLeituras.php?msg=erro");
        exit;
    }
}