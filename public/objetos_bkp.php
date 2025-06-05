<?php
session_start();
include '../includes/header.php';
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}

// Configuração de paginação
$registrosPorPagina = 20;
$paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $registrosPorPagina;

// Obter os valores para os filtros
$tiposObjeto = $pdo->query("SELECT ID, Nome FROM TiposObjeto ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
$situacoes = $pdo->query("SELECT ID, Nome FROM SituacoesObjeto ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
$locais = $pdo->query("SELECT ID, Nome FROM LocaisArmazenagem ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);

// Campos de busca
$filtros = [
    'tipo_objeto' => $_GET['tipo_objeto'] ?? '',
    'situacao' => $_GET['situacao'] ?? '',
    'local' => $_GET['local'] ?? '',
    'descricao' => $_GET['descricao'] ?? '',
    'lacre' => $_GET['lacre'] ?? '',
];

// Construção da query para listar objetos
$queryBase = "
    SELECT 
        o.ID AS ObjetoID,
        o.ProcedimentoID AS ProcedimentoID,
        t.Nome AS TipoObjeto,
        o.Descricao,
        o.Quantidade,
        o.LacreAtual,
        o.DataApreensao,
        s.Nome AS Situacao,
        la.Nome AS LocalArmazenagem,
        p.NumeroProcedimento
    FROM Objetos o
    INNER JOIN TiposObjeto t ON o.TipoObjetoID = t.ID
    INNER JOIN SituacoesObjeto s ON o.SituacaoID = s.ID
    INNER JOIN LocaisArmazenagem la ON o.LocalArmazenagemID = la.ID
    LEFT JOIN Procedimentos p ON o.ProcedimentoID = p.ID
    WHERE o.TipoObjetoID != 4
";

// Adiciona os filtros selecionados
$queryCondicoes = "";
if (!empty($filtros['tipo_objeto'])) {
    $queryCondicoes .= " AND t.ID = :tipo_objeto";
}
if (!empty($filtros['situacao'])) {
    $queryCondicoes .= " AND s.ID = :situacao";
}
if (!empty($filtros['local'])) {
    $queryCondicoes .= " AND la.ID = :local";
}
if (!empty($filtros['descricao'])) {
    $queryCondicoes .= " AND o.Descricao LIKE :descricao";
}
if (!empty($filtros['lacre'])) {
    $queryCondicoes .= " AND o.LacreAtual LIKE :lacre";
}

// Total de registros para paginação
$totalQuery = "SELECT COUNT(*) FROM Objetos o WHERE o.TipoObjetoID != 4" . $queryCondicoes;
$stmtTotal = $pdo->prepare($totalQuery);
foreach ($filtros as $key => $value) {
    if (!empty($value)) {
        $stmtTotal->bindValue(":$key", $key === 'descricao' || $key === 'lacre' ? "%$value%" : $value, PDO::PARAM_STR);
    }
}
$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetchColumn();

// Adicionar limite e offset à query
$queryFinal = $queryBase . $queryCondicoes . " ORDER BY o.DataApreensao DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($queryFinal);

foreach ($filtros as $key => $value) {
    if (!empty($value)) {
        $stmt->bindValue(":$key", $key === 'descricao' || $key === 'lacre' ? "%$value%" : $value, PDO::PARAM_STR);
    }
}
$stmt->bindValue(":limit", $registrosPorPagina, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

$stmt->execute();
$objetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total de páginas
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);
?>

<div class="container mt-5" style="max-width: 95%; margin: 0 auto;">
    <h1 class="text-center">Lista de Objetos</h1>

    <!-- Formulário de filtros -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="tipo_objeto" class="form-label">Tipo de Objeto</label>
                <select name="tipo_objeto" id="tipo_objeto" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($tiposObjeto as $tipo): ?>
                        <option value="<?= $tipo['ID'] ?>" <?= $filtros['tipo_objeto'] == $tipo['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="situacao" class="form-label">Situação</label>
                <select name="situacao" id="situacao" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($situacoes as $situacao): ?>
                        <option value="<?= $situacao['ID'] ?>" <?= $filtros['situacao'] == $situacao['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($situacao['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="local" class="form-label">Local de Armazenagem</label>
                <select name="local" id="local" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($locais as $local): ?>
                        <option value="<?= $local['ID'] ?>" <?= $filtros['local'] == $local['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($local['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <input type="text" name="descricao" id="descricao" class="form-control" value="<?= htmlspecialchars($filtros['descricao']) ?>" placeholder="Descrição do Objeto">
            </div>
            <div class="col-md-4 mb-3">
                <label for="lacre" class="form-label">Lacre Atual</label>
                <input type="text" name="lacre" id="lacre" class="form-control" value="<?= htmlspecialchars($filtros['lacre']) ?>" placeholder="Lacre Atual">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="objetos.php" class="btn btn-secondary">Limpar Filtros</a>
    </form>

    <!-- Tabela de objetos -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="white-space: nowrap;">Tipo de Objeto</th>
                <th>Descrição</th>
                <th>Quantidade</th>
                <th>Lacre Atual</th>
                <th>Data da Apreensão</th>
                <th>Local de Armazenagem</th>
                <th>Procedimento Relacionado</th>
            </tr>
        </thead>
<tbody>
    <?php if ($objetos): ?>
        <?php foreach ($objetos as $objeto): ?>
            <tr>
                <td><?= htmlspecialchars($objeto['TipoObjeto']) ?></td>
                <td><?= htmlspecialchars($objeto['Descricao']) ?></td>
                <td><?= htmlspecialchars($objeto['Quantidade']) ?></td>
                <td><?= !empty($objeto['LacreAtual']) ? htmlspecialchars($objeto['LacreAtual']) : 'S/L' ?></td>
                <td><?= htmlspecialchars(date('d/m/Y', strtotime($objeto['DataApreensao']))) ?></td>
                <td><?= htmlspecialchars($objeto['LocalArmazenagem']) ?></td>
                <td>
                    <?php if (!empty($objeto['ProcedimentoID'])): ?>
                        <a href="ver_procedimento.php?id=<?= htmlspecialchars($objeto['ProcedimentoID']) ?>">
                            <?= htmlspecialchars($objeto['NumeroProcedimento']) ?>
                        </a>
                    <?php else: ?>
                        Não informado
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center">Nenhum objeto encontrado.</td>
        </tr>
    <?php endif; ?>
</tbody>

    </table>

    <!-- Paginação -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include '../includes/footer.php'; ?>
