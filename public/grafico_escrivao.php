<?php
session_start();
include '../includes/header.php'; // Inclui o cabeçalho e configurações globais
include_once '../config/db.php'; // Conexão com o banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}

// Consultar os dados de distribuição de IP por escrivão
$queryIP = "
    SELECT u.Nome AS Escrivao, COUNT(p.ID) AS TotalIPs
    FROM Procedimentos p
    INNER JOIN Usuarios u ON p.EscrivaoID = u.ID
    WHERE p.TipoID = 1 -- Apenas inquéritos policiais (IP)
      AND p.SituacaoID NOT IN (4, 5, 7) -- Excluir situações específicas
    GROUP BY u.Nome
    ORDER BY TotalIPs DESC
";
$stmtIP = $pdo->prepare($queryIP);
$stmtIP->execute();
$dadosEscrivaoIP = $stmtIP->fetchAll(PDO::FETCH_ASSOC);


// Consultar os dados de distribuição de VPI por escrivão
$queryVPI = "
    SELECT u.Nome AS Escrivao, COUNT(p.ID) AS TotalVPIs
    FROM Procedimentos p
    INNER JOIN Usuarios u ON p.EscrivaoID = u.ID
    WHERE p.TipoID = 2 -- Apenas verificações preliminares de informação (VPI)
    GROUP BY u.Nome
    ORDER BY TotalVPIs DESC
";
$stmtVPI = $pdo->prepare($queryVPI);
$stmtVPI->execute();
$dadosEscrivaoVPI = $stmtVPI->fetchAll(PDO::FETCH_ASSOC);

// Consultar os dados de movimentações do tipo "Requisição MP" por escrivão
$queryReqMP = "
    SELECT u.Nome AS Escrivao, COUNT(m.ID) AS TotalReqMPs
    FROM Movimentacoes m
    INNER JOIN Procedimentos p ON m.ProcedimentoID = p.ID
    INNER JOIN Usuarios u ON p.EscrivaoID = u.ID
    WHERE m.TipoID = 1 
      AND m.Situacao = 'Em andamento' -- Considerar apenas movimentações em andamento
    GROUP BY u.Nome
    ORDER BY TotalReqMPs DESC
";
$stmtReqMP = $pdo->prepare($queryReqMP);
$stmtReqMP->execute();
$dadosEscrivaoReqMP = $stmtReqMP->fetchAll(PDO::FETCH_ASSOC);


// Dados para os gráficos
$escrivaosIP = [];
$totaisIP = [];
foreach ($dadosEscrivaoIP as $row) {
    $escrivaosIP[] = $row['Escrivao'];
    $totaisIP[] = $row['TotalIPs'];
}

$escrivaosVPI = [];
$totaisVPI = [];
foreach ($dadosEscrivaoVPI as $row) {
    $escrivaosVPI[] = $row['Escrivao'];
    $totaisVPI[] = $row['TotalVPIs'];
}

$escrivaosReqMP = [];
$totaisReqMP = [];
foreach ($dadosEscrivaoReqMP as $row) {
    $escrivaosReqMP[] = $row['Escrivao'];
    $totaisReqMP[] = $row['TotalReqMPs'];
}
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Distribuição de procedimentos em andamento por Escrivão</h1>

    <!-- Container para exibir os gráficos em linha -->
    <div class="row">
        <!-- Gráfico de IP por escrivão -->
        <div class="col-md-4">
            <h2 class="text-center">IPs</h2>
            <canvas id="graficoEscrivaoIP" style="max-height: 300px;"></canvas>
        </div>

        <!-- Gráfico de VPI por escrivão -->
        <div class="col-md-4">
            <h2 class="text-center">VPIs</h2>
            <canvas id="graficoEscrivaoVPI" style="max-height: 300px;"></canvas>
        </div>

        <!-- Gráfico de Requisição MP por escrivão -->
        <div class="col-md-4">
            <h2 class="text-center">Requisições MP</h2>
            <canvas id="graficoReqMP" style="max-height: 300px;"></canvas>
        </div>
    </div>
</div>

<!-- Inclui o Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gráfico de Pizza - IP por escrivão
        const ctxIP = document.getElementById('graficoEscrivaoIP').getContext('2d');
        new Chart(ctxIP, {
            type: 'pie',
            data: {
                labels: <?= json_encode($escrivaosIP) ?>,
                datasets: [{
                    data: <?= json_encode($totaisIP) ?>,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Gráfico de Pizza - VPI por escrivão
        const ctxVPI = document.getElementById('graficoEscrivaoVPI').getContext('2d');
        new Chart(ctxVPI, {
            type: 'pie',
            data: {
                labels: <?= json_encode($escrivaosVPI) ?>,
                datasets: [{
                    data: <?= json_encode($totaisVPI) ?>,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Gráfico de Pizza - Requisição MP por escrivão
        const ctxReqMP = document.getElementById('graficoReqMP').getContext('2d');
        new Chart(ctxReqMP, {
            type: 'pie',
            data: {
                labels: <?= json_encode($escrivaosReqMP) ?>,
                datasets: [{
                    data: <?= json_encode($totaisReqMP) ?>,
                    backgroundColor: [
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)'
                    ],
                    borderColor: [
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    });
</script>


<?php include '../includes/footer.php'; ?>
