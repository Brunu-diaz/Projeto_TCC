<?php
session_start();
require_once __DIR__ . '/../model/dao/Conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die("Token de segurança inválido.");
    }

    try {
        $conn = Conexao::getConexao();
        if ($conn === null) {
            throw new Exception("Falha ao conectar com o banco de dados.");
        }

        // Preparar configurações do sistema a serem salvas
        $configuracoes = [
            'dia_vencimento'          => filter_var($_POST['dia_vencimento'] ?? '10', FILTER_VALIDATE_INT) ?: '10',
            'alerta_vazamento'        => isset($_POST['alerta_vazamento']) ? '1' : '0',
            'alerta_inadimplencia'    => isset($_POST['alerta_inadimplencia']) ? '1' : '0',
            'modo_manutencao'         => isset($_POST['modo_manutencao']) ? '1' : '0',
            'nome_condominio'         => filter_var($_POST['nome_condominio'] ?? 'Residencial MedidaCerta', FILTER_SANITIZE_STRING)
        ];

        $valor_fixo = filter_var($_POST['valor_fixo'] ?? '0.00', FILTER_VALIDATE_FLOAT) ?: 0.00;
        $valor_m3 = filter_var($_POST['valor_m3'] ?? '0.00', FILTER_VALIDATE_FLOAT) ?: 0.00;
        $taxa_esgoto = filter_var($_POST['taxa_esgoto'] ?? '100', FILTER_VALIDATE_FLOAT) ?: 100.00;
        $id_tarifa = filter_input(INPUT_POST, 'id_tarifa', FILTER_VALIDATE_INT) ?: 0;

        $conn->beginTransaction();

        // 1. Salvar Configurações Gerais
        $stmtC = $conn->prepare("INSERT INTO configuracoes (chave, valor) VALUES (:chave, :valor) 
                                 ON DUPLICATE KEY UPDATE valor = :valor_up, updated_at = CURRENT_TIMESTAMP");
        foreach ($configuracoes as $chave => $valor) {
            $stmtC->execute([
                ':chave'    => $chave,
                ':valor'    => (string)$valor,
                ':valor_up' => (string)$valor
            ]);
        }

        // 2. Atualizar Tarifa Ativa
        if ($id_tarifa <= 0) {
            $activeTarifa = $conn->query("SELECT id_tarifa FROM tarifa ORDER BY data_vigencia DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $id_tarifa = $activeTarifa['id_tarifa'] ?? 1;
        }

        $checkTarifa = $conn->prepare("SELECT COUNT(*) as count FROM tarifa WHERE id_tarifa = :id_tarifa");
        $checkTarifa->execute([':id_tarifa' => $id_tarifa]);
        $tarifaExists = $checkTarifa->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        if ($tarifaExists == 0) {
            $stmtInsert = $conn->prepare("INSERT INTO tarifa (id_tarifa, nome, valor_fixo, valor_m3, taxa_esgoto, data_vigencia) 
                                         VALUES (:id_tarifa, 'Tarifa Padrão', :valor_fixo, :valor_m3, :taxa_esgoto, CURDATE())");
            $stmtInsert->execute([
                ':id_tarifa' => $id_tarifa,
                ':valor_fixo' => $valor_fixo,
                ':valor_m3'   => $valor_m3,
                ':taxa_esgoto' => $taxa_esgoto
            ]);
        } else {
            $stmtT = $conn->prepare("UPDATE tarifa SET valor_fixo = :valor_fixo, valor_m3 = :valor_m3, taxa_esgoto = :taxa_esgoto, data_vigencia = CURDATE() WHERE id_tarifa = :id_tarifa");
            $stmtT->execute([
                ':valor_fixo' => $valor_fixo,
                ':valor_m3'   => $valor_m3,
                ':taxa_esgoto' => $taxa_esgoto,
                ':id_tarifa'  => $id_tarifa
            ]);
        }

        // 3. Salvar Faixas de Consumo (Tabela tarifa_faixas)
        $conn->exec("DELETE FROM tarifa_faixas WHERE id_tarifa = " . (int)$id_tarifa);

        $faixasValidas = 0;
        $faixasRejeitadas = 0;

        if (isset($_POST['faixa_limite']) && is_array($_POST['faixa_limite']) && !empty($_POST['faixa_limite'])) {
            $stmtF = $conn->prepare("INSERT INTO tarifa_faixas (id_tarifa, limite_inferior, limite_superior, valor_m3) 
                                     VALUES (:id_tarifa, :inferior, :superior, :valor)");
            
            $limite_inferior = 0;
            
            foreach ($_POST['faixa_limite'] as $index => $limite_superior) {
                // Limpar e converter valor (remove espaços, converte vírgula em ponto)
                $valor_str = trim($_POST['faixa_valor'][$index] ?? '');
                $valor_m3 = 0.00;
                
                if (!empty($valor_str)) {
                    $valor_m3 = (float)str_replace(',', '.', $valor_str);
                }
                
                $limite_superior = (int)$limite_superior;
                
                // Validação: limite_superior deve ser positivo e maior que limite_inferior e valor_m3 deve ser positivo
                if ($limite_superior > 0 && $limite_superior > $limite_inferior && $valor_m3 >= 0.01) {
                    try {
                        $stmtF->execute([
                            ':id_tarifa' => $id_tarifa,
                            ':inferior' => $limite_inferior,
                            ':superior' => $limite_superior,
                            ':valor'    => $valor_m3
                        ]);
                        $faixasValidas++;
                        $limite_inferior = $limite_superior;
                    } catch (PDOException $e) {
                        error_log("Erro ao inserir faixa: " . $e->getMessage());
                        $faixasRejeitadas++;
                    }
                } else {
                    $faixasRejeitadas++;
                    error_log("Faixa rejeitada - Index: $index, Limite: $limite_superior, Valor: $valor_m3, Limite anterior: $limite_inferior");
                }
            }
        }

        // Se nenhuma faixa válida foi inserida, criar faixa padrão
        if ($faixasValidas === 0) {
            $stmtF = $conn->prepare("INSERT INTO tarifa_faixas (id_tarifa, limite_inferior, limite_superior, valor_m3) 
                                     VALUES (:id_tarifa, :inferior, :superior, :valor)");
            $stmtF->execute([
                ':id_tarifa' => $id_tarifa,
                ':inferior' => 0,
                ':superior' => 999,
                ':valor'    => 8.26  // Valor padrão CAESB
            ]);
            error_log("Nenhuma faixa válida fornecida. Faixa padrão criada.");
        } else {
            error_log("Faixas salvas com sucesso. Válidas: $faixasValidas, Rejeitadas: $faixasRejeitadas");
        }

        $conn->commit();
        
        // Redirecionar com sucesso
        header("Location: ../view/configuracoesAdmin.php?sucesso=1");
        exit;

    } catch (PDOException $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Erro PDO - MedidaCerta: " . $e->getMessage());
        header("Location: ../view/configuracoesAdmin.php?erro=1&msg=db_error");
        exit;
        
    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Erro Geral - MedidaCerta: " . $e->getMessage());
        header("Location: ../view/configuracoesAdmin.php?erro=1&msg=general_error");
        exit;
    }
} else {
    // Se alguém tentar acessar diretamente sem POST
    header("Location: ../view/configuracoesAdmin.php");
    exit;
}