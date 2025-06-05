<?php
session_start();
include '../includes/header.php'; // Inclui a navbar e configurações globais
include_once '../config/db.php'; // Conexão com o banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}

// Consulta para calcular a idade média dos procedimentos por escrivão, separados por TipoID
$query = "
    SELECT 
        u.Nome AS Escrivao,
        p.TipoID,
        AVG(DATEDIFF(CURDATE(), p.DataInstauracao)) AS IdadeMediaDias
    FROM Procedimentos p
    INNER JOIN Usuarios u ON p.EscrivaoID = u.ID
    WHERE p.DataInstauracao IS NOT NULL -- Garante que só considere procedimentos com data válida
      AND p.SituacaoID NOT IN (4, 5, 7, 8) -- Exclui as situações especificadas
    GROUP BY u.Nome, p.TipoID
    ORDER BY u.Nome, p.TipoID
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para calcular a idade média geral da delegacia
$queryMediaGeral = "
    SELECT 
        AVG(DATEDIFF(CURDATE(), p.DataInstauracao)) AS IdadeMediaGeral
    FROM Procedimentos p
    WHERE p.DataInstauracao IS NOT NULL -- Garante que só considere procedimentos com data válida
      AND p.SituacaoID NOT IN (4, 5, 7, 8) -- Exclui as situações especificadas
";
$stmtMediaGeral = $pdo->prepare($queryMediaGeral);
$stmtMediaGeral->execute();
$mediaGeral = $stmtMediaGeral->fetch(PDO::FETCH_ASSOC)['IdadeMediaGeral'];

// Consulta para calcular o tempo médio de conclusão de IPs
$queryTempoConclusaoIP = "
    SELECT 
        AVG(DATEDIFF(m.DataConclusao, p.DataInstauracao)) AS TempoMedioConclusao
    FROM Movimentacoes m
    INNER JOIN Procedimentos p ON m.ProcedimentoID = p.ID
    WHERE m.TipoID = (SELECT ID FROM TiposMovimentacao WHERE Nome = 'Remessa de IP')
      AND p.TipoID = 1 -- Apenas IPs
      AND p.DataInstauracao IS NOT NULL
      AND m.DataConclusao IS NOT NULL
";
$stmtTempoConclusaoIP = $pdo->prepare($queryTempoConclusaoIP);
$stmtTempoConclusaoIP->execute();
$tempoMedioConclusaoIP = $stmtTempoConclusaoIP->fetch(PDO::FETCH_ASSOC)['TempoMedioConclusao'];

// Consulta para encontrar o procedimento mais antigo ainda aberto
$queryProcedimentoMaisAntigo = "
    SELECT 
        DATEDIFF(CURDATE(), MIN(p.DataInstauracao)) AS DiasAberto
    FROM Procedimentos p
    WHERE p.SituacaoID NOT IN (4, 5, 7, 8) -- Exclui situações de procedimentos concluídos
      AND p.DataInstauracao IS NOT NULL
";
$stmtProcedimentoMaisAntigo = $pdo->prepare($queryProcedimentoMaisAntigo);
$stmtProcedimentoMaisAntigo->execute();
$diasProcedimentoMaisAntigo = $stmtProcedimentoMaisAntigo->fetch(PDO::FETCH_ASSOC)['DiasAberto'];

// Consulta para calcular o tempo médio de conclusão das movimentações do tipo "Requisição MP"
$queryTempoConclusaoRequisicaoMP = "
    SELECT 
        AVG(DATEDIFF(m.DataConclusao, m.DataRequisicao)) AS TempoMedioConclusaoRequisicao
    FROM Movimentacoes m
    WHERE m.TipoID = 1 
      AND m.DataRequisicao IS NOT NULL
      AND m.DataConclusao IS NOT NULL
";
$stmtTempoConclusaoRequisicaoMP = $pdo->prepare($queryTempoConclusaoRequisicaoMP);
$stmtTempoConclusaoRequisicaoMP->execute();
$tempoMedioConclusaoRequisicaoMP = $stmtTempoConclusaoRequisicaoMP->fetch(PDO::FETCH_ASSOC)['TempoMedioConclusaoRequisicao'];

// Consulta para encontrar a requisição "Requisição MP" mais antiga ainda em aberto
$queryRequisicaoMPMaisAntiga = "
    SELECT 
        DATEDIFF(CURDATE(), MIN(m.DataRequisicao)) AS DiasAberta
    FROM Movimentacoes m
    WHERE m.TipoID = 1
      AND m.DataRequisicao IS NOT NULL
      AND m.Situacao = 'Em andamento'
";
$stmtRequisicaoMPMaisAntiga = $pdo->prepare($queryRequisicaoMPMaisAntiga);
$stmtRequisicaoMPMaisAntiga->execute();
$diasRequisicaoMPMaisAntiga = $stmtRequisicaoMPMaisAntiga->fetch(PDO::FETCH_ASSOC)['DiasAberta'];

?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Indicadores da Delegacia</h1>

    <div class="row">
        <!-- Card: Idade Média Geral -->
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-dark">
                <div class="card-body">
                    <h5 class="card-title text-primary">Tempo médio do passivo</h5>
                    <p class="card-text display-6">
                        <strong><?= $mediaGeral !== null ? number_format($mediaGeral, 2) . " dias" : "Sem dados" ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card: Tempo Médio de Conclusão de IPs -->
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-dark">
                <div class="card-body">
                    <h5 class="card-title text-success">Tempo Médio de Conclusão de IPs</h5>
                    <p class="card-text display-6">
                        <strong><?= $tempoMedioConclusaoIP !== null ? number_format($tempoMedioConclusaoIP, 2) . " dias" : "Sem dados" ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card: Procedimento Mais Antigo -->
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-dark">
                <div class="card-body">
                    <h5 class="card-title text-danger">Procedimento Mais Antigo Apurando</h5>
                    <p class="card-text display-6">
                        <strong><?= $diasProcedimentoMaisAntigo !== null ? $diasProcedimentoMaisAntigo . " dias" : "Sem dados" ?></strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Card: Tempo Médio de Conclusão de Requisição MP -->
        <div class="col-md-6">
            <div class="card text-center shadow-sm border-dark">
                <div class="card-body">
                    <h5 class="card-title text-info">Tempo Médio de Conclusão - Requisição MP</h5>
                    <p class="card-text display-6">
                        <strong><?= $tempoMedioConclusaoRequisicaoMP !== null ? number_format($tempoMedioConclusaoRequisicaoMP, 2) . " dias" : "Sem dados" ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card: Requisição MP Mais Antiga -->
        <div class="col-md-6">
            <div class="card text-center shadow-sm border-dark">
                <div class="card-body">
                    <h5 class="card-title text-warning">Requisição MP Mais Antiga (Em Aberto)</h5>
                    <p class="card-text display-6">
                        <strong><?= $diasRequisicaoMPMaisAntiga !== null ? $diasRequisicaoMPMaisAntiga . " dias" : "Sem dados" ?></strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
