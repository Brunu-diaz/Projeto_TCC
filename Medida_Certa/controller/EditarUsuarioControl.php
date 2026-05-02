<?php
require_once __DIR__ . '/TravaAdmin.php'; 
require_once __DIR__ . '/../model/dto/UsuarioDTO.php';
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_usuario'])) {
    
    $id = $_POST['id_usuario'];
    $usuarioDAO = new UsuarioDAO();

    // Captura dos dados conforme os 'name' do seu formulário HTML
    $nome     = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $cpf_cnpj = filter_input(INPUT_POST, 'cpf_cnpj', FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_perfil = filter_input(INPUT_POST, 'perfil', FILTER_VALIDATE_INT);
    $nova_senha = $_POST['nova_senha']; 

    if ($nome && $email && $username) {
        $usuarioDTO = new UsuarioDTO();
        $usuarioDTO->setNome($nome);
        $usuarioDTO->setEmail($email);
        $usuarioDTO->setCpfCnpj($cpf_cnpj);
        $usuarioDTO->setTelefone($telefone);
        $usuarioDTO->setUsername($username);
        $usuarioDTO->setIdPerfil($id_perfil);
        
        // Só define a senha no DTO se o campo não estiver vazio
        if (!empty($nova_senha)) {
            $usuarioDTO->setSenha(password_hash($nova_senha, PASSWORD_DEFAULT));
        } else {
            $usuarioDTO->setSenha(null);
        }

        // CHAMA O NOVO MÉTODO QUE CRIAMOS ACIMA
        $resultado = $usuarioDAO->editarUsuarioAdministrador($usuarioDTO, $id);
        
        if ($resultado) {
            header("Location: ../view/editarusuario.php?id=$id&sucesso=atualizado");
            exit();
        } else {
            header("Location: ../view/editarusuario.php?id=$id&erro=falha_ao_salvar");
            exit();
        }

    } else {
        header("Location: ../view/editarusuario.php?id=$id&erro=campos_obrigatorios");
        exit();
    }
} else {
    header("Location: ../view/listarUsuarios.php");
    exit();
}