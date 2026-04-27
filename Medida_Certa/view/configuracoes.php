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

// Busca dados básicos para o Header não quebrar
try {
    $conn = Conexao::getConexao();
    $sql = "SELECT nome, email, telefone, cpf_cnpj, foto FROM usuario WHERE id_usuario = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header("Location: ../controller/logout.php");
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

$nome = $usuario['nome'] ?? 'Cliente';
$email = $usuario['email'] ?? '';
$telefone = $usuario['telefone'] ?? '';
$cpf_cnpj = $usuario['cpf_cnpj'] ?? '';

// Lógica de iniciais para o header
$nome_limpo = trim($nome);
$partes_nome = explode(' ', $nome_limpo);
$primeira_inicial = mb_substr($partes_nome[0], 0, 1);
$ultima_inicial = (count($partes_nome) > 1) ? mb_substr(end($partes_nome), 0, 1) : "";
$iniciais = strtoupper($primeira_inicial . $ultima_inicial);

// Lógica da foto para o header (Reaproveitando a que funcionou)
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
$foto_perfil_url = $foto_url_header;
$tem_foto = $tem_foto_header;
$iniciais_header = $iniciais;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Configurações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/perfil.css"> </head>
<body>

    <?php
    // Como perfil.php já está na pasta 'view', o caminho para o include é este:
    include_once __DIR__ . '/includes/headerCliente.php';
    ?>

    <main class="container py-5">
        <div class="row mb-4">
            <div class="col-12 text-center text-md-start">
                <h4 class="fw-bold text-dark">Configurações</h4>
                <p class="text-muted">Personalize sua experiência no sistema MedidaCerta.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 p-3 p-md-4" style="border-radius: 15px;">
                    <div class="card-body">
                        
                        <section class="mb-5">
                            <h5 class="fw-bold mb-4"><i class="bi bi-bell me-2 text-primary"></i>Notificações</h5>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">Alertas de Consumo Elevado</h6>
                                        <p class="text-muted small mb-0">Receba um aviso quando seu consumo diário fugir da média.</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked style="width: 2.5em; height: 1.25em;">
                                    </div>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">Relatórios Mensais por E-mail</h6>
                                        <p class="text-muted small mb-0">Receba o fechamento da sua fatura diretamente no e-mail.</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked style="width: 2.5em; height: 1.25em;">
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="mb-5">
                            <h5 class="fw-bold mb-4"><i class="bi bi-shield-lock me-2 text-primary"></i>Privacidade e Dados</h5>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">Perfil Público no Condomínio</h6>
                                        <p class="text-muted small mb-0">Permitir que o síndico veja seu histórico completo.</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" style="width: 2.5em; height: 1.25em;">
                                    </div>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom-0">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">Exportar meus Dados (LGPD)</h6>
                                        <p class="text-muted small mb-0">Baixar histórico de consumo em formato PDF ou Excel.</p>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3">Exportar</button>
                                </div>
                            </div>
                        </section>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="dashboard.php" class="btn btn-light rounded-pill px-4">Voltar</a>
                            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Salvar Preferências</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../view/includes/footerCliente.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>