<?php
// 1. Inicia a sessão (embora agora usemos $_GET, é boa prática manter para segurança/auth)
if (!isset($_SESSION)) {
    session_start();
}

// 2. Importações necessárias
require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// 3. Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recupera e sanitiza os dados do formulário
    $id_usuario  = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
    $endereco    = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_SPECIAL_CHARS);
    $numero      = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_SPECIAL_CHARS);
    $bloco       = filter_input(INPUT_POST, 'bloco', FILTER_SANITIZE_SPECIAL_CHARS);
    $complemento = filter_input(INPUT_POST, 'complemento', FILTER_SANITIZE_SPECIAL_CHARS);

    // Validação de campos obrigatórios (incluindo o novo select de complemento)
    if (!$id_usuario || !$endereco || !$numero || !$complemento) {
        $msgErro = urlencode("Preencha todos os campos obrigatórios corretamente.");
        header("Location: ../view/cadastrarUnidade.php?erro=$msgErro");
        exit;
    }

    try {
        $pdo = Conexao::getConexao();

        // SQL para inserção
        $sql = "INSERT INTO unidade (id_usuario, endereco, numero, bloco, complemento) 
                VALUES (:id_usuario, :endereco, :numero, :bloco, :complemento)";

        $stmt = $pdo->prepare($sql);

        // Bind dos valores para evitar SQL Injection
        $stmt->bindValue(':id_usuario', $id_usuario);
        $stmt->bindValue(':endereco', $endereco);
        $stmt->bindValue(':numero', $numero);
        $stmt->bindValue(':bloco', strtoupper($bloco)); // Padronização do bloco
        $stmt->bindValue(':complemento', $complemento); // Recebe "Residencial" ou "Comercial"

        if ($stmt->execute()) {
            // SUCESSO: Redireciona para a listagem (ajuste o nome da view se necessário)
            header("Location: ../view/cadastrarUnidades.php?sucesso=1");
            exit;
        } else {
            throw new Exception("Erro ao inserir dados no banco.");
        }

    } catch (Exception $e) {
        // ERRO: Redireciona de volta para o formulário com a mensagem de erro
        $msgErro = urlencode("Não foi possível cadastrar a unidade: " . $e->getMessage());
        header("Location: ../view/cadastrarUnidade.php?erro=$msgErro");
        exit;
    }
} else {
    // Se tentarem acessar o control diretamente via URL
    header("Location: ../view/cadastrarUnidades.php");
    exit;
}