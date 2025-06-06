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

// Adicionar após a declaração dos outros filtros
$filtroMovimentacao = $_GET['filtro_movimentacao'] ?? '';

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
    sp.ID AS SituacaoID,
    op.Nome AS Origem,
    p.DataInstauracao,
    sp.Cor AS Cor,
    (
        SELECT COUNT(*) > 0 
        FROM Objetos o 
        WHERE o.ProcedimentoID = p.ID
    ) AS TemObjetos,
        (
            SELECT 1 
            FROM FavoritosUsuarios fu 
            WHERE fu.ProcedimentoID = p.ID AND fu.UsuarioID = :usuarioLogadoID
        ) AS Favorito,
    u.Nome AS EscrivaoNome,
    (
        SELECT CASE 
            WHEN p.TipoID = 2 AND sp.ID IN (9, 10, 11) THEN -- Ignora status específicos de VPI
                0
            WHEN sp.ID IN (7) THEN -- Arquivada (IP)
                0
            WHEN sp.ID IN (5, 6) THEN -- Enviado ao PJ com/sem autoria
                CASE 
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m 
                        WHERE m.ProcedimentoID = p.ID 
                        AND m.TipoID = 1 
                        AND m.Situacao = 'Em andamento'
                    ) THEN 0 -- Não tem Requisição MP pendente
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ) THEN 2
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 15 DAY)
                    ) THEN 1
                    ELSE 0
                END
            ELSE -- Outros status
                CASE 
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ) THEN 2
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 15 DAY)
                    ) THEN 1
                    ELSE 0
                END
        END
    ) AS StatusMovimentacao,
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

// Modificar a query principal para incluir o filtro de movimentação
if (!empty($filtroMovimentacao)) {
    $filterCondition = "
        CASE 
            WHEN p.TipoID = 2 AND sp.ID IN (9, 10, 11) THEN -- Ignora status específicos de VPI
                0
            WHEN sp.ID IN (7) THEN -- Arquivada (IP)
                0
            WHEN sp.ID IN (5, 6) THEN -- Enviado ao PJ com/sem autoria
                CASE 
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m 
                        WHERE m.ProcedimentoID = p.ID 
                        AND m.TipoID = 1 
                        AND m.Situacao = 'Em andamento'
                    ) THEN 0 -- Não tem Requisição MP pendente
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ) THEN 2
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 15 DAY)
                    ) THEN 1
                    ELSE 0
                END
            ELSE -- Outros status
                CASE 
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ) THEN 2
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 15 DAY)
                    ) THEN 1
                    ELSE 0
                END
        END = :filtro_movimentacao";

    $query .= " AND " . $filterCondition;
    $params['filtro_movimentacao'] = (int)$filtroMovimentacao;
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

// Modificar o filtro de movimentação na query principal
if (!empty($filtroMovimentacao)) {
    $filterCondition = "
        CASE 
            WHEN p.TipoID = 2 AND sp.ID IN (9, 10, 11) THEN -- Ignora status específicos de VPI
                0
            WHEN sp.ID IN (7) THEN -- Arquivada (IP)
                0
            WHEN sp.ID IN (5, 6) THEN -- Enviado ao PJ com/sem autoria
                CASE 
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m 
                        WHERE m.ProcedimentoID = p.ID 
                        AND m.TipoID = 1 
                        AND m.Situacao = 'Em andamento'
                    ) THEN 0 -- Não tem Requisição MP pendente
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ) THEN 2
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 15 DAY)
                    ) THEN 1
                    ELSE 0
                END
            ELSE -- Outros status
                CASE 
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ) THEN 2
                    WHEN NOT EXISTS (
                        SELECT 1 
                        FROM Movimentacoes m3 
                        WHERE m3.ProcedimentoID = p.ID 
                        AND m3.DataCriacao > DATE_SUB(NOW(), INTERVAL 15 DAY)
                    ) THEN 1
                    ELSE 0
                END
        END = :filtro_movimentacao";

    $query .= " AND " . $filterCondition;
    $countQuery .= " AND " . $filterCondition;
    $params['filtro_movimentacao'] = (int)$filtroMovimentacao;
    $countParams['filtro_movimentacao'] = (int)$filtroMovimentacao;
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

