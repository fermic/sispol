<?php
session_start();
include '../includes/header.php'; // Inclui o cabeçalho e configurações globais

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}

// Ano selecionado pelo usuário, padrão é o ano atual
$anoSelecionado = $_GET['ano'] ?? date('Y');

// Função para formatar o mês no formato brasileiro "MM/YYYY"
function formatarMes($anoMes) {
    $date = DateTime::createFromFormat('Y-m', $anoMes);
    return $date ? $date->format('m/Y') : $anoMes; // Formata como "MM/YYYY"
}

// Consulta ao banco de dados para gráficos
include_once "../config/db.php";

// Consulta para instaurações de inquéritos
$queryInstaurações = "
    SELECT DATE_FORMAT(DataInstauracao, '%Y-%m') AS Mes, COUNT(ID) AS Quantidade
    FROM Procedimentos
    WHERE TipoID = 1 AND YEAR(DataInstauracao) = :ano
    GROUP BY DATE_FORMAT(DataInstauracao, '%Y-%m')
    ORDER BY Mes
";
$stmtInstaurações = $pdo->prepare($queryInstaurações);
$stmtInstaurações->execute(['ano' => $anoSelecionado]);
$resultadosInstaurações = $stmtInstaurações->fetchAll(PDO::FETCH_ASSOC);

// Consulta para remessas de inquéritos
$queryRemessas = "
    SELECT DATE_FORMAT(DataConclusao, '%Y-%m') AS Mes, COUNT(ID) AS Quantidade
    FROM Movimentacoes
    WHERE TipoID = 5 AND Situacao = 'Finalizado' AND YEAR(DataConclusao) = :ano
    GROUP BY DATE_FORMAT(DataConclusao, '%Y-%m')
    ORDER BY Mes
";
$stmtRemessas = $pdo->prepare($queryRemessas);
$stmtRemessas->execute(['ano' => $anoSelecionado]);
$resultadosRemessas = $stmtRemessas->fetchAll(PDO::FETCH_ASSOC);

// Criar um array com todos os meses do ano no formato "Y-m"
$mesesAno = [];
for ($i = 1; $i <= 12; $i++) {
    $mesFormatado = sprintf('%04d-%02d', $anoSelecionado, $i); // Formato "YYYY-MM"
    $mesesAno[$mesFormatado] = formatarMes($mesFormatado); // Formato "MM/YYYY"
}

// Preencher os dados para instaurações e remessas
$instauraçõesPorMes = array_fill_keys(array_keys($mesesAno), 0);
$remessasPorMes = array_fill_keys(array_keys($mesesAno), 0);

foreach ($resultadosInstaurações as $row) {
    $instauraçõesPorMes[$row['Mes']] = $row['Quantidade'];
}
foreach ($resultadosRemessas as $row) {
    $remessasPorMes[$row['Mes']] = $row['Quantidade'];
}

// Converter os dados para JSON para o gráfico
$mesesJSON = json_encode(array_values($mesesAno));
$instauraçõesJSON = json_encode(array_values($instauraçõesPorMes));
$remessasJSON = json_encode(array_values($remessasPorMes));

$totalInstaurações = array_sum($instauraçõesPorMes);
$totalRemessas = array_sum($remessasPorMes);
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Gráficos de Inquéritos Policiais</h1>
    
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
    
    <!-- Exibição dos Totais -->
    <div class="row text-center mb-4">
        <div class="col-md-6">
            <div class="alert alert-info">
                <h4>Total de Instaurações de Inquérito</h4>
                <h2><?= htmlspecialchars($totalInstaurações) ?></h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-success">
                <h4>Total de Remessas de Inquérito</h4>
                <h2><?= htmlspecialchars($totalRemessas) ?></h2>
            </div>
        </div>
    </div>

    <!-- Gráfico -->
    <div class="card shadow-sm p-4 mb-4">
        <canvas id="graficoInquéritos" width="100%" height="40"></canvas>
    </div>

    <!-- Tabela com os dados -->
    <div class="table-responsive">
        <h2 class="text-center mb-3">Dados Detalhados</h2>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Mês</th>
                    <th>Instaurações de Inquérito</th>
                    <th>Remessas de Inquérito</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mesesAno as $mesFormato => $mesBrasileiro): ?>
                    <tr>
                        <td><?= htmlspecialchars($mesBrasileiro) ?></td>
                        <td><?= htmlspecialchars($instauraçõesPorMes[$mesFormato]) ?></td>
                        <td><?= htmlspecialchars($remessasPorMes[$mesFormato]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoInquéritos').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= $mesesJSON ?>,
            datasets: [
                {
                    label: 'Instaurações de Inquérito',
                    data: <?= $instauraçõesJSON ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Remessas de Inquérito',
                    data: <?= $remessasJSON ?>,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
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
                        text: 'Meses'
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
</script>

<?php include '../includes/footer.php'; ?>
