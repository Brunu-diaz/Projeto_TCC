<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../model/dao/Conexao.php';
// Mantenha suas travas de segurança aqui
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
    <link rel="stylesheet" href="../assets/css/unificado.css">
    
    <style>
        :root {
            --primary-color: #0061ff;
            --surface-color: #ffffff;
            --text-main: #1a1d23;
            --text-muted: #64748b;
            --bg-body: #fbfcfe;
        }

        body { 
            background-color: var(--bg-body); 
            color: var(--text-main);
        }

        .support-header {
            padding: 60px 0 40px;
        }

        .card-contact {
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 20px;
            background: var(--surface-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .card-contact:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.04);
            border-color: var(--primary-color);
        }

        .icon-circle {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 20px;
        }

        .form-container {
            background: var(--surface-color);
            border-radius: 24px;
            border: 1px solid rgba(0,0,0,0.04);
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }

        .form-control, .form-select {
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 0.95rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(0,97,255,0.1);
            border-color: var(--primary-color);
            background-color: #fff;
        }

        .btn-submit {
            background: var(--primary-color);
            border: none;
            padding: 14px 32px;
            border-radius: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #0052d9;
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(0,97,255,0.2);
        }

        /* Estilo para as etiquetas de prioridade */
        .badge-priority {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 12px;
            border-radius: 20px;
        }
    </style>
</head>
<body>

    <?php include '../view/includes/header.php'; ?>

    <section class="support-header">
        <div class="container text-center">
            <h1 class="display-5 fw-bold mb-3">Como podemos ajudar hoje?</h1>
            <p class="text-muted mx-auto" style="max-width: 500px;">Nossa equipe de especialistas está pronta para garantir que o <strong>MedidaCerta</strong> funcione perfeitamente para você.</p>
        </div>
    </section>

    <main class="container mb-5">
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <div class="card-contact h-100 p-4">
                    <div class="icon-circle" style="background: #e7f5ed; color: #0ca654;">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h5 class="fw-bold">WhatsApp Business</h5>
                    <p class="text-muted small">Ideal para dúvidas urgentes de faturamento ou acesso rápido.</p>
                    <a href="#" class="text-decoration-none fw-bold small text-success">Conversar agora <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-contact h-100 p-4">
                    <div class="icon-circle" style="background: #eef2ff; color: #4338ca;">
                        <i class="bi bi-envelope-at"></i>
                    </div>
                    <h5 class="fw-bold">E-mail Oficial</h5>
                    <p class="text-muted small">Envie capturas de tela ou logs de erros para análise profunda.</p>
                    <a href="mailto:suporte@medidacerta.com" class="text-decoration-none fw-bold small text-primary">suporte@medidacerta.com</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-contact h-100 p-4">
                    <div class="icon-circle" style="background: #f1f5f9; color: #334155;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <h5 class="fw-bold">Base de Conhecimento</h5>
                    <p class="text-muted small">Tutoriais em vídeo e guias passo a passo para administradores.</p>
                    <a href="#" class="text-decoration-none fw-bold small text-dark">Acessar manuais <i class="bi bi-box-arrow-up-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-container">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; color: white;">
                            <i class="bi bi-send-fill"></i>
                        </div>
                        <h4 class="fw-bold mb-0">Nova Solicitação</h4>
                    </div>

                    <form action="../controller/EnviarSuporte.php" method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">QUAL O ASSUNTO?</label>
                                <select class="form-select" name="assunto" required>
                                    <option value="" disabled selected>Selecione uma categoria...</option>
                                    <option value="erro">Relatar erro ou bug</option>
                                    <option value="sugestao">Sugestão de melhoria</option>
                                    <option value="financeiro">Questões financeiras</option>
                                    <option value="outro">Outros assuntos</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">NÍVEL DE PRIORIDADE</label>
                                <select class="form-select" name="prioridade">
                                    <option value="rotina">Rotina (Até 48h)</option>
                                    <option value="importante">Importante (Até 24h)</option>
                                    <option value="critico">Crítico (Urgente)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">MENSAGEM</label>
                                <textarea class="form-control" name="mensagem" rows="5" placeholder="Descreva o que você precisa com o máximo de detalhes possível..." required></textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-between align-items-center mt-5">
                                <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> Você receberá a cópia deste chamado no seu e-mail cadastrado.</p>
                                <button type="submit" class="btn btn-primary btn-submit shadow-sm text-white">
                                    Enviar Solicitação
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include '../view/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>