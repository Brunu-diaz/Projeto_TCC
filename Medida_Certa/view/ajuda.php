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
    <title>MedidaCerta - Central de Ajuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/perfil.css">
</head>
<body class="bg-light">

    <?php include_once __DIR__ . '/includes/headerCliente.php'; ?>

    <main class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Como podemos ajudar?</h2>
            <p class="text-muted">Encontre respostas rápidas ou entre em contato com nosso suporte.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center" style="border-radius: 20px;">
                    <div class="display-5 text-primary mb-3">
                        <i class="bi bi-droplet-half"></i>
                    </div>
                    <h5 class="fw-bold">Dicas de Consumo</h5>
                    <p class="small text-muted">Aprenda a identificar vazamentos e reduzir sua conta mensal em até 30%.</p>
                    <button class="btn btn-outline-primary btn-sm rounded-pill w-100 mt-auto">Ver Dicas</button>
                </div>
            </div>

            <div class="col-md-5 col-lg-4">
                <div class="card h-100 border-0 shadow-sm p-4 text-center" style="border-radius: 20px;">
                    <div class="display-5 text-success mb-3">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h5 class="fw-bold">Fale Conosco</h5>
                    <p class="small text-muted">Dúvidas sobre sua medição ou problemas técnicos? Nossa equipe responde em até 24h.</p>
                    <a href="mailto:suporte@useecuide.com.br" class="btn btn-success btn-sm rounded-pill w-100 mt-auto">Enviar E-mail</a>
                </div>
            </div>
        </div>

        <div class="row mt-5 justify-content-center">
            <div class="col-lg-8">
                <h5 class="fw-bold mb-4">Perguntas Frequentes</h5>
                <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Como é feita a medição da água?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                A medição é realizada por sensores ultrassônicos instalados no hidrômetro da sua unidade, enviando os dados em tempo real para o nosso servidor via rede sem fio.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Meu gráfico de consumo parou de atualizar, o que fazer?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Verifique sua conexão com a internet. Se o problema persistir por mais de 2h, pode ser uma manutenção no gateway do condomínio.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/includes/footerCliente.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>