<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedidaCerta - Quem Somos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%230d6efd' d='M8 16a6 6 0 0 0 6-6c0-1.65-1.35-4-6-10-4.65 6-6 8.35-6 10a6 6 0 0 0 6 6z'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/quemsomos.css">
    <style>
        /* Container para alinhar os dois membros */
        .equipe-wrapper {
            display: flex;
            flex-wrap: wrap;
            /* Quebra para duas linhas em mobile */
            gap: 1.5rem;
            /* Espaçamento entre os membros */
        }

        /* Wrapper para um membro individual (Ícone + Texto) */
        .membro-wrapper {
            display: flex;
            align-items: start;
            /* Alinha o ícone com o topo do nome */
            gap: 1rem;
            /* Espaçamento entre o ícone e o texto */
            flex: 1;
            /* Faz os dois membros terem larguras iguais */
            min-width: 250px;
            /* Largura mínima para mobile */
        }

        /* Estilo para o ícone circular */
        .membro-icon-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(13, 110, 253, 0.08);
            /* bg-primary-subtle */
            color: #0d6efd;
            /* text-primary */
            flex-shrink: 0;
            /* Impede o ícone de encolher */
        }

        /* Container principal da composição das duas fotos */
        .team-double-wrapper {
            position: relative;
            width: 100%;
            max-width: 360px;
            height: 340px;
            margin: 0 auto;
        }

        /* Base comum para as caixas das fotos */
        .creator-box {
            position: absolute;
            width: 210px;
            height: 210px;
            transition: transform 0.3s ease, z-index 0.2s;
        }

        .creator-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Garante que o rosto não fique esticado */
        }

        /* Posicionamento da Foto da Jeovanice (Superior Esquerda) */
        .photo-jeovanice {
            top: -10px;
            left: -15px;
            z-index: 5;
        }

        /* Posicionamento da Foto do Bruno (Inferior Direita - Sobreposto) */
        .photo-bruno {
            bottom: 20px;
            right: 25px;
            z-index: 10;
        }

        /* Efeito de destaque ao passar o mouse */
        .creator-box:hover {
            transform: scale(1.05);
            z-index: 12;
            /* Traz a foto focada para a frente */
        }

        /* Ajustes finos para Mobile */
        @media (max-width: 576px) {
            .team-double-wrapper {
                max-width: 290px;
                height: 270px;
            }

            .creator-box {
                width: 160px;
                height: 160px;
            }
        }
    </style>
</head>

