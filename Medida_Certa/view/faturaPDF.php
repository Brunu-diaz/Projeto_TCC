<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Importações necessárias
require_once __DIR__ . '/../model/dao/Conexao.php';
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';

// 1. VERIFICAÇÃO DE SEGURANÇA
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['perfil'])) {
    header("Location: login.php?erro=sessao_expirada");
    exit;
}

if (!function_exists('getNomeMes')) {
    function getNomeMes($mes) {
        $meses = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
        ];
        return $meses[(int)$mes] ?? 'Indef';
    }
}

$id_fatura = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id_leitura = filter_input(INPUT_GET, 'leitura', FILTER_VALIDATE_INT);
$id_logado = $_SESSION['id_usuario'];
$perfil_logado = $_SESSION['perfil']; // 'Administrador' ou 'Cliente'

if (!$id_fatura && !$id_leitura) {
    die("Erro: Parâmetros da fatura ausentes.");
}

try {
    $pdo = Conexao::getConexao();

    // 2. BUSCA COMPLETA (Fatura + Unidade + Leitura + Tarifa)
    $sqlBusca = "SELECT f.*, l.valor_medido, l.data_leitura, l.mes_referencia, l.ano_referencia,
                        u.bloco, u.numero, u.id_usuario as dono_unidade,
                        t.valor_m3, t.taxa_esgoto
                 FROM fatura f
                 INNER JOIN leitura l ON f.id_leitura = l.id_leitura
                 INNER JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
                 INNER JOIN unidade u ON h.id_unidade = u.id_unidade
                 LEFT JOIN tarifa t ON f.id_tarifa = t.id_tarifa
                 WHERE (:id_fatura IS NOT NULL AND f.id_fatura = :id_fatura)
                    OR (:id_leitura IS NOT NULL AND f.id_leitura = :id_leitura)";
    
    $stmt = $pdo->prepare($sqlBusca);
    $stmt->execute([
        ':id_fatura' => $id_fatura,
        ':id_leitura' => $id_leitura
    ]);
    $dadosFatura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dadosFatura) {
        die("Fatura não encontrada.");
    }

    // 3. TRAVA DE SEGURANÇA (ADM vê tudo, Cliente só a dele)
    if ($perfil_logado !== 'Administrador' && $id_logado != $dadosFatura['dono_unidade']) {
        header("Location: dashboard.php?erro=acesso_negado");
        exit;
    }

    // 4. DADOS DO MORADOR E BENEFÍCIOS
    $usuarioDAO = new UsuarioDAO();
    $dadosMorador = $usuarioDAO->buscarUsuarioPorId($dadosFatura['dono_unidade']);
    $nomeMorador = $dadosMorador['nome'] ?? 'Cliente';

    $sqlBeneficio = "SELECT percentual_desconto, tipo_beneficio 
                     FROM tarifa_social 
                     WHERE id_usuario = :id_user AND status_beneficio = 'Ativo' 
                     LIMIT 1";
    $stmtBen = $pdo->prepare($sqlBeneficio);
    $stmtBen->execute([':id_user' => $dadosFatura['dono_unidade']]);
    $beneficio = $stmtBen->fetch(PDO::FETCH_ASSOC);

    // 5. CÁLCULOS
    $statusFatura = $dadosFatura['status_pagamento'] ?? 'Pendente';
    $consumoM3 = (float)($dadosFatura['consumo_m3'] ?? 0);
    $valorM3 = (float)($dadosFatura['valor_m3'] ?? 0);
    $taxaEsgoto = (float)($dadosFatura['taxa_esgoto'] ?? 0);

    $valorConsumoBase = $consumoM3 * $valorM3;
    $subtotalBruto = $valorConsumoBase + $taxaEsgoto;

    $economiaReal = 0;
    if (!empty($beneficio)) {
        $percentual = (float)$beneficio['percentual_desconto'] / 100;
        $economiaReal = $valorConsumoBase * $percentual;
    }
    $totalExibicao = $subtotalBruto - $economiaReal;

} catch (Exception $e) {
    die("Erro técnico: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Fatura - MedidaCerta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/faturaPDF.css">
    </head>
<body>
    <div class="invoice-wrapper">
        <div class="invoice-card">
            <div class="invoice-header">
                <div class="invoice-top">
                    <div class="invoice-brand">
                        <div class="invoice-brand-icon">MC</div>
                        <div class="invoice-brand-title">
                            <h1>MedidaCerta</h1>
                            <p>Fatura de consumo de Água</p>
                        </div>
                    </div>
                    <div class="invoice-actions">
                        <button class="btn-primary" type="button" onclick="window.print()">Baixar/Imprimir</button>
                    </div>
                </div>

                <div class="invoice-meta">
                    <div class="invoice-meta-item">
                        <span>Cliente</span>
                        <strong><?= htmlspecialchars($nomeMorador, ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="invoice-meta-item">
                        <span>Unidade</span>
                        <strong>Bloco <?= htmlspecialchars($dadosFatura['bloco'], ENT_QUOTES, 'UTF-8') ?> - Nº <?= htmlspecialchars($dadosFatura['numero'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="invoice-meta-item">
                        <span>Referência</span>
                        <strong><?= getNomeMes($dadosFatura['mes_referencia']) ?> / <?= htmlspecialchars($dadosFatura['ano_referencia'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="invoice-meta-item">
                        <span>Fatura #<?= htmlspecialchars($dadosFatura['id_fatura'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="invoice-badge <?= $statusFatura === 'Pago' ? 'badge-paid' : ($statusFatura === 'Atrasado' ? 'badge-late' : 'badge-pending') ?>">
                            <?= htmlspecialchars($statusFatura, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="invoice-body">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Consumo de Água (<?= number_format($consumoM3, 1, ',', '.') ?> m³ × R$ <?= number_format($valorM3, 2, ',', '.') ?>)</td>
                            <td class="text-end">R$ <?= number_format($valorConsumoBase, 2, ',', '.') ?></td>
                        </tr>

                        <?php if ($economiaReal > 0.01): ?>
                            <tr style="color: #059669; font-weight: 600; background-color: #f0fdf4;">
                                <td>
                                    Benefício: <?= htmlspecialchars($beneficio['tipo_beneficio'] ?? 'Tarifa Social') ?> (<?= (int)$beneficio['percentual_desconto'] ?>%)
                                </td>
                                <td class="text-end">- R$ <?= number_format($economiaReal, 2, ',', '.') ?></td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <td>Taxa de Esgoto / Disponibilidade</td>
                            <td class="text-end">R$ <?= number_format($taxaEsgoto, 2, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="invoice-total">
                    <div class="invoice-total-box">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">
                            <span>Subtotal Bruto:</span>
                            <span>R$ <?= number_format($subtotalBruto, 2, ',', '.') ?></span>
                        </div>

                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                            <span>Vencimento:</span>
                            <strong><?= date('d/m/Y', strtotime($dadosFatura['data_vencimento'])) ?></strong>
                        </div>

                        <div class="invoice-final" style="margin-top: 10px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 700;">TOTAL A PAGAR:</span>
                            <span style="font-size: 1.3rem; font-weight: 800;">R$ <?= number_format($totalExibicao, 2, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($statusFatura !== 'Pago'): ?>
                    <div class="checkout-container mt-4 d-print-none">
                        <div class="bg-primary text-white p-2 px-3">
                            <small class="fw-bold"><i class="bi bi-shield-check me-2"></i>CHECKOUT SEGURO MEDIDACERTA</small>
                        </div>

                        <ul class="nav nav-tabs nav-payment bg-light" id="paymentTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="pix-tab" data-bs-toggle="tab" data-bs-target="#pix" type="button">PIX</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="card-tab" data-bs-toggle="tab" data-bs-target="#card" type="button">Cartão</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="boleto-tab" data-bs-toggle="tab" data-bs-target="#boleto" type="button">Boleto</button>
                            </li>
                        </ul>

                        <div class="tab-content p-4 bg-white" id="paymentTabContent">
                            <div class="tab-pane fade show active" id="pix" role="tabpanel">
                                <div class="row align-items-center">
                                    <div class="col-md-4 text-center">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=PIX_MEDIDACERTA_<?= $id_fatura ?>" class="img-fluid border p-2">
                                    </div>
                                    <div class="col-md-8">
                                        <p class="small text-muted">Aponte a câmera do celular ou copie o código abaixo:</p>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" id="pixCode" value="00020101021126580014br.gov.bcb.pix..." readonly>
                                            <button class="btn btn-primary btn-sm" onclick="copyValue('pixCode')">Copiar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="card" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="small fw-bold">Número do Cartão</label>
                                        <input type="text" class="form-control" placeholder="0000 0000 0000 0000">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small fw-bold">Validade</label>
                                        <input type="text" class="form-control" placeholder="MM/AA">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small fw-bold">CVV</label>
                                        <input type="text" class="form-control" placeholder="123" maxlength="3">
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="boleto" role="tabpanel">
                                <div class="text-center">
                                    <p class="mb-2 small">Linha Digitável:</p>
                                    <div class="bg-light p-2 mb-3 font-monospace small border">
                                        23793.38128 60033.488511 14006.333481 9 968300000<?= str_replace(['.', ','], '', number_format($totalExibicao, 2)) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-light border-top">
                            <form action="../controller/ConfirmarPagamento.php" method="POST">
                                <input type="hidden" name="id_fatura" value="<?= $dadosFatura['id_fatura'] ?>">
                                <button type="submit" class="btn btn-success w-100 fw-bold py-2 btn-confirmar">
                                    EFETUAR PAGAMENTO: R$ <?= number_format($totalExibicao, 2, ',', '.') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success text-center mt-4">
                        <h4 class="fw-bold mb-0 text-uppercase"><i class="bi bi-check-all"></i> Comprovante de Quitação</h4>
                        <p class="small mb-0">Esta fatura encontra-se liquidada em nossos sistemas.</p>
                    </div>
                <?php endif; ?>

                <p class="invoice-note">
                    Esta fatura foi gerada automaticamente pelo sistema <strong>MedidaCerta</strong>.
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyValue(id) {
            var copyText = document.getElementById(id);
            copyText.select();
            navigator.clipboard.writeText(copyText.value);
            alert("Código copiado com sucesso!");
        }

        document.querySelector('form').onsubmit = function() {
            let btn = document.querySelector('.btn-confirmar');
            if(btn) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';
                btn.classList.add('disabled');
            }
        };
    </script>
</body>
</html>