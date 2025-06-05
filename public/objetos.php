<?php
require_once '../config/db.php';
session_start();

// Verificar se o usuário está logado
$usuarioID = $_SESSION['usuario_id'] ?? null;
if (!$usuarioID) {
    $_SESSION['error_message'] = "Você precisa estar logado para acessar esta página.";
    header("Location: login.php");
    exit;
}

// Função para exportar CSV (deve estar antes de qualquer saída HTML)
function exportarCSV($pdo, $searchTerm, $tipoObjeto, $situacaoID, $procedimentoID, $dataInicio, $dataFim, $campoOrdenacao, $direcao) {
    // Query para exportação (sem LIMIT)
    $query = "
        SELECT 
            o.ID,
            o.Descricao,
            o.Quantidade,
            DATE_FORMAT(o.DataApreensao, '%d/%m/%Y') as DataApreensao,
            o.LacreAtual,
            t.Nome as TipoObjeto,
            s.Nome as Situacao,
            CONCAT(tp.Nome, ' ', p.NumeroProcedimento) as Procedimento,
            (
                SELECT COUNT(*) 
                FROM MovimentacoesObjeto mo 
                WHERE mo.ObjetoID = o.ID
            ) as TotalMovimentacoes,
            (
                SELECT DATE_FORMAT(mo.DataMovimentacao, '%d/%m/%Y %H:%i')
                FROM MovimentacoesObjeto mo 
                WHERE mo.ObjetoID = o.ID 
                ORDER BY mo.DataMovimentacao DESC 
                LIMIT 1
            ) as UltimaMovimentacao
        FROM Objetos o
        LEFT JOIN TiposObjeto t ON o.TipoObjetoID = t.ID
        LEFT JOIN SituacoesObjeto s ON o.SituacaoID = s.ID
        LEFT JOIN Procedimentos p ON o.ProcedimentoID = p.ID
        LEFT JOIN TiposProcedimento tp ON p.TipoID = tp.ID
        WHERE 1=1
    ";

    $params = [];

    if (!empty($searchTerm)) {
        $query .= " AND (
            o.Descricao LIKE :search OR
            o.LacreAtual LIKE :search OR
            p.NumeroProcedimento LIKE :search OR
            t.Nome LIKE :search
        )";
        $params['search'] = "%$searchTerm%";
    }

    if (!empty($tipoObjeto)) {
        $query .= " AND o.TipoObjetoID = :tipo_id";
        $params['tipo_id'] = $tipoObjeto;
    }

    if (!empty($situacaoID)) {
        $query .= " AND o.SituacaoID = :situacao_id";
        $params['situacao_id'] = $situacaoID;
    }

    if (!empty($procedimentoID)) {
        $query .= " AND o.ProcedimentoID = :procedimento_id";
        $params['procedimento_id'] = $procedimentoID;
    }

    if (!empty($dataInicio)) {
        $query .= " AND o.DataApreensao >= :data_inicio";
        $params['data_inicio'] = $dataInicio;
    }

    if (!empty($dataFim)) {
        $query .= " AND o.DataApreensao <= :data_fim";
        $params['data_fim'] = $dataFim;
    }

    $query .= " ORDER BY $campoOrdenacao $direcao";

    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Limpar qualquer saída anterior
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Configurar headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="objetos_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Cabeçalhos do CSV
    fputcsv($output, [
        'ID',
        'Descrição',
        'Quantidade',
        'Data Apreensão',
        'Lacre Atual',
        'Tipo de Objeto',
        'Situação',
        'Procedimento',
        'Total de Movimentações',
        'Última Movimentação'
    ], ';');

    // Dados
    foreach ($dados as $linha) {
        fputcsv($output, [
            $linha['ID'],
            $linha['Descricao'],
            $linha['Quantidade'],
            $linha['DataApreensao'],
            $linha['LacreAtual'] ?? '',
            $linha['TipoObjeto'] ?? '',
            $linha['Situacao'] ?? '',
            $linha['Procedimento'] ?? '',
            $linha['TotalMovimentacoes'],
            $linha['UltimaMovimentacao'] ?? ''
        ], ';');
    }

    fclose($output);
    exit;
}

