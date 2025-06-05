<?php
include '../includes/header.php';
require_once '../config/db.php';

$anoSelecionado = $_GET['ano'] ?? date('Y');

// Homicídios e Feminicídios Consumados
$queryCrimesContraVida = "
    SELECT 
        COUNT(DISTINCT v.ID) AS TotalVitimas, 
        COUNT(DISTINCT p.ID) AS TotalProcedimentos
    FROM Vitimas_Crimes vc
    INNER JOIN Vitimas v ON vc.VitimaID = v.ID
    INNER JOIN Procedimentos p ON v.ProcedimentoID = p.ID
    WHERE vc.CrimeID IN (1, 2)
      AND vc.Modalidade = 'Consumado'
      AND p.Dependente = 0
      " . ($anoSelecionado ? "AND YEAR(p.DataFato) = :ano" : "");
$stmtCrimesContraVida = $pdo->prepare($queryCrimesContraVida);
if ($anoSelecionado) {
    $stmtCrimesContraVida->bindValue(':ano', $anoSelecionado, PDO::PARAM_INT);
}
$stmtCrimesContraVida->execute();
$totalCrimesContraVida = $stmtCrimesContraVida->fetch(PDO::FETCH_ASSOC);

// Tentativas de Homicídio
$queryTentativasHomicidio = "
    SELECT 
        COUNT(DISTINCT v.ID) AS TotalVitimas,
        COUNT(DISTINCT p.ID) AS TotalProcedimentos
    FROM Vitimas_Crimes vc
    INNER JOIN Vitimas v ON vc.VitimaID = v.ID
    INNER JOIN Procedimentos p ON v.ProcedimentoID = p.ID
    WHERE vc.CrimeID = 1
      AND vc.Modalidade = 'Tentado'
      AND p.Dependente = 0
      AND p.SituacaoID != 11
      " . ($anoSelecionado ? "AND YEAR(p.DataFato) = :ano" : "");

$stmtTentativas = $pdo->prepare($queryTentativasHomicidio);
if ($anoSelecionado) {
    $stmtTentativas->bindValue(':ano', $anoSelecionado, PDO::PARAM_INT);
}
$stmtTentativas->execute();
$totalTentativas = $stmtTentativas->fetch(PDO::FETCH_ASSOC);

// Confrontos Policiais
$queryConfrontosPoliciais = "
    SELECT 
        COUNT(DISTINCT v.ID) AS TotalVitimas, 
        COUNT(DISTINCT p.ID) AS TotalProcedimentos
    FROM Vitimas_Crimes vc
    INNER JOIN Vitimas v ON vc.VitimaID = v.ID
    INNER JOIN Procedimentos p ON v.ProcedimentoID = p.ID
    WHERE vc.CrimeID = 14
      AND vc.Modalidade = 'Consumado'
      AND p.Dependente = 0
      " . ($anoSelecionado ? "AND YEAR(p.DataFato) = :ano" : "");
$stmtConfrontosPoliciais = $pdo->prepare($queryConfrontosPoliciais);
if ($anoSelecionado) {
    $stmtConfrontosPoliciais->bindValue(':ano', $anoSelecionado, PDO::PARAM_INT);
}
$stmtConfrontosPoliciais->execute();
$totalConfrontosPoliciais = $stmtConfrontosPoliciais->fetch(PDO::FETCH_ASSOC);

// Suicídios
$querySuicidios = "
    SELECT 
        COUNT(DISTINCT v.ID) AS TotalVitimas, 
        COUNT(DISTINCT p.ID) AS TotalProcedimentos
    FROM Vitimas_Crimes vc
    INNER JOIN Vitimas v ON vc.VitimaID = v.ID
    INNER JOIN Procedimentos p ON v.ProcedimentoID = p.ID
    WHERE vc.CrimeID = 16
      AND vc.Modalidade = 'Consumado'
      AND p.Dependente = 0
      " . ($anoSelecionado ? "AND YEAR(p.DataFato) = :ano" : "");
$stmtSuicidios = $pdo->prepare($querySuicidios);
if ($anoSelecionado) {
    $stmtSuicidios->bindValue(':ano', $anoSelecionado, PDO::PARAM_INT);
}
$stmtSuicidios->execute();
$totalSuicidios = $stmtSuicidios->fetch(PDO::FETCH_ASSOC);

// Tabela Detalhada
$queryDetalhada = "
    SELECT 
        CONCAT(c.Nome, ' - ', vc.Modalidade) AS Crime, 
        COUNT(DISTINCT v.ID) AS TotalVitimas, 
        COUNT(DISTINCT p.ID) AS TotalProcedimentos
    FROM Vitimas_Crimes vc
    INNER JOIN Crimes c ON vc.CrimeID = c.ID
    INNER JOIN Vitimas v ON vc.VitimaID = v.ID
    INNER JOIN Procedimentos p ON v.ProcedimentoID = p.ID
    WHERE vc.CrimeID IN (1, 2, 14, 16)
      AND vc.Modalidade IN ('Consumado', 'Tentado')
      AND p.Dependente = 0
      AND p.SituacaoID != 11
      " . ($anoSelecionado ? "AND YEAR(p.DataFato) = :ano" : "") . "
    GROUP BY vc.CrimeID, vc.Modalidade