<div class="container mt-5" style="max-width: 95%; margin: 0 auto;">
    <!-- Cabeçalho e botão "Novo Procedimento" -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h3">Procedimentos</h1>
<a href="cadastrar_procedimento.php" class="btn btn-primary btn-l d-flex align-items-center">
    <i class="bi bi-plus-circle me-2"></i> Novo Procedimento
</a>
    </div>

    <!-- Total de Procedimentos -->
    <p class="text-left text-muted">
        Total de Procedimentos Encontrados: <strong><?= htmlspecialchars($totalRegistros) ?></strong>
    </p>

<!-- Formulário de Pesquisa -->
<div class="bg-light p-3 rounded shadow-sm border border-secondary mb-4">
    <form method="GET">
        <div class="row g-3 align-items-center">
            <!-- Primeira linha de filtros -->
            <div class="col-md-2">
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

            <div class="col-md-2">
                <label for="tipo" class="form-label">Tipo</label>
                <select name="tipo" id="tipo" class="form-select">
                    <option value="">Todos os Tipos</option>
                    <option value="IP" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'IP') ? 'selected' : '' ?>>IP</option>
                    <option value="VPI" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'VPI') ? 'selected' : '' ?>>VPI</option>
                </select>
            </div>

            <div class="col-md-2">
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
            
            <div class="col-md-2">
                <label for="requisicoes_mp" class="form-label">Requisições MP</label>
                <select name="requisicoes_mp" id="requisicoes_mp" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" <?= (isset($_GET['requisicoes_mp']) && $_GET['requisicoes_mp'] === '1') ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= (isset($_GET['requisicoes_mp']) && $_GET['requisicoes_mp'] === '0') ? 'selected' : '' ?>>Não</option>
                </select>
            </div>
            
            <div class="col-md-2">
    <label for="favorito" class="form-label">Favoritos</label>
    <select name="favorito" id="favorito" class="form-select">
        <option value="">Todos</option>
        <option value="1" <?= (isset($_GET['favorito']) && $_GET['favorito'] === '1') ? 'selected' : '' ?>>Sim</option>
        <option value="0" <?= (isset($_GET['favorito']) && $_GET['favorito'] === '0') ? 'selected' : '' ?>>Não</option>
    </select>
</div>

            <div class="col-md-2">
                <label for="filtro_movimentacao" class="form-label">Movimentação</label>
                <select name="filtro_movimentacao" id="filtro_movimentacao" class="form-select">
                    <option value="">Todas</option>
                    <option value="2" <?= $filtroMovimentacao === '2' ? 'selected' : '' ?>>+30 dias</option>
                    <option value="1" <?= $filtroMovimentacao === '1' ? 'selected' : '' ?>>+15 dias</option>
                </select>
            </div>
        </div>

        <!-- Segunda linha com campo de busca e botões -->
        <div class="row g-3 align-items-center mt-3">
            <div class="col-md-9">
                <label for="search" class="form-label">Pesquisar</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Digite para pesquisar..." value="<?= htmlspecialchars($searchTerm) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label d-none d-md-block">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search"></i> Pesquisar
                    </button>
                    <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary flex-grow-1">
                        <i class="bi bi-x-circle"></i> Limpar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Tabela de Exibição -->
<div class="table-responsive border border-secondary rounded shadow-sm">
    <table class="table table-striped table-sm text-nowrap">
        <thead class="table-dark">
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
                    <td colspan="8" class="text-center">Nenhum procedimento encontrado.</td>
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
<span class="badge mb-2 <?= $situacaoClasse ?>">
    <?php if (!empty($procedimento['Favorito'])): ?>
        <i class="bi bi-star-fill me-1 text-warning" title="Favorito"></i>
    <?php endif; ?>
    <?= htmlspecialchars($procedimento['Situacao'] ?? '') ?>
