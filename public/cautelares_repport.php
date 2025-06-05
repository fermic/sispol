<?php
include '../includes/header.php';
require_once '../config/db.php'; // Conexão com o banco de dados

// Filtrar por data
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01'); // Primeiro dia do mês atual
$dataFim = $_GET['data_fim'] ?? date('Y-m-d'); // Data atual

// Estatísticas de procedimentos
$statsQuery = "
SELECT 
    -- IP Instaurados (DataInstauracao e TipoID = 1)
    COUNT(DISTINCT CASE 
        WHEN p.TipoID = 1 AND p.DataInstauracao BETWEEN :dataInicio AND :dataFim THEN p.ID 
    END) AS IPInstaurados,
    
    -- IP Remetidos (com filtro na DataConclusao)
    COUNT(DISTINCT CASE 
        WHEN m.TipoID = 5 AND m.Situacao = 'Finalizado' AND m.DataConclusao BETWEEN :dataInicio AND :dataFim THEN m.ID 
    END) AS IPRemetidos,
    
    -- Com Autoria
    COUNT(DISTINCT CASE 
        WHEN p.SituacaoID = 4 AND m.TipoID = 5 AND m.Situacao = 'Finalizado' AND m.DataConclusao BETWEEN :dataInicio AND :dataFim THEN p.ID 
    END) AS ComAutoria,
    
    -- Sem Autoria
    COUNT(DISTINCT CASE 
        WHEN p.SituacaoID = 5 AND m.TipoID = 5 AND m.Situacao = 'Finalizado' AND m.DataConclusao BETWEEN :dataInicio AND :dataFim THEN p.ID 
    END) AS SemAutoria,
    
    -- Cumprimento de Diligências
    COUNT(DISTINCT CASE 
        WHEN m.TipoID = 1 AND m.Situacao = 'Finalizado' AND m.DataConclusao BETWEEN :dataInicio AND :dataFim THEN m.ID 
    END) AS CumprimentoDiligencias
FROM Procedimentos p
LEFT JOIN Movimentacoes m ON p.ID = m.ProcedimentoID
WHERE 1=1

";

$params = [];
if ($dataInicio && $dataFim) {
    $params['dataInicio'] = $dataInicio;
    $params['dataFim'] = $dataFim;
}

$stmtStats = $pdo->prepare($statsQuery);
$stmtStats->execute($params);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);


$stmtStats = $pdo->prepare($statsQuery);
$stmtStats->execute($params);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

// Consultar detalhes dos IPs instaurados
$ipsQuery = "
    SELECT 
        p.NumeroProcedimento AS IP,
        op.Nome AS Origem,
        GROUP_CONCAT(DISTINCT c.Nome SEPARATOR ', ') AS Crimes,
        p.DataInstauracao,
        GROUP_CONCAT(DISTINCT v.Nome SEPARATOR ', ') AS Vitimas,
        GROUP_CONCAT(DISTINCT v.Idade SEPARATOR ', ') AS Idades,
        sp.Nome AS Situacao
    FROM Procedimentos p
    LEFT JOIN OrigensProcedimentos op ON p.OrigemID = op.ID
    LEFT JOIN Vitimas v ON v.ProcedimentoID = p.ID
    LEFT JOIN Vitimas_Crimes vc ON v.ID = vc.VitimaID
    LEFT JOIN Crimes c ON vc.CrimeID = c.ID
    LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID
    WHERE p.TipoID = 1 AND p.DataInstauracao BETWEEN :dataInicio AND :dataFim
    GROUP BY p.ID
    ORDER BY p.DataInstauracao DESC
";

$stmtIPs = $pdo->prepare($ipsQuery);
$stmtIPs->execute(['dataInicio' => $dataInicio, 'dataFim' => $dataFim]);
$ips = $stmtIPs->fetchAll(PDO::FETCH_ASSOC);


