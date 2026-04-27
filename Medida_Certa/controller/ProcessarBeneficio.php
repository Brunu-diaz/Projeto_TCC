<?php
/**
 * PROJETO: MedidaCerta
 * OBJETIVO: Processar a ativação ou suspensão de benefícios sociais
 */

require_once __DIR__ . '/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_SANITIZE_NUMBER_INT);
    $acao = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Constantes de regra de negócio (Poderiam vir de um config.php)
    $LIMITE_CONSUMO_SOCIAL = 30;

    if (!$id_usuario || !$acao || !in_array($acao, ['ativar', 'suspender'], true)) {
        header("Location: ../view/admin_beneficios.php?status=erro_dados");
        exit;
    }

    try {
        $pdo = Conexao::getConexao();

        if ($acao === 'ativar') {
            // 1. Verificação de segurança (Consumo e Débitos)
            $sqlCheckRegra = "SELECT 
                (SELECT consumo_calculado FROM leitura l 
                 JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro 
                 WHERE h.id_unidade = un.id_unidade 
                 ORDER BY l.data_leitura DESC LIMIT 1) as consumo,
                (SELECT COUNT(*) FROM fatura f 
                 JOIN leitura l2 ON f.id_leitura = l2.id_leitura 
                 JOIN hidrometro h2 ON l2.id_hidrometro = h2.id_hidrometro
                 WHERE h2.id_unidade = un.id_unidade 
                 AND f.status_pagamento != 'Pago' AND f.data_vencimento < CURRENT_DATE()) as debitos
                FROM unidade un WHERE un.id_usuario = :id";
            
            $stmtRegra = $pdo->prepare($sqlCheckRegra);
            $stmtRegra->execute([':id' => $id_usuario]);
            $regra = $stmtRegra->fetch(PDO::FETCH_ASSOC);

            if (($regra['consumo'] ?? 0) > $LIMITE_CONSUMO_SOCIAL || ($regra['debitos'] ?? 0) > 0) {
                header("Location: ../view/admin_beneficios.php?status=regra_violada");
                exit;
            }

            // 2. Verifica se o usuário já tem um registro na tarifa_social
            $sqlExist = "SELECT id_tarifa_social FROM tarifa_social WHERE id_usuario = :id";
            $stmtExist = $pdo->prepare($sqlExist);
            $stmtExist->execute([':id' => $id_usuario]);
            $registroExistente = $stmtExist->fetch();

            if ($registroExistente) {
                // APENAS ATUALIZA o registro que já existe
                $sqlUp = "UPDATE tarifa_social SET 
                            status_beneficio = 'Ativo', 
                            data_inicio = CURRENT_DATE(),
                            observacao_gestao = 'Reativado pelo administrador'
                          WHERE id_usuario = :id";
            } else {
                // CRIA um novo apenas se for a primeira vez
                $sqlUp = "INSERT INTO tarifa_social (
                            id_usuario, tipo_beneficio, categoria_beneficio, 
                            percentual_desconto, limite_m3, status_beneficio, 
                            data_inicio, data_cadastro
                          ) VALUES (
                            :id, 'Tarifa Social', 'Social', 50.00, :limite, 'Ativo', 
                            CURRENT_DATE(), CURRENT_TIMESTAMP()
                          )";
            }
        } 
        else if ($acao === 'suspender') {
            // Alterado de 'Suspenso' para 'Inativo' para coincidir com o ENUM padrão
            $sqlUp = "UPDATE tarifa_social 
                      SET status_beneficio = 'Inativo', 
                          observacao_gestao = 'Suspenso manualmente pelo administrador' 
                      WHERE id_usuario = :id";
        }

        $params = [':id' => $id_usuario];
        if ($acao === 'ativar' && !$registroExistente) {
            $params[':limite'] = $LIMITE_CONSUMO_SOCIAL;
        }
        
        $stmt = $pdo->prepare($sqlUp);
        $stmt->execute($params);

        header("Location: ../view/admin_beneficios.php?status=sucesso");

    } catch (Exception $e) {
        error_log("Erro ao processar benefício: " . $e->getMessage());
        header("Location: ../view/admin_beneficios.php?status=erro_processamento");
        exit;
    }
} else {
    header("Location: ../view/admin_beneficios.php");
}