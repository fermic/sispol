<?php
session_start();
include '../includes/header.php'; // Inclui o cabeçalho e configurações globais
include_once "../config/db.php"; // Conexão com o banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}

// Ano selecionado pelo usuário, padrão é o ano atual
$anoSelecionado = $_GET['ano'] ?? date('Y');

// Consulta ao banco de dados para obter os dados de OMP "Em andamento" por responsável
$queryOMP = "
    SELECT 
        u.Nome AS Responsavel,
        SUM(CASE WHEN m.Situacao = 'Em andamento' THEN 1 ELSE 0 END) AS EmAndamento,
        SUM(CASE WHEN m.Situacao = 'Finalizado' THEN 1 ELSE 0 END) AS Finalizado
    FROM Movimentacoes m
    INNER JOIN Usuarios u ON m.ResponsavelID = u.ID
    WHERE m.TipoID = 4 AND YEAR(m.DataVencimento) = :ano
    GROUP BY u.Nome
    ORDER BY u.Nome
";
$stmtOMP = $pdo->prepare($queryOMP);
$stmtOMP->execute(['ano' => $anoSelecionado]);
$dadosOMP = $stmtOMP->fetchAll(PDO::FETCH_ASSOC);

$stmtOMP = $pdo->prepare($queryOMP);
$stmtOMP->execute(['ano' => $anoSelecionado]);
$dadosOMP = $stmtOMP->fetchAll(PDO::FETCH_ASSOC);

// Consulta para calcular o tempo médio de conclusão de OMP
$queryTempoMedio = "
    SELECT 
        u.Nome AS Responsavel,
        AVG(DATEDIFF(m.DataConclusao, m.DataCriacao)) AS MediaDias
    FROM Movimentacoes m
    INNER JOIN Usuarios u ON m.ResponsavelID = u.ID
    WHERE m.TipoID = 4 AND m.Situacao = 'Finalizado' AND YEAR(m.DataConclusao) = :ano
    GROUP BY u.Nome
    ORDER BY u.Nome
";
$stmtTempoMedio = $pdo->prepare($queryTempoMedio);
$stmtTempoMedio->execute(['ano' => $anoSelecionado]);
$dadosTempoMedio = $stmtTempoMedio->fetchAll(PDO::FETCH_ASSOC);

// Cálculo geral do tempo médio de conclusão de OMP
$queryTempoGeral = "
    SELECT 
        AVG(DATEDIFF(m.DataConclusao, m.DataCriacao)) AS MediaGeral
    FROM Movimentacoes m
    WHERE m.TipoID = 4 AND m.Situacao = 'Finalizado' AND YEAR(m.DataConclusao) = :ano
";
$stmtTempoGeral = $pdo->prepare($queryTempoGeral);
$stmtTempoGeral->execute(['ano' => $anoSelecionado]);
$mediaGeral = $stmtTempoGeral->fetch(PDO::FETCH_ASSOC)['MediaGeral'];

// Consulta para OMP "Em andamento" com prazo vencido e não vencido
$queryPrazo = "
    SELECT 
        SUM(CASE WHEN m.DataVencimento < CURDATE() THEN 1 ELSE 0 END) AS Vencido,
        SUM(CASE WHEN m.DataVencimento >= CURDATE() THEN 1 ELSE 0 END) AS NaoVencido
    FROM Movimentacoes m
    WHERE m.TipoID = 4 AND m.Situacao = 'Em andamento' AND YEAR(m.DataVencimento) = :ano
";
$stmtPrazo = $pdo->prepare($queryPrazo);
$stmtPrazo->execute(['ano' => $anoSelecionado]);
$prazoDados = $stmtPrazo->fetch(PDO::FETCH_ASSOC);

// Preencher os dados para os gráficos
$responsaveis = [];
$emAndamento = [];
$finalizado = [];
foreach ($dadosOMP as $row) {
    $responsaveis[] = $row['Responsavel'];
    $emAndamento[] = $row['EmAndamento'];
    $finalizado[] = $row['Finalizado'];
}

// Converter os dados para JSON
$responsaveisJSON = json_encode($responsaveis);
$emAndamentoJSON = json_encode($emAndamento);
$finalizadoJSON = json_encode($finalizado);

// Dados para o gráfico de pizza
$prazoVencido = $prazoDados['Vencido'] ?? 0;
$prazoNaoVencido = $prazoDados['NaoVencido'] ?? 0;

// Converter os dados para JSON para os gráficos
$responsaveisJSON = json_encode($responsaveis);
$emAndamentoJSON = json_encode($emAndamento);
$prazoJSON = json_encode([$prazoVencido, $prazoNaoVencido]);
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Distribuição de OMP (Em andamento)</h1>

    <!-- Formulário para selecionar o ano -->
    <form method="GET" class="mb-4">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <label for="ano" class="form-label">Selecione o Ano</label>
                <select id="ano" name="ano" class="form-select">
                    <?php for ($i = date('Y'); $i >= 2000; $i--): ?>
                        <option value="<?= $i ?>" <?= ($i == $anoSelecionado) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label d-none d-md-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Gráfico -->
    <div class="card shadow-sm p-4 mb-4">
        <canvas id="graficoOMP" width="100%" height="40"></canvas>
    </div>

<!-- Gráfico de Pizza -->
<div class="card shadow-sm p-4 mb-4">
    <h2 class="text-center">Proporção de OMPs vencidas e não vencidas</h2>
    <div class="d-flex justify-content-center">
        <canvas id="graficoPrazoOMP"></canvas>
    </div>
</div>


    <!-- Tabela com os dados -->
<div class="table-responsive">
    <h2 class="text-center mb-3">Dados Detalhados</h2>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Responsável</th>
                <th>Em andamento</th>
                <th>Finalizado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dadosOMP as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Responsavel']) ?></td>
                    <td><?= htmlspecialchars($row['EmAndamento']) ?></td>
                    <td><?= htmlspecialchars($row['Finalizado']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


    <!-- Tabela de Tempo Médio -->
    <div class="table-responsive mt-4">
        <h2 class="text-center mb-3">Tempo Médio de Conclusão de OMP</h2>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Responsável</th>
                    <th>Tempo Médio (dias)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dadosTempoMedio as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Responsavel']) ?></td>
                        <td><?= number_format($row['MediaDias'], 2) ?> dias</td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td><strong>Geral</strong></td>
                    <td><strong><?= $mediaGeral !== null ? number_format($mediaGeral, 2) . " dias" : "Sem dados" ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Barras
const ctx = document.getElementById('graficoOMP').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= $responsaveisJSON ?>,
        datasets: [
            {
                label: 'Em andamento',
                data: <?= $emAndamentoJSON ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            },
            {
                label: 'Finalizado',
                data: <?= $finalizadoJSON ?>,
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                enabled: true
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Responsáveis'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Quantidade'
                },
                beginAtZero: true
            }
        }
    }
});

    // Gráfico de Pizza
    const ctxPie = document.getElementById('graficoPrazoOMP').getContext('2d');
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Vencido', 'Não vencido'],
            datasets: [{
                data: <?= $prazoJSON ?>,
                backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(75, 192, 192, 0.2)'],
                borderColor: ['rgba(255, 99, 132, 1)', 'rgba(75, 192, 192, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: { enabled: true }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
