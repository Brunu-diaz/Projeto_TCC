<?php
//Controller responsável por processar o cadastro de usuário
require_once '../model/dto/UsuarioDTO.php';
require_once '../model/dao/UsuarioDAO.php';
// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

 // Conferir se a pessoa aceitou os termos
if (!isset($_POST['termos'])) {
    echo "<script>alert('Você precisa aceitar os termos!'); window.history.back();</script>";
    exit;
}

 // Recebe e sanitiza os dados do formulário
 $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
 $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
 $senha = $_POST['senha'];
 $confirmar_senha = $_POST['confirmar_senha'];

 // Validação de Senhas Iguais
    if ($senha !== $confirmar_senha) {
        echo "<script>
                alert('As senhas digitadas não coincidem!'); 
                window.history.back();
              </script>";
        exit;
    }

 // Validação básica
 if ($nome && $email && $senha) {

 // Instancia o DTO e preenche com os dados
 $usuarioDTO = new UsuarioDTO();
 $usuarioDTO->setNome($nome);
 $usuarioDTO->setEmail($email);
 $usuarioDTO->setSenha($senha);

 // Instancia o DAO e chama o método de cadastro
 $usuarioDAO = new UsuarioDAO();
 $resultado = $usuarioDAO->cadastrarUsuario($usuarioDTO);
 

 // Exibe alerta JavaScript com o resultado
 if ($resultado) {
 echo "<script>
 alert('Usuário cadastrado com sucesso!');
 window.location.href = '../../HTML/login.html';
 </script>";
 } else {
 echo "<script>
 alert('Erro ao cadastrar usuário!');
 window.history.back();
 </script>";
 } 
} else {
 echo "<script>
 alert('Preencha todos os campos corretamente!');
 window.history.back();
 </script>";
 }
}
?>