";

$stmtDetalhada = $pdo->prepare($queryDetalhada);
if ($anoSelecionado) {
    $stmtDetalhada->bindValue(':ano', $anoSelecionado, PDO::PARAM_INT);
}
$stmtDetalhada->execute();
$dadosTabela = $stmtDetalhada->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    /* Sobrescreve estilos conflitantes do arquivo styles.css */
    .dashboard-page {
        margin: 0;
        padding: 0;
        display: block; /* Remove display: flex do body */
    }
    
    .dashboard-container {
        background: #f8f9fa;
        min-height: calc(100vh - 160px); /* Ajusta para o footer */
        padding: 2rem 0;
    }
    
    .stat-card {
        border-radius: 15px;
        border: none;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        height: 100%;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
    }
    
    .stat-card .card-body {
        padding: 2rem;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 200px;
        text-align: center;
    }
    
    .stat-card h4 {
        font-size: 1.1rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
        text-transform: uppercase;
        min-height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    
    .stat-card .display-4 {
        font-size: 4rem;
        font-weight: 700;
        margin-bottom: 0;
        line-height: 1;
    }
    
    .card-danger {
        background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
    }
    
    .card-warning {
        background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);
    }
    
    .card-dark {
        background: linear-gradient(135deg, #343a40 0%, #212529 100%);
    }
    
    .card-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(45deg);
    }
    
    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    /* Sobrescreve estilos da tabela responsiva do styles.css */
    .dashboard-table-responsive {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        border: none !important; /* Sobrescreve o estilo do styles.css */
    }
    
    .dashboard-table {
        margin-bottom: 0;
    }
    
    .dashboard-table thead th {
        background: #1a1a2e;
        color: white;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        padding: 1rem;
    }
    
    .dashboard-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        font-weight: 500;
        border-color: #eef2f7;
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
    }
    
    .dashboard-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .btn-filter {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        border: none;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }
    
    .form-select {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 0.6rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 2rem;
        text-align: center;
    }
    
    /* Garante que o footer não cubra o conteúdo */
    .footer-space {
        height: 80px;
        display: block;
    }
    
    @media (max-width: 768px) {
        .stat-card .display-4 {
            font-size: 2.5rem;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
    }
</style>

<div class="dashboard-page">
    <div class="dashboard-container">
        <div class="container">
            <h1 class="page-title">Relatório de Crimes Consumados e Fatos com Resultado Morte</h1>

            <div class="filter-section">
                <form method="GET">
                    <div class="row justify-content-center align-items-end">
                        <div class="col-md-4">
                            <label for="ano" class="form-label">Selecione o Ano</label>
                            <select id="ano" name="ano" class="form-select">
                                <option value="">Todos os anos</option>
                                <?php for ($i = date('Y'); $i >= 2000; $i--): ?>
                                    <option value="<?= $i ?>" <?= ($i == $anoSelecionado) ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-filter w-100">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row">
                <div class="col-md-3 mb-4 d-flex">
                    <div class="card stat-card card-danger text-white w-100">
                        <div class="card-body">
                            <h4>Homicídio/Feminicídio</h4>
                            <p class="display-4"><?= $totalCrimesContraVida['TotalVitimas'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4 d-flex">
                    <div class="card stat-card card-warning text-dark w-100">
                        <div class="card-body">
                            <h4>Tentativas de Homicídio</h4>
                            <p class="display-4"><?= $totalTentativas['TotalVitimas'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4 d-flex">
                    <div class="card stat-card card-dark text-white w-100">
                        <div class="card-body">
                            <h4>Confrontos Policiais</h4>
                            <p class="display-4"><?= $totalConfrontosPoliciais['TotalVitimas'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4 d-flex">
                    <div class="card stat-card card-secondary text-white w-100">
                        <div class="card-body">
                            <h4>Suicídios</h4>
                            <p class="display-4"><?= $totalSuicidios['TotalVitimas'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-table-responsive mt-5">
                <table class="table table-hover dashboard-table">
                    <thead>
                        <tr>
                            <th>Crime/Fato Investigado</th>
                            <th>Total de Vítimas</th>
                            <th>Total de Procedimentos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dadosTabela as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['Crime']) ?></td>
                                <td><?= htmlspecialchars($row['TotalVitimas']) ?></td>
                                <td><?= htmlspecialchars($row['TotalProcedimentos']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Espaço para evitar que o footer cubra o conteúdo -->
            <div class="footer-space"></div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>