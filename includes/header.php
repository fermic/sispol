<?php
ob_start();
session_start();
include_once __DIR__ . '/../config/config.php';

include_once 'functions.php';

if (!isset($_SESSION['usuario_id'])) {
    // Salva a URL atual na sessão antes de redirecionar
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISPOL - Sistema de Procedimentos Policiais</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js"></script>

    <!-- CSS do Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- JavaScript do Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Confetti Browser -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
    
    <style>

        .navbar {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 0.5rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.5rem;
            color: #fff !important;
            letter-spacing: 0.5px;
        }
        
        .brand-icon {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px;
            border-radius: 10px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .brand-icon i {
            font-size: 1.2rem;
            color: #fff;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 6px;
            transition: all 0.3s ease;
            margin: 0 0.2rem;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff !important;
            transform: translateY(-1px);
        }
        
        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: #fff !important;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 0.5rem;
            min-width: 200px;
        }
        
        .dropdown-item {
            padding: 0.7rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            margin: 0.2rem 0;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            transform: translateX(3px);
        }
        
        .badge.bg-success {
            background: linear-gradient(135deg, #00b09b, #96c93d) !important;
            font-weight: 500;
            font-size: 0.7rem;
            padding: 0.3em 0.6em;
            border-radius: 12px;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            margin-left: 1rem;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
        }
        
        .user-avatar i {
            color: #fff;
            font-size: 1rem;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.4rem 1rem;
            border-radius: 6px;
            color: #fff !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: rgba(0, 0, 0, 0.1);
                padding: 1rem;
                border-radius: 10px;
                margin-top: 1rem;
            }
            
            .nav-link {
                padding: 0.7rem 1rem !important;
                margin: 0.2rem 0;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container" style="max-width: 95%; margin: 0 auto;">
        <a class="navbar-brand" href="<?= BASE_URL ?>/public/dashboard.php">
            <span class="brand-icon">
                <i class="bi bi-shield-fill-check"></i>
            </span>
            SISPOL
        </a>
        <button 
            class="navbar-toggler border-0" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#navbarNav" 
            aria-controls="navbarNav" 
            aria-expanded="false" 
            aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/procedimentos.php">
                        <i class="bi bi-folder-fill me-1"></i> Procedimentos
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/desaparecimentos.php">
                        <i class="bi bi-person-x-fill me-1"></i> Desaparecimentos
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="relatorioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-text-fill me-1"></i> Relatórios
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="relatorioDropdown">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/cautelares_repport.php">
                                <i class="bi bi-file-text me-2"></i> Relatório Padrão
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/planilha.php">
                                <i class="bi bi-table me-2"></i> Planilha
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/oficios.php">
                                <i class="bi bi-envelope-paper me-2"></i> Ofícios
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/listar_cumprimentos.php">
                                <i class="bi bi-check2-square me-2"></i> Listar Cumprimentos de Cautelares
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/cotas.php">
                                <i class="bi bi-chat-square-text me-2"></i> Listar Cotas Ministeriais
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/recompensas/index.php" target="_blank">
                                <i class="bi bi-award me-2"></i> Escala de Recompensas
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/senhas.php">
                                <i class="bi bi-key me-2"></i> Senhas
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/armas.php">
                                <i class="bi bi-shield-fill-exclamation me-2"></i> Planilha de Armas <span class="badge bg-success ms-1">Novo</span>
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/objetos.php">
                                <i class="bi bi-box-seam me-2"></i> Planilha de Objetos <span class="badge bg-success ms-1">Novo</span>
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/veiculos/listar_veiculos.php">
                                <i class="bi bi-car-front me-2"></i> Planilha de Viaturas <span class="badge bg-success ms-1">Novo</span>
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/policiais/listar_policiais.php">
                                <i class="bi bi-person-badge me-2"></i> Planilha de Policiais <span class="badge bg-success ms-1">Novo</span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/public/pesquisa_avancada.php">
                        <i class="bi bi-search me-1"></i> Pesquisa Avançada
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownGraficos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bar-chart-fill me-1"></i> Estatísticas
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownGraficos">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/grafico_remessa.php">
                                <i class="bi bi-graph-up me-2"></i> Instauração x Remessa
                            </a>
                        </li>
                        
                         <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/grafico_escrivao.php">
                                <i class="bi bi-person-lines-fill me-2"></i> Carga por Escrivão
                            </a>
                        </li>
                        
                         <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/idade_media_procedimentos.php">
                                <i class="bi bi-clock-history me-2"></i> Duração média dos procedimentos
                            </a>
                        </li>
                        
                         <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/public/grafico_omp.php">
                                <i class="bi bi-clipboard-data me-2"></i> Indicadores OMP
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            
            <?php if (isset($_SESSION['usuario'])): ?>
                <div class="user-menu">
                    <span class="user-avatar">
                        <i class="bi bi-person-fill"></i>
                    </span>
                    <a class="nav-link logout-btn" href="<?= BASE_URL ?>/public/logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i> Sair
                    </a>
                </div>
            <?php else: ?>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/public/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Adiciona classe 'active' ao link da página atual
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const links = document.querySelectorAll('.nav-link');
        
        links.forEach(link => {
            if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
                link.classList.add('active');
                
                // Se for um item de dropdown, marca também o dropdown como ativo
                const parentLi = link.closest('.dropdown');
                if (parentLi) {
                    parentLi.querySelector('.dropdown-toggle').classList.add('active');
                }
            }
        });
    });
</script>