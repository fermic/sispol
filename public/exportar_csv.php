<?php
// Configurar cabeçalhos para download do arquivo CSV com UTF-8
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=procedimentos.csv');

// Conexão com o banco de dados
include '../config/db.php';

// Abrir saída para gerar o CSV
$output = fopen('php://output', 'w');

// Escrever BOM para suporte UTF-8 no Excel
fwrite($output, "\xEF\xBB\xBF");

// Cabeçalhos do CSV
$headers = [
    'Tipo',
    'Número',
    'Situação',
    'Origem',
    'Data de Instauração',
    'Vítimas',
    'Idades',
    'Investigados',
    'Crimes e Naturezas',
    'Meios Empregados',
    'RAIs',
    'Processos Judiciais',
    'Requisição do MP?',
    'Data da Remessa',
    'Motivo',
    'Escrivão'
];
fputcsv($output, $headers, ';');

// Definir parâmetros de consulta
$ano = $_GET['ano'] ?? date('Y'); // Ano padrão é o ano atual
$tipo = $_GET['tipo'] ?? '';

// Determinar o filtro de ano
$whereAno = ($ano !== 'todos') ? "YEAR(p.DataInstauracao) = :ano" : "1=1";

// Consulta SQL para buscar os dados
$query = "
    SELECT 
    tp.Nome AS TipoProcedimento,
    p.NumeroProcedimento,
    sp.Nome AS SituacaoProcedimento,
    op.Nome AS Origem,
    p.DataInstauracao,
    (
        SELECT GROUP_CONCAT(DISTINCT v.Nome SEPARATOR '\n') 
        FROM Vitimas v 
        WHERE v.ProcedimentoID = p.ID
    ) AS Vitimas,
    (
        SELECT GROUP_CONCAT(DISTINCT v.Idade SEPARATOR '\n')
        FROM Vitimas v
        WHERE v.ProcedimentoID = p.ID
    ) AS IdadesDasVitimas,
    (
        SELECT GROUP_CONCAT(DISTINCT i.Nome SEPARATOR '\n') 
        FROM Investigados i 
        WHERE i.ProcedimentoID = p.ID
    ) AS Investigados,
    (
        SELECT GROUP_CONCAT(DISTINCT CONCAT(c.Nome, ' - ', vc.Modalidade) SEPARATOR '\n')
        FROM Vitimas_Crimes vc
        LEFT JOIN Crimes c ON vc.CrimeID = c.ID
        WHERE vc.VitimaID IN (
            SELECT v.ID FROM Vitimas v WHERE v.ProcedimentoID = p.ID
        )
    ) AS CrimesENaturezas,
    (
        SELECT GROUP_CONCAT(DISTINCT me.Nome SEPARATOR ', ') 
        FROM ProcedimentosMeiosEmpregados pme 
        LEFT JOIN MeiosEmpregados me ON pme.MeioEmpregadoID = me.ID 
        WHERE pme.ProcedimentoID = p.ID
    ) AS MeiosEmpregados,
    (
        SELECT GROUP_CONCAT(DISTINCT r.Numero SEPARATOR '\n')
        FROM RAIs r
        WHERE r.ProcedimentoID = p.ID
    ) AS RAIs,
    (
        SELECT GROUP_CONCAT(DISTINCT pj.Numero SEPARATOR '\n')
        FROM ProcessosJudiciais pj
        WHERE pj.ProcedimentoID = p.ID
    ) AS ProcessosJudiciais,
    (
        SELECT DATE_FORMAT(MAX(m.DataConclusao), '%d/%m/%Y')
        FROM Movimentacoes m
        WHERE m.ProcedimentoID = p.ID 
          AND m.TipoID = 5
          AND m.Situacao = 'Finalizado'
    ) AS DataRemessa,
    (
        SELECT COUNT(*)
        FROM Movimentacoes m
        WHERE m.ProcedimentoID = p.ID 
          AND m.TipoID = (SELECT ID FROM TiposMovimentacao WHERE Nome = 'Requisição MP')
          AND m.Situacao = 'Em andamento'
    ) AS PossuiRequisicaoMP,
    p.MotivoAparente AS Motivo,
    uEscrivao.Nome AS Escrivao
FROM Procedimentos p
LEFT JOIN TiposProcedimento tp ON p.TipoID = tp.ID
LEFT JOIN OrigensProcedimentos op ON p.OrigemID = op.ID
LEFT JOIN Usuarios uEscrivao ON p.EscrivaoID = uEscrivao.ID
LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID
WHERE $whereAno
" . ($tipo ? " AND p.TipoID = :tipo" : "") . "
ORDER BY p.DataInstauracao ASC
";

// Preparar e executar a consulta
$stmt = $pdo->prepare($query);
if ($ano !== 'todos') {
    $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
}
if ($tipo) {
    $stmt->bindValue(':tipo', $tipo, PDO::PARAM_INT);
}
$stmt->execute();
$procedimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Escrever os dados no CSV
foreach ($procedimentos as $procedimento) {
    // Converter Possui Requisição do MP para "SIM" ou "NÃO"
    $possuiRequisicaoMP = ($procedimento['PossuiRequisicaoMP'] > 0) ? 'SIM' : 'NÃO';

    fputcsv($output, [
        $procedimento['TipoProcedimento'] ?? 'N/A',
        $procedimento['NumeroProcedimento'] ?? 'N/A',
        $procedimento['SituacaoProcedimento'] ?? 'N/A',
        $procedimento['Origem'] ?? 'N/A',
        $procedimento['DataInstauracao'] ? date('d/m/Y', strtotime($procedimento['DataInstauracao'])) : 'N/A',
        $procedimento['Vitimas'] ?? 'N/A', // Quebra de linha em vítimas
        $procedimento['IdadesDasVitimas'] ?? 'N/A', // Quebra de linha em idades
        $procedimento['Investigados'] ?? 'N/A',
        $procedimento['CrimesENaturezas'] ?? 'N/A', // Crimes e Naturezas combinados
        $procedimento['MeiosEmpregados'] ?? 'N/A',
        $procedimento['RAIs'] ?? 'N/A',
        $procedimento['ProcessosJudiciais'] ?? 'N/A',
        $possuiRequisicaoMP,
        $procedimento['DataRemessa'] ?? 'N/A',
        $procedimento['Motivo'] ?? 'N/A',
        $procedimento['Escrivao'] ?? 'N/A',
    ], ';');
}

// Fechar saída
fclose($output);
exit;
?>