// Verificar se é uma requisição de exportação CSV ANTES de qualquer saída HTML
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $searchTerm = $_GET['search'] ?? '';
    $tipoObjeto = $_GET['tipo_id'] ?? '';
    $situacaoID = $_GET['situacao_id'] ?? '';
    $procedimentoID = $_GET['procedimento_id'] ?? '';
    $dataInicio = $_GET['data_inicio'] ?? '';
    $dataFim = $_GET['data_fim'] ?? '';
    $ordenarPor = $_GET['order_by'] ?? 'DataApreensao';
    $direcao = $_GET['order_dir'] ?? 'DESC';
    
    // Colunas permitidas para ordenação
    $colunasPermitidas = [
        'DataApreensao' => 'o.DataApreensao',
        'Descricao' => 'o.Descricao',
        'TipoObjeto' => 't.Nome',
        'Situacao' => 's.Nome',
        'NumeroProcedimento' => 'p.NumeroProcedimento',
        'LacreAtual' => 'o.LacreAtual',
        'Quantidade' => 'o.Quantidade'
    ];
    
    $campoOrdenacao = $colunasPermitidas[$ordenarPor] ?? $colunasPermitidas['DataApreensao'];
    $direcao = in_array(strtoupper($direcao), ['ASC', 'DESC']) ? strtoupper($direcao) : 'DESC';
    
    exportarCSV($pdo, $searchTerm, $tipoObjeto, $situacaoID, $procedimentoID, $dataInicio, $dataFim, $campoOrdenacao, $direcao);
}

// Agora incluir o header após verificar se não é exportação
include '../includes/header.php';

// Configuração de paginação
$itensPorPagina = $_GET['per_page'] ?? 25;
$itensPorPagina = max(10, min(100, (int)$itensPorPagina));
$paginaAtual = $_GET['pagina'] ?? 1;
$paginaAtual = max(1, (int)$paginaAtual);
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Configuração de filtros
$searchTerm = $_GET['search'] ?? '';
$tipoObjeto = $_GET['tipo_id'] ?? '';
$situacaoID = $_GET['situacao_id'] ?? '';
$procedimentoID = $_GET['procedimento_id'] ?? '';
$dataInicio = $_GET['data_inicio'] ?? '';
$dataFim = $_GET['data_fim'] ?? '';

// Configuração de ordenação
$ordenarPor = $_GET['order_by'] ?? 'DataApreensao';
$direcao = $_GET['order_dir'] ?? 'DESC';
$direcao = in_array(strtoupper($direcao), ['ASC', 'DESC']) ? strtoupper($direcao) : 'DESC';

// Colunas permitidas para ordenação
$colunasPermitidas = [
    'DataApreensao' => 'o.DataApreensao',
    'Descricao' => 'o.Descricao',
    'TipoObjeto' => 't.Nome',
    'Situacao' => 's.Nome',
    'NumeroProcedimento' => 'p.NumeroProcedimento',
    'LacreAtual' => 'o.LacreAtual',
    'Quantidade' => 'o.Quantidade'
];

$campoOrdenacao = $colunasPermitidas[$ordenarPor] ?? $colunasPermitidas['DataApreensao'];

// Consultar tipos de objetos
$queryTipos = "SELECT ID, Nome FROM TiposObjeto ORDER BY Nome";
$stmtTipos = $pdo->query($queryTipos);
$tiposObjeto = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);

// Consultar situações
$querySituacoes = "SELECT ID, Nome FROM SituacoesObjeto ORDER BY Nome";
$stmtSituacoes = $pdo->query($querySituacoes);
$situacoes = $stmtSituacoes->fetchAll(PDO::FETCH_ASSOC);

