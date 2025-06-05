<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISPOL - Dashboard de Crimes</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom styles -->
    <style>
        :root {
            --primary-color: #1a3c8a;
            --secondary-color: #e9ecef;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --dark-color: #212529;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --gray-color: #6c757d;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .page-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 25px;
            border: none;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
        }
        
        .stat-card .card-body {
            padding: 25px;
        }
        
        .stat-card .card-header {
            border-bottom: none;
            padding: 15px 25px;
        }
        
        .stat-card .card-title {
            margin-bottom: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .stat-card .display-4 {
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .stat-card-icon {
            font-size: 3rem;
            opacity: 0.3;
            position: absolute;
            right: 15px;
            bottom: 15px;
        }
        
        .card-homicidio {
            background: linear-gradient(135deg, var(--danger-color), #e74c3c);
            color: white;
        }
        
        .card-tentativas {
            background: linear-gradient(135deg, var(--warning-color), #f39c12);
            color: #343a40;
        }
        
        .card-confrontos {
            background: linear-gradient(135deg, var(--dark-color), #2c3e50);
            color: white;
        }
        
        .card-suicidios {
            background: linear-gradient(135deg, var(--gray-color), #7f8c8d);
            color: white;
        }
        
        .data-table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
        }
        
        .data-table .table {
            margin-bottom: 0;
        }
        
        .data-table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
        }
        
        .data-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .filter-card {
            border-radius: 10px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border: none;
            background-color: white;
        }
        
        .filter-card .card-body {
            padding: 25px;
        }
        
        .filter-card .form-select, .filter-card .form-control {
            border-radius: 50px;
            padding: 10px 15px;
        }
        
        .filter-card .btn {
            border-radius: 50px;
            padding: 10px 25px;
        }
        
        .dashboard-title {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 30px;
            position: relative;
            display: inline-block;
        }
        
        .dashboard-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--info-color));
            border-radius: 2px;
        }
        
        .charts-container {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            border-radius: 10px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border: none;
            overflow: hidden;
        }
        
        .chart-card .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 15px 25px;
        }
        
        .chart-card .card-body {
            padding: 25px;
        }
        
        .btn-export {
            border-radius: 50px;
            padding: 8px 20px;
            margin-left: 10px;
            font-size: 0.9rem;
        }
        
        .btn-export i {
            margin-right: 5px;
        }
        
        .export-dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
            padding: 10px;
        }
        
        .export-dropdown-item {
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 0.9rem;
        }
        
        .export-dropdown-item:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .export-dropdown-item i {
            margin-right: 10px;
            font-size: 1rem;
        }
        
        @media (max-width: 768px) {
            .stat-card .display-4 {
                font-size: 2rem;
            }
            
            .stat-card-icon {
                font-size: 2.5rem;
            }
            
            .dashboard-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="/api/placeholder/150/40" alt="SISPOL">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-home"></i> Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-file-alt"></i> Procedimentos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-search"></i> Consultas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-chart-bar"></i> Relatórios</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> Usuário
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog"></i> Perfil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header da página -->
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="m-0"><i class="fas fa-chart-pie"></i> Dashboard de Crimes</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#" class="text-white">Início</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-white">Relatórios</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Crimes com Resultado Morte</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Título do Dashboard -->
        <div class="row">
            <div class="col-md-8">
                <h2 class="dashboard-title">Relatório de Crimes Consumados e Fatos com Resultado Morte</h2>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group">
                    <button class="btn btn-outline-primary btn-export" type="button">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu export-dropdown-menu">
                        <li><a class="dropdown-item export-dropdown-item" href="#"><i class="far fa-file-pdf"></i> PDF</a></li>
                        <li><a class="dropdown-item export-dropdown-item" href="#"><i class="far fa-file-excel"></i> Excel</a></li>
                        <li><a class="dropdown-item export-dropdown-item" href="#"><i class="far fa-file-csv"></i> CSV</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item export-dropdown-item" href="#"><i class="fas fa-print"></i> Imprimir</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Filtro de Ano -->
        <div class="row">
            <div class="col-md-12">
                <div class="card filter-card">
                    <div class="card-body">
                        <form method="GET" class="mb-0">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label for="ano" class="form-label">Selecione o Ano</label>
                                    <select id="ano" name="ano" class="form-select">
                                        <option value="">Todos os anos</option>
                                        <option value="2025" selected>2025</option>
                                        <option value="2024">2024</option>
                                        <option value="2023">2023</option>
                                        <option value="2022">2022</option>
                                        <option value="2021">2021</option>
                                        <option value="2020">2020</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="periodo" class="form-label">Período</label>
                                    <select id="periodo" name="periodo" class="form-select">
                                        <option value="anual" selected>Anual</option>
                                        <option value="semestral">Semestral</option>
                                        <option value="trimestral">Trimestral</option>
                                        <option value="mensal">Mensal</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-2"></i> Aplicar Filtros
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cards de Estatísticas -->
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card stat-card card-homicidio">
                    <div class="card-body position-relative">
                        <h5 class="card-title">Homicídio/Feminicídio</h5>
                        <p class="display-4 mt-3 mb-0">45</p>
                        <div class="stat-card-icon">
                            <i class="fas fa-skull-crossbones"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-white py-2">
                        <small><i class="fas fa-chart-line me-1"></i> +5% em relação ao ano anterior</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card card-tentativas">
                    <div class="card-body position-relative">
                        <h5 class="card-title">Tentativas de Homicídio</h5>
                        <p class="display-4 mt-3 mb-0">67</p>
                        <div class="stat-card-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-dark py-2">
                        <small><i class="fas fa-chart-line me-1"></i> -3% em relação ao ano anterior</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card card-confrontos">
                    <div class="card-body position-relative">
                        <h5 class="card-title">Confrontos Policiais</h5>
                        <p class="display-4 mt-3 mb-0">12</p>
                        <div class="stat-card-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-white py-2">
                        <small><i class="fas fa-chart-line me-1"></i> +2% em relação ao ano anterior</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card card-suicidios">
                    <div class="card-body position-relative">
                        <h5 class="card-title">Suicídios</h5>
                        <p class="display-4 mt-3 mb-0">28</p>
                        <div class="stat-card-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-white py-2">
                        <small><i class="fas fa-chart-line me-1"></i> +8% em relação ao ano anterior</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráficos -->
        <div class="row charts-container">
            <div class="col-md-8 mb-4">
                <div class="card chart-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-chart-line me-2"></i> Evolução de Crimes por Mês (2025)</span>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-expand-alt"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <canvas id="evolutionChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card chart-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-chart-pie me-2"></i> Distribuição de Crimes</span>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-expand-alt"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <canvas id="distributionChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabela Detalhada -->
        <div class="row">
            <div class="col-12">
                <div class="card chart-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-table me-2"></i> Detalhamento de Crimes (2025)</span>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary me-2">
                                <i class="fas fa-sync-alt"></i> Atualizar
                            </button>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">#</th>
                                        <th scope="col">Crime/Fato Investigado</th>
                                        <th scope="col" class="text-center">Total de Vítimas</th>
                                        <th scope="col" class="text-center">Total de Procedimentos</th>
                                        <th scope="col" class="text-center">Variação Anual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">1</td>
                                        <td>Homicídio - Consumado</td>
                                        <td class="text-center">39</td>
                                        <td class="text-center">36</td>
                                        <td class="text-center text-danger">+8%</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">2</td>
                                        <td>Feminicídio - Consumado</td>
                                        <td class="text-center">6</td>
                                        <td class="text-center">6</td>
                                        <td class="text-center text-danger">+20%</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">3</td>
                                        <td>Homicídio - Tentado</td>
                                        <td class="text-center">67</td>
                                        <td class="text-center">60</td>
                                        <td class="text-center text-success">-3%</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">4</td>
                                        <td>Confronto Policial - Consumado</td>
                                        <td class="text-center">12</td>
                                        <td class="text-center">8</td>
                                        <td class="text-center text-danger">+2%</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">5</td>
                                        <td>Suicídio - Consumado</td>
                                        <td class="text-center">28</td>
                                        <td class="text-center">28</td>
                                        <td class="text-center text-danger">+8%</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <td colspan="2" class="text-end"><strong>Total</strong></td>
                                        <td class="text-center"><strong>152</strong></td>
                                        <td class="text-center"><strong>138</strong></td>
                                        <td class="text-center"><strong>+5%</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-md-start">
                    <p class="mb-0">© 2025 SISPOL - Todos os direitos reservados</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Versão 2.5.3</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts necessários -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Dados para o gráfico de evolução
        const evolutionData = {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [
                {
                    label: 'Homicídio/Feminicídio',
                    data: [5, 4, 3, 6, 2, 3, 4, 5, 3, 4, 3, 3],
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Tentativas',
                    data: [7, 6, 5, 8, 4, 5, 6, 7, 5, 6, 4, 4],
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Confrontos',
                    data: [1, 0, 2, 1, 1, 0, 2, 1, 1, 1, 1, 1],
                    borderColor: '#212529',
                    backgroundColor: 'rgba(33, 37, 41, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Suicídios',
                    data: [3, 2, 2, 3, 1, 2, 4, 2, 2, 3, 2, 2],
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
                    tension: 0.3,
                    fill: true
                }
            ]
        };

        // Dados para o gráfico de distribuição
        const distributionData = {
            labels: ['Homicídio/Feminicídio', 'Tentativas', 'Confrontos', 'Suicídios'],
            datasets: [{
                data: [45, 67, 12, 28],
                backgroundColor: [
                    '#dc3545',
                    '#ffc107',
                    '#212529',
                    '#6c757d'
                ],
                borderWidth: 0
            }]
        };

        // Configuração do gráfico de evolução
        const evolutionChart = new Chart(
            document.getElementById('evolutionChart'),
            {
                type: 'line',
                data: evolutionData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            }
        );

        // Configuração do gráfico de distribuição
        const distributionChart = new Chart(
            document.getElementById('distributionChart'),
            {
                type: 'doughnut',
                data: distributionData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            }
        );
    </script>
</body>
</html>