<?php

/**
 * PROJETO: MedidaCerta
 * OBJETIVO: Gestão Administrativa de Benefícios com Filtros Padronizados
 */

require_once __DIR__ . '/../controller/TravaAdmin.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

// Captura de filtros (seguindo o padrão do listarusuarios)
$filtroNome  = trim(filter_input(INPUT_GET, 'nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$filtroBloco = trim(filter_input(INPUT_GET, 'bloco', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

$totalInadimplentes = 0;
$totalBeneficiosAtivos = 0;
$listaMoradores = [];

try {
    $pdo = Conexao::getConexao();

    // 1. Contadores para os Cards
    // Inadimplentes: usuários com faturas NÃO PAGAS (status Pendente ou Atrasado)
    $sqlInad = "SELECT COUNT(DISTINCT u.id_usuario) as total 
                FROM fatura f 
                JOIN leitura l ON f.id_leitura = l.id_leitura 
                JOIN hidrometro h ON l.id_hidrometro = h.id_hidrometro
                JOIN unidade u ON h.id_unidade = u.id_unidade
                WHERE f.status_pagamento IN ('Pendente', 'Atrasado')";
    $totalInadimplentes = (int) ($pdo->query($sqlInad)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    $sqlAtivos = "SELECT COUNT(*) as total FROM tarifa_social WHERE status_beneficio = 'Ativo'";
    $totalBeneficiosAtivos = (int) ($pdo->query($sqlAtivos)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // 2. Query Principal com Filtros Dinâmicos
    $sqlPrincipal = "SELECT DISTINCT
        u.id_usuario, u.nome, un.bloco, un.numero, un.id_unidade,
        ts.status_beneficio,
        -- Buscamos o consumo calculado (diferença) e não o valor medido (leitura total)
        COALESCE((SELECT l.consumo_calculado 
          FROM leitura l 
          JOIN hidrometro h2 ON l.id_hidrometro = h2.id_hidrometro
          WHERE h2.id_unidade = un.id_unidade 
          ORDER BY l.data_leitura DESC, l.id_leitura DESC LIMIT 1), 0) as ultimo_consumo,
        
        COALESCE((SELECT COUNT(DISTINCT f2.id_fatura) 
          FROM fatura f2 
          JOIN leitura l2 ON f2.id_leitura = l2.id_leitura 
          JOIN hidrometro h2 ON l2.id_hidrometro = h2.id_hidrometro
          WHERE h2.id_unidade = un.id_unidade 
          AND f2.status_pagamento IN ('Pendente', 'Atrasado')), 0) as qtd_debitos
    FROM usuario u
    JOIN unidade un ON u.id_usuario = un.id_usuario
    LEFT JOIN tarifa_social ts ON u.id_usuario = ts.id_usuario AND ts.status_beneficio = 'Ativo'
    WHERE 1=1";

    $params = [];
    if (!empty($filtroNome)) {
        $sqlPrincipal .= " AND u.nome LIKE :nome";
        $params[':nome'] = "%$filtroNome%";
    }
    if (!empty($filtroBloco)) {
        $sqlPrincipal .= " AND un.bloco = :bloco";
        $params[':bloco'] = $filtroBloco;
    }

    $sqlPrincipal .= " ORDER BY un.bloco ASC, un.numero ASC";

    $stmt = $pdo->prepare($sqlPrincipal);
    $stmt->execute($params);
    $listaMoradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erro no Gestão de Benefícios: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Gestão de Benefícios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* Estilização padronizada igual listarusuarios */
        .search-wrapper {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 15px;
            border: 1px solid #edf2f7;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }

        .search-input-group {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }

        .search-input-group .form-control {
            border-radius: 12px;
            padding-left: 45px;
            height: 45px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }

        .search-input-group .form-control:focus {
            background-color: #fff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .search-input-group .bi-search {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            color: #a0aec0;
            font-size: 1.1rem;
        }

        /* Mantendo seus estilos originais de benefícios */
        .table-card {
            border-radius: 15px;
            overflow: hidden;
        }

        .status-badge-apto {
            background-color: #dcfce7;
            color: #16a34a;
            font-size: 0.75rem;
        }

        .status-badge-inapto {
            background-color: #fee2e2;
            color: #dc2626;
            font-size: 0.75rem;
        }

        /* Container do Alerta Flutuante */
        .alert-floating-container {
            position: fixed;
            top: 25px;
            left: 50%;
            z-index: 1060;
            transform: translateX(-50%);
        }

        .alert-floating-container .alert {
            background: #ffffff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 500;
            color: #198754;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <?php include '../view/includes/header.php'; ?>

    <?php if (isset($_GET['status'])): ?>
        <div id="sucessoAlert" class="alert-floating-container">
            <?php
            $status = $_GET['status'];
            $config = [
                'sucesso' => ['class' => 'success', 'icon' => 'bi-check-circle-fill', 'msg' => 'Operação realizada com sucesso!'],
                'regra_violada' => ['class' => 'danger', 'icon' => 'bi-exclamation-triangle-fill', 'msg' => 'Erro: Morador não cumpre os requisitos.'],
                'erro' => ['class' => 'danger', 'icon' => 'bi-x-circle-fill', 'msg' => 'Erro ao processar a solicitação.']
            ];
            $current = $config[$status] ?? $config['erro'];
            ?>
            <div class="alert alert-<?= $current['class'] ?> shadow-lg border-0 d-flex align-items-center animate__animated animate__fadeInRight" role="alert">
                <i class="bi <?= $current['icon'] ?> fs-4 me-3"></i>
                <div>
                    <span class="small"><?= $current['msg'] ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <main class="container main-content flex-grow-1 pt-0 pb-4">

        <div class="row g-3 mb-4 justify-content-center">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm h-100 border-0 border-start border-4 border-primary">
                        <div class="card-body">
                            <small class="text-primary fw-bold text-uppercase">Benefícios Ativos</small>
                            <h3 class="fw-bold mt-1 mb-0">
                                <span class="counter" data-target="<?= $totalBeneficiosAtivos ?>"><?= $totalBeneficiosAtivos ?></span>
                            </h3>
                            <div class="mt-2 text-primary small"><i class="bi bi-patch-check"></i> Monitoramento via tarifa_social</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card stat-card shadow-sm h-100 border-0 border-start border-4 border-danger">
                        <div class="card-body">
                            <small class="text-danger fw-bold text-uppercase">Inadimplentes</small>
                            <h3 class="fw-bold mt-1 mb-0">
                                <span class="counter" data-target="<?= $totalInadimplentes ?>"><?= $totalInadimplentes ?></span>
                            </h3>
                            <div class="mt-2 small text-muted">Unidades com faturas vencidas</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card stat-card shadow-sm h-100 border-0 border-start border-4 border-info">
                        <div class="card-body">
                            <small class="text-info fw-bold text-uppercase">Regra do Rateio</small>
                            <h3 class="fw-bold mt-1 mb-0">
                                <span class="counter" data-target="30">30</span> m³
                            </h3>
                            <div class="mt-2 small text-muted">Limite para Tarifa Social</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="search-wrapper">
                <div class="search-input-group">
                    <i class="bi bi-search"></i>
                    <input type="text" id="inputPesquisa" class="form-control"
                        placeholder="Pesquisar por nome, bloco ou unidade...">
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 table-card">
                        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Controle de Unidades e Aptidão</h6>
                            <span class="badge bg-light text-dark border">Atualizado agora</span>
                        </div>

                        <div class="table-responsive p-3">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr class="small text-uppercase text-muted">
                                        <th>Unidade</th>
                                        <th>Morador</th>
                                        <th>Último Consumo</th>
                                        <th>Financeiro</th>
                                        <th>Aptidão</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tabelaMoradores"> <?php if (empty($listaMoradores)): ?>
                                        <tr class="sem-dados">
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                                Nenhum registro encontrado.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listaMoradores as $m):
                                                                        $ultimoConsumo = isset($m['ultimo_consumo']) ? (float) $m['ultimo_consumo'] : 0.0;
                                                                        $isApto = ($ultimoConsumo > 0 && $ultimoConsumo <= 30 && $m['qtd_debitos'] == 0);
                                        ?>
                                            <tr class="morador-row">
                                                <td><span class="fw-bold">Bl. <?= $m['bloco'] ?> - <?= $m['numero'] ?></span></td>
                                                <td class="nome-alvo"><?= htmlspecialchars($m['nome']) ?></td>
                                                <td><?= $ultimoConsumo > 0 ? number_format($ultimoConsumo, 2, ',', '.') . ' m³' : '<span class="text-muted">Sem leitura</span>' ?></td>
                                                <td>
                                                    <?php if ($m['qtd_debitos'] > 0): ?>
                                                        <span class="badge status-badge-inapto"><i class="bi bi-x-circle me-1"></i> Inadimplente</span>
                                                    <?php else: ?>
                                                        <span class="badge status-badge-apto"><i class="bi bi-check-circle me-1"></i> Em dia</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($m['status_beneficio'] == 'Ativo'): ?>
                                                        <span class="text-success small fw-bold">Ativo</span>
                                                    <?php elseif ($isApto): ?>
                                                        <span class="text-primary small fw-bold">Apto</span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Não cumpre requisitos</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <form method="POST" action="../controller/ProcessarBeneficio.php">
                                                        <input type="hidden" name="id_usuario" value="<?= $m['id_usuario'] ?>">
                                                        <?php if ($m['status_beneficio'] === 'Ativo'): ?>
                                                            <button type="submit" name="acao" value="suspender" class="btn btn-sm btn-outline-danger rounded-pill px-3">Suspender</button>
                                                        <?php elseif ($isApto): ?>
                                                            <button type="submit" name="acao" value="ativar" class="btn btn-sm btn-primary rounded-pill px-3">Ativar</button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-sm btn-light rounded-pill px-3 text-muted" disabled>Bloqueado</button>
                                                        <?php endif; ?>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <tr id="avisoVazio" class="d-none">
                                        <td colspan="6" class="text-center py-5">
                                            <i class="bi bi-search fs-2 text-muted d-block mb-2"></i>
                                            <span class="text-muted">Nenhum morador encontrado para esta busca.</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
    </main>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('inputPesquisa');
            const rows = document.querySelectorAll('.morador-row'); // Seleciona as linhas da tabela
            const emptyNotice = document.getElementById('avisoVazio');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                let hasResults = false;

                rows.forEach(row => {
                    // Pega todo o texto da linha (Nome + Bloco + Unidade)
                    const textoLinha = row.innerText.toLowerCase();

                    if (textoLinha.includes(query)) {
                        row.classList.remove('d-none');
                        hasResults = true;
                    } else {
                        row.classList.add('d-none');
                    }
                });

                // Exibe a mensagem "Nenhum resultado" se necessário
                if (!hasResults && query !== "") {
                    emptyNotice.classList.remove('d-none');
                } else {
                    emptyNotice.classList.add('d-none');
                }
            });
        });
    </script>

    <script>
        const sucessoAlert = document.getElementById('sucessoAlert');
        const alertToRemove = sucessoAlert;

        if (alertToRemove) {
            // 1. Limpa o parâmetro 'status' da URL sem recarregar a página
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('status'); // Aqui mudamos para 'status' que é o seu padrão nesta página
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }

            // 2. Animação de sumir o alerta após 3 segundos (conforme seu padrão)
            setTimeout(() => {
                alertToRemove.style.transition = "opacity 0.6s ease";
                alertToRemove.style.opacity = "0";
                setTimeout(() => alertToRemove.remove(), 600);
            }, 3000);
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const counters = document.querySelectorAll('.counter');

            counters.forEach(counter => {
                counter.innerText = '0';

                const updateCount = () => {
                    const target = parseFloat(counter.getAttribute('data-target'));
                    const currentText = counter.innerText.replace(/\./g, '').replace(',', '.');
                    const count = parseFloat(currentText);

                    // Ajuste de velocidade
                    const increment = target / 40;

                    if (count < target) {
                        const nextValue = count + increment;

                        counter.innerText = nextValue.toLocaleString('pt-BR', {
                            minimumFractionDigits: 0, // FORÇA pelo menos uma casa (ex: 10,0)
                            maximumFractionDigits: 2
                        });

                        setTimeout(updateCount, 20);
                    } else {
                        // VALOR FINAL: Força a formatação exata
                        counter.innerText = target.toLocaleString('pt-BR', {
                            minimumFractionDigits: 0, // Garante que a vírgula apareça no final
                            maximumFractionDigits: 2
                        });
                    }
                };

                updateCount();
            });
        });
    </script>
</body>

</html>