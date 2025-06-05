<?php
require_once '../config/db.php'; // Conexão com o banco de dados
require_once '../assets/tcpdf/tcpdf.php'; // Inclua a biblioteca TCPDF

// Obter os filtros de data
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
$dataFim = $_GET['data_fim'] ?? date('Y-m-d');

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
";


$stmtDesaparecidos = $pdo->prepare($desaparecidosQuery);
$stmtDesaparecidos->execute(['dataInicio' => $dataInicio, 'dataFim' => $dataFim]);
$desaparecidos = $stmtDesaparecidos->fetchAll(PDO::FETCH_ASSOC);



// Consulta para obter as operações policiais de outras diligências
$outrasDiligenciasQuery = "
    SELECT 
        tm.Nome AS Tipo,
        p.NumeroProcedimento AS RAI,
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


// Demais consultas (IPs, remetidos, desaparecidos, outras diligências, etc.) podem ser replicadas aqui

// Gerar o HTML para o PDF
$html = '<h1>Estatísticas do Grupo de Investigação de Homicídios de Rio Verde</h1>';
$html .= '<br>';
$html .= '<h3>Período: ' . htmlspecialchars(date('d/m/Y', strtotime($dataInicio))) . ' a ' . htmlspecialchars(date('d/m/Y', strtotime($dataFim))) . '</h3>';
$html .= '<br>';




// Estatísticas de Procedimentos
$html .= '<h2>Estatísticas de Procedimentos</h2>';
$html .= '
<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
            <th>IP Instaurados</th>
            <th>IP Remetidos</th>
            <th>Com Autoria</th>
            <th>Sem Autoria</th>
            <th>Cumprimento de Diligências</th>
        </tr>
    </thead>
    <tbody>
        <tr style="text-align: center; font-weight: bold;">
            <td>' . htmlspecialchars($stats['IPInstaurados'] ?? 0) . '</td>
            <td>' . htmlspecialchars($stats['IPRemetidos'] ?? 0) . '</td>
            <td>' . htmlspecialchars($stats['ComAutoria'] ?? 0) . '</td>
            <td>' . htmlspecialchars($stats['SemAutoria'] ?? 0) . '</td>
            <td>' . htmlspecialchars($stats['CumprimentoDiligencias'] ?? 0) . '</td>
        </tr>
    </tbody>
</table><br>';

// Estatísticas de Cautelares
// Estatísticas de Cautelares
$html .= '<h2>Estatísticas de Cautelares por Tipo</h2>';
$html .= '
<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
            <th>Tipo de Cautelar</th>
            <th>Total Solicitadas</th>
            <th>Total Cumpridas</th>
        </tr>
    </thead>
    <tbody>';
if (empty($cautelaresDetalhadas)) {
    $html .= '<tr style="text-align: center; font-weight: bold;">
        <td colspan="3">Nenhum dado encontrado.</td>
    </tr>';
} else {
    foreach ($cautelaresDetalhadas as $cautelar) {
        $html .= '
        <tr style="text-align: center;">
            <td>' . htmlspecialchars($cautelar['TipoCautelar'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($cautelar['TotalSolicitadas'] ?? 0) . '</td>
            <td>' . htmlspecialchars($cautelar['TotalCumpridas'] ?? 0) . '</td>
        </tr>';
    }
}
$html .= '</tbody>
</table><br>';


// Tabela de Inquéritos Policiais Instaurados
$html .= '<h2>Inquéritos Policiais Instaurados</h2>';
$html .= '
<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
            <th>IP</th>
            <th>Origem</th>
            <th>Crimes</th>
            <th>Data de Instauração</th>
            <th>Vítima(s)</th>
            <th>Idade(s)</th>
            <th>Situação</th>
        </tr>
    </thead>
    <tbody>';
if (empty($ips)) {
    $html .= '<tr>
        <td colspan="7" style="text-align:center;">Nenhum inquérito policial encontrado.</td>
    </tr>';
} else {
    foreach ($ips as $ip) {
        $html .= '<tr>
            <td>' . htmlspecialchars($ip['IP']) . '</td>
            <td>' . htmlspecialchars($ip['Origem'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($ip['Crimes'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars(date('d/m/Y', strtotime($ip['DataInstauracao']))) . '</td>
            <td>' . htmlspecialchars($ip['Vitimas'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($ip['Idades'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($ip['Situacao'] ?? 'N/A') . '</td>
        </tr>';
    }
}
$html .= '</tbody>
</table><br>';


// Tabela de Inquéritos Policiais Remetidos ao Poder Judiciário
$html .= '<h2>Inquéritos Policiais Remetidos ao Poder Judiciário</h2>';
$html .= '
<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
            <th>IP</th>
            <th>Origem</th>
            <th>Natureza</th>
            <th>Data</th>
            <th>Vítima(s)</th>
            <th>Idade(s)</th>
            <th>Situação</th>
        </tr>
    </thead>
    <tbody>';
