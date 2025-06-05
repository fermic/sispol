<?php
session_start();
include '../includes/header.php';
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<div class='alert alert-danger text-center'>Você precisa estar logado para acessar esta página.</div>";
    include '../includes/footer.php';
    exit;
}

// Obter os valores para os filtros
$especies = $pdo->query("SELECT ID, Nome FROM ArmaEspecie ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
$calibres = $pdo->query("SELECT ID, Nome FROM ArmaCalibre ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
$marcas = $pdo->query("SELECT ID, Nome FROM ArmaMarca ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
$situacoes = $pdo->query("SELECT ID, Nome FROM SituacoesObjeto ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
$locais = $pdo->query("SELECT ID, Nome FROM LocaisArmazenagem ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);

// Campos de busca
$filtros = [
    'especie' => $_GET['especie'] ?? '',
    'calibre' => $_GET['calibre'] ?? '',
    'marca' => $_GET['marca'] ?? '',
    'situacao' => $_GET['situacao'] ?? '',
    'local' => $_GET['local'] ?? '',
    'numero_serie' => $_GET['numero_serie'] ?? '',
    'lacre' => $_GET['lacre'] ?? '',
    'possui_processo' => $_GET['possui_processo'] ?? '',
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_fim' => $_GET['data_fim'] ?? '',
];

// Contar o total de registros para paginação
$query_count = "
    SELECT COUNT(*) AS total
    FROM Objetos o
    INNER JOIN ArmasFogo a ON o.ID = a.ObjetoID
    INNER JOIN ArmaEspecie ae ON a.EspecieID = ae.ID
    INNER JOIN ArmaCalibre ac ON a.CalibreID = ac.ID
    INNER JOIN ArmaMarca am ON a.MarcaID = am.ID
    INNER JOIN ArmaModelo amo ON a.ModeloID = amo.ID
    INNER JOIN SituacoesObjeto s ON o.SituacaoID = s.ID
    INNER JOIN LocaisArmazenagem la ON o.LocalArmazenagemID = la.ID
    INNER JOIN Procedimentos p ON o.ProcedimentoID = p.ID
    LEFT JOIN ProcessosJudiciais pj ON a.ProcessoJudicialID = pj.ID
    WHERE 1=1
";

// Adiciona os filtros na contagem
if (!empty($filtros['especie'])) $query_count .= " AND ae.ID = :especie";
if (!empty($filtros['calibre'])) $query_count .= " AND ac.ID = :calibre";
if (!empty($filtros['marca'])) $query_count .= " AND am.ID = :marca";
if (!empty($filtros['situacao'])) $query_count .= " AND s.ID = :situacao";
if (!empty($filtros['local'])) $query_count .= " AND la.ID = :local";
if (!empty($filtros['numero_serie'])) $query_count .= " AND a.NumeroSerie LIKE :numero_serie";
if (!empty($filtros['lacre'])) $query_count .= " AND o.LacreAtual LIKE :lacre";
if ($filtros['possui_processo'] === 'sim') $query_count .= " AND a.ProcessoJudicialID IS NOT NULL";
if ($filtros['possui_processo'] === 'nao') $query_count .= " AND a.ProcessoJudicialID IS NULL";
// Adiciona filtros de período na contagem
if (!empty($filtros['data_inicio'])) $query_count .= " AND o.DataApreensao >= :data_inicio";
if (!empty($filtros['data_fim'])) $query_count .= " AND o.DataApreensao <= :data_fim";

// Prepara e executa a contagem
$stmt_count = $pdo->prepare($query_count);
foreach ($filtros as $key => $value) {
    if (!empty($value) && $key !== 'possui_processo') {
        if ($key === 'numero_serie' || $key === 'lacre') {
            $stmt_count->bindValue(":$key", "%$value%", PDO::PARAM_STR);
        } else if ($key === 'data_inicio' || $key === 'data_fim') {
            $stmt_count->bindValue(":$key", $value, PDO::PARAM_STR);
        } else {
            $stmt_count->bindValue(":$key", $value, PDO::PARAM_STR);
        }
    }
}
$stmt_count->execute();
$total_records = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

// Configuração de paginação
$records_per_page = 20;
$total_pages = ceil($total_records / $records_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Construção da query para listar armas
$query = "
    SELECT 
        o.ID AS ObjetoID,
        o.ProcedimentoID as ProcedimentoID,
        s.Nome AS Situacao,
        ae.Nome AS Especie,
        ac.Nome AS Calibre,
        am.Nome AS Marca,
        amo.Nome AS Modelo,
        a.NumeroSerie,
        o.LacreAtual,
        o.DataApreensao,
        la.Nome AS LocalArmazenagem,
        p.NumeroProcedimento,
        pj.Numero AS Processo
    FROM Objetos o
    INNER JOIN ArmasFogo a ON o.ID = a.ObjetoID
    INNER JOIN ArmaEspecie ae ON a.EspecieID = ae.ID
    INNER JOIN ArmaCalibre ac ON a.CalibreID = ac.ID
    INNER JOIN ArmaMarca am ON a.MarcaID = am.ID
    INNER JOIN ArmaModelo amo ON a.ModeloID = amo.ID
    INNER JOIN SituacoesObjeto s ON o.SituacaoID = s.ID
    INNER JOIN LocaisArmazenagem la ON o.LocalArmazenagemID = la.ID
    INNER JOIN Procedimentos p ON o.ProcedimentoID = p.ID
    LEFT JOIN ProcessosJudiciais pj ON a.ProcessoJudicialID = pj.ID
    WHERE 1=1
";

// Adiciona os filtros selecionados
if (!empty($filtros['especie'])) $query .= " AND ae.ID = :especie";
if (!empty($filtros['calibre'])) $query .= " AND ac.ID = :calibre";
if (!empty($filtros['marca'])) $query .= " AND am.ID = :marca";
if (!empty($filtros['situacao'])) $query .= " AND s.ID = :situacao";
if (!empty($filtros['local'])) $query .= " AND la.ID = :local";
if (!empty($filtros['numero_serie'])) $query .= " AND a.NumeroSerie LIKE :numero_serie";
if (!empty($filtros['lacre'])) $query .= " AND o.LacreAtual LIKE :lacre";
if ($filtros['possui_processo'] === 'sim') $query .= " AND a.ProcessoJudicialID IS NOT NULL";
if ($filtros['possui_processo'] === 'nao') $query .= " AND a.ProcessoJudicialID IS NULL";
// Adiciona filtros de período
if (!empty($filtros['data_inicio'])) $query .= " AND o.DataApreensao >= :data_inicio";
if (!empty($filtros['data_fim'])) $query .= " AND o.DataApreensao <= :data_fim";

// Ordenação e paginação
$query .= " ORDER BY o.DataApreensao DESC LIMIT :offset, :limit";

$stmt = $pdo->prepare($query);
foreach ($filtros as $key => $value) {
    if (!empty($value) && $key !== 'possui_processo') {
        if ($key === 'numero_serie' || $key === 'lacre') {
            $stmt->bindValue(":$key", "%$value%", PDO::PARAM_STR);
        } else if ($key === 'data_inicio' || $key === 'data_fim') {
            $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
        }
    }
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$armas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar se há algum filtro aplicado
$filtros_aplicados = array_filter($filtros, function($value) {
    return $value !== '';
});
$tem_filtros = !empty($filtros_aplicados);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Controle de Armas</title>
    <!-- Incluindo Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .form-control, .form-select {
            border-radius: 5px;
        }
        .btn {
            border-radius: 5px;
            padding: 8px 16px;
            font-weight: 500;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-success {
            background-color: #198754;
            border-color: #198754;
        }
        .table {
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #495057;
            font-weight: 600;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: 500;
        }
        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .collapse-toggle {
            cursor: pointer;
        }
        .filter-badge {
            background-color: #e7f5ff;
            color: #0d6efd;
            margin-right: 5px;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 5px;
        }
        .filter-badge i {
            margin-left: 5px;
            cursor: pointer;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4" style="max-width: 95%; margin: 0 auto;">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0"><i class="fas fa-gun me-2"></i> Armas de Fogo</h2>
                    <a href="adicionar_arma.php" class="btn btn-success"><i class="fas fa-plus me-1"></i> Nova Arma</a>
                </div>
                <div class="card-body">
                    <!-- Resumo da pesquisa -->
                    <?php if ($tem_filtros): ?>
                        <div class="mb-3">
                            <p class="mb-2"><strong><i class="fas fa-filter me-1"></i> Filtros aplicados:</strong></p>
                            <div class="filter-badges">
                                <?php if (!empty($filtros['especie'])): 
                                    $nome_especie = '';
                                    foreach ($especies as $especie) {
                                        if ($especie['ID'] == $filtros['especie']) {
                                            $nome_especie = $especie['Nome'];
                                            break;
                                        }
                                    }
                                ?>
                                    <span class="filter-badge">
                                        Espécie: <?= htmlspecialchars($nome_especie) ?>
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($filtros['calibre'])): 
                                    $nome_calibre = '';
                                    foreach ($calibres as $calibre) {
                                        if ($calibre['ID'] == $filtros['calibre']) {
                                            $nome_calibre = $calibre['Nome'];
                                            break;
                                        }
                                    }
                                ?>
                                    <span class="filter-badge">
                                        Calibre: <?= htmlspecialchars($nome_calibre) ?>
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($filtros['marca'])): 
                                    $nome_marca = '';
                                    foreach ($marcas as $marca) {
                                        if ($marca['ID'] == $filtros['marca']) {
                                            $nome_marca = $marca['Nome'];
                                            break;
                                        }
                                    }
                                ?>
                                    <span class="filter-badge">
                                        Marca: <?= htmlspecialchars($nome_marca) ?>
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($filtros['situacao'])): 
                                    $nome_situacao = '';
                                    foreach ($situacoes as $situacao) {
                                        if ($situacao['ID'] == $filtros['situacao']) {
                                            $nome_situacao = $situacao['Nome'];
                                            break;
                                        }
                                    }
                                ?>
                                    <span class="filter-badge">
                                        Situação: <?= htmlspecialchars($nome_situacao) ?>
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($filtros['local'])): 
                                    $nome_local = '';
                                    foreach ($locais as $local) {
                                        if ($local['ID'] == $filtros['local']) {
                                            $nome_local = $local['Nome'];
                                            break;
                                        }
                                    }
                                ?>
                                    <span class="filter-badge">
                                        Local: <?= htmlspecialchars($nome_local) ?>
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($filtros['numero_serie'])): ?>
                                    <span class="filter-badge">
                                        Nº Série: <?= htmlspecialchars($filtros['numero_serie']) ?>
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($filtros['lacre'])): ?>
                                    <span class="filter-badge">
                                        Lacre: <?= htmlspecialchars($filtros['lacre']) ?>
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($filtros['possui_processo'] === 'sim'): ?>
                                    <span class="filter-badge">
                                        Com processo judicial
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php elseif ($filtros['possui_processo'] === 'nao'): ?>
                                    <span class="filter-badge">
                                        Sem processo judicial
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])): ?>
                                    <span class="filter-badge">
                                        Período: <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?>
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php elseif (!empty($filtros['data_inicio'])): ?>
                                    <span class="filter-badge">
                                        A partir de: <?= date('d/m/Y', strtotime($filtros['data_inicio'])) ?>
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php elseif (!empty($filtros['data_fim'])): ?>
                                    <span class="filter-badge">
                                        Até: <?= date('d/m/Y', strtotime($filtros['data_fim'])) ?>
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <a href="armas.php" class="btn btn-sm btn-outline-secondary ms-2">
                                    <i class="fas fa-eraser me-1"></i> Limpar Todos
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Resultados -->
                    <div class="mb-3">
                        <p><strong>Total de registros:</strong> <?= $total_records ?></p>
                    </div>

                    <!-- Acordeão de filtros -->
                    <div class="accordion mb-4" id="filtrosAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="filtrosHeading">
                                <button class="accordion-button <?= $tem_filtros ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse" aria-expanded="<?= $tem_filtros ? 'true' : 'false' ?>" aria-controls="filtrosCollapse">
                                    <i class="fas fa-filter me-2"></i> Filtros de Busca
                                </button>
                            </h2>
                            <div id="filtrosCollapse" class="accordion-collapse collapse <?= $tem_filtros ? 'show' : '' ?>" aria-labelledby="filtrosHeading" data-bs-parent="#filtrosAccordion">
                                <div class="accordion-body">
                                    <!-- Formulário de filtros -->
                                    <form method="GET" class="mb-4">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="especie" class="form-label"><i class="fas fa-tag me-1"></i> Espécie</label>
                                                <select name="especie" id="especie" class="form-select">
                                                    <option value="">Todas</option>
                                                    <?php foreach ($especies as $especie): ?>
                                                        <option value="<?= $especie['ID'] ?>" <?= $filtros['especie'] == $especie['ID'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($especie['Nome']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="calibre" class="form-label"><i class="fas fa-bullseye me-1"></i> Calibre</label>
                                                <select name="calibre" id="calibre" class="form-select">
                                                    <option value="">Todos</option>
                                                    <?php foreach ($calibres as $calibre): ?>
                                                        <option value="<?= $calibre['ID'] ?>" <?= $filtros['calibre'] == $calibre['ID'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($calibre['Nome']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="marca" class="form-label"><i class="fas fa-trademark me-1"></i> Marca</label>
                                                <select name="marca" id="marca" class="form-select">
                                                    <option value="">Todas</option>
                                                    <?php foreach ($marcas as $marca): ?>
                                                        <option value="<?= $marca['ID'] ?>" <?= $filtros['marca'] == $marca['ID'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($marca['Nome']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="situacao" class="form-label"><i class="fas fa-info-circle me-1"></i> Situação</label>
                                                <select name="situacao" id="situacao" class="form-select">
                                                    <option value="">Todas</option>
                                                    <?php foreach ($situacoes as $situacao): ?>
                                                        <option value="<?= $situacao['ID'] ?>" <?= $filtros['situacao'] == $situacao['ID'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($situacao['Nome']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="local" class="form-label"><i class="fas fa-warehouse me-1"></i> Local de Armazenagem</label>
                                                <select name="local" id="local" class="form-select">
                                                    <option value="">Todos</option>
                                                    <?php foreach ($locais as $local): ?>
                                                        <option value="<?= $local['ID'] ?>" <?= $filtros['local'] == $local['ID'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($local['Nome']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="numero_serie" class="form-label"><i class="fas fa-hashtag me-1"></i> Número de Série</label>
                                                <input type="text" name="numero_serie" id="numero_serie" class="form-control" value="<?= htmlspecialchars($filtros['numero_serie']) ?>" placeholder="Número de Série">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="lacre" class="form-label"><i class="fas fa-seal me-1"></i> Lacre Atual</label>
                                                <input type="text" name="lacre" id="lacre" class="form-control" value="<?= htmlspecialchars($filtros['lacre']) ?>" placeholder="Lacre Atual">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="possui_processo" class="form-label"><i class="fas fa-gavel me-1"></i> Possui Processo Vinculado?</label>
                                                <select name="possui_processo" id="possui_processo" class="form-select">
                                                    <option value="">Todos</option>
                                                    <option value="sim" <?= $filtros['possui_processo'] == 'sim' ? 'selected' : '' ?>>Sim</option>
                                                    <option value="nao" <?= $filtros['possui_processo'] == 'nao' ? 'selected' : '' ?>>Não</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="data_inicio" class="form-label"><i class="fas fa-calendar-alt me-1"></i> Data Inicial</label>
                                                <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="data_fim" class="form-label"><i class="fas fa-calendar-alt me-1"></i> Data Final</label>
                                                <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= htmlspecialchars($filtros['data_fim']) ?>">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <button type="submit" class="btn btn-primary me-2">
                                                    <i class="fas fa-search me-1"></i> Filtrar
                                                </button>
                                                <a href="armas.php" class="btn btn-secondary">
                                                    <i class="fas fa-eraser me-1"></i> Limpar Filtros
                                                </a>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-outline-success">
                                                    <i class="fas fa-file-excel me-1"></i> Exportar Excel
                                                </button>
                                                <button type="button" class="btn btn-outline-danger ms-2">
                                                    <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela responsiva de armas -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Situação</th>
                                    <th>Espécie</th>
                                    <th>Calibre</th>
                                    <th>Marca/Modelo</th>
                                    <th>Nº Série</th>
                                    <th>Lacre</th>
                                    <th>Data Apreensão</th>
                                    <th>Local</th>
                                    <th>Procedimento</th>
                                    <th>Processo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($armas): ?>
                                    <?php foreach ($armas as $arma): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $situacao_classe = 'bg-secondary';
                                                if (strtolower($arma['Situacao']) === 'apreendida') {
                                                    $situacao_classe = 'bg-danger';
                                                } elseif (strtolower($arma['Situacao']) === 'restituída') {
                                                    $situacao_classe = 'bg-success';
                                                } elseif (strtolower($arma['Situacao']) === 'em perícia') {
                                                    $situacao_classe = 'bg-warning text-dark';
                                                } elseif (strtolower($arma['Situacao']) === 'em custódia') {
                                                    $situacao_classe = 'bg-info text-dark';
                                                }
                                                ?>
                                                <span class="status-badge <?= $situacao_classe ?> text-white">
                                                    <?= htmlspecialchars($arma['Situacao']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($arma['Especie']) ?></td>
                                            <td><?= htmlspecialchars($arma['Calibre']) ?></td>
                                            <td><?= htmlspecialchars($arma['Marca']) ?> / <?= htmlspecialchars($arma['Modelo']) ?></td>
                                            <td><?= htmlspecialchars($arma['NumeroSerie']) ?></td>
                                            <td><?= htmlspecialchars($arma['LacreAtual']) ?></td>
                                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($arma['DataApreensao']))) ?></td>
                                            <td><?= htmlspecialchars($arma['LocalArmazenagem']) ?></td>
                                            <td>
                                                <?php if (!empty($arma['NumeroProcedimento'])): ?>
                                                    <a href="ver_procedimento.php?id=<?= htmlspecialchars($arma['ProcedimentoID']) ?>" class="text-primary">
                                                        <i class="fas fa-folder-open me-1"></i>
                                                        <?= htmlspecialchars($arma['NumeroProcedimento']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="fas fa-times-circle me-1"></i> Não informado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($arma['Processo'])): ?>
                                                    <i class="fas fa-gavel me-1 text-success"></i>
                                                    <?= htmlspecialchars($arma['Processo']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="fas fa-times-circle me-1"></i> Sem processo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="ver_arma.php?id=<?= $arma['ObjetoID'] ?>" class="btn btn-sm btn-info" title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="editar_arma.php?id=<?= $arma['ObjetoID'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="historico_arma.php?id=<?= $arma['ObjetoID'] ?>" class="btn btn-sm btn-secondary" title="Histórico">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i> Nenhuma arma encontrada com os critérios selecionados.
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação aprimorada -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Paginação">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" tabindex="-1">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" tabindex="-1">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                                
                                <?php 
                                // Mostrar no máximo 5 links de páginas
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $start_page + 4);
                                $start_page = max(1, $end_page - 4);
                                
                                for ($i = $start_page; $i <= $end_page; $i++): 
                                ?>
                                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                                <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                    <!-- Informação de paginação -->
                    <div class="text-center text-muted small">
                        Exibindo registros <?= min(($current_page - 1) * $records_per_page + 1, $total_records) ?> 
                        a <?= min($current_page * $records_per_page, $total_records) ?> 
                        de <?= $total_records ?> no total
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remover filtros individuais
    document.querySelectorAll('.filter-badge i').forEach(function(icon) {
        icon.addEventListener('click', function() {
            const badge = this.parentElement;
            const text = badge.textContent.trim();
            
            let param = '';
            if (text.startsWith('Espécie:')) param = 'especie';
            else if (text.startsWith('Calibre:')) param = 'calibre';
            else if (text.startsWith('Marca:')) param = 'marca';
            else if (text.startsWith('Situação:')) param = 'situacao';
            else if (text.startsWith('Local:')) param = 'local';
            else if (text.startsWith('Nº Série:')) param = 'numero_serie';
            else if (text.startsWith('Lacre:')) param = 'lacre';
            else if (text.includes('processo judicial')) param = 'possui_processo';
            else if (text.startsWith('Período:') || text.startsWith('A partir de:') || text.startsWith('Até:')) {
                // Remover ambos os parâmetros de data
                const url = new URL(window.location.href);
                url.searchParams.delete('data_inicio');
                url.searchParams.delete('data_fim');
                window.location.href = url.toString();
                return;
            }
            
            if (param) {
                const url = new URL(window.location.href);
                url.searchParams.delete(param);
                window.location.href = url.toString();
            }
        });
    });
    
    // Habilitar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>