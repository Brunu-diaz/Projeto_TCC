<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['id_usuario'] = 3;
    $_SESSION['id_perfil'] = 2;
    $_GET['leitura'] = 4;

    ob_start();
    include __DIR__ . '/view/gerar_pdf.php';
    $output = ob_get_clean();
    echo "OUTPUT_BEGIN\n";
    echo $output;
    echo "\nOUTPUT_END\n";
} catch (Throwable $t) {
    echo 'THROWABLE: ' . $t->getMessage();
}