// Consultar procedimentos (para filtro)
$queryProcedimentos = "
    SELECT DISTINCT p.ID, p.NumeroProcedimento, tp.Nome as TipoProcedimento
    FROM Procedimentos p
    INNER JOIN TiposProcedimento tp ON p.TipoID = tp.ID
    INNER JOIN Objetos o ON o.ProcedimentoID = p.ID
    ORDER BY p.NumeroProcedimento DESC
    LIMIT 1000
";
$stmtProcedimentos = $pdo->query($queryProcedimentos);
$procedimentos = $stmtProcedimentos->fetchAll(PDO::FETCH_ASSOC);

// Construir a query principal
$query = "
    SELECT 
        o.ID,
        o.Descricao,
        o.Quantidade,
        DATE_FORMAT(o.DataApreensao, '%d/%m/%Y') as DataApreensaoFormatada,
        o.DataApreensao,
        o.LacreAtual,
        t.Nome as TipoObjeto,
        s.Nome as Situacao,
        p.ID as ProcedimentoID,
        p.NumeroProcedimento,
        tp.Nome as TipoProcedimento,
        (
            SELECT COUNT(*) 
            FROM MovimentacoesObjeto mo 
            WHERE mo.ObjetoID = o.ID
        ) as TotalMovimentacoes,
        (
            SELECT DATE_FORMAT(mo.DataMovimentacao, '%d/%m/%Y %H:%i')
            FROM MovimentacoesObjeto mo 
            WHERE mo.ObjetoID = o.ID 
            ORDER BY mo.DataMovimentacao DESC 
            LIMIT 1
        ) as UltimaMovimentacao
    FROM Objetos o
    LEFT JOIN TiposObjeto t ON o.TipoObjetoID = t.ID
    LEFT JOIN SituacoesObjeto s ON o.SituacaoID = s.ID
    LEFT JOIN Procedimentos p ON o.ProcedimentoID = p.ID
    LEFT JOIN TiposProcedimento tp ON p.TipoID = tp.ID
    WHERE 1=1
";

$params = [];

if (!empty($searchTerm)) {
    $query .= " AND (
        o.Descricao LIKE :search OR
        o.LacreAtual LIKE :search OR
        p.NumeroProcedimento LIKE :search OR
        t.Nome LIKE :search
    )";
    $params['search'] = "%$searchTerm%";
}

if (!empty($tipoObjeto)) {
    $query .= " AND o.TipoObjetoID = :tipo_id";
    $params['tipo_id'] = $tipoObjeto;
}

if (!empty($situacaoID)) {
    $query .= " AND o.SituacaoID = :situacao_id";
    $params['situacao_id'] = $situacaoID;
}

if (!empty($procedimentoID)) {
    $query .= " AND o.ProcedimentoID = :procedimento_id";
    $params['procedimento_id'] = $procedimentoID;
}

if (!empty($dataInicio)) {
    $query .= " AND o.DataApreensao >= :data_inicio";
    $params['data_inicio'] = $dataInicio;
}

if (!empty($dataFim)) {
    $query .= " AND o.DataApreensao <= :data_fim";
    $params['data_fim'] = $dataFim;
}

// Contar total de registros para paginação
$countQuery = "SELECT COUNT(DISTINCT o.ID) as total FROM Objetos o
    LEFT JOIN TiposObjeto t ON o.TipoObjetoID = t.ID
    LEFT JOIN SituacoesObjeto s ON o.SituacaoID = s.ID
    LEFT JOIN Procedimentos p ON o.ProcedimentoID = p.ID
    LEFT JOIN TiposProcedimento tp ON p.TipoID = tp.ID
    WHERE 1=1";

if (!empty($searchTerm)) {
    $countQuery .= " AND (
        o.Descricao LIKE :search OR
        o.LacreAtual LIKE :search OR
        p.NumeroProcedimento LIKE :search OR
        t.Nome LIKE :search
    )";
}