// Estatísticas detalhadas de cautelares por tipo
$queryCautelares = "
    SELECT 
        TipoCautelar,
        SUM(TotalSolicitadas) AS TotalSolicitadas,
        SUM(TotalCumpridas) AS TotalCumpridas
    FROM (
        SELECT 
            tc.Nome AS TipoCautelar,
            SUM(isc.QuantidadeSolicitada) AS TotalSolicitadas,
            0 AS TotalCumpridas
        FROM TiposCautelar tc
        LEFT JOIN ItensSolicitacaoCautelar isc ON tc.ID = isc.TipoCautelarID
        LEFT JOIN SolicitacoesCautelares sc ON isc.SolicitacaoCautelarID = sc.ID
        WHERE sc.DataSolicitacao BETWEEN :dataInicio AND :dataFim
        GROUP BY tc.ID, tc.Nome

        UNION ALL

        SELECT 
            COALESCE(tc.Nome, 'Avulso') AS TipoCautelar,
            0 AS TotalSolicitadas,
            SUM(cc.QuantidadeCumprida) AS TotalCumpridas
        FROM CumprimentosCautelares cc
        LEFT JOIN TiposCautelar tc ON cc.TipoCautelarID = tc.ID
        WHERE cc.DataCumprimento BETWEEN :dataInicio AND :dataFim
        GROUP BY tc.ID, tc.Nome
    ) AS combined
    GROUP BY TipoCautelar
";

$stmt = $pdo->prepare($queryCautelares);
$stmt->execute([':dataInicio' => $dataInicio, ':dataFim' => $dataFim]);
$cautelaresDetalhadas = $stmt->fetchAll(PDO::FETCH_ASSOC);




// Consulta para obter os Inquéritos Policiais Remetidos ao Poder Judiciário
$remetidosQuery = "
    SELECT 
        p.NumeroProcedimento AS IP,
        op.Nome AS Origem,
        GROUP_CONCAT(DISTINCT c.Nome SEPARATOR ', ') AS Natureza,
        m.DataConclusao AS Data,
        GROUP_CONCAT(DISTINCT v.Nome SEPARATOR ', ') AS Vitimas,
        GROUP_CONCAT(DISTINCT v.Idade SEPARATOR ', ') AS Idade,
        sp.Nome AS Situacao
    FROM Movimentacoes m
    LEFT JOIN Procedimentos p ON m.ProcedimentoID = p.ID
    LEFT JOIN OrigensProcedimentos op ON p.OrigemID = op.ID
    LEFT JOIN Vitimas v ON v.ProcedimentoID = p.ID
    LEFT JOIN Vitimas_Crimes vc ON v.ID = vc.VitimaID
    LEFT JOIN Crimes c ON vc.CrimeID = c.ID
    LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID
    WHERE m.TipoID = 5 AND m.Situacao = 'Finalizado' AND m.DataConclusao BETWEEN :dataInicio AND :dataFim
    GROUP BY p.ID
    ORDER BY m.DataConclusao DESC
";

$stmtRemetidos = $pdo->prepare($remetidosQuery);
$stmtRemetidos->execute(['dataInicio' => $dataInicio, 'dataFim' => $dataFim]);
$remetidos = $stmtRemetidos->fetchAll(PDO::FETCH_ASSOC);

// Contar o total de IPs remetidos
$totalRemetidos = count($remetidos);



// Consulta para obter os dados de Pessoas Desaparecidas
$desaparecidosQuery = "
    SELECT 
        RAI,
        'Desaparecimento' AS Tipo,
        'Localização de pessoa' AS Especie,
        Situacao AS Resultado,
        Vitima AS Envolvidos,
        DataLocalizacao AS Data
    FROM Desaparecidos
    WHERE Situacao = 'Encontrado'
      AND (DataDesaparecimento BETWEEN :dataInicio AND :dataFim
           OR DataLocalizacao BETWEEN :dataInicio AND :dataFim)
    ORDER BY DataLocalizacao DESC
";



$stmtDesaparecidos = $pdo->prepare($desaparecidosQuery);
$stmtDesaparecidos->execute(['dataInicio' => $dataInicio, 'dataFim' => $dataFim]);
$desaparecidos = $stmtDesaparecidos->fetchAll(PDO::FETCH_ASSOC);



// Consulta para obter as operações policiais de outras diligências
$outrasDiligenciasQuery = "
    SELECT 
        tm.Nome AS Tipo,
        p.NumeroProcedimento AS NumeroProcedimento,
        GROUP_CONCAT(DISTINCT c.Nome SEPARATOR ', ') AS Especie,
        GROUP_CONCAT(DISTINCT v.Nome SEPARATOR '\n') AS Envolvidos, -- Usar quebra de linha no delimitador
        m.DataConclusao AS Data
    FROM Movimentacoes m
    LEFT JOIN Procedimentos p ON m.ProcedimentoID = p.ID
    LEFT JOIN TiposMovimentacao tm ON m.TipoID = tm.ID
    LEFT JOIN Vitimas v ON v.ProcedimentoID = p.ID
    LEFT JOIN Vitimas_Crimes vc ON v.ID = vc.VitimaID
    LEFT JOIN Crimes c ON vc.CrimeID = c.ID
    WHERE 
        m.TipoID  IN (8)
        AND m.Situacao = 'Finalizado'
        AND m.DataConclusao BETWEEN :dataInicio AND :dataFim
    GROUP BY m.ID
    ORDER BY m.DataConclusao DESC
