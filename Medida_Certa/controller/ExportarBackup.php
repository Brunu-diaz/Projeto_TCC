<?php
session_start();
require_once __DIR__ . '/../model/dao/Conexao.php';

// Apenas administradores podem fazer backup
if (!isset($_SESSION['id_usuario'])) {
    die("Acesso negado.");
}

try {
    $conn = Conexao::getConexao();
    $tables = array();
    $result = $conn->query('SHOW TABLES');
    while($row = $result->fetch(PDO::FETCH_NUM)){
        $tables[] = $row[0];
    }

    $return = "";
    foreach($tables as $table){
        $result = $conn->query('SELECT * FROM '.$table);
        $num_fields = $result->columnCount();
        
        $return .= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = $conn->query('SHOW CREATE TABLE '.$table)->fetch(PDO::FETCH_NUM);
        $return .= "\n\n".$row2[1].";\n\n";
        
        while($row = $result->fetch(PDO::FETCH_NUM)){
            $return .= 'INSERT INTO '.$table.' VALUES(';
            for($j=0; $j<$num_fields; $j++){
                $row[$j] = addslashes($row[$j]);
                if (isset($row[$j])) { $return .= '"'.$row[$j].'"' ; } else { $return .= '""'; }
                if ($j<($num_fields-1)){ $return .= ','; }
            }
            $return .= ");\n";
        }
        $return .= "\n\n\n";
    }

    // Gerar arquivo
    $filename = 'backup_medidacerta_'.date('Y-m-d').'.sql';
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"".$filename."\"");
    echo $return;
    exit;

} catch (Exception $e) {
    die("Erro ao gerar backup: " . $e->getMessage());
}