if (!empty($tipoObjeto)) {
    $countQuery .= " AND o.TipoObjetoID = :tipo_id";
}

if (!empty($situacaoID)) {
    $countQuery .= " AND o.SituacaoID = :situacao_id";
}

if (!empty($procedimentoID)) {
    $countQuery .= " AND o.ProcedimentoID = :procedimento_id";
}

if (!empty($dataInicio)) {
    $countQuery .= " AND o.DataApreensao >= :data_inicio";
}

if (!empty($dataFim)) {
    $countQuery .= " AND o.DataApreensao <= :data_fim";
}

$stmtCount = $pdo->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmtCount->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmtCount->execute();
$totalRegistros = $stmtCount->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Adicionar ordenação e paginação
$query .= " ORDER BY $campoOrdenacao $direcao LIMIT :offset, :limit";
$params['offset'] = $offset;
$params['limit'] = $itensPorPagina;

// Executar a query
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$objetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para exportar CSV

// Função para gerar URL de ordenação
function urlOrdenacao($coluna, $searchTerm, $tipoObjeto, $situacaoID, $procedimentoID, $dataInicio, $dataFim, $ordenarPor, $direcao, $itensPorPagina) {
    $novaDirecao = ($ordenarPor == $coluna && $direcao == 'ASC') ? 'DESC' : 'ASC';
    
    $params = [
        'order_by' => $coluna,
        'order_dir' => $novaDirecao,
        'search' => $searchTerm,
        'tipo_id' => $tipoObjeto,
        'situacao_id' => $situacaoID,
        'procedimento_id' => $procedimentoID,
        'data_inicio' => $dataInicio,
        'data_fim' => $dataFim,
        'per_page' => $itensPorPagina,
        'pagina' => 1
    ];
    
    return '?' . http_build_query(array_filter($params));
}

// Função para gerar ícone de ordenação
function iconeOrdenacao($coluna, $ordenarPor, $direcao) {
    if ($ordenarPor != $coluna) {
        return '<i class="bi bi-arrow-down-up text-muted"></i>';
    }
    
    return $direcao == 'ASC' 
        ? '<i class="bi bi-arrow-up text-primary"></i>' 
        : '<i class="bi bi-arrow-down text-primary"></i>';
}
?>

