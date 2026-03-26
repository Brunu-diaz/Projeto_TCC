<?php
/**
* Este arquivo funciona como um controlador de fluxo para a alteração de status
* de um usuário (Ativo/Inativo).
*/
// 1. Importação da Camada de Persistência (Model/DAO)
require_once '../Model/dao/UsuarioDAO.php';
// 2. Verificação de Segurança
if (isset($_POST['id']) && isset($_POST['status'])) {

 $id = $_POST['id'];
 $novoStatus = $_POST['status'];
 // 3. Instanciação do DAO
 $usuarioDAO = new UsuarioDAO();
 // 4. Execução da alteração no banco de dados
 $resultado = $usuarioDAO->alterarStatus($id, $novoStatus);
 // ============================================================
 // 5. EXPLICAÇÃO DETALHADA DO "IF" (Lógica de Decisão)
 // ============================================================

 /* COMO O IF FUNCIONA AQUI:
 1. O programa chega nesta linha e olha o valor da variável
$novoStatus.
 2. Ele faz uma comparação lógica: "O valor que está aqui dentro
é igual a 1?".
 */
 if ($novoStatus == 1) {
 // O QUE ELE FARÁ:
 // Se a resposta for SIM (Verdadeiro), o PHP entra NESTE BLOCO.
 // Ele ignora tudo o que está no 'else' e define a mensagem
abaixo:
 $msg = "Usuário ativado!";

 } else {
 // O QUE ELE FARÁ:
 // Se a resposta for NÃO (Falso), ou seja, se o status for 0 ou qualquer outro valor,
 // o PHP pula o bloco de cima e executa obrigatoriamente este aqui.
 $msg = "Usuário desativado!";
 }
 // Após sair do if/else, a variável $msg já possui o texto correto aguardado.
 // ============================================================
 // 6. Resposta ao Usuário via Script (JavaScript)
 if ($resultado) {
 //ESTE SEGUNDO 'IF' VERIFICA O SUCESSO DO BANCO:
 //Se $resultado for true: Gera o alerta com a $msg definida acima.
//window.location.href: Redireciona o navegador para a página da lista.  */
 echo "<script>
 alert('$msg');
 window.location.href = '../View/listarUsuarios.php';
 </script>";
 } else {
//Se $resultado for false: O erro aconteceu na conexão ou na query SQL.

 echo "<script>
 alert('Erro ao processar solicitação no banco de
dados.');
 window.location.href = '../View/listarUsuarios.php';
 </script>";
 }
} else {
//Redirecionamento de segurança caso o arquivo seja acessado sem dados POST
 header("Location: ../View/listarUsuarios.php");
 exit();
}
?>