<?php
// Caminho onde o backup será salvo
$backup_dir = __DIR__ . "/backups";
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// Nome do arquivo de backup
$backup_file = $backup_dir . "/backup_" . date('Y-m-d') . ".csv";

// URL pública do backup
$backup_url = "http://gestao.seg.br/sistema/public/backups/" . basename($backup_file); // Certifique-se de que essa URL funciona!

// Conexão com o banco de dados
include '../config/db.php';

// Abrir saída para gerar o CSV
$output = fopen($backup_file, 'w');

// Escrever BOM para suporte UTF-8 no Excel
fwrite($output, "\xEF\xBB\xBF");

// Cabeçalhos do CSV
$headers = [
    'Tipo', 'Número', 'Situação', 'Origem', 'Data de Instauração', 
    'Vítimas', 'Idades', 'Investigados', 'Crimes e Naturezas', 
    'Meios Empregados', 'RAIs', 'Processos Judiciais', 
    'Requisição do MP?', 'Data da Remessa', 'Motivo', 'Escrivão'
];
fputcsv($output, $headers, ';');

// Consulta SQL **sem restrição de ano** para trazer **todos os registros**
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
ORDER BY p.DataInstauracao ASC
";

// Preparar e executar a consulta **sem restrição de ano**
$stmt = $pdo->prepare($query);
$stmt->execute();
$procedimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Escrever os dados no CSV
foreach ($procedimentos as $procedimento) {
    // Converter "Possui Requisição do MP" para "SIM" ou "NÃO"
    $possuiRequisicaoMP = ($procedimento['PossuiRequisicaoMP'] > 0) ? 'SIM' : 'NÃO';

    fputcsv($output, [
        $procedimento['TipoProcedimento'] ?? 'N/A',
        $procedimento['NumeroProcedimento'] ?? 'N/A',
        $procedimento['SituacaoProcedimento'] ?? 'N/A',
        $procedimento['Origem'] ?? 'N/A',
        $procedimento['DataInstauracao'] ? date('d/m/Y', strtotime($procedimento['DataInstauracao'])) : 'N/A',
        $procedimento['Vitimas'] ?? 'N/A',
        $procedimento['IdadesDasVitimas'] ?? 'N/A',
        $procedimento['Investigados'] ?? 'N/A',
        $procedimento['CrimesENaturezas'] ?? 'N/A',
        $procedimento['MeiosEmpregados'] ?? 'N/A',
        $procedimento['RAIs'] ?? 'N/A',
        $procedimento['ProcessosJudiciais'] ?? 'N/A',
        $possuiRequisicaoMP,
        $procedimento['DataRemessa'] ?? 'N/A',
        $procedimento['Motivo'] ?? 'N/A',
        $procedimento['Escrivao'] ?? 'N/A',
    ], ';');
}

// Fechar saída do CSV
fclose($output);

// 🔹 Lista de números para envio do backup
$numeros = [
    "5564999225006",  // Número 1
    "5562993205072"   // Número 2
];

// 🔹 URL da API do WhatsApp Web JS
$whatsapp_api_url = "http://85.239.238.30:3000/send-file";

// 🔹 Enviar o backup para cada número
foreach ($numeros as $numero) {
    $data = [
        "number" => $numero,
        "fileUrl" => $backup_url,
        "caption" => "Segue o backup completo dos procedimentos (todos os anos)."
    ];

    $ch = curl_init($whatsapp_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);

    echo "Backup enviado para $numero. Resposta: " . $response . "\n";
}
?>