<div class="container-fluid px-4 mt-4">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-box-seam me-2 text-primary"></i>
                Gestão de Objetos
            </h1>
            <p class="text-muted mb-0">
                <i class="bi bi-info-circle me-1"></i>
                <?= number_format($totalRegistros) ?> objeto(s) encontrado(s)
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= '?' . http_build_query(array_filter([
                'export' => 'csv',
                'search' => $searchTerm,
                'tipo_id' => $tipoObjeto,
                'situacao_id' => $situacaoID,
                'procedimento_id' => $procedimentoID,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'order_by' => $ordenarPor,
                'order_dir' => $direcao
            ])) ?>" class="btn btn-outline-success">
                <i class="bi bi-download me-2"></i>
                Exportar CSV
            </a>
            <a href="adicionar_objeto.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Novo Objeto
            </a>
        </div>
    </div>

    <!-- Filtros Avançados -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="bi bi-funnel me-2"></i>
                Filtros de Pesquisa
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <!-- Busca Geral -->
                <div class="col-md-6 col-lg-4">
                    <label for="search" class="form-label">Busca Geral</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="Descrição, lacre, procedimento..." 
                               value="<?= htmlspecialchars($searchTerm) ?>">
                    </div>
                </div>

                <!-- Tipo de Objeto -->
                <div class="col-md-6 col-lg-3">
                    <label for="tipo_id" class="form-label">Tipo de Objeto</label>
                    <select class="form-select" id="tipo_id" name="tipo_id">
                        <option value="">Todos os tipos</option>
                        <?php foreach ($tiposObjeto as $tipo): ?>
                            <option value="<?= $tipo['ID'] ?>" <?= $tipoObjeto == $tipo['ID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['Nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Situação -->
                <div class="col-md-6 col-lg-3">
                    <label for="situacao_id" class="form-label">Situação</label>
                    <select class="form-select" id="situacao_id" name="situacao_id">
                        <option value="">Todas as situações</option>
                        <?php foreach ($situacoes as $situacao): ?>
                            <option value="<?= $situacao['ID'] ?>" <?= $situacaoID == $situacao['ID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($situacao['Nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Procedimento -->
                <div class="col-md-6 col-lg-2">
                    <label for="procedimento_id" class="form-label">Procedimento</label>
                    <select class="form-select" id="procedimento_id" name="procedimento_id">
                        <option value="">Todos</option>
                        <?php foreach ($procedimentos as $proc): ?>
                            <option value="<?= $proc['ID'] ?>" <?= $procedimentoID == $proc['ID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($proc['TipoProcedimento'] . ' ' . $proc['NumeroProcedimento']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Data Início -->
                <div class="col-md-6 col-lg-3">
                    <label for="data_inicio" class="form-label">Data Início</label>
                    <input type="date" 
                           class="form-control" 
                           id="data_inicio" 
                           name="data_inicio" 
                           value="<?= htmlspecialchars($dataInicio) ?>">
                </div>

                <!-- Data Fim -->
                <div class="col-md-6 col-lg-3">
                    <label for="data_fim" class="form-label">Data Fim</label>
                    <input type="date" 
                           class="form-control" 
                           id="data_fim" 
                           name="data_fim" 
                           value="<?= htmlspecialchars($dataFim) ?>">
                </div>

                <!-- Itens por Página -->
                <div class="col-md-6 col-lg-2">
                    <label for="per_page" class="form-label">Por Página</label>
                    <select class="form-select" id="per_page" name="per_page">
                        <option value="10" <?= $itensPorPagina == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= $itensPorPagina == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $itensPorPagina == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $itensPorPagina == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>

                <!-- Botões -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>
                            Filtrar
                        </button>
                        <a href="objetos.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Limpar
                        </a>
                    </div>
                </div>

                <!-- Campos ocultos para manter ordenação -->
                <input type="hidden" name="order_by" value="<?= htmlspecialchars($ordenarPor) ?>">
                <input type="hidden" name="order_dir" value="<?= htmlspecialchars($direcao) ?>">
            </form>
        </div>
    </div>

    <!-- Tabela de Objetos -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabelaObjetos">
                    <thead class="table-dark">
                        <tr>
                            <th class="sortable" style="min-width: 200px;">
                                <a href="<?= urlOrdenacao('NumeroProcedimento', $searchTerm, $tipoObjeto, $situacaoID, $procedimentoID, $dataInicio, $dataFim, $ordenarPor, $direcao, $itensPorPagina) ?>" 
                                   class="text-white text-decoration-none d-flex align-items-center justify-content-between">
                                    Procedimento
                                    <?= iconeOrdenacao('NumeroProcedimento', $ordenarPor, $direcao) ?>
                                </a>
                            </th>
                            <th class="sortable" style="min-width: 150px;">
                                <a href="<?= urlOrdenacao('TipoObjeto', $searchTerm, $tipoObjeto, $situacaoID, $procedimentoID, $dataInicio, $dataFim, $ordenarPor, $direcao, $itensPorPagina) ?>" 
                                   class="text-white text-decoration-none d-flex align-items-center justify-content-between">
                                    Tipo
                                    <?= iconeOrdenacao('TipoObjeto', $ordenarPor, $direcao) ?>
                                </a>
                            </th>
                            <th class="sortable" style="min-width: 250px;">
                                <a href="<?= urlOrdenacao('Descricao', $searchTerm, $tipoObjeto, $situacaoID, $procedimentoID, $dataInicio, $dataFim, $ordenarPor, $direcao, $itensPorPagina) ?>" 
                                   class="text-white text-decoration-none d-flex align-items-center justify-content-between">
                                    Descrição
                                    <?= iconeOrdenacao('Descricao', $ordenarPor, $direcao) ?>
                                </a>
                            </th>
                            <th class="sortable text-center" style="min-width: 100px;">
                                <a href="<?= urlOrdenacao('Quantidade', $searchTerm, $tipoObjeto, $situacaoID, $procedimentoID, $dataInicio, $dataFim, $ordenarPor, $direcao, $itensPorPagina) ?>" 
                                   class="text-white text-decoration-none d-flex align-items-center justify-content-between">
                                    Qtd.
                                    <?= iconeOrdenacao('Quantidade', $ordenarPor, $direcao) ?>
                                </a>
                            </th>
                            <th class="sortable" style="min-width: 120px;">
                                <a href="<?= urlOrdenacao('LacreAtual', $searchTerm, $tipoObjeto, $situacaoID, $procedimentoID, $dataInicio, $dataFim, $ordenarPor, $direcao, $itensPorPagina) ?>" 
                                   class="text-white text-decoration-none d-flex align-items-center justify-content-between">
                                    Lacre
                                    <?= iconeOrdenacao('LacreAtual', $ordenarPor, $direcao) ?>
                                </a>
                            </th>
                            <th class="sortable" style="min-width: 120px;">
                                <a href="<?= urlOrdenacao('Situacao', $searchTerm, $tipoObjeto, $situacaoID, $procedimentoID, $dataInicio, $dataFim, $ordenarPor, $direcao, $itensPorPagina) ?>" 
                                   class="text-white text-decoration-none d-flex align-items-center justify-content-between">
                                    Situação
                                    <?= iconeOrdenacao('Situacao', $ordenarPor, $direcao) ?>
                                </a>
                            </th>
                            <th class="sortable" style="min-width: 130px;">
                                <a href="<?= urlOrdenacao('DataApreensao', $searchTerm, $tipoObjeto, $situacaoID, $procedimentoID, $dataInicio, $dataFim, $ordenarPor, $direcao, $itensPorPagina) ?>" 
                                   class="text-white text-decoration-none d-flex align-items-center justify-content-between">
                                    Data Apreensão
                                    <?= iconeOrdenacao('DataApreensao', $ordenarPor, $direcao) ?>
                                </a>
                            </th>
                            <th style="min-width: 180px;">Última Movimentação</th>
                            <th style="min-width: 200px;" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($objetos)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-search fs-1 d-block mb-3"></i>
                                        <h5>Nenhum objeto encontrado</h5>
                                        <p class="mb-0">Tente ajustar os filtros de pesquisa</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($objetos as $objeto): ?>
                                <tr class="align-middle">
                                    <td>
                                        <?php if (!empty($objeto['ProcedimentoID'])): ?>
                                            <a href="ver_procedimento.php?id=<?= $objeto['ProcedimentoID'] ?>" 
                                               class="text-decoration-none fw-medium">
                                                <span class="badge bg-secondary me-1">
                                                    <?= htmlspecialchars($objeto['TipoProcedimento']) ?>
                                                </span>
                                                <br>
                                                <?= htmlspecialchars($objeto['NumeroProcedimento']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">
                                                <i class="bi bi-dash me-1"></i>
                                                Sem procedimento
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                            <?= htmlspecialchars($objeto['TipoObjeto']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($objeto['Descricao']) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">
                                            <?= $objeto['Quantidade'] ?> un.
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($objeto['LacreAtual'])): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-shield-lock me-1"></i>
                                                <?= htmlspecialchars($objeto['LacreAtual']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $objeto['Situacao'] == 'Em custódia' ? 'success' : ($objeto['Situacao'] == 'Devolvido' ? 'secondary' : 'warning') ?>">
                                            <?= htmlspecialchars($objeto['Situacao']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            <?= $objeto['DataApreensaoFormatada'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($objeto['UltimaMovimentacao']): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= $objeto['UltimaMovimentacao'] ?>
                                            </small>
                                            <br>
                                            <small class="badge bg-light text-dark">
                                                <?= $objeto['TotalMovimentacoes'] ?> mov.
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">
                                                <i class="bi bi-dash me-1"></i>
                                                Sem movimentações
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="registrar_movimentacao_objeto.php?objeto_id=<?= $objeto['ID'] ?><?= !empty($objeto['ProcedimentoID']) ? '&procedimento_id=' . $objeto['ProcedimentoID'] : '' ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Registrar Movimentação">
                                                <i class="bi bi-arrow-left-right"></i>
                                            </a>
                                            <?php if (!empty($objeto['ProcedimentoID'])): ?>
                                                <a href="ver_procedimento.php?id=<?= $objeto['ProcedimentoID'] ?>" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="Ver Procedimento">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="adicionar_objeto.php?objeto_id=<?= $objeto['ID'] ?><?= !empty($objeto['ProcedimentoID']) ? '&procedimento_id=' . $objeto['ProcedimentoID'] : '' ?>" 
                                               class="btn btn-sm btn-outline-warning"
                                               title="Editar Objeto">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#historicoModal<?= $objeto['ID'] ?>"
                                                    title="Ver Histórico">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                        </div>

                                        <!-- Modal de Histórico -->
                                        <div class="modal fade" id="historicoModal<?= $objeto['ID'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-clock-history me-2"></i>
                                                            Histórico de Movimentações
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        $stmt = $pdo->prepare("
                                                            SELECT 
                                                                mo.*,
                                                                tmo.Nome as TipoMovimentacao,
                                                                u.Nome as UsuarioNome,
                                                                p.ID as ProcedimentoID,
                                                                p.NumeroProcedimento
                                                            FROM MovimentacoesObjeto mo
                                                            LEFT JOIN TiposMovimentacaoObjeto tmo ON mo.TipoMovimentacaoID = tmo.ID
                                                            LEFT JOIN Usuarios u ON mo.UsuarioID = u.ID
                                                            LEFT JOIN Objetos o ON mo.ObjetoID = o.ID
                                                            LEFT JOIN Procedimentos p ON o.ProcedimentoID = p.ID
                                                            WHERE mo.ObjetoID = :objetoID
                                                            ORDER BY mo.DataMovimentacao DESC
                                                        ");
                                                        $stmt->execute([':objetoID' => $objeto['ID']]);
                                                        $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                        ?>

                                                        <?php if (empty($movimentacoes)): ?>
                                                            <div class="text-center py-4">
                                                                <i class="bi bi-clock text-muted fs-1"></i>
                                                                <p class="text-muted mt-2">Nenhuma movimentação registrada.</p>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Data</th>
                                                                            <th>Tipo</th>
                                                                            <th>Destino</th>
                                                                            <th>Responsável</th>
                                                                            <th>Usuário</th>
                                                                            <th>Observação</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($movimentacoes as $mov): ?>
                                                                            <tr>
                                                                                <td>
                                                                                    <small>
                                                                                        <?= date('d/m/Y H:i', strtotime($mov['DataMovimentacao'])) ?>
                                                                                    </small>
                                                                                </td>
                                                                                <td>
                                                                                    <span class="badge bg-secondary">
                                                                                        <?= htmlspecialchars($mov['TipoMovimentacao']) ?>
                                                                                    </span>
                                                                                </td>
                                                                                <td><?= htmlspecialchars($mov['Destino']) ?></td>
                                                                                <td><?= htmlspecialchars($mov['Responsavel']) ?></td>
                                                                                <td><?= htmlspecialchars($mov['UsuarioNome']) ?></td>
                                                                                <td><?= htmlspecialchars($mov['Observacao']) ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPaginas > 1): ?>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando <?= number_format(($paginaAtual - 1) * $itensPorPagina + 1) ?> a 
                            <?= number_format(min($paginaAtual * $itensPorPagina, $totalRegistros)) ?> de 
                            <?= number_format($totalRegistros) ?> registros
                        </div>
                        
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php
                                $baseUrl = '?' . http_build_query(array_filter([
                                    'search' => $searchTerm,
                                    'tipo_id' => $tipoObjeto,
                                    'situacao_id' => $situacaoID,
                                    'procedimento_id' => $procedimentoID,
                                    'data_inicio' => $dataInicio,
                                    'data_fim' => $dataFim,
                                    'order_by' => $ordenarPor,
                                    'order_dir' => $direcao,
                                    'per_page' => $itensPorPagina
                                ]));
                                ?>
                                
                                <?php if ($paginaAtual > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $baseUrl ?>&pagina=1">
                                            <i class="bi bi-chevron-double-left"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $baseUrl ?>&pagina=<?= $paginaAtual - 1 ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $inicio = max(1, $paginaAtual - 2);
                                $fim = min($totalPaginas, $paginaAtual + 2);
                                
                                for ($i = $inicio; $i <= $fim; $i++):
                                ?>
                                    <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $baseUrl ?>&pagina=<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($paginaAtual < $totalPaginas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $baseUrl ?>&pagina=<?= $paginaAtual + 1 ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $baseUrl ?>&pagina=<?= $totalPaginas ?>">
                                            <i class="bi bi-chevron-double-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Estilos personalizados para a nova interface */
.container-fluid {
    max-width: 98%;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table th.sortable {
    cursor: pointer;
    user-select: none;
}

/* Melhorias visuais para o cabeçalho da tabela */
.table-dark th {
    background-color: #212529 !important;
    border-color: #32383e !important;
}

.table th.sortable a {
    color: #fff !important;
    text-decoration: none !important;
}

.table th.sortable:hover a {
    color: #fff !important;
}

.table th.sortable:hover {
    background-color: rgba(0, 123, 255, 0.2) !important;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.table th.sortable:active {
    transform: translateY(0);
}

.table td {
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
}

.badge {
    font-weight: 500;
    font-size: 0.75rem;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.form-label {
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
    margin-bottom: 0.25rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

/* Melhorias de responsividade */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .btn-group {
        flex-wrap: wrap;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}

/* Animações suaves */
.card, .btn, .badge {
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

/* Loading state para botões */
.btn.loading {
    pointer-events: none;
    opacity: 0.6;
}

.btn.loading::after {
    content: "";
    width: 1rem;
    height: 1rem;
    margin-left: 0.5rem;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    display: inline-block;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-submit no filtro de itens por página
    document.getElementById('per_page').addEventListener('change', function() {
        this.closest('form').submit();
    });

    // Indicador de loading para botões de ação
    document.querySelectorAll('.btn-group .btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (this.href) {
                this.classList.add('loading');
            }
        });
    });

    // Confirmação para exportação de grandes volumes
    <?php if ($totalRegistros > 1000): ?>
    document.querySelector('a[href*="export=csv"]').addEventListener('click', function(e) {
        if (!confirm('Você está prestes a exportar <?= number_format($totalRegistros) ?> registros. Isso pode levar alguns minutos. Deseja continuar?')) {
            e.preventDefault();
        }
    });
    <?php endif; ?>

    // Highlight de busca na tabela
    const searchTerm = '<?= addslashes($searchTerm) ?>';
    if (searchTerm) {
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        document.querySelectorAll('tbody td').forEach(function(cell) {
            if (cell.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                cell.innerHTML = cell.innerHTML.replace(regex, '<mark>$1</mark>');
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>