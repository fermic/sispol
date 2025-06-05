<?php
include '../includes/header.php'; // Inclui configurações globais

// Inicializar a query base
$query = "
    SELECT 
        p.ID AS ProcedimentoID,
        p.NumeroProcedimento,
        sp.Nome AS Situacao,
        tp.Nome AS Tipo,
        op.Nome AS Origem,
        p.Dependente, -- Incluído o campo Dependente
        GROUP_CONCAT(DISTINCT me.Nome SEPARATOR ', ') AS MeiosEmpregados,
        (SELECT GROUP_CONCAT(DISTINCT c.Nome SEPARATOR ', ') 
         FROM Vitimas_Crimes vc
         JOIN Crimes c ON vc.CrimeID = c.ID
         WHERE vc.VitimaID IN (
             SELECT v.ID FROM Vitimas v WHERE v.ProcedimentoID = p.ID
         )) AS Crimes,
        (SELECT GROUP_CONCAT(DISTINCT v.Nome SEPARATOR ', ') 
         FROM Vitimas v 
         WHERE v.ProcedimentoID = p.ID) AS Vitimas,
        (SELECT GROUP_CONCAT(DISTINCT i.Nome SEPARATOR ', ') 
         FROM Investigados i 
         WHERE i.ProcedimentoID = p.ID) AS Investigados,
        GROUP_CONCAT(DISTINCT tm.Nome SEPARATOR ', ') AS TiposMovimentacao
    FROM Procedimentos p
    LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID
    LEFT JOIN TiposProcedimento tp ON p.TipoID = tp.ID
    LEFT JOIN OrigensProcedimentos op ON p.OrigemID = op.ID
    LEFT JOIN ProcedimentosMeiosEmpregados pme ON p.ID = pme.ProcedimentoID
    LEFT JOIN MeiosEmpregados me ON pme.MeioEmpregadoID = me.ID
    LEFT JOIN Movimentacoes m ON m.ProcedimentoID = p.ID
    LEFT JOIN TiposMovimentacao tm ON m.TipoID = tm.ID
    WHERE 1=1
";

// Inicializar array de parâmetros
$params = [];

// Adicionar filtros dinamicamente
if (!empty($_GET['situacao_id'])) {
    $query .= " AND sp.ID = :situacao_id";
    $params['situacao_id'] = $_GET['situacao_id'];
}

if (!empty($_GET['tipo_id'])) {
    $query .= " AND tp.ID = :tipo_id";
    $params['tipo_id'] = $_GET['tipo_id'];
}

if (!empty($_GET['origem_id'])) {
    $query .= " AND op.ID = :origem_id";
    $params['origem_id'] = $_GET['origem_id'];
}

if (!empty($_GET['data_fato_inicio'])) {
    $query .= " AND p.DataFato >= :data_fato_inicio";
    $params['data_fato_inicio'] = $_GET['data_fato_inicio'];
}

if (!empty($_GET['data_fato_fim'])) {
    $query .= " AND p.DataFato <= :data_fato_fim";
    $params['data_fato_fim'] = $_GET['data_fato_fim'];
}

if (!empty($_GET['data_instauracao_inicio'])) {
    $query .= " AND p.DataInstauracao >= :data_instauracao_inicio";
    $params['data_instauracao_inicio'] = $_GET['data_instauracao_inicio'];
}

if (!empty($_GET['data_instauracao_fim'])) {
    $query .= " AND p.DataInstauracao <= :data_instauracao_fim";
    $params['data_instauracao_fim'] = $_GET['data_instauracao_fim'];
}

if (!empty($_GET['escrivao_id'])) {
    $query .= " AND p.EscrivaoID = :escrivao_id";
    $params['escrivao_id'] = $_GET['escrivao_id'];
}

if (!empty($_GET['delegado_id'])) {
    $query .= " AND p.DelegadoID = :delegado_id";
    $params['delegado_id'] = $_GET['delegado_id'];
}

if (!empty($_GET['meios_empregados'])) {
    $meioIds = array_map('intval', $_GET['meios_empregados']);
    $query .= " AND EXISTS (
        SELECT 1
        FROM ProcedimentosMeiosEmpregados pme
        WHERE pme.ProcedimentoID = p.ID
          AND pme.MeioEmpregadoID IN (" . implode(',', $meioIds) . ")
    )";
}


