<?php
session_start();
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

<div class="container mt-5">
    <h1 class="text-center mb-4">Relatório de Crimes Consumados e Fatos com Resultado Morte</h1>

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

    <div class="row text-center">
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h4 class="card-title">Homicídio/Feminicídio</h4>
                    <p class="display-4 fw-bold"><?= $totalCrimesContraVida['TotalVitimas'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h4 class="card-title">Tentativas de Homicídio</h4>
                    <p class="display-4 fw-bold"><?= $totalTentativas['TotalVitimas'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h4 class="card-title">Confrontos Policiais</h4>
                    <p class="display-4 fw-bold"><?= $totalConfrontosPoliciais['TotalVitimas'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h4 class="card-title">Suicídios</h4>
                    <p class="display-4 fw-bold"><?= $totalSuicidios['TotalVitimas'] ?? 0 ?></p>
                </div>
            </div>
        </div>
    </div>

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
