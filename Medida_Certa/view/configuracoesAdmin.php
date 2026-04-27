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
    'valor_m3'             => '0.00',
    'taxa_esgoto'          => '0',
    'dia_vencimento'       => '10',
    'alerta_vazamento'     => '0',
    'alerta_inadimplencia' => '0',
    'modo_manutencao'      => '0',
    'nome_condominio'      => 'Condomínio MedidaCerta'
];

// Mapeamento Chave Interna => Coluna 'chave' no Banco (Conforme sua imagem 7b866b.png)
$chavesBanco = [
    'valor_m3'        => 'tarifa_minima_valor',
    'taxa_esgoto'     => 'taxa_esgoto_percentual',
    'modo_manutencao' => 'modo_manutencao',
    'nome_condominio' => 'nome_condominio'
];

try {
    $conn = Conexao::getConexao();

    // 1. Dados do Usuário para o Header
    $sqlU = "SELECT nome, foto FROM usuario WHERE id_usuario = :id";
    $stmtU = $conn->prepare($sqlU);
    $stmtU->execute([':id' => $id_usuario]);
    $usuario = $stmtU->fetch(PDO::FETCH_ASSOC);
    $nomeAdmin = $usuario['nome'] ?? 'Admin';

    // 2. Busca de Configurações no Banco
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
            border-left: 4px solid #198754;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 500;
            color: #198754;
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
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item active">Configurações</li>
                    </ol>
                </nav>
            </div>
            <i class="bi bi-sliders text-muted fs-4"></i>
        </div>
    </div>

    <main class="main-container container mb-5" style="max-width: 1000px;">

        <form action="../controller/SalvarConfiguracoes.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

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
                            <div class="section-icon bg-blue-soft"><i class="bi bi-calculator"></i></div>
                            <h5 class="fw-bold mb-0">Parâmetros de Cálculo</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">VALOR DO M³ (R$)</label>
                                <input type="number" step="0.01" class="form-control" name="valor_m3" value="<?= $val['valor_m3'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">TAXA ESGOTO (%)</label>
                                <input type="number" class="form-control" name="taxa_esgoto" value="<?= $val['taxa_esgoto'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">VENCIMENTO PADRÃO</label>
                                <select class="form-select" name="dia_vencimento">
                                    <?php for ($d = 5; $d <= 25; $d += 5): ?>
                                        <option value="<?= $d ?>" <?= $val['dia_vencimento'] == $d ? 'selected' : '' ?>>Dia <?= $d ?></option>
                                    <?php endfor; ?>
                                </select>
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

                    <div class="d-flex justify-content-end gap-2 border-top pt-4 mt-3">
                        <a href="dashboardAdmin.php" class="btn btn-light rounded-pill px-4 text-muted">Voltar</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Aplicar Configurações</button>
                    </div>

                </div>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php include '../view/includes/footer.php'; ?>

    <script>
    const sucessoAlert = document.getElementById('sucessoAlert');
    const erroAlert = document.getElementById('erroAlert');
    const alertToRemove = sucessoAlert || erroAlert;

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
</body>

</html>