<?php
session_start();
require_once __DIR__ . '/../model/dao/Conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_leitura = filter_input(INPUT_POST, 'id_leitura', FILTER_VALIDATE_INT);

    if ($id_leitura) {
        try {
            $pdo = Conexao::getConexao();

            // 1. Busca a leitura atual, o hidrômetro e o ID do usuário vinculado
            $sqlLeitura = "SELECT l.*, h.id_hidrometro, u.id_usuario 
                           FROM leitura l 
                           INNER JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro 
                           INNER JOIN unidade u ON h.id_unidade = u.id_unidade
                           WHERE l.id_leitura = :id_leitura";
            $stmtL = $pdo->prepare($sqlLeitura);
            $stmtL->execute([':id_leitura' => $id_leitura]);
            $leituraAtual = $stmtL->fetch(PDO::FETCH_ASSOC);

            if (!$leituraAtual) throw new Exception("Leitura não encontrada.");
            $id_usuario = $leituraAtual['id_usuario'];

            // 2. Cálculo do consumo real (Valor Medido - Anterior)
            $sqlAnterior = "SELECT valor_medido FROM leitura 
                            WHERE id_hidrometro = :id_h AND id_leitura < :id_l 
                            ORDER BY id_leitura DESC LIMIT 1";
            $stmtA = $pdo->prepare($sqlAnterior);
            $stmtA->execute([':id_h' => $leituraAtual['id_hidrometro'], ':id_l' => $id_leitura]);
            $leituraAnterior = $stmtA->fetch(PDO::FETCH_ASSOC);

            $valorAnterior = $leituraAnterior ? $leituraAnterior['valor_medido'] : 0;
            $consumo_m3 = $leituraAtual['valor_medido'] - $valorAnterior;

            // 3. Busca a tarifa vigente
            $tarifa = $pdo->query("SELECT * FROM tarifa ORDER BY data_vigencia DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if (!$tarifa) throw new Exception("Nenhuma tarifa cadastrada no sistema.");

            // 4. Lógica de Valor Base (Antes dos benefícios)
            $valorFixo = (float)$tarifa['valor_fixo'];
            $taxaEsgotoPercentual = (float)$tarifa['taxa_esgoto']; // Ex: 100.00
            $idTarifa = (int)$tarifa['id_tarifa'];

            $stmtFaixas = $pdo->prepare("SELECT limite_inferior, limite_superior, valor_m3
                                         FROM tarifa_faixas
                                         WHERE id_tarifa = :id_tarifa
                                         ORDER BY limite_superior ASC");
            $stmtFaixas->execute([':id_tarifa' => $idTarifa]);
            $faixas = $stmtFaixas->fetchAll(PDO::FETCH_ASSOC);

            $consumoRestante = max(0, $consumo_m3);
            $valorAguaTotal = 0.0;
            $limiteAnterior = 0;
            $ultimaTarifaM3 = (float)$tarifa['valor_m3'];

            if (!empty($faixas)) {
                foreach ($faixas as $faixa) {
                    if ($consumoRestante <= 0) break;

                    $ultimaTarifaM3 = (float)$faixa['valor_m3'];
                    $limiteFaixa = (int)$faixa['limite_superior'] - $limiteAnterior;
                    $consumoNestaFaixa = min($consumoRestante, $limiteFaixa);
                    $valorNaFaixa = $consumoNestaFaixa * $ultimaTarifaM3;

                    $valorAguaTotal += $valorNaFaixa;
                    $consumoRestante -= $consumoNestaFaixa;
                    $limiteAnterior = (int)$faixa['limite_superior'];
                }
            }

            // Consumo excedente à última faixa
            if ($consumoRestante > 0) {
                $valorAguaTotal += ($consumoRestante * $ultimaTarifaM3);
            }

            // --- AJUSTE ITEM 2: CÁLCULO PRECISO DO TOTAL ---
            // 1. Valor da Água (Soma das faixas)
            $valorAguaTotal = round($valorAguaTotal, 2); 
            
            // 2. Valor do Esgoto (Calculado sobre o valor da Água)
            $valorEsgoto = round($valorAguaTotal * ($taxaEsgotoPercentual / 100), 2);
            
            // 3. Valor Variável Total (Água + Esgoto)
            $valorVariavelTotal = $valorAguaTotal + $valorEsgoto;
            
            // 4. Subtotal Bruto (Soma tudo: Água + Esgoto + Taxa Fixa)
            // Para o seu caso: 53.82 + 53.82 + 11.18 = 118.82
            $subtotalBruto = round($valorVariavelTotal + $valorFixo, 2);
            
            $valor_total = $subtotalBruto;
            $descontoAplicado = 0.0;
            $detalhamento = "Tarifa Comum"; 

            // --- VERIFICAÇÃO DE INADIMPLÊNCIA ---
            $sqlDivida = "SELECT COUNT(*) FROM fatura f
                          JOIN leitura l ON f.id_leitura = l.id_leitura
                          JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
                          JOIN unidade u ON h.id_unidade = u.id_unidade
                          WHERE u.id_usuario = :id 
                          AND f.status_pagamento IN ('Pendente', 'Atrasado')
                          AND f.data_vencimento < CURDATE()";

            $stmtD = $pdo->prepare($sqlDivida);
            $stmtD->execute([':id' => $id_usuario]);
            $isInadimplente = $stmtD->fetchColumn() > 0;

            if ($isInadimplente) {
                $detalhamento = "Tarifa Comum - Benefícios suspensos por inadimplência";
            } else {
                // --- LÓGICA DE BENEFÍCIOS DINÂMICOS ---

                // A. Verifica Tarifa Social
                $sqlSocial = "SELECT percentual_desconto, limite_m3 
                              FROM tarifa_social 
                              WHERE id_usuario = :id 
                              AND categoria_beneficio = 'SOCIAL' 
                              AND status_beneficio = 'Ativo' LIMIT 1";
                $stmtS = $pdo->prepare($sqlSocial);
                $stmtS->execute([':id' => $id_usuario]);
                $regraSocial = $stmtS->fetch(PDO::FETCH_ASSOC);

                if ($regraSocial && $consumo_m3 <= $regraSocial['limite_m3']) {
                    $perc = $regraSocial['percentual_desconto'] ?? 0;
                    $descontoAplicado = round($valorVariavelTotal * ($perc / 100), 2);
                    $valor_total = round($subtotalBruto - $descontoAplicado, 2);
                    $detalhamento = "Desconto Social: " . number_format($perc, 0) . "% aplicado.";
                } 
                // B. Verifica Bônus Economia
                else {
                    $sqlRegraBonus = "SELECT percentual_desconto 
                                      FROM tarifa_social 
                                      WHERE id_usuario = :id 
                                      AND categoria_beneficio = 'BONUS' 
                                      AND status_beneficio = 'Ativo' LIMIT 1";
                    $stmtRB = $pdo->prepare($sqlRegraBonus);
                    $stmtRB->execute([':id' => $id_usuario]);
                    $regraBonus = $stmtRB->fetch(PDO::FETCH_ASSOC);

                    if ($regraBonus) {
                        $anoAnterior = $leituraAtual['ano_referencia'] - 1;
                        $sqlHist = "SELECT f.consumo_m3 FROM fatura f
                                    JOIN leitura l ON f.id_leitura = l.id_leitura
                                    JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
                                    JOIN unidade u ON h.id_unidade = u.id_unidade
                                    WHERE u.id_usuario = :id 
                                    AND l.mes_referencia = :mes 
                                    AND l.ano_referencia = :ano_ant LIMIT 1";
                        
                        $stmtH = $pdo->prepare($sqlHist);
                        $stmtH->execute([
                            ':id' => $id_usuario, 
                            ':mes' => $leituraAtual['mes_referencia'], 
                            ':ano_ant' => $anoAnterior
                        ]);
                        $consumoPassado = $stmtH->fetchColumn() ?: 0;

                        if ($consumoPassado > 0 && $consumo_m3 < $consumoPassado) {
                            $percB = $regraBonus['percentual_desconto'] ?? 0;
                            $descontoAplicado = round($valorVariavelTotal * ($percB / 100), 2);
                            $valor_total = round($subtotalBruto - $descontoAplicado, 2);
                            $detalhamento = "Bônus Eficiência: " . number_format($percB, 0) . "% aplicado.";
                        }
                    }
                }
            }

            // 5. Insere na tabela fatura com o VALOR FINAL CORRETO
            $valor_final_db = round($valor_total, 2);

            $sqlFatura = "INSERT INTO fatura (id_leitura, id_tarifa, consumo_m3, valor_total, detalhamento_desconto, data_emissao, data_vencimento, status_pagamento) 
                          VALUES (:id_l, :id_t, :cons, :total, :detalhe, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'Pendente')";
            
            $stmtF = $pdo->prepare($sqlFatura);
            $stmtF->execute([
                ':id_l'    => $id_leitura,
                ':id_t'    => $tarifa['id_tarifa'],
                ':cons'    => $consumo_m3,
                ':total'   => $valor_final_db,
                ':detalhe' => $detalhamento 
            ]);

            $id_fatura = $pdo->lastInsertId();

            header("Location: ../view/gerar_pdf.php?id=" . $id_fatura . "&download=1");
            exit();

        } catch (Exception $e) {
            echo "Erro ao gerar fatura: " . $e->getMessage();
        }
    }
}