</span>

    <br>

    <!-- Nome do escrivão -->
    <?php if (!empty($procedimento['EscrivaoNome'])): ?>
        <small class="text-muted">
            <i class="bi bi-person-fill"></i> <?= htmlspecialchars($procedimento['EscrivaoNome']) ?>
        </small>
    <?php endif; ?>
    <br>

    <!-- Data de Instauração -->
    <?php if (!empty($procedimento['DataInstauracao'])): ?>
        <small class="text-muted">
            <i class="bi bi-calendar-event-fill"></i> Instauração: <?= htmlspecialchars(date('d/m/Y', strtotime($procedimento['DataInstauracao']))) ?>
        </small>
    <?php endif; ?>
    <br>

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

<td>
<?php
$tipo = $procedimento['TipoID'] == 1 ? 'IP ' : 'VPI ';
$numeroProcedimento = htmlspecialchars($procedimento['NumeroProcedimento'] ?? '');
?>

<?= $tipo ?>
<a href="https://spp.ssp.go.gov.br/documentos?procedimentoId=<?= $numeroProcedimento ?>" 
   target="_blank" 
   class="text-decoration-none">
    <?= $numeroProcedimento ?>
</a>
<!-- Ícone de cópia para o número do procedimento -->
<button type="button" 
        class="btn btn-link btn-sm p-0 ms-1 copy-btn" 
        data-copy-text="<?= $numeroProcedimento ?>"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="Copiar número do procedimento">
    <i class="bi bi-clipboard"></i>
</button>

<!-- Ícone de informação -->
<i class="bi bi-info-circle ms-2 text-primary"
   data-bs-toggle="tooltip"
   data-bs-placement="top"
   title="<?= htmlspecialchars($procedimento['MotivoAparente'] ?? 'Motivo não informado') ?>"></i>

<!-- Ícone de objetos -->
<?php if (!empty($procedimento['TemObjetos'])): ?>
    <i class="bi bi-box-seam ms-2 text-success"
       data-bs-toggle="tooltip"
       data-bs-placement="top"
       title="Este procedimento possui objetos vinculados"></i>
<?php endif; ?>

<!-- Ícone de alerta para movimentação antiga -->
<?php if (!empty($procedimento['StatusMovimentacao'])): ?>
    <?php if ($procedimento['StatusMovimentacao'] == 2): ?>
        <i class="bi bi-exclamation-circle-fill ms-2 text-danger"
           data-bs-toggle="tooltip"
           data-bs-placement="top"
           title="Sem movimentação há mais de 30 dias"></i>
    <?php elseif ($procedimento['StatusMovimentacao'] == 1): ?>
        <i class="bi bi-exclamation-circle-fill ms-2 text-warning"
           data-bs-toggle="tooltip"
           data-bs-placement="top"
           title="Sem movimentação há mais de 15 dias"></i>
    <?php endif; ?>
<?php endif; ?>

    <br>
<?php 
if (!empty($procedimento['RAIs'])): 
    $rais = explode("\n", $procedimento['RAIs']);
    $primeiroRai = htmlspecialchars($rais[0]); // Primeiro RAI
    $quantidadeAdicional = count($rais) - 1; // Quantidade adicional
?>
    RAI <a href="https://atendimento.ssp.go.gov.br/#/atendimento/<?= htmlspecialchars($primeiroRai) ?>" 
        target="_blank" class="text-decoration-none"><?= htmlspecialchars($primeiroRai) ?></a>
    <!-- Ícone de cópia para o RAI -->
    <button type="button" 
            class="btn btn-link btn-sm p-0 ms-1 copy-btn" 
            data-copy-text="<?= htmlspecialchars($primeiroRai) ?>"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="Copiar número do RAI">
        <i class="bi bi-clipboard"></i>
    </button>
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

<td class="text-left break-column">
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
                ? "<i class='bi bi-exclamation-triangle-fill $iconColor ms-1'></i>" 
                : '';

            $tooltipContent = 
                "<strong>Assunto:</strong> " . htmlspecialchars($prazo['assunto'] ?? 'Não informado') . "<br>" .
                "<strong>Data de Vencimento:</strong> " . $dataVencimento . "<br>" .
                "<strong>Categoria:</strong> " . $tipoMovimentacao . "<br>" .
                "<strong>Detalhes:</strong> " . $detalhes; // Adicionado Detalhes
            ?>
            <a 
                href="mov.php?id=<?= htmlspecialchars($prazoID ?? '') ?>&procedimento_id=<?= htmlspecialchars($procedimento['ProcedimentoID'] ?? '') ?>" 
                class="badge <?= $badgeClass ?> custom-link prazo-link" 
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

