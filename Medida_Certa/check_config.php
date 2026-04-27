<!DOCTYPE html>
<html>
<head>
    <title>Verificar Configurações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h1 class="mb-4">🔍 Debug - Tabela Configuracoes</h1>
        
        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        require_once 'model/dao/Conexao.php';
        
        try {
            $conn = Conexao::getConexao();
            
            // 1. Verifica se tabela existe
            echo "<h3>1️⃣ Tabela existe?</h3>";
            $result = $conn->query("SHOW TABLES LIKE 'configuracoes'");
            if ($result->rowCount() > 0) {
                echo '<div class="alert alert-success">✅ Tabela ENCONTRADA</div>';
            } else {
                echo '<div class="alert alert-danger">❌ Tabela NÃO ENCONTRADA</div>';
            }
            
            // 2. Estrutura
            echo "<h3 class='mt-4'>2️⃣ Estrutura da Tabela</h3>";
            echo '<table class="table table-bordered">';
            $cols = $conn->query("DESCRIBE configuracoes");
            foreach ($cols as $col) {
                echo "<tr>";
                echo "<td><strong>{$col['Field']}</strong></td>";
                echo "<td>{$col['Type']}</td>";
                echo "</tr>";
            }
            echo '</table>';
            
            // 3. Dados
            echo "<h3 class='mt-4'>3️⃣ Dados Armazenados</h3>";
            $query = "SELECT id_config, chave, valor FROM configuracoes";
            $data = $conn->query($query);
            $configs = $data->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($configs) > 0) {
                echo '<div class="alert alert-info">Total de registros: <strong>' . count($configs) . '</strong></div>';
                echo '<table class="table table-striped">';
                echo '<thead><tr><th>ID</th><th>CHAVE</th><th>VALOR</th></tr></thead>';
                echo '<tbody>';
                foreach ($configs as $cfg) {
                    echo "<tr>";
                    echo "<td>{$cfg['id_config']}</td>";
                    echo "<td><code>{$cfg['chave']}</code></td>";
                    echo "<td><strong>{$cfg['valor']}</strong></td>";
                    echo "</tr>";
                }
                echo '</tbody></table>';
            } else {
                echo '<div class="alert alert-warning">⚠️ Tabela está VAZIA!</div>';
            }
            
            // 4. Testa a Query como no código
            echo "<h3 class='mt-4'>4️⃣ Query com FETCH_KEY_PAIR</h3>";
            $stmtC = $conn->query("SELECT chave, valor FROM configuracoes");
            $configs_db = $stmtC->fetchAll(PDO::FETCH_KEY_PAIR);
            echo '<pre style="background:#f0f0f0; padding:10px; border-radius:5px;">';
            print_r($configs_db);
            echo '</pre>';
            
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">❌ ERRO: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>
</body>
</html>