<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="../index.php">
                    <i class="bi bi-droplet-fill me-2" aria-hidden="true"></i>
                    MedidaCerta
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link active fw-bold" href="quemsomos.php">Quem Somos</a></li>
                        <li class="nav-item"><a class="nav-link" href="../index.php#contato">Contato</a></li>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login <i class="bi bi-box-arrow-in-right"></i></a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-container bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card about-card p-4 p-md-5 bg-white">
                        <div class="card-body">
                            <div class="text-center mb-5">
                                <i class="bi bi-droplet-fill logo-login" aria-hidden="true"></i>
                                <h2 class="fw-bold text-dark mt-2 section-title">Sobre o MedidaCerta</h2>
                            </div>

                            <div class="row g-4 mb-5">
                                <div class="col-md-4">
                                    <div class="feature-box h-100 p-4 text-center">
                                        <div class="mb-3"><i class="bi bi-rocket-takeoff text-primary fs-1"></i></div>
                                        <h3 class="fw-bold h5 section-title">Missão</h3>
                                        <p class="small text-muted mt-3">Promover a consciência hídrica através da tecnologia, oferecendo transparência e justiça na medição de água.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-box h-100 p-4 text-center">
                                        <div class="mb-3"><i class="bi bi-eye text-primary fs-1"></i></div>
                                        <h3 class="fw-bold h5 section-title">Visão</h3>
                                        <p class="small text-muted mt-3">Ser a plataforma de referência em gestão hídrica inteligente no DF, transformando o consumo em dados acessíveis.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-box h-100 p-4 text-center">
                                        <div class="mb-3"><i class="bi bi-gem text-primary fs-1"></i></div>
                                        <h3 class="fw-bold h5 section-title">Valores</h3>
                                        <ul class="list-unstyled small text-muted mt-3 text-start d-inline-block">
                                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i>Inovação</li>
                                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i>Transparência</li>
                                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i>Sustentabilidade</li>
                                            <li><i class="bi bi-check2 text-primary me-2"></i>Justiça Social</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 mb-5">
                                <div class="col-md-4">
                                    <div class="feature-box h-100 p-4 text-center">
                                        <div class="mb-3">
                                            <i class="bi bi-shield-check text-primary fs-1"></i>
                                        </div>
                                        <h3 class="fw-bold h5 section-title">Confiabilidade</h3>
                                        <p class="small text-muted mt-3 mb-0">
                                            Dados auditáveis e precisos processados com criptografia para garantir a segurança das informações do condomínio.
                                        </p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="feature-box h-100 p-4 text-center">
                                        <div class="mb-3">
                                            <i class="bi bi-tree text-success fs-1"></i>
                                        </div>
                                        <h3 class="fw-bold h5 section-title">Efeito Ecológico</h3>
                                        <p class="small text-muted mt-3 mb-0">
                                            Foco total na redução drástica do desperdício através da identificação precoce de vazamentos e consumo consciente.
                                        </p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="feature-box h-100 p-4 text-center">
                                        <div class="mb-3">
                                            <i class="bi bi-phone text-primary fs-1"></i>
                                        </div>
                                        <h3 class="fw-bold h5 section-title">Mobilidade</h3>
                                        <p class="small text-muted mt-3 mb-0">
                                            Controle total na palma da sua mão. Monitore seu consumo e receba alertas de qualquer lugar através do smartphone.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center g-5 pt-5 border-top">
                                <div class="col-md-5 order-md-2 text-center">
                                    <div class="team-double-wrapper">
                                        <div class="creator-box photo-jeovanice shadow-lg rounded-circle p-1 bg-white">
                                            <img src="../assets/img/WhatsApp Image 2026-05-09 at 05.10.11.jpeg" alt="Jeovanice Rodrigues" class="img-fluid rounded-circle">
                                        </div>

                                        <div class="creator-box photo-bruno shadow-lg rounded-circle p-1 bg-white">
                                            <img src="../assets/img/WhatsApp Image 2026-05-17 at 17.18.20.jpeg" alt="Bruno da Silva" class="img-fluid rounded-circle">
                                        </div>

                                        <div class="drop-decorator badge bg-primary rounded-circle shadow d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; position: absolute; bottom: 5px; right: 25px; z-index: 15;">
                                            <i class="bi bi-droplet-fill text-white" style="font-size: 1.1rem;"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-7 order-md-1 text-start">
                                    <h2 class="fw-bold text-dark section-title">As Mentes por trás do Projeto</h2>
                                    <p class="text-muted lead mt-3">
                                        O MedidaCerta nasceu da visão estratégica e técnica de <strong>Bruno da Silva Dias</strong> e <strong>Jeovanice Rodrigues Gomes</strong>, estudantes de TI apaixonados por transformar a gestão hídrica em Brasília.
                                    </p>

                                    <div class="equipe-wrapper mt-4 mb-4">
                                        <div class="membro-wrapper">
                                            <div class="membro-icon-circle">
                                                <i class="bi bi-code-slash"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Bruno da Silva Dias</h6>
                                                <small class="text-muted">Desenvolvedor e Idealizador</small>
                                            </div>
                                        </div>

                                        <div class="membro-wrapper">
                                            <div class="membro-icon-circle">
                                                <i class="bi bi-lightbulb"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Jeovanice Rodrigues Gomes</h6>
                                                <small class="text-muted">Desenvolvedora e Gestora de Projeto</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-4 text-center">
        <div class="container">
            <p class="mb-1 text-white"><strong>&copy; 2026 MedidaCerta</strong> Sistema de Gestão de Água Condominial. Todos os direitos reservados.</p>
            <p class="mb-0 text-white small">CNPJ: 00.000.000/0001-00</p>
            <p class="mb-0 text-white small">Brasília-DF</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>