";


$stmtOutrasDiligencias = $pdo->prepare($outrasDiligenciasQuery);
$stmtOutrasDiligencias->execute(['dataInicio' => $dataInicio, 'dataFim' => $dataFim]);
$outrasDiligencias = $stmtOutrasDiligencias->fetchAll(PDO::FETCH_ASSOC);



$operacoesQuery = "
SELECT DISTINCT
    cc.RAI AS RAI,
    tc.Nome AS Tipo,
    'Cumprida' AS Resultado,
    ec.Nome AS Envolvido,
    cc.DataCumprimento AS DataCumprimento
FROM CumprimentosCautelares cc
LEFT JOIN TiposCautelar tc ON cc.TipoCautelarID = tc.ID
LEFT JOIN EnvolvidosCumprimentoCautelar ec ON cc.ID = ec.CumprimentoCautelarID
WHERE cc.DataCumprimento BETWEEN :dataInicio AND :dataFim
ORDER BY cc.DataCumprimento DESC, tc.Nome, ec.Nome
";

$stmtOperacoes = $pdo->prepare($operacoesQuery);
$stmtOperacoes->execute($params);
$operacoes = $stmtOperacoes->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="container mt-5">
    <h2 class="mb-4">Relatório de Procedimentos e Cautelares</h2>
<div class="mt-4 text-end">
    <a href="exportar_pdf.php?data_inicio=<?= htmlspecialchars($dataInicio) ?>&data_fim=<?= htmlspecialchars($dataFim) ?>" 
       class="btn btn-danger">
       Exportar para PDF
    </a>
</div>

    <!-- Filtro de Data -->
    <form method="get" class="mb-4">
        <div class="row g-3">
            <div class="col-md-5">
                <label for="data_inicio" class="form-label">Data Início</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= htmlspecialchars($dataInicio) ?>">
            </div>
            <div class="col-md-5">
                <label for="data_fim" class="form-label">Data Fim</label>
                <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= htmlspecialchars($dataFim) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Estatísticas de Procedimentos -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>IP Instaurados</th>
                <th>IP Remetidos</th>
                <th>Com Autoria</th>
                <th>Sem Autoria</th>
                <th>Cumprimentos de Diligências</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($stats['IPInstaurados'] ?? 0) ?></td>
                <td><?= htmlspecialchars($stats['IPRemetidos'] ?? 0) ?></td>
                <td><?= htmlspecialchars($stats['ComAutoria'] ?? 0) ?></td>
                <td><?= htmlspecialchars($stats['SemAutoria'] ?? 0) ?></td>
                <td><?= htmlspecialchars($stats['CumprimentoDiligencias'] ?? 0) ?></td>
            </tr>
        </tbody>
    </table>



    <!-- Estatísticas de Cautelares por Tipo -->
<div class="mt-5">
    <h3>Estatísticas de Cautelares por Tipo</h3>
