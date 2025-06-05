<?php
// Inclusão do cabeçalho e configurações globais
include '../includes/header.php'; 

// Obter o ID do usuário logado (ou null se não estiver logado)
$usuarioLogadoID = $_SESSION['usuario_id'] ?? null;

// Função para gerar uma URL com todos os parâmetros atuais
function gerarUrlComParametros($pagina) {
    $parametros = $_GET; // Obter todos os parâmetros atuais
    $parametros['pagina'] = $pagina; // Atualizar apenas o parâmetro de página
    return '?' . http_build_query($parametros); // Gerar query string
}

// Configuração de paginação
$itensPorPagina = 20; // Número de itens por página
$paginaAtual = $_GET['pagina'] ?? 1; // Página atual, padrão é 1
$paginaAtual = max(1, (int)$paginaAtual); // Garantir que seja pelo menos 1
$offset = ($paginaAtual - 1) * $itensPorPagina; // Calcular o deslocamento

// Configuração de filtros
$searchTerm = $_GET['search'] ?? ''; // Termo de busca
$tipoProcedimento = $_GET['tipo'] ?? ''; // Tipo do procedimento
$escrivaoID = $_GET['escrivao_id'] ?? $usuarioLogadoID; // ID do escrivão, padrão é o logado

// Consultar lista de escrivães
$queryEscrivaos = "
    SELECT u.ID, u.Nome
    FROM Usuarios u
    INNER JOIN Cargos c ON u.CargoID = c.ID
    WHERE c.Nome = 'Escrivão de Polícia'
";
$stmtEscrivaos = $pdo->prepare($queryEscrivaos);
$stmtEscrivaos->execute();
$escrivaos = $stmtEscrivaos->fetchAll(PDO::FETCH_ASSOC); // Lista de escrivães

// Consultar lista de situações de procedimentos
$querySituacoes = "
    SELECT ID, Nome
    FROM SituacoesProcedimento
";
$stmtSituacoes = $pdo->prepare($querySituacoes);
$stmtSituacoes->execute();
$situacoes = $stmtSituacoes->fetchAll(PDO::FETCH_ASSOC); // Lista de situações

// Consulta principal para obter os procedimentos
$query = "
    SELECT 
    p.ID AS ProcedimentoID,
    p.TipoID,
    (SELECT COUNT(*) FROM Anotacoes a WHERE a.ProcedimentoID = p.ID) AS AnotacoesCount,
    p.NumeroProcedimento, 
    p.MotivoAparente,
    sp.Nome AS Situacao,
    op.Nome AS Origem,
    p.DataInstauracao,
    sp.Cor AS Cor,
        (
            SELECT 1 
            FROM FavoritosUsuarios fu 
            WHERE fu.ProcedimentoID = p.ID AND fu.UsuarioID = :usuarioLogadoID
        ) AS Favorito,
    u.Nome AS EscrivaoNome,
    (
        SELECT GROUP_CONCAT(
            CONCAT(
                v.Nome, '\n',
                (
                    SELECT GROUP_CONCAT(
                        CONCAT(c.Nome, ' - ', vc.Modalidade) SEPARATOR '\n'
                    )
                    FROM Vitimas_Crimes vc
                    LEFT JOIN Crimes c ON vc.CrimeID = c.ID
                    WHERE vc.VitimaID = v.ID
                )
            ) SEPARATOR '\n\n'
        )
        FROM Vitimas v
        WHERE v.ProcedimentoID = p.ID
    ) AS Vitimas,
    (
        SELECT GROUP_CONCAT(DISTINCT i.Nome SEPARATOR '\n')
        FROM Investigados i
        WHERE i.ProcedimentoID = p.ID
    ) AS Investigados,
    (
        SELECT GROUP_CONCAT(r.Numero SEPARATOR '\n')
        FROM RAIs r
        WHERE r.ProcedimentoID = p.ID
    ) AS RAIs,
    (
        SELECT GROUP_CONCAT(pj.Numero SEPARATOR '\n')
        FROM ProcessosJudiciais pj
        WHERE pj.ProcedimentoID = p.ID
    ) AS ProcessosJudiciais,
    (
        SELECT JSON_ARRAYAGG(
            JSON_OBJECT(
                'id', m.ID,
                'dias_restantes', DATEDIFF(m.DataVencimento, CURDATE()),
                'tipo_id', m.TipoID,
                'nome_tipo_movimentacao', tm.Nome,
                'assunto', m.Assunto,
                'situacao', m.Situacao,
                'data_vencimento', DATE_FORMAT(m.DataVencimento, '%Y-%m-%d'),
                'prioridade', tm.Prioridade,
                'detalhes', m.Detalhes
            )
        )
        FROM Movimentacoes m
        LEFT JOIN TiposMovimentacao tm ON m.TipoID = tm.ID
        WHERE m.ProcedimentoID = p.ID AND m.Situacao = 'Em andamento'
    ) AS Movimentacoes,
    (
        SELECT m.DataConclusao
        FROM Movimentacoes m
        WHERE m.ProcedimentoID = p.ID 
          AND m.TipoID = 5 
          AND m.Situacao = 'Finalizado'
        ORDER BY m.DataConclusao DESC
        LIMIT 1
    ) AS RemessaDataConclusao,
