<?php
session_start();
include '../includes/header.php'; // Inclui o header
require_once '../config/db.php'; // Conexão com o banco de dados

// Obtém o ano selecionado, padrão é o ano atual
$anoSelecionado = $_GET['ano'] ?? date('Y');

// Consulta para total de vítimas de crimes contra a vida consumados (Homicídio e Feminicídio)
$queryCrimesContraVida = "
    SELECT 
        COUNT(DISTINCT v.ID) AS TotalVitimas, 
        COUNT(DISTINCT p.ID) AS TotalProcedimentos
    FROM Vitimas_Crimes vc
    INNER JOIN Vitimas v ON vc.VitimaID = v.ID
    INNER JOIN Procedimentos p ON v.ProcedimentoID = p.ID
    WHERE vc.CrimeID IN (1, 2) -- Homicídio e Feminicídio
      AND vc.Modalidade = 'Consumado'
      AND p.Dependente = 0 -- Exclui procedimentos dependentes
      " . ($anoSelecionado ? "AND YEAR(p.DataInstauracao) = :ano" : "");
$stmtCrimesContraVida = $pdo->prepare($queryCrimesContraVida);
if ($anoSelecionado) {
    $stmtCrimesContraVida->bindValue(':ano', $anoSelecionado, PDO::PARAM_INT);
}
$stmtCrimesContraVida->execute();
$totalCrimesContraVida = $stmtCrimesContraVida->fetch(PDO::FETCH_ASSOC);

// Consulta para vítimas de Confrontos Policiais
$queryConfrontosPoliciais = "
    SELECT 
        COUNT(DISTINCT v.ID) AS TotalVitimas, 
        COUNT(DISTINCT p.ID) AS TotalProcedimentos
    FROM Vitimas_Crimes vc
    INNER JOIN Vitimas v ON vc.VitimaID = v.ID
    INNER JOIN Procedimentos p ON v.ProcedimentoID = p.ID
    WHERE vc.CrimeID = 14 -- Confronto Policial
      AND vc.Modalidade = 'Consumado'
      AND p.Dependente = 0 -- Exclui procedimentos dependentes
      " . ($anoSelecionado ? "AND YEAR(p.DataInstauracao) = :ano" : "");
$stmtConfrontosPoliciais = $pdo->prepare($queryConfrontosPoliciais);
if ($anoSelecionado) {
    $stmtConfrontosPoliciais->bindValue(':ano', $anoSelecionado, PDO::PARAM_INT);
}
$stmtConfrontosPoliciais->execute();
$totalConfrontosPoliciais = $stmtConfrontosPoliciais->fetch(PDO::FETCH_ASSOC);

// Consulta para vítimas de Suicídios
$querySuicidios = "
    SELECT 
        COUNT(DISTINCT v.ID) AS TotalVitimas, 
        COUNT(DISTINCT p.ID) AS TotalProcedimentos
    FROM Vitimas_Crimes vc
    INNER JOIN Vitimas v ON vc.VitimaID = v.ID
    INNER JOIN Procedimentos p ON v.ProcedimentoID = p.ID
    WHERE vc.CrimeID = 16 -- Suicídio
      AND vc.Modalidade = 'Consumado'
      AND p.Dependente = 0 -- Exclui procedimentos dependentes
      " . ($anoSelecionado ? "AND YEAR(p.DataInstauracao) = :ano" : "");
$stmtSuicidios = $pdo->prepare($querySuicidios);
if ($anoSelecionado) {
    $stmtSuicidios->bindValue(':ano', $anoSelecionado, PDO::PARAM_INT);
}
$stmtSuicidios->execute();
$totalSuicidios = $stmtSuicidios->fetch(PDO::FETCH_ASSOC);

// Consulta detalhada para a tabela
$queryDetalhada = "
    SELECT 
        c.Nome AS Crime, 
        COUNT(DISTINCT v.ID) AS TotalVitimas, 
        COUNT(DISTINCT p.ID) AS TotalProcedimentos
    FROM Vitimas_Crimes vc
    INNER JOIN Crimes c ON vc.CrimeID = c.ID
    INNER JOIN Vitimas v ON vc.VitimaID = v.ID
    INNER JOIN Procedimentos p ON v.ProcedimentoID = p.ID
    WHERE vc.CrimeID IN (1, 2, 14, 16) -- Homicídio, Feminicídio, Confronto Policial, Suicídio
      AND vc.Modalidade = 'Consumado'
      AND p.Dependente = 0 -- Exclui procedimentos dependentes
      " . ($anoSelecionado ? "AND YEAR(p.DataInstauracao) = :ano" : "") . "
    GROUP BY vc.CrimeID
";
$stmtDetalhada = $pdo->prepare($queryDetalhada);
if ($anoSelecionado) {
    $stmtDetalhada->bindValue(':ano', $anoSelecionado, PDO::PARAM_INT);
}
$stmtDetalhada->execute();
$dadosTabela = $stmtDetalhada->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Relatório de Crimes Consumados e Fatos com Resultado Morte</h1>

    <!-- Filtro por ano -->
    <form method="GET" class="mb-4">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <label for="ano" class="form-label">Selecione o Ano</label>
                <select id="ano" name="ano" class="form-select">
                    <option value="">Todos os anos</option>
                    <?php for ($i = date('Y'); $i >= 2000; $i--): ?>
                        <option value="<?= $i ?>" <?= ($i == $anoSelecionado) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Indicadores -->
    <div class="row text-center">
        <div class="col-md-4 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h4 class="card-title">Vítimas de Crimes Contra a Vida</h4>
                    <p class="display-4 fw-bold"><?= $totalCrimesContraVida['TotalVitimas'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h4 class="card-title">Vítimas de Confrontos Policiais</h4>
                    <p class="display-4 fw-bold"><?= $totalConfrontosPoliciais['TotalVitimas'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h4 class="card-title">Vítimas de Suicídios</h4>
                    <p class="display-4 fw-bold"><?= $totalSuicidios['TotalVitimas'] ?? 0 ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela detalhada -->
    <div class="table-responsive mt-5">
        <table class="table table-bordered">
            <thead class="table-dark">
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
</div>

<?php include '../includes/footer.php'; ?>