<td class="text-center">
    <div class="d-flex flex-column">
        <!-- Botão Visualizar -->
        <a href="ver_procedimento.php?id=<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>" class="btn btn-secondary btn-sm mb-2">
            <i class="bi bi-eye me-2"></i> Visualizar
        </a>

<!-- Botão Anotações -->
<?php
// Define a classe do botão com base no número de anotações
$botaoAnotacoesClasse = ($procedimento['AnotacoesCount'] ?? 0) > 0 ? 'btn-warning' : 'btn-secondary';
?>
<button type="button" 
        class="btn <?= $botaoAnotacoesClasse ?> btn-sm mb-2"
        data-bs-toggle="modal" 
        data-bs-target="#modalAnotacoes<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>"
        data-procedimento-id="<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>"
        data-procedimento-numero="<?= htmlspecialchars($procedimento['NumeroProcedimento']) ?>">
    <i class="bi bi-journal-text me-2"></i> 
    Anotações (<?= $procedimento['AnotacoesCount'] ?? 0 ?>)
</button>

<!-- Modal de Anotações -->
<div class="modal fade" id="modalAnotacoes<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <h4 class="modal-title fw-bold">
                    <i class="bi bi-journal-text me-2"></i>
                    Anotações - <?= htmlspecialchars($procedimento['NumeroProcedimento']) ?>
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Formulário -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 text-primary fw-bold">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nova Anotação
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="formAnotacao<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>">
                            <input type="hidden" name="procedimento_id" value="<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>">
                            <div class="mb-3">
                                <textarea class="form-control form-control-lg" 
                                        name="anotacao" 
                                        rows="4" 
                                        placeholder="Digite sua anotação aqui..."
                                        required
                                        style="resize: vertical; min-height: 100px;"></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="bi bi-plus-circle me-2"></i>Adicionar Anotação
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de Anotações -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 text-primary fw-bold">
                            <i class="bi bi-list-ul me-2"></i>
                            Histórico de Anotações
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="anotacoes-lista" id="anotacoesLista<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Carregando anotações...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-0 py-3">
                <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>

        <!-- Botão Movimentação -->
        <a href="mov.php?procedimento_id=<?= htmlspecialchars($procedimento['ProcedimentoID']) ?>" 
           class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-2"></i> Movimentação
        </a>

    </div>
</td>

                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

        <nav aria-label="Navegação de página">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                        <a class="page-link" href="<?= gerarUrlComParametros($i) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
</div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true, // Permite exibir conteúdo com HTML no tooltip
            container: 'body' // Garante que o tooltip não quebre o layout
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true, // Permite conteúdo em HTML
            container: 'body', // Garante que a tooltip não quebre o layout
            customClass: 'tooltip-custom' // Aplica a classe customizada
        });
    });
});
</script>

<script>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true, // Permite conteúdo em HTML
            container: 'body' // Garante que o tooltip não quebre o layout
        });
    });
});
</script>