(SELECT a.Anotacao 
     FROM Anotacoes a 
     WHERE a.ProcedimentoID = p.ID 
     ORDER BY a.DataCriacao DESC 
     LIMIT 1) AS UltimaAnotacao
FROM Procedimentos p
LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID
LEFT JOIN OrigensProcedimentos op ON p.OrigemID = op.ID
LEFT JOIN Usuarios u ON p.EscrivaoID = u.ID
WHERE 1=1
";

// Configuração de filtros dinâmicos
$params = []; // Inicializar array para os parâmetros

$params['usuarioLogadoID'] = $usuarioLogadoID;

if (isset($_GET['favorito']) && $_GET['favorito'] !== '') {
    if ($_GET['favorito'] == '1') {
        // Listar apenas registros favoritados
        $query .= " AND EXISTS (
            SELECT 1 
            FROM FavoritosUsuarios fu 
            WHERE fu.ProcedimentoID = p.ID 
            AND fu.UsuarioID = :usuarioLogadoID
        )";
    } elseif ($_GET['favorito'] == '0') {
        // Listar apenas registros não favoritados
        $query .= " AND NOT EXISTS (
            SELECT 1 
            FROM FavoritosUsuarios fu 
            WHERE fu.ProcedimentoID = p.ID 
            AND fu.UsuarioID = :usuarioLogadoID
        )";
    }
    $params['usuarioLogadoID'] = $usuarioLogadoID; // Adicionar o parâmetro para a consulta
}



if (!empty($tipoProcedimento)) {
    $query .= " AND p.TipoID = :tipo";
    if ($tipoProcedimento === 'IP') {
        $params['tipo'] = 1; // ID de IP
    } elseif ($tipoProcedimento === 'VPI') {
        $params['tipo'] = 2; // ID de VPI
    } 
}

if (!empty($searchTerm)) {
    $query .= "
        AND (
            p.NumeroProcedimento LIKE :search OR
            sp.Nome LIKE :search OR
            EXISTS (
                SELECT 1 FROM Vitimas v 
                WHERE v.ProcedimentoID = p.ID 
                AND v.Nome LIKE :search
            ) OR
            EXISTS (
                SELECT 1 FROM Investigados i 
                WHERE i.ProcedimentoID = p.ID 
                AND i.Nome LIKE :search
            ) OR
            EXISTS (
                SELECT 1 FROM Vitimas_Crimes vc 
                LEFT JOIN Crimes c ON vc.CrimeID = c.ID 
                LEFT JOIN Vitimas v ON vc.VitimaID = v.ID 
                WHERE v.ProcedimentoID = p.ID 
                AND c.Nome LIKE :search
            ) OR
            EXISTS (
                SELECT 1 FROM RAIs r
                WHERE r.ProcedimentoID = p.ID 
                AND r.Numero LIKE :search
            ) OR
            EXISTS (
                SELECT 1 FROM ProcessosJudiciais pj
                WHERE pj.ProcedimentoID = p.ID 
                AND pj.Numero LIKE :search
            )
        )
    ";
    $params['search'] = '%' . $searchTerm . '%';
}