<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Tipo de Cautelar</th>
            <th>Total Solicitadas</th>
            <th>Total Cumpridas</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($cautelaresDetalhadas)): ?>
            <tr>
                <td colspan="3" class="text-center">Nenhum dado encontrado.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($cautelaresDetalhadas as $cautelar): ?>
                <tr>
                    <td><?= htmlspecialchars($cautelar['TipoCautelar'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($cautelar['TotalSolicitadas'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($cautelar['TotalCumpridas'] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</div>

    
    <!-- Título com o Total de IPs Instaurados -->
    <h3 class="mt-5">Inquéritos Policiais Instaurados: <span class="badge bg-primary"><?= htmlspecialchars($stats['IPInstaurados'] ?? 0) ?></span></h3>

    <!-- Tabela Detalhada de IPs Instaurados -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>IP</th>
                <th>Origem</th>
                <th>Crimes</th>
                <th>Data de Instauração</th>
                <th>Vítima(s)</th>
                <th>Idade(s)</th>
                <th>Situação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ips)): ?>
                <tr>
                    <td colspan="7" class="text-center">Nenhum inquérito policial encontrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($ips as $ip): ?>
                    <tr>
                        <td><?= htmlspecialchars($ip['IP']) ?></td>
                        <td><?= htmlspecialchars($ip['Origem'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($ip['Crimes'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($ip['DataInstauracao']))) ?></td>
                        <td><?= htmlspecialchars($ip['Vitimas'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($ip['Idades'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($ip['Situacao'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    
<div class="mt-5">
    <h3>Inquéritos Policiais Remetidos ao Poder Judiciário: <span class="badge bg-primary"><?= htmlspecialchars($totalRemetidos) ?></span></h3>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>IP</th>
                <th>Origem</th>
                <th>Natureza</th>
                <th>Data</th>
                <th>Vítima(s)</th>
                <th>Idade(s)</th>
                <th>Situação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($remetidos)): ?>
                <tr>
                    <td colspan="7" class="text-center">Nenhum inquérito policial encontrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($remetidos as $remetido): ?>
                    <tr>
                        <td><?= htmlspecialchars($remetido['IP']) ?></td>
                        <td><?= htmlspecialchars($remetido['Origem'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($remetido['Natureza'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($remetido['Data']))) ?></td>
                        <td><?= htmlspecialchars($remetido['Vitimas'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($remetido['Idade'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($remetido['Situacao'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
    
<!-- Operações Policiais - Cautelares -->
<div class="mt-5">
    <h3>Operações Policiais - Cautelares</h3>
 <table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>RAI</th>
            <th>Tipo</th>
            <th>Resultado</th>
            <th>Envolvido(s)</th>
            <th>Data</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($operacoes)): ?>
            <tr>
                <td colspan="5" class="text-center">Nenhuma operação policial encontrada.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($operacoes as $operacao): ?>
                <tr>
                    <td><?= htmlspecialchars(!empty($operacao['RAI']) ? $operacao['RAI'] : 'Não Informado', ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= htmlspecialchars($operacao['Tipo'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($operacao['Resultado'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($operacao['Envolvido'] ?? 'Nenhum envolvido', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?= isset($operacao['DataCumprimento']) && strtotime($operacao['DataCumprimento'])
                            ? htmlspecialchars(date('d/m/Y', strtotime($operacao['DataCumprimento'])), ENT_QUOTES, 'UTF-8')
                            : 'N/A' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</div>




<div class="mt-5">
    <h3>Operações Policiais - Localização de Pessoas Desaparecidas</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>RAI</th>
                <th>Tipo</th>
                <th>Espécie</th>
                <th>Resultado</th>
                <th>Envolvido</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($desaparecidos)): ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhuma pessoa desaparecida encontrada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($desaparecidos as $desaparecido): ?>
                    <tr>
                        <td><?= htmlspecialchars($desaparecido['RAI'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($desaparecido['Tipo']) ?></td>
                        <td><?= htmlspecialchars($desaparecido['Especie']) ?></td>
                        <td>Cumprido</td>
                        <td><?= htmlspecialchars($desaparecido['Envolvidos'] ?? 'N/A') ?></td>
                        <td><?= $desaparecido['Data'] ? htmlspecialchars(date('d/m/Y', strtotime($desaparecido['Data']))) : 'N/A' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>



<div class="mt-5">
    <h3>Operações Policiais - Outras Diligências</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Número Procedimento</th>
                <th>Tipo</th>
                <th>Espécie</th>
                <th>Resultado</th>
                <th>Envolvido(s)</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($outrasDiligencias)): ?>
                <tr>
                    <td colspan="5" class="text-center">Nenhuma outra diligência encontrada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($outrasDiligencias as $diligencia): ?>
                    <tr>
                        <td><?= htmlspecialchars($diligencia['NumeroProcedimento']) ?></td>
                        <td><?= htmlspecialchars($diligencia['Tipo']) ?></td>
                        <td><?= htmlspecialchars($diligencia['Especie'] ?? 'N/A') ?></td>
                        <td>Cumprido</td>
                        <td><?= nl2br(htmlspecialchars($diligencia['Envolvidos'] ?? 'N/A')) ?></td> 
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($diligencia['Data']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>







</div>



<?php include '../includes/footer.php'; ?>