if (empty($remetidos)) {
    $html .= '<tr>
        <td colspan="7" style="text-align:center;">Nenhum inquérito policial remetido encontrado.</td>
    </tr>';
} else {
    foreach ($remetidos as $remetido) {
        $html .= '<tr>
            <td>' . htmlspecialchars($remetido['IP']) . '</td>
            <td>' . htmlspecialchars($remetido['Origem'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($remetido['Natureza'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars(date('d/m/Y', strtotime($remetido['Data']))) . '</td>
            <td>' . htmlspecialchars($remetido['Vitimas'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($remetido['Idade'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($remetido['Situacao'] ?? 'N/A') . '</td>
        </tr>';
    }
}
$html .= '</tbody>
</table><br>';


// Operações Policiais - Cautelares
$html .= '<h2>Operações Policiais - Cautelares</h2>';
$html .= '
<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
            <th>RAI</th>
            <th>Tipo</th>
            <th>Resultado</th>
            <th>Envolvido(s)</th>
            <th>Data</th>
        </tr>
    </thead>
    <tbody>';
if (empty($operacoes)) {
    $html .= '
        <tr>
            <td colspan="5" style="text-align: center;">Nenhuma operação policial encontrada.</td>
        </tr>';
} else {
    foreach ($operacoes as $operacao) {
        $html .= '
        <tr style="text-align: center;">
            <td>' . htmlspecialchars($operacao['RAI'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($operacao['Tipo'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($operacao['Resultado'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($operacao['Envolvido'] ?? 'Nenhum envolvido', ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . (isset($operacao['DataCumprimento']) && strtotime($operacao['DataCumprimento'])
                ? htmlspecialchars(date('d/m/Y', strtotime($operacao['DataCumprimento'])), ENT_QUOTES, 'UTF-8')
                : 'N/A') . '</td>
        </tr>';
    }
}
$html .= '</tbody>
</table><br>';


// Tabela de Operações Policiais - Localização de Pessoas Desaparecidas
$html .= '<h2>Operações Policiais - Localização de Pessoas Desaparecidas</h2>';
$html .= '
<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
            <th>RAI</th>
            <th>Tipo</th>
            <th>Espécie</th>
            <th>Resultado</th>
            <th>Envolvido</th>
            <th>Data</th>
        </tr>
    </thead>
    <tbody>';
if (empty($desaparecidos)) {
    $html .= '<tr>
        <td colspan="6" style="text-align:center;">Nenhuma pessoa desaparecida encontrada.</td>
    </tr>';
} else {
    foreach ($desaparecidos as $desaparecido) {
        $html .= '<tr>
            <td>' . htmlspecialchars($desaparecido['RAI'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($desaparecido['Tipo'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($desaparecido['Especie'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($desaparecido['Resultado'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($desaparecido['Envolvidos'] ?? 'N/A') . '</td>
            <td>' . ($desaparecido['Data'] ? htmlspecialchars(date('d/m/Y', strtotime($desaparecido['Data']))) : 'N/A') . '</td>
        </tr>';
    }
}
$html .= '</tbody>
</table><br>';


// Tabela de Operações Policiais - Outras Diligências
$html .= '<h2>Operações Policiais - Outras Diligências</h2>';
$html .= '
<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
            <th>RAI</th>
            <th>Tipo</th>
            <th>Espécie</th>
            <th>Resultado</th>
            <th>Envolvido(s)</th>
            <th>Data</th>
        </tr>
    </thead>
    <tbody>';
if (empty($outrasDiligencias)) {
    $html .= '<tr>
        <td colspan="6" style="text-align:center;">Nenhuma outra diligência encontrada.</td>
    </tr>';
} else {
    foreach ($outrasDiligencias as $diligencia) {
        $html .= '<tr>
            <td>' . htmlspecialchars($diligencia['RAI'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($diligencia['Tipo'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($diligencia['Especie'] ?? 'N/A') . '</td>
            <td>Cumprido</td>
            <td>' . nl2br(htmlspecialchars($diligencia['Envolvidos'] ?? 'N/A')) . '</td>
            <td>' . htmlspecialchars(date('d/m/Y', strtotime($diligencia['Data']))) . '</td>
        </tr>';
    }
}
$html .= '</tbody>
</table><br>';

// Configuração do TCPDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false); // 'L' para paisagem
$pdf->setPrintHeader(false); // Remover cabeçalho
$pdf->setPrintFooter(false); // Remover rodapé
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);
$pdf->writeHTML($html);

// Saída do PDF
$pdf->Output('relatorio_completo.pdf', 'D'); // Download do PDF