if (isset($_GET['requisicoes_mp']) && $_GET['requisicoes_mp'] !== '') {
    if ($_GET['requisicoes_mp'] == '1') {
        $query .= " AND EXISTS (
            SELECT 1 
            FROM Movimentacoes m 
            WHERE m.ProcedimentoID = p.ID 
            AND m.TipoID = 1 
            AND m.Situacao = 'Em andamento'
        )";
    } elseif ($_GET['requisicoes_mp'] == '0') {
        $query .= " AND NOT EXISTS (
            SELECT 1 
            FROM Movimentacoes m 
            WHERE m.ProcedimentoID = p.ID 
            AND m.TipoID = 1 
            AND m.Situacao = 'Em andamento'
        )";
    }
}

if (!empty($_GET['situacao_id'])) {
    $query .= " AND p.SituacaoID = :situacao_id";
    $params['situacao_id'] = (int)$_GET['situacao_id'];
}

if (!empty($escrivaoID)) {
    $query .= " AND p.EscrivaoID = :escrivaoID";
    $params['escrivaoID'] = $escrivaoID;
}



// Ordenação e paginação
$query .= "
    GROUP BY p.ID
    ORDER BY 
    CASE 
        WHEN (SELECT tm.Prioridade FROM Movimentacoes m 
              LEFT JOIN TiposMovimentacao tm ON m.TipoID = tm.ID
              WHERE m.ProcedimentoID = p.ID AND m.Situacao = 'Em andamento'
              ORDER BY tm.Prioridade = 'Alta' DESC LIMIT 1) = 'Alta' 
        THEN 0 ELSE 1
    END, 
    CASE 
        WHEN (SELECT MIN(DATEDIFF(DataVencimento, CURDATE())) 
              FROM Movimentacoes m 
              WHERE m.ProcedimentoID = p.ID AND m.Situacao = 'Em andamento') IS NULL 
        THEN 1 ELSE 0
    END, 
    (SELECT MIN(DATEDIFF(DataVencimento, CURDATE())) 
     FROM Movimentacoes m 
     WHERE m.ProcedimentoID = p.ID AND m.Situacao = 'Em andamento') ASC,
     p.DataInstauracao DESC
    LIMIT :offset, :itensPorPagina
";

$params['offset'] = $offset;
$params['itensPorPagina'] = $itensPorPagina;

$stmt = $pdo->prepare($query);

foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}

try {
    $stmt->execute();
    $procedimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao executar consulta: " . $e->getMessage());
}


// Consulta para contagem total de registros
$countQuery = "
    SELECT COUNT(DISTINCT p.ID) AS total
    FROM Procedimentos p
    LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID
    LEFT JOIN Vitimas v ON v.ProcedimentoID = p.ID
    LEFT JOIN Investigados i ON i.ProcedimentoID = p.ID
    LEFT JOIN Vitimas_Crimes vc ON v.ID = vc.VitimaID
    LEFT JOIN Crimes c ON vc.CrimeID = c.ID
    WHERE 1=1
";

// Adicionar filtros à consulta de contagem
$countParams = [];
if (!empty($tipoProcedimento)) {
    $countQuery .= " AND p.TipoID = :tipo";
    $countParams['tipo'] = ($tipoProcedimento === 'IP') ? 1 : 2;
}

if (!empty($searchTerm)) {
    $countQuery .= "
        AND (
            p.NumeroProcedimento LIKE :search OR
            sp.Nome LIKE :search OR
            v.Nome LIKE :search OR
            i.Nome LIKE :search OR
            c.Nome LIKE :search
        )
    ";
    $countParams['search'] = '%' . $searchTerm . '%';
}

if (!empty($escrivaoID)) {
    $countQuery .= " AND p.EscrivaoID = :escrivaoID";
    $countParams['escrivaoID'] = $escrivaoID;
}

