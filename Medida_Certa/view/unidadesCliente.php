<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controller/TravaCliente.php';
require_once __DIR__ . '/../model/dao/Conexao.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    header("Location: ../login.php");
    exit;
}

try {
    $conn = Conexao::getConexao();

    // Busca dados para o Header
    $sqlUser = "SELECT nome, foto FROM usuario WHERE id_usuario = :id";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bindParam(':id', $id_usuario, PDO::PARAM_INT);
    $stmtUser->execute();
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // BUSCA CORRIGIDA: Usando 'numero' conforme seu modelo ER
    $sqlUnidades = "SELECT id_unidade, numero, bloco, endereco FROM unidade WHERE id_usuario = :id";
    $stmtUnidades = $conn->prepare($sqlUnidades);
    $stmtUnidades->bindParam(':id', $id_usuario, PDO::PARAM_INT);
    $stmtUnidades->execute();
    $unidades = $stmtUnidades->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar unidades: " . $e->getMessage());
}

// Lógica de iniciais e foto (Sua lógica padrão)
$nome = $usuario['nome'] ?? 'Cliente';
$partes_nome = explode(' ', trim($nome));
$iniciais_header = strtoupper(mb_substr($partes_nome[0], 0, 1) . (count($partes_nome) > 1 ? mb_substr(end($partes_nome), 0, 1) : ""));

$foto_db = $usuario['foto'] ?? '';
$foto_url_header = "";
$tem_foto_header = false;
if (!empty($foto_db)) {
    $base64 = base64_encode($foto_db);
    if ($base64) {
        $foto_url_header = 'data:image/jpeg;base64,' . $base64;
        $tem_foto_header = true;
    }
}

// 3. Processamento de Variáveis para a View
$nome = $usuario['nome'] ?? 'Cliente';
$email = $usuario['email'] ?? '';
$telefone = $usuario['telefone'] ?? '';
$cpf_cnpj = $usuario['cpf_cnpj'] ?? '';

// 4. Lógica de Iniciais (Sincronizada com o Header)
$nome_limpo = trim($nome);
$partes_nome = explode(' ', $nome_limpo);
$primeira_inicial = mb_substr($partes_nome[0], 0, 1);
$ultima_inicial = (count($partes_nome) > 1) ? mb_substr(end($partes_nome), 0, 1) : "";
$iniciais = strtoupper($primeira_inicial . $ultima_inicial);

// 5. Lógica da Foto (Sincronizada com o que o Header espera)
$foto_db = $usuario['foto'] ?? '';
$foto_url_header = "";    // Variável que o headerCliente.php usa
$tem_foto_header = false; // Variável que o headerCliente.php usa

if (!empty($foto_db)) {
    if (is_string($foto_db) && strpos($foto_db, 'data:image') === 0) {
        $foto_url_header = $foto_db;
        $tem_foto_header = true;
    } elseif (is_string($foto_db)) {
        $imagemPath = __DIR__ . '/../assets/img/perfil/' . $foto_db;
        if (file_exists($imagemPath)) {
            $foto_url_header = '../assets/img/perfil/' . rawurlencode($foto_db);
            $tem_foto_header = true;
        } else {
            $base64 = base64_encode($foto_db);
            if ($base64) {
                $foto_url_header = 'data:image/jpeg;base64,' . $base64;
                $tem_foto_header = true;
            }
        }
    }
}

// Variáveis de compatibilidade para o corpo do perfil.php
$foto_perfil_url = $foto_url_header;
$tem_foto = $tem_foto_header;
$iniciais_header = $iniciais; // Enviando as iniciais para o círculo do header
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Minhas Unidades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/perfil.css">

    <style>
        /* TÉCNICA PARA O FOOTER COLADO EMBAIXO */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ocupa no mínimo a altura total da tela */
            margin: 0;
        }

        main {
            flex: 1; /* Faz o conteúdo principal crescer e empurrar o footer */
        }
        
        /* Estilo do Card inspirado na imagem */
        .card-unidade {
            border: 1px solid #f1f5f9 !important;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            background: #fff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        /* Efeito Hover */
        .card-unidade:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
            border-color: #e2e8f0 !important;
        }

        /* Detalhe da borda lateral azul */
        .card-unidade::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            border-radius: 20px 0 0 20px;
            z-index: 10;
            background-color: #0d6efd;
        }

        /* Estilização interna */
        .icon-box {
            width: 45px;
            height: 45px;
            background: #f0f7ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #007bff;
        }

        .badge-ativo {
            background: #e8f5e9;
            color: #2e7d32;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 5px 15px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        .label-tipo {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>

<body class="bg-light">

    <?php include_once __DIR__ . '/includes/headerCliente.php'; ?>

    <main class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="fw-bold text-dark">Minhas Unidades</h4>
                <p class="text-muted">Imóveis vinculados ao seu CPF.</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if ($unidades): ?>
                <?php foreach ($unidades as $un): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-unidade shadow-sm p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-box">
                                    <i class="bi bi-house-door fs-5"></i>
                                </div>
                                <span class="badge-ativo shadow-sm">Ativo</span>
                            </div>

                            <h4 class="fw-bold mb-1">Unidade <?= htmlspecialchars($un['numero']) ?></h4>
                            <p class="text-muted mb-1 small"><?= htmlspecialchars($un['endereco'] ?? 'Quadra QNN 22 Conjunto J') ?></p>
                            <p class="text-dark small mb-4">Bloco: <strong><?= htmlspecialchars($un['bloco']) ?></strong></p>

                            <div class="label-tipo mb-4">
                                <span class="text-muted small">Tipo:</span>
                                <span class="text-primary fw-bold">Residencial</span>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="dashboard.php?id_unidade=<?= $un['id_unidade'] ?>" class="btn btn-outline-primary w-100 rounded-pill fw-bold btn-sm py-2">
                                        Ver Detalhes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Nenhuma unidade encontrada.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include_once __DIR__ . '/includes/footerCliente.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>