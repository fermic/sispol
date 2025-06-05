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

// Prepara e executa a contagem
$stmt_count = $pdo->prepare($query_count);
foreach ($filtros as $key => $value) {
    if (!empty($value) && $key !== 'possui_processo') {
        $stmt_count->bindValue(":$key", $key === 'numero_serie' || $key === 'lacre' ? "%$value%" : $value, PDO::PARAM_STR);
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

// Ordenação e paginação
$query .= " ORDER BY o.DataApreensao DESC LIMIT :offset, :limit";

$stmt = $pdo->prepare($query);
foreach ($filtros as $key => $value) {
    if (!empty($value) && $key !== 'possui_processo') {
        $stmt->bindValue(":$key", $key === 'numero_serie' || $key === 'lacre' ? "%$value%" : $value, PDO::PARAM_STR);
    }
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$armas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5" style="max-width: 95%; margin: 0 auto;">
    <h1 class="text-center">Armas de Fogo</h1>

    <!-- Formulário de filtros -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="especie" class="form-label">Espécie</label>
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
                <label for="calibre" class="form-label">Calibre</label>
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
                <label for="marca" class="form-label">Marca</label>
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
            <div class="col-md-4 mb-3">
                <label for="numero_serie" class="form-label">Número de Série</label>
                <input type="text" name="numero_serie" id="numero_serie" class="form-control" value="<?= htmlspecialchars($filtros['numero_serie']) ?>" placeholder="Número de Série">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="lacre" class="form-label">Lacre Atual</label>
                <input type="text" name="lacre" id="lacre" class="form-control" value="<?= htmlspecialchars($filtros['lacre']) ?>" placeholder="Lacre Atual">
            </div>
      
        
            <div class="col-md-4 mb-3">
                <label for="possui_processo" class="form-label">Possui Processo Vinculado?</label>
                <select name="possui_processo" id="possui_processo" class="form-select">
                    <option value="">Todos</option>
                    <option value="sim" <?= $filtros['possui_processo'] == 'sim' ? 'selected' : '' ?>>Sim</option>
                    <option value="nao" <?= $filtros['possui_processo'] == 'nao' ? 'selected' : '' ?>>Não</option>
                </select>
            </div>
    </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="armas.php" class="btn btn-secondary">Limpar Filtros</a>
    </form>

    <!-- Tabela de armas -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Situação</th>
                <th>Espécie</th>
                <th>Calibre</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Número de Série</th>
                <th>Lacre Atual</th>
                <th>Data da Apreensão</th>
                <th>Local de Armazenagem</th>
                <th>Procedimento Relacionado</th>
                <th>Processo</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($armas): ?>
                <?php foreach ($armas as $arma): ?>
                    <tr>
                        <td><?= htmlspecialchars($arma['Situacao']) ?></td>
                        <td><?= htmlspecialchars($arma['Especie']) ?></td>
                        <td><?= htmlspecialchars($arma['Calibre']) ?></td>
                        <td><?= htmlspecialchars($arma['Marca']) ?></td>
                        <td><?= htmlspecialchars($arma['Modelo']) ?></td>
                        <td><?= htmlspecialchars($arma['NumeroSerie']) ?></td>
                        <td><?= htmlspecialchars($arma['LacreAtual']) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($arma['DataApreensao']))) ?></td>
                        <td><?= htmlspecialchars($arma['LocalArmazenagem']) ?></td>
                        <td>
    <?php if (!empty($arma['NumeroProcedimento'])): ?>
        <a href="ver_procedimento.php?id=<?= htmlspecialchars($arma['ProcedimentoID']) ?>">
            <?= htmlspecialchars($arma['NumeroProcedimento']) ?>
        </a>
    <?php else: ?>
        Não informado
    <?php endif; ?>
</td>

                        <td><?= !empty($arma['Processo']) ? htmlspecialchars($arma['Processo']) : 'Sem processo vinculado' ?></td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" class="text-center">Nenhuma arma encontrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <nav aria-label="Pagination">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $current_page - 1 ?>" tabindex="-1">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $current_page + 1 ?>">Próxima</a>
            </li>
        </ul>
    </nav>

</div>

<?php include '../includes/footer.php'; ?>
