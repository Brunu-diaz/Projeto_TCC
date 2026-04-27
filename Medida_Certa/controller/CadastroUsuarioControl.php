<?php
/**
 * Controller: CadastroUsuarioControl.php
 * Finalidade: Realizar o cadastro simplificado de usuários (Pessoa + Login) 
 * sem a necessidade imediata de vincular uma unidade física.
 */

// 1. Segurança: Somente Administradores autenticados podem processar este formulário
require_once __DIR__ . '/TravaAdmin.php';
require_once __DIR__ . '/../model/dto/UsuarioDTO.php';
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 2. Sanitização e Captura de Dados
    // Utilizamos o mesmo padrão do seu cadastrounidadecontrol
    $nome             = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email            = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $cpf_cnpj         = $_POST['cpf_cnpj'];
    $telefone         = $_POST['telefone'];
    $username         = $_POST['username'];
    $senha_provisoria = $_POST['senha_provisoria'];
    
    // 3. Tratamento do Perfil de Acesso
    // Converte o valor do formulário para o ID correspondente na sua tabela 'perfil'
    $perfil_form = $_POST['perfil']; 
    $id_perfil   = ($perfil_form == 'Administrador') ? 1 : 2; 

    // 4. Validação dos Campos Obrigatórios
    if ($nome && $email && $username && $senha_provisoria) {
        
        // 5. Instância e População do DTO
        $usuarioDTO = new UsuarioDTO();
        $usuarioDTO->setNome($nome);
        $usuarioDTO->setEmail($email);
        $usuarioDTO->setCpfCnpj($cpf_cnpj);
        $usuarioDTO->setTelefone($telefone);
        $usuarioDTO->setUsername($username);
        
        // Criptografia da senha provisória (Segurança do MedidaCerta)
        $usuarioDTO->setSenha(password_hash($senha_provisoria, PASSWORD_DEFAULT));
        
        // Regras de Negócio: Perfil escolhido e Status Ativo = 0 (Pendente de Ativação)
        $usuarioDTO->setIdPerfil($id_perfil); 
        $usuarioDTO->setAtivo(0); 

        // 6. Chamada ao DAO utilizando o novo método isolado
        $usuarioDAO = new UsuarioDAO();
        
        // IMPORTANTE: Chamando o método que criamos para ignorar Unidade e Hidrômetro
        $resultado = $usuarioDAO->cadastrarUsuarioIsolado($usuarioDTO); 

        // 7. Feedback para a View
        if ($resultado) {
            header("Location: ../view/cadastrarusuario.php?sucesso=1");
            exit;
        } else {
            // Caso ocorra erro de duplicidade ou erro de conexão no DAO
            header("Location: ../view/cadastrarusuario.php?erro=Falha+tecnica+ao+salvar+no+banco+de+dados");
            exit;
        }
    } else {
        // Caso o administrador tente burlar a validação do HTML5
        header("Location: ../view/cadastrarusuario.php?erro=Preencha+todos+os+campos+obrigatorios");
        exit;
    }
} else {
    // Se tentarem acessar o arquivo diretamente sem POST
    header("Location: ../view/cadastrarusuario.php");
    exit;
}