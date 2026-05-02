<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    header("Location: ../login.php");
    exit;
}

// Configurações Padrão de Inicialização
$val = [
    'valor_fixo'           => '0.00',
    'valor_m3'             => '0.00',
    'taxa_esgoto'          => '100',
    'dia_vencimento'       => '10',
    'alerta_vazamento'     => '0',
    'alerta_inadimplencia' => '0',
    'modo_manutencao'      => '0',
    'nome_condominio'      => 'Condomínio MedidaCerta'
];

$currentTarifaId = 1;
$faixas = [];

$chavesBanco = [
    'dia_vencimento'       => 'dia_vencimento',
    'alerta_vazamento'     => 'alerta_vazamento',
    'alerta_inadimplencia' => 'alerta_inadimplencia',
    'modo_manutencao'      => 'modo_manutencao',
    'nome_condominio'      => 'nome_condominio'
];

try {
    $conn = Conexao::getConexao();

    // 1. Dados do Usuário para o Header
    $sqlU = "SELECT nome, foto FROM usuario WHERE id_usuario = :id";
    $stmtU = $conn->prepare($sqlU);
    $stmtU->execute([':id' => $id_usuario]);
    $usuario = $stmtU->fetch(PDO::FETCH_ASSOC);
    $nomeAdmin = $usuario['nome'] ?? 'Admin';

    // 2. Busca de Configurações do Sistema
    $stmtC = $conn->query("SELECT chave, valor FROM configuracoes");
    $configs_db = $stmtC->fetchAll(PDO::FETCH_KEY_PAIR);

    if ($configs_db) {
        foreach ($val as $key => $default) {
            $chaveRealNoBanco = $chavesBanco[$key] ?? $key;
            if (isset($configs_db[$chaveRealNoBanco])) {
                $val[$key] = $configs_db[$chaveRealNoBanco];
            }
        }
    }

    // 3. Busca da tarifa ativa mais recente
    $stmtT = $conn->query("SELECT id_tarifa, valor_fixo, valor_m3, taxa_esgoto FROM tarifa ORDER BY data_vigencia DESC LIMIT 1");
    $tarifaBase = $stmtT->fetch(PDO::FETCH_ASSOC);
    $currentTarifaId = 1;

    if ($tarifaBase) {
        $currentTarifaId = (int)$tarifaBase['id_tarifa'];
        $val['valor_fixo'] = $tarifaBase['valor_fixo'] ?? $val['valor_fixo'];
        $val['valor_m3'] = $tarifaBase['valor_m3'] ?? $val['valor_m3'];
        $val['taxa_esgoto'] = $tarifaBase['taxa_esgoto'] ?? $val['taxa_esgoto'];
    }

    // 4. Busca as faixas de consumo da tarifa ativa
    $stmtF = $conn->prepare("SELECT * FROM tarifa_faixas WHERE id_tarifa = :id_tarifa ORDER BY limite_superior ASC");
    $stmtF->execute([':id_tarifa' => $currentTarifaId]);
    $faixas = $stmtF->fetchAll(PDO::FETCH_ASSOC);

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
} catch (PDOException $e) {
    error_log("Erro de Conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>MedidaCerta - Configurações Administrativas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .section-icon {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .bg-blue-soft {
            background-color: #e7f0ff;
            color: #0d6efd;
        }

        .bg-orange-soft {
            background-color: #fff4e6;
            color: #fd7e14;
        }

        .bg-red-soft {
            background-color: #ffeef0;
            color: #dc3545;
        }

        .bg-purple-soft {
            background-color: #f3f0ff;
            color: #6f42c1;
        }

        .card-main {
            border-radius: 16px;
            border: none;
        }

        .alert-floating-container {
            position: fixed;
            top: 25px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1060;
        }

        .alert-compacto {
            background: #ffffff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 500;
            color: #198754;
        }

        .btn-lg {
            padding: 1rem;
            font-size: 1rem;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php include '../view/includes/header.php'; ?>

    <div class="alert-floating-container">
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-compacto fade show" id="sucessoAlert">
                <i class="bi bi-check-circle-fill me-2"></i> Configurações aplicadas com sucesso!
            </div>
        <?php endif; ?>
    </div>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Configurações do Sistema</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-primary">Admin</a></li>
                        <li class="breadcrumb-item active">Configurações</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="javascript:history.back()" class="btn btn-outline-secondary rounded-3 px-4 shadow-sm">
                    Cancelar
                </a>
            </div>
        </div>
    </div>

    <main class="main-container container mb-5" style="max-width: 1000px;">

        <form action="../controller/SalvarConfiguracoes.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id_tarifa" value="<?= htmlspecialchars($currentTarifaId) ?>">

            <div class="card card-main shadow-sm p-4">
                <div class="card-body">

                    <section class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="section-icon bg-purple-soft"><i class="bi bi-building"></i></div>
                            <h5 class="fw-bold mb-0">Identificação</h5>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">NOME DO CONDOMÍNIO (PARA FATURAS)</label>
                                <input type="text" class="form-control border rounded-3" name="nome_condominio" value="<?= htmlspecialchars($val['nome_condominio']) ?>">
                            </div>
                        </div>
                    </section>

                    <section class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="section-icon bg-blue-soft"><i class="bi bi-bank"></i></div>
                            <h5 class="fw-bold mb-0">Tarifa Geral</h5>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">TAXA FIXA DISPONIBILIDADE (R$)</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" step="0.01" class="form-control" name="valor_fixo" value="<?= htmlspecialchars($val['valor_fixo']) ?>">
                                </div>
                                <div class="form-text">Cobrança fixa mensal para disponibilidade do serviço.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">VALOR PADRÃO POR m³ (R$)</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" step="0.01" class="form-control" name="valor_m3" value="<?= htmlspecialchars($val['valor_m3']) ?>">
                                </div>
                                <div class="form-text">Usado como fallback caso o consumo ultrapasse a última faixa ou quando não houver faixas.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">TAXA ESGOTO (%)</label>
                                <input type="number" step="0.01" class="form-control" name="taxa_esgoto" value="<?= htmlspecialchars($val['taxa_esgoto']) ?>">
                                <div class="form-text">Percentual aplicado sobre o valor da água.</div>
                            </div>
                        </div>
                    </section>

                    <section class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="section-icon bg-blue-soft"><i class="bi bi-table"></i></div>
                            <h5 class="fw-bold mb-0">Faixas de Consumo</h5>
                        </div>
                        <p class="small text-muted mb-4">Defina as faixas progressivas de consumo. O cálculo da água considera cada faixa na ordem e utiliza o valor padrão por m³ somente se o consumo ultrapassar a última faixa.</p>

                        <label class="form-label small fw-bold text-muted">TABELA DE FAIXAS DE CONSUMO</label>
                        <div id="container-faixas">
                            <?php foreach ($faixas as $index => $faixa): ?>
                                <div class="row g-2 mb-2 align-items-center faixa-row">
                                    <div class="col-md-5">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Até</span>
                                            <input type="number" name="faixa_limite[]" class="form-control faixa-limite" value="<?= htmlspecialchars($faixa['limite_superior']) ?>" placeholder="m³" required>
                                            <span class="input-group-text">m³</span>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">R$</span>
                                            <input type="number" step="0.01" name="faixa_valor[]" class="form-control faixa-valor" value="<?= htmlspecialchars($faixa['valor_m3']) ?>" placeholder="Valor/m³" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-faixa"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="btn-add-faixa" class="btn btn-outline-primary btn-sm mt-2 rounded-pill">
                            <i class="bi bi-plus-circle me-1"></i> Adicionar Faixa
                        </button>
                    </section>

                    <section class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="section-icon bg-orange-soft"><i class="bi bi-shield-lock"></i></div>
                            <h5 class="fw-bold mb-0">Configurações do Sistema</h5>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">DIA VENCIMENTO PADRÃO</label>
                                <div class="input-group">
                                    <input type="number" min="1" max="31" class="form-control" name="dia_vencimento" value="<?= htmlspecialchars($val['dia_vencimento']) ?>">
                                    <span class="input-group-text">do mês</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="section-icon bg-orange-soft"><i class="bi bi-megaphone"></i></div>
                            <h5 class="fw-bold mb-0">Alertas e Segurança</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                                <div>
                                    <h6 class="mb-0 fw-semibold">Notificar Suspeita de Vazamento</h6>
                                    <p class="text-muted small mb-0">Ativar análise de consumo contínuo 24h.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="alerta_vazamento" value="1" <?= $val['alerta_vazamento'] == '1' ? 'checked' : '' ?> style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                                <div>
                                    <h6 class="mb-0 fw-semibold">Alertas de Inadimplência</h6>
                                    <p class="text-muted small mb-0">Notificar quando houver atrasos no pagamento.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="alerta_inadimplencia" value="1" <?= $val['alerta_inadimplencia'] == '1' ? 'checked' : '' ?> style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <div>
                                    <h6 class="mb-0 fw-semibold">Modo de Manutenção</h6>
                                    <p class="text-muted small mb-0">Bloqueia acesso de moradores ao painel.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="modo_manutencao" value="1" <?= $val['modo_manutencao'] == '1' ? 'checked' : '' ?> style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="mb-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="section-icon bg-red-soft"><i class="bi bi-database-down"></i></div>
                            <h5 class="fw-bold mb-0">Backup de Segurança</h5>
                        </div>
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Base de Dados MySQL</span>
                                <p class="text-muted small mb-0">Última exportação: <?= date('d/m/Y H:i') ?></p>
                            </div>
                            <a href="../controller/ExportarBackup.php" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold">
                                <i class="bi bi-download me-1"></i> Fazer Backup Agora
                            </a>
                        </div>
                    </section>

                    <div class="row g-3 pt-2 justify-content-center">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm py-3">
                                Salvar Alterações
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php include '../view/includes/footer.php'; ?>

    <script>
        const sucessoAlert = document.getElementById('sucessoAlert');
        const alertToRemove = sucessoAlert;

        if (alertToRemove) {
            // 1. Limpa o parâmetro da URL sem recarregar a página
            // Isso impede que o F5 mostre a mensagem de novo
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('sucesso');
                url.searchParams.delete('erro');
                window.history.replaceState({}, document.title, url.pathname);
            }

            // 2. Animação de sumir o alerta após 3 segundos
            setTimeout(() => {
                alertToRemove.style.transition = "opacity 0.6s ease";
                alertToRemove.style.opacity = "0";
                setTimeout(() => alertToRemove.remove(), 600);
            }, 3000);
        }
    </script>
    <script>
        document.getElementById('btn-add-faixa').addEventListener('click', function() {
            const container = document.getElementById('container-faixas');
            const template = `
        <div class="row g-2 mb-2 align-items-center faixa-row">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Até</span>
                    <input type="number" name="faixa_limite[]" class="form-control faixa-limite" placeholder="m³" required>
                    <span class="input-group-text">m³</span>
                </div>
            </div>
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">R$</span>
                    <input type="number" step="0.01" name="faixa_valor[]" class="form-control faixa-valor" placeholder="Valor/m³" required>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-faixa"><i class="bi bi-trash"></i></button>
            </div>
        </div>`;
            container.insertAdjacentHTML('beforeend', template);
        });

        // Delegação de evento para remover faixas
        document.getElementById('container-faixas').addEventListener('click', function(e) {
            if (e.target.closest('.remove-faixa')) {
                e.target.closest('.faixa-row').remove();
            }
        });

        // Validação do formulário antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const faixasContainer = document.getElementById('container-faixas');
            const faixasRows = faixasContainer.querySelectorAll('.faixa-row');

            if (faixasRows.length === 0) {
                alert('⚠️ Aviso:\nVocê não adicionou nenhuma faixa de consumo.\nUma faixa padrão será criada automaticamente.');
                return; // Permite enviar (criará faixa padrão no backend)
            }

            let todasValidas = true;
            const erros = [];

            faixasRows.forEach((row, index) => {
                const inputLimite = row.querySelector('input[name="faixa_limite[]"]');
                const inputValor = row.querySelector('input[name="faixa_valor[]"]');

                const limite = parseInt(inputLimite.value) || 0;
                const valorM3 = parseFloat(inputValor.value.toString().replace(',', '.')) || 0;

                // Validação
                if (!inputLimite.value || limite <= 0) {
                    todasValidas = false;
                    inputLimite.classList.add('is-invalid');
                    inputLimite.style.borderColor = '#dc3545';
                    erros.push(`Faixa ${index + 1}: Limite deve ser > 0`);
                } else {
                    inputLimite.classList.remove('is-invalid');
                    inputLimite.style.borderColor = '';
                }

                if (!inputValor.value || valorM3 < 0.01) {
                    todasValidas = false;
                    inputValor.classList.add('is-invalid');
                    inputValor.style.borderColor = '#dc3545';
                    erros.push(`Faixa ${index + 1}: Valor deve ser ≥ R$ 0.01`);
                } else {
                    inputValor.classList.remove('is-invalid');
                    inputValor.style.borderColor = '';
                }
            });

            if (!todasValidas) {
                e.preventDefault();
                alert('❌ Erro de validação:\n\n' + erros.join('\n'));
                return;
            }

            console.log('✅ Faixas validadas com sucesso. Enviando...');
        });
    </script>
</body>

</html>