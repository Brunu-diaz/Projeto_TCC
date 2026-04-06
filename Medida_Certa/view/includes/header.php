<?php
// Pega apenas o nome do arquivo (ex: unidades.php)
$pagina_atual = basename($_SERVER['PHP_SELF']); 
?>

<header class="header-gradient pb-5">
    <nav class="navbar navbar-expand-lg navbar-dark pt-3">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="admin.php">
                <i class="bi bi-droplet-fill me-2 text-primary"></i>
                <span class="fs-5">MedidaCerta</span>
                <span class="badge bg-primary rounded-pill text-white ms-2 text-uppercase fw-bold admin-badge-style">
                    ADMIN
                </span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navPrincipal">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-1">
                    
                    <li class="nav-item">
    <a class="nav-link custom-nav-link d-flex align-items-center px-3 <?php echo ($pagina_atual == 'admin.php') ? 'active' : ''; ?>" href="admin.php">
        <i class="bi bi-speedometer2 me-2"></i> Visão Geral
    </a>
</li>

                    <li class="nav-item">
                        <a class="nav-link custom-nav-link d-flex align-items-center px-3 <?php echo ($pagina_atual == 'unidades.php') ? 'active' : ''; ?>" href="unidades.php">
                            <i class="bi bi-building me-2"></i> Unidades
                        </a>
                    </li>

                    <li class="nav-item dropdown list-unstyled ms-lg-2">
                        <a class="nav-link-capsule position-relative d-inline-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-bell fs-5 text-white"></i>
                            <span class="custom-notification-badge">2</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-0 mt-2" style="width: 320px; overflow: hidden;">
                            <li class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                                <span class="fw-bold small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Notificações</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size: 0.6rem;">2 NOVAS</span>
                            </li>

                            <div class="notification-scroll">
                                <li>
                                    <a class="dropdown-item p-3 border-bottom d-flex align-items-start gap-3" href="#">
                                        <div class="icon-circle bg-danger-subtle text-danger">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 small fw-bold text-danger">Vazamento Detectado!</p>
                                            <p class="mb-1 text-muted small" style="font-size: 0.8rem;">Unidade 402 - Bloco B apresenta consumo atípico.</p>
                                            <small class="text-uppercase fw-bold opacity-50" style="font-size: 0.6rem;">há 5 min</small>
                                        </div>
                                    </a>
                                </li>
                            </div>

                            <li>
                                <a class="dropdown-item small text-center p-2 fw-bold text-primary bg-light" href="#">Ver todas</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link-profile d-flex align-items-center text-decoration-none text-white dropdown-toggle px-2" href="#" data-bs-toggle="dropdown">
                            <div class="profile-circle me-2">BD</div>
                            <span class="small d-none d-md-inline">Bruno Dias</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li><a class="dropdown-item" href="perfil.php">Meu Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="login.php">Sair</a></li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
</header>