<?php
require_once 'funcoes.php';
session_start();

// Exibe mensagem da sessão (se existir) e limpa
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']);
}
// Definir o número de registros por página
$registrosPorPagina = 20;
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($paginaAtual - 1) * $registrosPorPagina;

// Contar o número total de registros
$totalRegistros = $pdo->query("SELECT COUNT(*) FROM rec_recompensas")->fetchColumn();

// Calcular o número total de páginas
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Buscar os registros para a página atual
$stmt = $pdo->prepare("SELECT r.*, p.nome AS policial_nome 
                       FROM rec_recompensas r 
                       LEFT JOIN rec_policiais p ON r.policial_id = p.id
                       ORDER BY r.id DESC
                       LIMIT :inicio, :quantidade");
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindValue(':quantidade', $registrosPorPagina, PDO::PARAM_INT);
$stmt->execute();
$recompensas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$proximoPolicial = obterProximoPolicial($pdo);

$filtroPolicial = isset($_GET['filtro_policial']) ? (int)$_GET['filtro_policial'] : null;

$sql = "SELECT r.*, p.nome AS policial_nome 
        FROM rec_recompensas r 
        LEFT JOIN rec_policiais p ON r.policial_id = p.id";

// Adiciona o filtro, se aplicável
if ($filtroPolicial) {
    $sql .= " WHERE r.policial_id = :policial_id";
}

// Adiciona ordenação por ID e paginação
$sql .= " ORDER BY r.id DESC LIMIT :inicio, :quantidade";

$stmt = $pdo->prepare($sql);

// Define os parâmetros
if ($filtroPolicial) {
    $stmt->bindValue(':policial_id', $filtroPolicial, PDO::PARAM_INT);
}
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindValue(':quantidade', $registrosPorPagina, PDO::PARAM_INT);
$stmt->execute();
$recompensas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Recompensas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>
    
<?php if (isset($mensagem)): ?>
    <div class="alert alert-<?= htmlspecialchars($mensagem['tipo']) ?> text-center">
        <?= htmlspecialchars($mensagem['conteudo']) ?>
    </div>
<?php endif; ?>
<div class="container mt-5">


    
    <h1 class="mb-4">Escala de Recompensas</h1>
    
    <div class="alert alert-warning text-center my-3">
    Próximo Policial na Ordem: <h3><?= htmlspecialchars($proximoPolicial['nome'] ?? 'Nenhum policial cadastrado') ?></h3>
</div>

<div class="d-flex justify-content-between align-items-end mb-4">
    <!-- Formulário -->
    <form method="GET" class="d-flex flex-grow-1">
        <div class="row align-items-end flex-grow-1">
            <div class="col-md-4">
                <label for="filtro_policial" class="form-label">Filtrar por Policial</label>
                <select class="form-select" id="filtro_policial" name="filtro_policial">
                    <option value="">Todos</option>
                    <?php foreach (obterPoliciais($pdo) as $policial): ?>
                        <option value="<?= $policial['id'] ?>" <?= isset($_GET['filtro_policial']) && $_GET['filtro_policial'] == $policial['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($policial['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </div>
    </form>

    <!-- Botão Cadastrar -->
    <a href="cadastrar.php" class="btn btn-primary align-self-end ms-3">
        <i class="bi bi-plus-circle"></i> Cadastrar Recompensa
    </a>
</div>



    
<div class="table-responsive d-none d-md-block">
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Data da Solicitação</th>
                <th>RAI</th>
                <th>Policial</th>
                <th>Observações</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($recompensas as $rec): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($rec['data_solicitacao'])) ?></td>
                <td><?= htmlspecialchars($rec['rai']) ?></td>
                <td><?= htmlspecialchars($rec['policial_nome'] ?? 'Delegacia') ?></td>
                <td><?= htmlspecialchars($rec['observacoes']) ?></td>
                <td>
<span class="badge <?= $rec['status'] === 'Aprovada' ? 'bg-success' : ($rec['status'] === 'Solicitada' ? 'bg-warning text-dark' : 'bg-danger') ?>">
    <?= htmlspecialchars($rec['status']) ?>
</span>

                </td>
            <td>
                <div class="d-flex gap-2">
                    <a href="editar.php?id=<?= $rec['id'] ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <?php if ($rec['status'] === 'Solicitada'): ?>
                        <a href="aprovar.php?id=<?= $rec['id'] ?>" class="btn btn-success btn-sm" 
                           onclick="return confirm('Tem certeza que deseja aprovar esta recompensa?');">
                            <i class="bi bi-check-circle"></i> Aprovar
                        </a>
                    <?php endif; ?>
                    <a href="excluir.php?id=<?= $rec['id'] ?>" class="btn btn-danger btn-sm" 
                       onclick="return confirm('Tem certeza que deseja excluir esta recompensa?');">
                        <i class="bi bi-trash"></i> Excluir
                    </a>
                </div>
            </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="d-md-none">
    <?php foreach ($recompensas as $rec): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">RAI: <?= htmlspecialchars($rec['rai']) ?></h5>
                <p class="card-text">
                    <strong>Data da Solicitação:</strong> <?= date('d/m/Y', strtotime($rec['data_solicitacao'])) ?><br>
                    <strong>Policial:</strong> <?= htmlspecialchars($rec['policial_nome'] ?? 'Delegacia') ?><br>
                    <strong>Status:</strong> 
                    <span class="badge <?= $rec['status'] === 'Paga' ? 'bg-success' : ($rec['status'] === 'Solicitada' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                        <?= htmlspecialchars($rec['status']) ?>
                    </span><br>
                    <strong>Observações:</strong> <?= htmlspecialchars($rec['observacoes']) ?>
                </p>
                <div class="d-flex gap-2">
                    <a href="editar.php?id=<?= $rec['id'] ?>" class="btn btn-warning btn-sm flex-fill">Editar</a>
                    <a href="excluir.php?id=<?= $rec['id'] ?>" class="btn btn-danger btn-sm flex-fill" 
                       onclick="return confirm('Tem certeza que deseja excluir esta recompensa?');">Excluir</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


    
<nav aria-label="Paginação">
    <ul class="pagination justify-content-center">
        <?php if ($paginaAtual > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?pagina=<?= $paginaAtual - 1 ?>" aria-label="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($paginaAtual < $totalPaginas): ?>
            <li class="page-item">
                <a class="page-link" href="?pagina=<?= $paginaAtual + 1 ?>" aria-label="Próximo">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

    
</div>
</body>
</html>