// Filtro de Favoritos
if (isset($_GET['favorito']) && $_GET['favorito'] !== '') {
    if ($_GET['favorito'] == '1') {
        // Contar apenas registros favoritados
        $countQuery .= " AND EXISTS (
            SELECT 1 
            FROM FavoritosUsuarios fu 
            WHERE fu.ProcedimentoID = p.ID 
            AND fu.UsuarioID = :usuarioLogadoID
        )";
    } elseif ($_GET['favorito'] == '0') {
        // Contar apenas registros não favoritados
        $countQuery .= " AND NOT EXISTS (
            SELECT 1 
            FROM FavoritosUsuarios fu 
            WHERE fu.ProcedimentoID = p.ID 
            AND fu.UsuarioID = :usuarioLogadoID
        )";
    }
    $countParams['usuarioLogadoID'] = $usuarioLogadoID;
}


if (isset($_GET['requisicoes_mp']) && $_GET['requisicoes_mp'] !== '') {
    if ($_GET['requisicoes_mp'] == '1') {
        $countQuery .= " AND EXISTS (
            SELECT 1 
            FROM Movimentacoes m 
            WHERE m.ProcedimentoID = p.ID 
            AND m.TipoID = 1 
            AND m.Situacao = 'Em andamento'
        )";
    } elseif ($_GET['requisicoes_mp'] == '0') {
        $countQuery .= " AND NOT EXISTS (
            SELECT 1 
            FROM Movimentacoes m 
            WHERE m.ProcedimentoID = p.ID 
            AND m.TipoID = 1 
            AND m.Situacao = 'Em andamento'
        )";
    }
}

if (!empty($_GET['situacao_id'])) {
    $countQuery .= " AND p.SituacaoID = :situacao_id";
    $countParams['situacao_id'] = (int)$_GET['situacao_id'];
}

// Executar a consulta de contagem
$countStmt = $pdo->prepare($countQuery);
foreach ($countParams as $key => $value) {
    $countStmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$countStmt->execute();
$totalRegistros = $countStmt->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina); // Calcular total de páginas
?>

