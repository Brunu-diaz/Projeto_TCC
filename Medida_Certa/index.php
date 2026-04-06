<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Home</title>
    <!-- CSS do Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Seus ícones (Bootstrap Icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Favicon (Ícone na aba do navegador) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <!-- Hero Section -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container">
                <!-- Logo com o ícone de gota -->
                <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
                    <i class="bi bi-droplet-fill me-2" aria-hidden="true"></i> <!-- Ícone de gota -->
                        MedidaCerta
                </a>
                    <!-- Botão para celular (hamburger) -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <!-- Links do Menu -->
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto text-white">
                        <li class="nav-item"><a class="nav-link active fw-bold" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="view/quemsomos.php">Quem Somos</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#contato">Contato</a></li>
                        <li class="nav-item"><a class="nav-link" href="view/dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="view/admin.php">Administrador</a></li>
                        <li class="nav-item"><a class="nav-link" href="view/unidades.php">Unidades</a></li>
                        <li class="nav-item"><a class="nav-link" href="view/login.php">Login <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i></a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div id="carouselMedidaCerta" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">

    <!-- Slide 1: Boas Vindas -->
    <div class="carousel-item active" style="height: 550px;">
        <img src="assets/img/Design sem nome (37).png" class="d-block w-100 h-100" alt="MedidaCerta" style="object-fit: cover;" loading="lazy">
        <div class="carousel-caption d-flex flex-column h-100 align-items-center justify-content-center">
            <i class="bi bi-droplet-fill display-1 mb-3 text-white" aria-hidden="true"></i>
            <h1 class="display-4 fw-bold text-white">Bem-vindo ao MedidaCerta</h1>
            <p class="lead text-white">A tecnologia que transforma a gestão de água no seu condomínio</p>
            <a href="view/login.php" class="btn btn-primary btn-lg mt-3 fw-bold">Acessar Painel</a>
        </div>
    </div>

    <!-- Slide 2: Economia -->
    <div class="carousel-item" style="height: 550px;">
    <img src="assets/img/Design sem nome (38).png" class="d-block w-100 h-100" alt="MedidaCerta" style="object-fit: cover;" loading="lazy">
    <div class="carousel-caption d-flex flex-column h-100 align-items-start justify-content-center text-start">
        <div class="p-4 rounded">
            <h1 class="display-4 fw-bold text-white">Economia Real</h1>
            <p class="lead text-white">Reduza o desperdício em até 30% com a individualização precisa</p>
        </div>
    </div>
    </div>

    <!-- Slide 3: Transparência -->
    <div class="carousel-item" style="height: 550px;">
        <img src="assets/img/Design sem nome (39).png" class="d-block w-100 h-100" alt="MedidaCerta" style="object-fit: cover;" loading="lazy">
    <div class="carousel-caption d-flex flex-column h-100 align-items-center justify-content-center">
            <i class="bi bi-file-earmark-check display-1 mb-3 text-white" aria-hidden="true"></i>
            <h1 class="display-4 fw-bold text-white">Transparência Total</h1>
            <p class="lead text-white">Faturas detalhadas em PDF disponíveis a qualquer momento para o morador</p>
        </div>
    </div>
    </div>

    <!-- BOTÕES DE CONTROLE (Certifique-se que o data-bs-target é o ID da div principal) -->
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselMedidaCerta" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselMedidaCerta" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Próximo</span>
    </button>
</div>
    <!-- Seção de Funcionalidades -->
    <section id="funcionalidades" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Por que o MedidaCerta?</h2>
                <p class="lead text-muted">Tecnologia a serviço da economia e do consumo consciente.</p>
            </div>
            <div class="row g-4 text-center">
                <!-- Card 1 -->
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="medida-card">
                        <i class="bi bi-graph-up-arrow text-primary fs-1 mb-3" aria-hidden="true"></i>
                        <h4>Gráficos de Consumo</h4>
                        <p class="text-muted">Acompanhe seu histórico mensal e visualize tendências de gasto de forma clara.</p>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="medida-card">
                        <i class="bi bi-file-earmark-pdf text-primary fs-1 mb-3" aria-hidden="true"></i>
                        <h4>Faturas em PDF</h4>
                        <p class="text-muted">Geração automática de demonstrativos detalhados para cada unidade.</p>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="medida-card">
                        <i class="bi bi-shield-check text-primary fs-1 mb-3" aria-hidden="true"></i>
                        <h4>Precisão Total</h4>
                        <p class="text-muted">Cálculos automáticos baseados em tarifas reais, eliminando erros humanos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Seção "Como Funciona" -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="fw-bold mb-4">Gestão simplificada na palma da mão</h2>
                <ul class="list-unstyled">
                    <li class="mb-3 d-flex align-items-start">
                        <i class="bi bi-1-circle-fill text-primary me-3 fs-4" aria-hidden="true"></i>
                        <div><strong>Medição:</strong> O leiturista realiza a leitura e o administrador registra a leitura no sistema.</div>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                        <i class="bi bi-2-circle-fill text-primary me-3 fs-4" aria-hidden="true"></i>
                        <div><strong>Processamento:</strong> O MedidaCerta calcula o consumo e gera o valor.</div>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                        <i class="bi bi-3-circle-fill text-primary me-3 fs-4" aria-hidden="true"></i>
                        <div><strong>Disponibilização:</strong> O morador recebe o alerta e acessa o PDF.</div>
                    </li>
                </ul>
            </div>
            <div class="col-lg-6 text-center" data-aos="fade-left">
                <!-- Aqui você pode colocar uma imagem do seu sistema no futuro -->
                    <img src="assets/img/Design sem nome (39).png" class="d-block w-100 h-100 img-fluid rounded-4 shadow" alt="MedidaCerta" style="object-fit: cover;" loading="lazy">
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white text-center">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4" data-aos="zoom-in">
                <div class="stat-icon-box">
                    <i class="bi bi-piggy-bank-fill" aria-hidden="true"></i> </div>
                <h2 class="fw-bold text-primary">30%</h2>
                <p class="text-muted text-uppercase fw-bold small">Economia Média</p>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-icon-box">
                    <i class="bi bi-cloud-check-fill" aria-hidden="true"></i> </div>
                <h2 class="fw-bold text-primary">100%</h2>
                <p class="text-muted text-uppercase fw-bold small">Digital e Sem Papel</p>
            </div>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="400">
                <div class="stat-icon-box">
                    <i class="bi bi-speedometer" aria-hidden="true"></i>
                </div>
                <h2 class="fw-bold text-primary">+1500</h2>
                <p class="text-muted text-uppercase fw-bold small">Leituras Realizadas</p>
            </div>
        </div>
    </div>
</section>

<section id="implementacao" class="py-5 bg-light ">
    <div class="container">
        <div class="row mb-5 text-center">
            <div class="col-lg-8 mx-auto">
                <h2 class="fw-bold">Sua Gestão de Água Levada a Sério</h2>
                <p class="lead text-muted">Elimine erros humanos e garanta justiça na cobrança com uma transição suave e assistida.</p>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4" data-aos="fade-up">
                <div class="medida-card">
                    <div class="benefit-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></div>
                    <h5 class="fw-bold">Fim da Planilha de Papel</h5>
                    <p class="small text-muted">Os dados do leiturista vão direto para o sistema, eliminando riscos de perda de informação ou rasuras.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="medida-card">
                    <div class="benefit-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></div>
                    <h5 class="fw-bold">Cobrança Justa</h5>
                    <p class="small text-muted">Cada morador visualiza exatamente o que consumiu através de faturas detalhadas em PDF.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="medida-card">
                    <div class="benefit-icon"><i class="bi bi-journal-check" aria-hidden="true"></i></div>
                    <h5 class="fw-bold">Histórico Inviolável</h5>
                    <p class="small text-muted">Dados armazenados de forma segura para consultas futuras, auditorias e análise de consumo.</p>
                </div>
            </div>
        </div>

        <div class="implementation-section p-4 p-md-5">
            <h3 class="fw-bold text-center">Como implementamos no seu Condomínio</h3><br>
            <div class="row g-4">
                
                <div class="col-md-6 col-lg-3" data-aos="zoom-in">
                    <div class="step-card">
                        <div class="fw-bold mb-2">PASSO 01</div>
                        <h6>Configuração Assistida</h6>
                        <p class="small text-muted">Cadastramos suas unidades, hidrômetros e tarifas para que o sistema reflita a realidade do seu condomínio.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="100">
                    <div class="step-card">
                        <div class="fw-bold mb-2">PASSO 02</div>
                        <h6>Treinamento Prático</h6>
                        <p class="small text-muted">Capacitamos o administrador para a gestão dos dados.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="200">
                    <div class="step-card">
                        <div class="fw-bold mb-2">PASSO 03</div>
                        <h6>Validação de Leitura</h6>
                        <p class="small text-muted">Acompanhamos o primeiro ciclo de medição para garantir que o processamento dos cálculos esteja 100% correto.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="300">
                    <div class="step-card">
                        <div class="fw-bold mb-2">PASSO 04</div>
                        <h6>Entrega de Resultados</h6>
                        <p class="small text-muted">As faturas são geradas automaticamente e o condomínio começa a colher os frutos da transparência.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center fw-bold">Dúvidas Frequentes</h2><br><br>
        <div class="accordion w-75 mx-auto" id="faqMedidaCerta">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#item1">
                        Como acesso minha fatura?
                    </button>
                </h2>
                <div id="item1" class="accordion-collapse collapse" data-bs-parent="#faqMedidaCerta">
                    <div class="accordion-body">
                        Basta realizar o login com seu e-mail e senha e clicar no botão "Baixar PDF" no seu painel.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#item2">
                        O sistema detecta vazamentos?
                    </button>
                </h2>
                <div id="item2" class="accordion-collapse collapse" data-bs-parent="#faqMedidaCerta">
                    <div class="accordion-body">
                        Sim! Nosso algoritmo identifica aumentos bruscos de consumo e emite um alerta preventivo.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#item3">
                        O sistema funciona sem internet?
                    </button>
                </h2>
                <div id="item3" class="accordion-collapse collapse  " data-bs-parent="#faqMedidaCerta">
                    <div class="accordion-body">
                        Para garantir a sincronização dos dados e a segurança do seu histórico, é necessária uma conexão ativa. No entanto, o sistema é otimizado para funcionar bem mesmo em conexões de baixa velocidade.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#item4">
                        O sistema MedidaCerta precisa de instalação no computador?
                    </button>
                </h2>
                <div id="item4" class="accordion-collapse collapse" data-bs-parent="#faqMedidaCerta">
                    <div class="accordion-body">
                        Não. O sistema é 100% baseado na nuvem (SaaS). Você pode acessar de qualquer navegador (Chrome, Safari, Edge) no seu computador, tablet ou smartphone, sem ocupar espaço no seu dispositivo.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção de Contato -->
<section id="contato" class="py-5 bg-white">
    <div class="container">
        <div class="row g-5">
            <!-- Coluna 1: Informações de Contato -->
            <div class="col-lg-5">
                <h2 class="fw-bold text-primary mb-4">Fale Conosco</h2>
                <p class="text-muted mb-4">
                    Tem dúvidas sobre como implementar o sistema no seu condomínio? 
                    Nossa equipe está pronta para ajudar.
                </p>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                        <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Localização</h6>
                        <small class="text-muted">Brasília, DF - Brasil</small>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                        <i class="bi bi-whatsapp" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">WhatsApp</h6>
                        <small class="text-muted">(11) 99999-9999</small>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                        <i class="bi bi-envelope-at-fill" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">E-mail</h6>
                        <small class="text-muted">contato@medidacerta.com.br</small>
                    </div>
                </div>
            </div>

            <!-- Coluna 2: Formulário -->
            <div class="col-lg-7">
                <div class="border-0 p-4">
                    <form method="post" autocomplete="off" action="https://formspree.io/f/xjgazdoj">
                        <input type="hidden" name="_next" value="http://127.0.0.1:5500/index.html?#/obrigado.html">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="inome">Seu Nome</label>
                                <input type="text" name="nome" class="form-control bg-light border-0" id="inome" placeholder="Nome completo" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="iemail">E-mail</label>
                                <input type="email" name="email" class="form-control bg-light border-0" id="iemail" placeholder="email@exemplo.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="icond">Nome do Condomínio</label>
                                <input type="text" name="condominio" class="form-control bg-light border-0" placeholder="Ex: Edifício Gota de Ouro" id="icond" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="imens">Mensagem</label>
                                <textarea class="form-control bg-light border-0" name="mensagem" rows="4" placeholder="Como podemos ajudar?" id="imens" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                    Enviar Solicitação <i class="bi bi-send ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer Simples -->
<footer class="py-4 text-center text-white">
    <div class="container">
        <p class="mb-0">&copy; 2026 MedidaCerta - Sistema de Gestão de Água Condominial. Todos os direitos reservados.</p>
        <p class="mb-0">CNPJ: 00.000.000/0001-00</p>
        <p class="mb-0">Brasília-DF</p>
    </div>
</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>