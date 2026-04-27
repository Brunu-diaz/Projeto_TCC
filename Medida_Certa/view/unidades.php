<?php
// 1. A trava de segurança DEVE ser a primeira coisa
require_once __DIR__ . '/../controller/TravaAdmin.php';
// Segurança: Apenas ADM acessa
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../model/dao/Conexao.php';

try {
    $pdo = Conexao::getConexao(); 

    // Buscamos todas as unidades cadastradas
    $sql = "SELECT * FROM unidade ORDER BY numero ASC";
    $stmt = $pdo->query($sql);
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar unidades: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Gerenciar Unidades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/unificado.css">
    <style>
        /* --- ESTILIZAÇÃO DO FILTRO MINIMALISTA --- */
        .search-wrapper {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 15px;
            border: 1px solid #edf2f7;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
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

        /* --- ESTILIZAÇÃO PREMIUM DOS CARDS --- */
        .unit-card { 
            border: 1px solid #f1f5f9 !important; 
            border-radius: 20px; 
            position: relative;
            overflow: hidden; 
            background: #fff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .unit-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            border-radius: 20px 0 0 20px;
            z-index: 10;
        }

        .card-residencial::before { background-color: #0d6efd; } 
        .card-comercial::before { background-color: #fd7e14; }

        .text-comercial { color: #fd7e14 !important; }
        .bg-comercial-subtle { background-color: #fffaf0 !important; }
        .icon-residencial { background-color: #ebf8ff !important; color: #0d6efd !important; }

        .unit-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.06) !important;
            border-color: #e2e8f0 !important;
        }

        .icon-square {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Botão Personalizado Laranja para Comercial */
        .btn-outline-orange {
            color: #fd7e14;
            border-color: #fd7e14;
            background-color: transparent;
            transition: all 0.2s ease;
        }

        .btn-outline-orange:hover {
            background-color: #fd7e14;
            border-color: #fd7e14;
            color: #fff;
        }
    </style>
</head>

<body>

    <?php include '../view/includes/header.php'; ?>

    <div class="container page-header-box mb-4">
        <div class="bg-white py-3 px-4 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 16px; border: 1px solid #f1f5f9;">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Gerenciar Unidades</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="admin.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item active">Unidades</li>
                    </ol>
                </nav>
            </div>

            <a href="cadastrarUnidades.php" class="btn btn-primary rounded-3 px-4 shadow-sm d-flex align-items-center fw-bold" style="height: 42px; font-size: 0.9rem;">
                <i class="bi bi-plus-lg me-2"></i>Nova Unidade
            </a>
        </div>
    </div>

    <main class="main-container container mb-5">
        
        <div class="search-wrapper">
            <div class="search-input-group">
                <i class="bi bi-search"></i>
                <input type="text" id="inputPesquisa" class="form-control" 
                       placeholder="Pesquisar por número, bloco ou tipo...">
            </div>
        </div>

        <div class="row g-4" id="containerUnidades">
            <?php if (count($unidades) > 0): ?>
                <?php foreach ($unidades as $u): 
                    $complemento = $u['complemento'] ?? '';
                    $isComercial = (mb_stripos($complemento, 'Comercial') !== false);
                    
                    $classeTipo = $isComercial ? 'card-comercial' : 'card-residencial';
                    $iconStyle = $isComercial ? 'bg-comercial-subtle text-comercial' : 'icon-residencial';
                    $iconClass = $isComercial ? 'bi-briefcase' : 'bi-house';
                ?>
                    <div class="col-md-6 col-xl-4 unidade-card-item">
                        <div class="card unit-card h-100 shadow-sm <?= $classeTipo ?>">
                            <div class="card-body p-4 text-dark">
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="icon-square <?= $iconStyle ?> rounded-3">
                                        <i class="bi <?= $iconClass ?> fs-5"></i>
                                    </div>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 d-flex align-items-center justify-content-center" style="font-size: 0.7rem; font-weight: 700; min-width: 80px; height: 32px;">
                                        <?= ($u['ativo'] ?? 1) ? 'ATIVO' : 'INATIVO' ?>
                                    </span>
                                </div>

                                <h5 class="fw-bold mb-1 card-title" style="letter-spacing: -0.5px;">Unidade <?= htmlspecialchars($u['numero']) ?></h5>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($u['endereco']) ?></p>
                                <p class="text-muted small">Bloco: <span class="fw-bold text-dark"><?= htmlspecialchars($u['bloco']) ?></span></p>

                                <div class="p-3 rounded-3 my-3" style="background-color: #fcfcfc; border: 1px solid #f8fafc;">
                                    <div class="d-flex justify-content-between align-items-center mb-0 small">
                                        <span class="text-muted">Tipo:</span>
                                        <span class="fw-bold <?= $isComercial ? 'text-comercial' : 'text-primary' ?>">
                                            <?= htmlspecialchars($u['complemento'] ?: 'Residencial') ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <a href="detalhes_unidade_admin.php?id=<?= $u['id_unidade'] ?>" 
                                           class="btn <?= $isComercial ? 'btn-outline-orange' : 'btn-outline-primary' ?> btn-sm w-100 rounded-pill py-2 fw-bold" 
                                           style="font-size: 0.8rem;">Ver Detalhes</a>
                                    </div>
                                    <div class="col-6">
                                        <a href="editar_unidade.php?id=<?= $u['id_unidade'] ?>" class="btn btn-light btn-sm w-100 rounded-pill py-2 border-0 fw-bold" style="font-size: 0.8rem; background-color: #f1f5f9;">Editar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div id="avisoVazio" class="col-12 text-center py-5 d-none">
                    <div class="bg-white p-5 rounded-4 shadow-sm border">
                        <i class="bi bi-search fs-1 text-muted"></i>
                        <p class="text-muted mt-3 mb-0">Nenhuma unidade encontrada para sua busca.</p>
                        <button onclick="clearSearch()" class="btn btn-link btn-sm mt-2 text-primary">Limpar pesquisa</button>
                    </div>
                </div>

            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-building-exclamation fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Nenhuma unidade cadastrada no sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('inputPesquisa');
            const unitCards = document.querySelectorAll('.unidade-card-item');
            const emptyNotice = document.getElementById('avisoVazio');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                let hasResults = false;

                unitCards.forEach(card => {
                    const content = card.textContent.toLowerCase();
                    if (content.includes(query)) {
                        card.classList.remove('d-none');
                        hasResults = true;
                    } else {
                        card.classList.add('d-none');
                    }
                });

                if (!hasResults && query !== "") {
                    emptyNotice.classList.remove('d-none');
                } else {
                    emptyNotice.classList.add('d-none');
                }
            });
        });

        function clearSearch() {
            const input = document.getElementById('inputPesquisa');
            input.value = '';
            input.dispatchEvent(new Event('input'));
            input.focus();
        }
    </script>
</body>
</html>