<!-- Adicionar antes do fechamento do body -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Função para copiar texto
    function copyToClipboard(text) {
        // Criar um elemento textarea temporário
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';  // Evitar scroll
        textarea.style.opacity = '0';       // Tornar invisível
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            // Executar o comando de cópia
            const successful = document.execCommand('copy');
            if (!successful) {
                throw new Error('Falha ao copiar');
            }
            return true;
        } catch (err) {
            console.error('Erro ao copiar:', err);
            return false;
        } finally {
            // Remover o textarea temporário
            document.body.removeChild(textarea);
        }
    }

    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });

    // Adicionar evento de clique para os botões de cópia
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Prevenir comportamento padrão do botão
            
            const textToCopy = this.getAttribute('data-copy-text');
            const tooltip = bootstrap.Tooltip.getInstance(this);
            
            // Tentar copiar o texto
            if (copyToClipboard(textToCopy)) {
                // Atualizar o ícone temporariamente
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                
                // Mudar para ícone de check
                icon.className = 'bi bi-check2';
                this.classList.add('text-success');
                
                // Atualizar tooltip
                if (tooltip) {
                    tooltip.dispose();
                }
                
                const newTooltip = new bootstrap.Tooltip(this, {
                    title: 'Copiado!',
                    trigger: 'manual'
                });
                newTooltip.show();
                
                // Restaurar após 2 segundos
                setTimeout(() => {
                    icon.className = originalClass;
                    this.classList.remove('text-success');
                    if (newTooltip) {
                        newTooltip.dispose();
                    }
                    new bootstrap.Tooltip(this, {
                        title: this.getAttribute('title'),
                        trigger: 'hover'
                    });
                }, 2000);
            } else {
                // Mostrar mensagem de erro
                if (tooltip) {
                    tooltip.dispose();
                }
                
                const errorTooltip = new bootstrap.Tooltip(this, {
                    title: 'Erro ao copiar!',
                    trigger: 'manual'
                });
                errorTooltip.show();
                
                setTimeout(() => {
                    if (errorTooltip) {
                        errorTooltip.dispose();
                    }
                    new bootstrap.Tooltip(this, {
                        title: this.getAttribute('title'),
                        trigger: 'hover'
                    });
                }, 2000);
            }
        });
    });
});
</script>

<style>
/* Ajustes no modal de anotações - DESIGN MODERNO */
.modal-xl {
    max-width: 1200px;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    padding: 1.5rem 2rem;
    border-bottom: none;
}

.modal-body {
    padding: 2rem !important;
    background-color: #f8f9fa !important;
}

.modal-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid #e9ecef;
}

/* Cards dentro do modal */
.card {
    border-radius: 12px;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    padding: 1rem 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* Avatar circular */
.avatar-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

/* Estrutura das anotações - DESIGN MELHORADO */
.anotacao-item {
    padding: 1.5rem !important;
    margin: 0;
    transition: background-color 0.3s ease;
}

.anotacao-item:hover {
    background-color: rgba(0,123,255,0.02);
}

.anotacao-item:last-child {
    border-bottom: none !important;
}

/* Conteúdo da anotação - VISUAL APRIMORADO */
.anotacao-conteudo {
    color: #495057;
    line-height: 1.7;
    white-space: normal;
    word-break: break-word;
    text-align: left;
    padding: 1rem 1.5rem !important;
    margin: 0 !important;
    margin-left: 0 !important;
    padding-left: 1.5rem !important;
    text-indent: 0 !important;
    display: block;
    width: 100%;
    font-size: 0.95rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    position: relative;
}

/* Botões melhorados */
.btn-lg {
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 500;
}

.rounded-pill {
    border-radius: 50px !important;
}

/* Textarea melhorado */
.form-control-lg {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control-lg:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.1);
    transform: translateY(-1px);
}

/* Efeitos de loading melhorados */
.spinner-border {
    width: 2rem;
    height: 2rem;
}

/* Responsividade */
@media (max-width: 768px) {
    .modal-xl {
        max-width: 95%;
        margin: 1rem;
    }
    
    .modal-body {
        padding: 1rem !important;
    }
    
    .avatar-circle {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .anotacao-item {
        padding: 1rem !important;
    }
}

/* Garantir alinhamento - mantendo as regras anteriores */
.anotacoes-lista .anotacao-item {
    margin-left: 0 !important;
    padding-left: 1.5rem !important;
}

.anotacoes-lista .anotacao-conteudo {
    margin-left: 0 !important;
    text-indent: 0 !important;
    border: none !important;
    background: white !important;
}

.modal-body .anotacoes-lista .anotacao-conteudo {
    position: relative;
    left: 0 !important;
    transform: none !important;
    margin-left: 0 !important;
}

.anotacao-item .avatar-circle {
    margin-left: 0 !important;
    margin-right: 1rem !important;
}

/* Estilo para o botão de cópia */
.copy-btn {
    color: #6c757d;
    transition: all 0.2s ease;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    margin-left: 0.25rem;
}

.copy-btn:hover {
    color: #007bff;
    transform: scale(1.1);
}

.copy-btn.text-success {
    color: #28a745 !important;
}

.copy-btn:focus {
    outline: none;
    box-shadow: none;
}
</style>

<?php include '../includes/footer.php'; ?>