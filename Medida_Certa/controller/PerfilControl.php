<?php
session_start();
require_once '../model/dto/UsuarioDTO.php';
require_once '../model/dao/UsuarioDAO.php';

$usuarioDAO = new UsuarioDAO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verificação de CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: ../view/perfil.php?erro=1");
        exit();
    }

    $id_usuario = $_SESSION['id_usuario'];
    $usuarioAtual = $usuarioDAO->buscarPorId($id_usuario);
    $fotoAtual = $usuarioAtual['foto'] ?? '';
    $removerFoto = $_POST['remover_foto'] ?? '0';
    
    $usuarioDTO = new UsuarioDTO();
    $usuarioDTO->setNome($_POST['nome']);
    $usuarioDTO->setEmail($_POST['email']);
    $usuarioDTO->setTelefone($_POST['telefone']);

    $nomeFoto = "manter"; // Flag para o DAO saber que não deve mexer na foto

    // 2. Lógica de Upload de Nova Foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $arquivo_tmp = $_FILES['foto']['tmp_name'];
        $tamanho = $_FILES['foto']['size'];
        $nomeOriginal = $_FILES['foto']['name'];
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        
        $extensoes_permitidas = ['jpg', 'jpeg', 'png'];

        if ($tamanho <= 2 * 1024 * 1024 && in_array($extensao, $extensoes_permitidas)) {
            $uploadDir = __DIR__ . '/../assets/img/perfil/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $nomeFoto = 'perfil_' . $id_usuario . '_' . time() . '.' . $extensao;
            $destino = $uploadDir . $nomeFoto;

            if (move_uploaded_file($arquivo_tmp, $destino)) {
                if (!empty($fotoAtual) && strpos($fotoAtual, 'data:image') !== 0) {
                    $arquivoAntigo = $uploadDir . $fotoAtual;
                    if (file_exists($arquivoAntigo)) {
                        unlink($arquivoAntigo);
                    }
                }
            } else {
                $nomeFoto = "manter";
            }
        }
    } elseif ($removerFoto === '1') {
        $nomeFoto = null; // O DAO deve entender NULL como "deletar foto do banco"

        if (!empty($fotoAtual) && strpos($fotoAtual, 'data:image') !== 0) {
            $arquivoAntigo = __DIR__ . '/../assets/img/perfil/' . $fotoAtual;
            if (file_exists($arquivoAntigo)) {
                unlink($arquivoAntigo);
            }
        }
    }

    // 4. Chamada ao DAO
    $sucesso = $usuarioDAO->atualizarPerfil($id_usuario, $usuarioDTO, $nomeFoto);

    $redirect = basename($_POST['redirect'] ?? 'perfil.php');
    $redirect = in_array($redirect, ['perfil.php', 'perfilAdmin.php']) ? $redirect : 'perfil.php';

    if ($sucesso) {
        header("Location: ../view/$redirect?sucesso=1");
    } else {
        header("Location: ../view/$redirect?erro=1");
    }
    exit();
}