if (!empty($_GET['crimes']) && !empty($_GET['modalidade'])) {
    $crimeIds = array_map('intval', $_GET['crimes']);
    $modalidades = is_array($_GET['modalidade']) ? $_GET['modalidade'] : [$_GET['modalidade']];
    $placeholders = [];

    foreach ($modalidades as $index => $modalidade) {
        $placeholder = ":modalidade_$index";
        $placeholders[] = $placeholder;
        $params[$placeholder] = $modalidade;
    }

    $query .= " AND EXISTS (
        SELECT 1
        FROM Vitimas_Crimes vc
        JOIN Crimes c ON vc.CrimeID = c.ID
        JOIN Vitimas v ON vc.VitimaID = v.ID
        WHERE v.ProcedimentoID = p.ID
          AND vc.CrimeID IN (" . implode(',', $crimeIds) . ")
          AND vc.Modalidade IN (" . implode(',', $placeholders) . ")
    )";
} elseif (!empty($_GET['crimes'])) {
    $crimeIds = array_map('intval', $_GET['crimes']);
    $query .= " AND EXISTS (
        SELECT 1
        FROM Vitimas_Crimes vc
        JOIN Crimes c ON vc.CrimeID = c.ID
        WHERE vc.VitimaID IN (
            SELECT v.ID FROM Vitimas v WHERE v.ProcedimentoID = p.ID
        )
        AND vc.CrimeID IN (" . implode(',', $crimeIds) . ")
    )";
} elseif (!empty($_GET['modalidade'])) {
    $modalidades = is_array($_GET['modalidade']) ? $_GET['modalidade'] : [$_GET['modalidade']];
    $placeholders = [];
    foreach ($modalidades as $index => $modalidade) {
        $placeholder = ":modalidade_$index";
        $placeholders[] = $placeholder;
        $params[$placeholder] = $modalidade;
    }
    $query .= " AND EXISTS (
        SELECT 1
        FROM Vitimas_Crimes vc
        JOIN Vitimas v ON vc.VitimaID = v.ID
        WHERE v.ProcedimentoID = p.ID
        AND vc.Modalidade IN (" . implode(',', $placeholders) . ")
    )";
}



if (!empty($_GET['movimentacao_tipo'])) {
    $query .= " AND m.TipoID = :movimentacao_tipo";
    $params['movimentacao_tipo'] = $_GET['movimentacao_tipo'];
}

if (!empty($_GET['movimentacao_situacao'])) {
    if ($_GET['movimentacao_situacao'] === 'Atrasada') {
        $query .= " AND m.DataVencimento < CURDATE() AND m.Situacao != 'Finalizada'";
    } else {
        $query .= " AND m.Situacao = :movimentacao_situacao";
        $params['movimentacao_situacao'] = $_GET['movimentacao_situacao'];
    }
}

if (!empty($_GET['movimentacao_data_inicio'])) {
    $query .= " AND m.DataVencimento >= :movimentacao_data_inicio";
    $params['movimentacao_data_inicio'] = $_GET['movimentacao_data_inicio'];
}

if (!empty($_GET['movimentacao_data_fim'])) {
    $query .= " AND m.DataVencimento <= :movimentacao_data_fim";
    $params['movimentacao_data_fim'] = $_GET['movimentacao_data_fim'];
}

// Agrupar resultados
$query .= " GROUP BY p.ID";



// Preparar e executar a consulta
$stmt = $pdo->prepare($query);

file_put_contents('debug_sql.log', $query . PHP_EOL . print_r($params, true), FILE_APPEND);

foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Contagem de registros
$totalRegistros = count($resultados);



?>

<div class="container mt-5">
    <h1 class="text-center">Resultados da Pesquisa Avançada</h1>
    <p class="text-left text-muted">
    Total de Registros Encontrados: <strong><?= htmlspecialchars($totalRegistros) ?></strong>
</p>


    <?php if (empty($resultados)): ?>
        <p class="text-center text-muted">Nenhum procedimento encontrado.</p>
    <?php else: ?>
        <table class="table table-striped table-sm mt-4">
            <thead class="table-dark">
                <tr>
                    <th>Situação</th>
                    <th>Tipo</th>
                    <th>Origem</th>
                    <th>Número do Procedimento</th>
                    <th>Meios Empregados</th>
                    <th>Crimes</th>
                    <th>Vítimas</th>
                    <th>Investigados</th>
                    <th>Ações</th>
                </tr>
            </thead>
<tbody>
    <?php foreach ($resultados as $resultado): ?>
        <tr class="<?= $resultado['Dependente'] == 1 ? 'table-danger' : '' ?>">
            <td><?= htmlspecialchars($resultado['Situacao'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($resultado['Tipo'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($resultado['Origem'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($resultado['NumeroProcedimento'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($resultado['MeiosEmpregados'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($resultado['Crimes'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($resultado['Vitimas'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($resultado['Investigados'] ?? 'N/A') ?></td>
            <td>
                <a href="ver_procedimento.php?id=<?= htmlspecialchars($resultado['ProcedimentoID']) ?>" class="btn btn-sm btn-primary">Ver</a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>


        </table>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
