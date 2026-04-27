<?php
require_once '../model/dto/UsuarioDTO.php';
require_once '../model/dao/UsuarioDAO.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitização e Captura de Dados Pessoais
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $cpf_cnpj = $_POST['cpf_cnpj'];
    $telefone = $_POST['telefone'];
    $username = $_POST['username'];
    $senha_provisoria = $_POST['senha_provisoria'];
    
    // CAPTURA DO PERFIL (Vindo do <select> do formulário)
    $id_perfil = filter_input(INPUT_POST, 'id_perfil', FILTER_SANITIZE_NUMBER_INT);

    // Dados da Unidade
    $tipo_unidade = $_POST['tipo_unidade'];
    $endereco = $_POST['endereco'];
    $numero = $_POST['numero'];
    $bloco = $_POST['bloco'];
    $codigo_hidrometro = $_POST['codigo_hidrometro'];

    // Validação mínima
    if ($nome && $email && $username && $senha_provisoria && $id_perfil) {
        $usuarioDTO = new UsuarioDTO();
        
        // Populando o DTO com dados pessoais
        $usuarioDTO->setNome($nome);
        $usuarioDTO->setEmail($email);
        $usuarioDTO->setCpfCnpj($cpf_cnpj);
        $usuarioDTO->setTelefone($telefone);
        $usuarioDTO->setUsername($username);
        $usuarioDTO->setSenha(password_hash($senha_provisoria, PASSWORD_DEFAULT));
        
        // AJUSTE: Perfil dinâmico e Status inicial Inativo (0)
        $usuarioDTO->setIdPerfil($id_perfil); 
        $usuarioDTO->setAtivo(0); // Força o ativo = 0 para troca de senha no 1º acesso

        // Populando o DTO com dados da Unidade
        $usuarioDTO->setTipoUnidade($tipo_unidade);
        $usuarioDTO->setEndereco($endereco);
        $usuarioDTO->setNumero($numero);
        $usuarioDTO->setBloco($bloco);
        $usuarioDTO->setCodigoHidrometro($codigo_hidrometro);

        $usuarioDAO = new UsuarioDAO();
        $resultado = $usuarioDAO->cadastrarUsuario($usuarioDTO);

        if ($resultado) {
            header("Location: ../view/cadastrarUnidades.php?sucesso=1");
            exit;
        } else {
            header("Location: ../view/cadastrarUnidades.php?erro=Falha+no+cadastro");
            exit;
        }
    } else {
        // Se faltar algum campo obrigatório
        header("Location: ../view/cadastrarUnidades.php?erro=Preencha+todos+os+campos+obrigatorios");
        exit;
    }
}