<style>
    /* Estilos modernos para a página de procedimentos */
    .procedimentos-container {
        background: #f8f9fa;
        min-height: calc(100vh - 140px);
        padding: 2rem 0;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }
    
    .btn-novo-procedimento {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        border: none;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 10px;
        transition: all 0.3s ease;
        color: white;
        box-shadow: 0 4px 6px rgba(13, 110, 253, 0.3);
    }
    
    .btn-novo-procedimento:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(13, 110, 253, 0.4);
        color: white;
    }
    
    .filtros-container {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-select, .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 0.7rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
        background: #fff;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .btn-pesquisar {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        border: none;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 8px;
        transition: all 0.3s ease;
        height: calc(2.7rem + 4px);
    }
    
    .btn-pesquisar:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }
    
    .btn-limpar {
        background: #6c757d;
        border: none;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 8px;
        transition: all 0.3s ease;
        height: calc(2.7rem + 4px);
        color: white;
    }
    
    .btn-limpar:hover {
        background: #5a6268;
        transform: translateY(-2px);
        color: white;
    }
    
    .table-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background: #1a1a2e;
        color: white;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        white-space: nowrap;
    }
    
    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        font-weight: 500;
        border-bottom: 1px solid #eef2f7;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge {
        font-weight: 600;
        padding: 0.5rem 0.7rem;
        border-radius: 6px;
        font-size: 0.8rem;
    }
    
    .badge-purple {
        background: linear-gradient(135deg, #800080 0%, #6a006a 100%);
        color: white;
    }
    
    .procedimento-info {
        line-height: 1.6;
    }
    
    .procedimento-link {
        text-decoration: none;
        color: #0d6efd;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .procedimento-link:hover {
        color: #0a58ca;
        text-decoration: underline;
    }
    
    .btn-acao {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-acao:hover {
        transform: translateY(-2px);
    }
    
    .badge-pill {
        border-radius: 50px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .pagination {
        margin-top: 2rem;
    }
    
    .page-link {
        border: none;
        border-radius: 8px;
        margin: 0 0.2rem;
        padding: 0.5rem 1rem;
        font-weight: 600;
        color: #0d6efd;
        transition: all 0.3s ease;
    }
    
    .page-link:hover {
        background: #e9ecef;
        color: #0a58ca;
    }
    
    .page-item.active .page-link {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: white;
    }
    
    .prazo-badge {
        border-radius: 50px;
        padding: 0.4rem 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        margin: 0.2rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .prazo-badge:hover {
        transform: translateY(-2px);
        text-decoration: none;
    }
    
    @media (max-width: 768px) {
        .filtros-container {
            padding: 1rem;
        }
        
        .table-container {
            padding: 1rem;
        }
        
        .page-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }
</style>

<div class="procedimentos-container">
    <div class="container" style="max-width: 95%; margin: 0 auto;">
        <!-- Cabeçalho e botão "Novo Procedimento" -->
        <div class="page-header">
            <h1>Procedimentos</h1>
            <a href="cadastrar_procedimento.php" class="btn btn-novo-procedimento">
                <i class="bi bi-plus-circle"></i> Novo Procedimento
            </a>
        </div>

        <!-- Total de Procedimentos -->
        <p class="text-muted mb-4">
            Total de Procedimentos Encontrados: <strong><?= htmlspecialchars($totalRegistros) ?></strong>
        </p>

        <!-- Formulário de Pesquisa -->
        <div class="filtros-container">
            <form method="GET">
                <div class="row g-3">
                    <!-- Campo de filtro por Escrivão -->
                    <div class="col-md-3">
                        <label for="escrivao_id" class="form-label">Escrivão</label>
                        <select name="escrivao_id" id="escrivao_id" class="form-select">
                            <option value="">Todos os Escrivães</option>
                            <?php foreach ($escrivaos as $escrivao): ?>
                                <option value="<?= htmlspecialchars($escrivao['ID']) ?>" <?= ($escrivaoID == $escrivao['ID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($escrivao['Nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campo de filtro por Tipo -->
                    <div class="col-md-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select">
                            <option value="">Todos os Tipos</option>
                            <option value="IP" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'IP') ? 'selected' : '' ?>>IP</option>
                            <option value="VPI" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'VPI') ? 'selected' : '' ?>>VPI</option>
                        </select>
                    </div>

                    <!-- Campo de filtro por Situação -->
                    <div class="col-md-3">
                        <label for="situacao_id" class="form-label">Situação</label>
                        <select name="situacao_id" id="situacao_id" class="form-select">
                            <option value="">Todas as Situações</option>
                            <?php foreach ($situacoes as $situacao): ?>
                                <option value="<?= htmlspecialchars($situacao['ID']) ?>" <?= (isset($_GET['situacao_id']) && $_GET['situacao_id'] == $situacao['ID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($situacao['Nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Campo de filtro por Requisições MP -->
                    <div class="col-md-3">
                        <label for="requisicoes_mp" class="form-label">Requisições MP</label>
                        <select name="requisicoes_mp" id="requisicoes_mp" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" <?= (isset($_GET['requisicoes_mp']) && $_GET['requisicoes_mp'] === '1') ? 'selected' : '' ?>>Sim</option>
                            <option value="0" <?= (isset($_GET['requisicoes_mp']) && $_GET['requisicoes_mp'] === '0') ? 'selected' : '' ?>>Não</option>
                        </select>
                    </div>
                </div>

                <!-- Segunda linha de filtros -->
                <div class="row g-3 mt-1">
                    <!-- Campo de filtro por Favorito -->
                    <div class="col-md-3">
                        <label for="favorito" class="form-label">Favoritos</label>
                        <select name="favorito" id="favorito" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" <?= (isset($_GET['favorito']) && $_GET['favorito'] === '1') ? 'selected' : '' ?>>Sim</option>
                            <option value="0" <?= (isset($_GET['favorito']) && $_GET['favorito'] === '0') ? 'selected' : '' ?>>Não</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="search" class="form-label">Pesquisar</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Digite para pesquisar..." value="<?= htmlspecialchars($searchTerm) ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label d-none d-md-block">&nbsp;</label> <!-- Para manter alinhamento vertical -->
                        <div class="d-flex gap-2">
                            <!-- Botão de Pesquisar -->
                            <button type="submit" class="btn btn-pesquisar w-50">
                                <i class="bi bi-search"></i> Pesquisar
                            </button>
                            <!-- Botão de Limpar -->
                            <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-limpar w-50">
                                <i class="bi bi-x-circle"></i> Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabela de Exibição -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Situação</th>
                            <th>Procedimento</th>
                            <th>Vítimas</th>
                            <th>Investigados</th>
                            <th>Prazo (dias)</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($procedimentos)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Nenhum procedimento encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($procedimentos as $procedimento): ?>
                                <tr>
                                    <td>
                                        <?php
                                        // Define a classe CSS para a cor da situação
                                        $situacaoClasse = htmlspecialchars($procedimento['Cor'] ?? 'badge-secondary');

                                        // Verifica se há Requisição MP
                                        $hasRequisicaoMP = false;
                                        $movimentacoes = json_decode($procedimento['Movimentacoes'] ?? '[]', true);

                                        if (!empty($movimentacoes)) {
                                            foreach ($movimentacoes as $movimentacao) {
                                                if (
                                                    isset($movimentacao['tipo_id'], $movimentacao['situacao']) && 
                                                    $movimentacao['situacao'] === 'Em andamento' && 
                                                    (int) $movimentacao['tipo_id'] === 1
                                                ) {
                                                    $hasRequisicaoMP = true;
                                                    $situacaoClasse = 'badge-purple'; // Aplica a cor roxa
                                                    break;
                                                }
                                            }
                                        }
                                        ?>

                                        <!-- Situação -->
                                        <span class="badge badge-pill <?= $situacaoClasse ?> mb-2">
                                            <?php if (!empty($procedimento['Favorito'])): ?>
                                                <i class="bi bi-star-fill text-warning"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($procedimento['Situacao'] ?? '') ?>
                                        </span>
                                        <br>

                                        <!-- Nome do escrivão -->
                                        <?php if (!empty($procedimento['EscrivaoNome'])): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-person-fill"></i> <?= htmlspecialchars($procedimento['EscrivaoNome']) ?>
                                            </small>
                                            <br>
                                        <?php endif; ?>

                                        <!-- Data de Instauração -->
                                        <?php if (!empty($procedimento['DataInstauracao'])): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar-event-fill"></i> Instauração: <?= htmlspecialchars(date('d/m/Y', strtotime($procedimento['DataInstauracao']))) ?>
                                            </small>
                                            <br>
                                        <?php endif; ?>

                                        <!-- Exibir "Remessa" -->
                                        <?php if (!empty($procedimento['RemessaDataConclusao'])): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar-event-fill"></i> 
                                                Remessa: <?= htmlspecialchars(date('d/m/Y', strtotime($procedimento['RemessaDataConclusao']))) ?>
                                            </small>
                                            <br>
                                        <?php endif; ?>

                                        <!-- Exibir "Requisição MP" -->
                                        <?php if ($hasRequisicaoMP): ?>
                                            <small class="text-purple">
                                                <i class="bi bi-tag-fill"></i> <strong>Requisição MP</strong>
                                            </small>
                                        <?php endif; ?>
                                    </td>

                                    <td class="procedimento-info">
                                        <?php
                                        $tipo = $procedimento['TipoID'] == 1 ? 'IP ' : 'VPI ';
                                        $numeroProcedimento = htmlspecialchars($procedimento['NumeroProcedimento'] ?? '');
                                        ?>

                                        <?= $tipo ?>
                                        <a href="https://spp.ssp.go.gov.br/documentos?procedimentoId=<?= $numeroProcedimento ?>" 
                                           target="_blank" 
                                           class="procedimento-link">
                                            <?= $numeroProcedimento ?>
                                        </a>
                                        
                                        <!-- Ícone de informação -->
                                        <i class="bi bi-info-circle ms-2 text-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="<?= htmlspecialchars($procedimento['MotivoAparente'] ?? 'Motivo não informado') ?>"></i>
                                        <br>
                                        
                                        <?php 
                                        if (!empty($procedimento['RAIs'])): 
                                            $rais = explode("\n", $procedimento['RAIs']);
                                            $primeiroRai = htmlspecialchars($rais[0]); // Primeiro RAI
                                            $quantidadeAdicional = count($rais) - 1; // Quantidade adicional
                                        ?>
                                            RAI <a href="https://atendimento.ssp.go.gov.br/#/atendimento/<?= htmlspecialchars($primeiroRai) ?>" 
                                                target="_blank" class="procedimento-link"><?= htmlspecialchars($primeiroRai) ?></a>
                                            <?php if ($quantidadeAdicional > 0): ?>
                                                (+<?= $quantidadeAdicional ?>)
                                            <?php endif; ?><br>
                                        <?php else: ?>
                                            <span class="text-muted">Nenhum RAI cadastrado</span>
                                        <?php endif; ?>
                                        
                                        <?= htmlspecialchars($procedimento['Origem'] ?? 'Origem não informada') ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($procedimento['Vitimas'])): ?>
                                            <?php 
                                            // Divide as vítimas pelo delimitador "\n\n"
                                            $vitimas = explode("\n\n", $procedimento['Vitimas']);
                                            foreach ($vitimas as $vitimaDetalhes): 
                                                // Divide o nome da vítima e os crimes
                                                $detalhes = explode("\n", $vitimaDetalhes);
                                                $nomeVitima = strtoupper(trim($detalhes[0] ?? '')); // Nome em uppercase
                                            ?>
                                                <div class="mb-3"> <!-- Espaçamento entre vítimas -->
                                                    <strong><i class="bi bi-person-fill"></i> <?= htmlspecialchars($nomeVitima) ?></strong>
                                                    <?php 
                                                    // Exibe os crimes relacionados, cada um em uma linha
                                                    foreach (array_slice($detalhes, 1) as $crime): ?>
                                                        <div class="text-muted">
                                                            (<?= htmlspecialchars(trim($crime)) ?>)
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Nenhuma vítima cadastrada</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php 
                                        $investigados = explode("\n", $procedimento['Investigados'] ?? '');
                                        if (!empty($procedimento['Investigados']) && count($investigados) > 0): 
                                            foreach ($investigados as $investigado): ?>
                                                <div><strong><i class="bi bi-person-fill"></i> <?= strtoupper(htmlspecialchars($investigado)) ?></strong></div>
                                            <?php endforeach; 
                                        else: ?>
                                            <span class="text-muted">Ignorado(a)</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php 
                                        $prazos = json_decode($procedimento['Movimentacoes'] ?? '[]', true);

                                        if (!empty($prazos)): 
                                            usort($prazos, function ($a, $b) {
                                                return $a['dias_restantes'] <=> $b['dias_restantes'];
                                            });

                                            foreach ($prazos as $prazo): 
                                                $diasRestantes = $prazo['dias_restantes'] ?? 'N/A';
                                                $prioridade = htmlspecialchars($prazo['prioridade'] ?? ''); 
                                                $dataVencimento = !empty($prazo['data_vencimento']) 
                                                    ? date('d/m/Y', strtotime($prazo['data_vencimento'])) 
                                                    : 'Não informado'; 
                                                $tipoMovimentacao = htmlspecialchars($prazo['nome_tipo_movimentacao'] ?? 'Não informado'); 
                                                $prazoID = $prazo['id'] ?? null; 
                                                $detalhes = htmlspecialchars($prazo['detalhes'] ?? 'Não informado'); // Campo Detalhes

                                                if ($diasRestantes <= PRAZO_LARANJA) {
                                                    $badgeClass = 'text-bg-danger'; 
                                                    $iconColor = 'text-white';
                                                } elseif ($diasRestantes <= PRAZO_AMARELO) {
                                                    $badgeClass = 'text-bg-warning'; 
                                                    $iconColor = 'text-dark';
                                                } elseif ($diasRestantes <= PRAZO_VERDE) {
                                                    $badgeClass = 'text-bg-success'; 
                                                    $iconColor = 'text-white';
                                                } else {
                                                    $badgeClass = 'text-bg-success'; 
                                                    $iconColor = 'text-white';
                                                }

                                                $alertIcon = ($prioridade === 'Alta') 
                                                    ? "<i class='bi bi-exclamation-triangle-fill $iconColor'></i>" 
                                                    : '';

                                                $tooltipContent = 
                                                    "<strong>Assunto:</strong> " . htmlspecialchars($prazo['assunto'] ?? 'Não informado') . "<br>" .
                                                    "<strong>Data de Vencimento:</strong> " . $dataVencimento . "<br>" .
                                                    "<strong>Categoria:</strong> " . $tipoMovimentacao . "<br>" .
                                                    "<strong>Detalhes:</strong> " . $detalhes; // Adicionado Detalhes
                                                ?>
                                                <a 
                                                    href="mov.php?id=<?= htmlspecialchars($prazoID ?? '') ?>&procedimento_id=<?= htmlspecialchars($procedimento['ProcedimentoID'] ?? '') ?>" 
                                                    class="badge prazo-badge <?= $badgeClass ?>" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    title="<?= nl2br(htmlspecialchars($tooltipContent)) ?>" 
                                                    data-bs-custom-class="tooltip-custom"
                                                >
                                                    <?= htmlspecialchars($diasRestantes) ?> <?= $alertIcon ?>
                                                </a>
                                            <?php endforeach; 
                                        else: ?>
                                            <span class="text-success">Sem prazos</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            <!-- Botão Visualizar -->
                                            <a href="ver_procedimento.php?id=<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>" class="btn btn-secondary btn-acao">
                                                <i class="bi bi-eye"></i> Visualizar
                                            </a>
                                            
                                            <!-- Botão Anotações -->
                                            <?php
                                            // Define a classe do botão com base no número de anotações
                                            $botaoAnotacoesClasse = ($procedimento['AnotacoesCount'] ?? 0) > 0 ? 'btn-warning' : 'btn-secondary';
                                            ?>
                                            <a href="anotacoes.php?id=<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>" 
                                               class="btn <?= $botaoAnotacoesClasse ?> btn-acao"
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="<?= nl2br(htmlspecialchars($procedimento['UltimaAnotacao'] ?? 'Sem anotações disponíveis')) ?>">
                                                <i class="bi bi-journal-text"></i> 
                                                Anotações (<?= $procedimento['AnotacoesCount'] ?? 0 ?>)
                                            </a>

                                            <!-- Botão Movimentação -->
                                            <a href="mov.php?procedimento_id=<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>" 
                                               class="btn btn-primary btn-acao">
                                                <i class="bi bi-plus-circle"></i> Movimentação
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginação -->
        <nav aria-label="Navegação de página">
            <ul class="pagination justify-content-center">
                <?php if ($paginaAtual > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= gerarUrlComParametros($paginaAtual - 1) ?>">Anterior</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                        <a class="page-link" href="<?= gerarUrlComParametros($i) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($paginaAtual < $totalPaginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= gerarUrlComParametros($paginaAtual + 1) ?>">Próximo</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl, {
                html: true,
                container: 'body'
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipo'); // Campo Tipo
        const situacaoSelect = document.getElementById('situacao_id'); // Campo Situação

        // Salvar todas as opções iniciais de "Situação" para uso posterior
        const situacoesOriginais = Array.from(situacaoSelect.options);

        // Função para atualizar opções do campo "Situação"
        function atualizarSituacoes() {
            const tipoSelecionado = tipoSelect.value;

            // Verificar se o tipo foi selecionado
            if (!tipoSelecionado) {
                situacaoSelect.innerHTML = '<option value="">Informe o tipo</option>'; // Exibir mensagem padrão
                situacaoSelect.disabled = true; // Desabilitar o campo
                return;
            }

            // Mapear tipos para suas situações correspondentes
            const situacoesPorTipo = {
                IP: [1, 2, 3, 4, 5, 6, 7], // IDs de situações correspondentes ao tipo "IP"
                VPI: [8, 9, 10, 11] // IDs de situações correspondentes ao tipo "VPI"
            };

            // Obter as opções correspondentes ao tipo selecionado
            const situacoesPermitidas = situacoesPorTipo[tipoSelecionado] || [];

            // Atualizar o select com as opções válidas
            situacaoSelect.innerHTML = '<option value="">Todos</option>'; // Opção padrão inicial
            situacoesOriginais.forEach(option => {
                if (situacoesPermitidas.includes(parseInt(option.value))) {
                    situacaoSelect.appendChild(option.cloneNode(true)); // Adicionar a opção ao select
                }
            });

            situacaoSelect.disabled = false; // Habilitar o campo
        }

        // Ouvir mudanças no campo "Tipo"
        tipoSelect.addEventListener('change', atualizarSituacoes);

        // Inicializar opções na carga da página
        atualizarSituacoes();
    });
</script>

<?php include '../includes/footer.php'; ?>