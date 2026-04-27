<?php
require_once __DIR__ . '/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Verifica se o acesso é via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/unidades.php");
    exit;
}

// 1. Captura e higieniza os dados do formulário
$id_unidade       = filter_input(INPUT_POST, 'id_unidade', FILTER_SANITIZE_NUMBER_INT);
$id_usuario       = filter_input(INPUT_POST, 'id_usuario', FILTER_SANITIZE_NUMBER_INT);
$endereco         = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_SPECIAL_CHARS);
$numero           = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_SPECIAL_CHARS);
$bloco            = filter_input(INPUT_POST, 'bloco', FILTER_SANITIZE_SPECIAL_CHARS);
$complemento      = filter_input(INPUT_POST, 'complemento', FILTER_SANITIZE_SPECIAL_CHARS);

$codigo_serial    = filter_input(INPUT_POST, 'codigo_hidrometro', FILTER_SANITIZE_SPECIAL_CHARS);
$modelo           = filter_input(INPUT_POST, 'modelo_hidrometro', FILTER_SANITIZE_SPECIAL_CHARS);
$status_hidrometro = filter_input(INPUT_POST, 'status_hidrometro', FILTER_SANITIZE_SPECIAL_CHARS);

// Validação básica de ID
if (!$id_unidade) {
    header("Location: ../view/unidades.php?erro=" . urlencode("ID da unidade inválido."));
    exit;
}

try {
    $pdo = Conexao::getConexao();
    
    // Inicia Transação (Garante que ou muda tudo ou não muda nada)
    $pdo->beginTransaction();

    // 2. Atualiza a tabela UNIDADE
    $sqlUnidade = "UPDATE unidade SET 
                    endereco = :endereco, 
                    numero = :numero, 
                    bloco = :bloco, 
                    complemento = :complemento, 
                    id_usuario = :id_usuario 
                   WHERE id_unidade = :id_unidade";
    
    $stmtU = $pdo->prepare($sqlUnidade);
    $stmtU->execute([
        ':endereco'   => $endereco,
        ':numero'     => $numero,
        ':bloco'      => $bloco,
        ':complemento' => $complemento,
        ':id_usuario' => $id_usuario,
        ':id_unidade' => $id_unidade
    ]);

    // 3. Atualiza a tabela HIDROMETRO
    // Nota: Usamos o id_unidade para encontrar o hidrômetro vinculado
    $sqlHidro = "UPDATE hidrometro SET 
                    codigo = :codigo, 
                    modelo = :modelo, 
                    status = :status 
                 WHERE id_unidade = :id_unidade";
    
    $stmtH = $pdo->prepare($sqlHidro);
    $stmtH->execute([
        ':codigo'     => strtoupper($codigo_serial), // Garante caixa alta no banco
        ':modelo'     => $modelo,
        ':status'     => $status_hidrometro,
        ':id_unidade' => $id_unidade
    ]);

    // Confirma as alterações
    $pdo->commit();

    // Redireciona com sucesso
    header("Location: ../view/unidades.php?sucesso=" . urlencode("Unidade e Hidrômetro atualizados com sucesso!"));
    exit;

} catch (PDOException $e) {
    // Se algo der errado, desfaz tudo
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log do erro (opcional) e redirecionamento
    header("Location: ../view/unidades.php?erro=" . urlencode("Erro ao atualizar: " . $e->getMessage()));
    exit;
}