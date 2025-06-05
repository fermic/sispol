<?php
session_start();
include '../includes/header.php'; // Inclui a navbar e configurações globais
include_once '../config/db.php'; // Conexão com o banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}

// Termo de busca do formulário
$searchTerm = $_GET['search'] ?? '';

// Query para buscar todas as movimentações
$query = "
    SELECT 
        p.NumeroProcedimento,
        tm.Nome AS TipoMovimentacao,
        m.Assunto,
        m.Detalhes,
        DATE_FORMAT(m.DataVencimento, '%d/%m/%Y') AS DataVencimento,
        m.Situacao,
        p.ID AS ProcedimentoID
    FROM Movimentacoes m
    INNER JOIN Procedimentos p ON m.ProcedimentoID = p.ID
    INNER JOIN TiposMovimentacao tm ON m.TipoID = tm.ID
    WHERE 1=1
";

// Adicionar filtros de pesquisa se um termo foi fornecido
$params = [];
if (!empty($searchTerm)) {
    $query .= " AND (
        p.NumeroProcedimento LIKE :search OR
        m.Assunto LIKE :search OR
        m.Detalhes LIKE :search
    )";
    $params['search'] = '%' . $searchTerm . '%';
}

// Ordenar por Data de Vencimento e executar a consulta
$query .= " ORDER BY m.DataVencimento ASC";
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
}
$stmt->execute();
$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Lista de Movimentações</h1>

    <!-- Formulário de Pesquisa -->
    <form method="GET" class="bg-light p-3 rounded shadow-sm border border-secondary mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-10">
                <label for="search" class="form-label">Pesquisar</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    class="form-control" 
                    placeholder="Digite Procedimento, Assunto ou Detalhes" 
                    value="<?= htmlspecialchars($searchTerm) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label d-none d-md-block">&nbsp;</label> <!-- Para alinhamento -->
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Pesquisar
                </button>
            </div>
        </div>
    </form>

    <!-- Tabela de Movimentações -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Procedimento</th>
                    <th>Tipo</th>
                    <th>Assunto</th>
                    <th>Detalhes</th>
                    <th>Data de Vencimento</th>
                    <th>Situação</th>
                    <th>Ver Procedimento</th>
                </tr>
            </thead>
<tbody>
    <?php if (!empty($movimentacoes)): ?>
        <?php foreach ($movimentacoes as $movimentacao): ?>
            <tr>
                <td><?= htmlspecialchars($movimentacao['NumeroProcedimento'] ?? '') ?></td>
                <td><?= htmlspecialchars($movimentacao['TipoMovimentacao'] ?? '') ?></td>
                <td><?= htmlspecialchars($movimentacao['Assunto'] ?? '') ?></td>
                <td><?= htmlspecialchars($movimentacao['Detalhes'] ?? '') ?></td>
                <td><?= htmlspecialchars($movimentacao['DataVencimento'] ?? '') ?></td>
                <td><?= htmlspecialchars($movimentacao['Situacao'] ?? '') ?></td>
                <td>
                    <a href="ver_procedimento.php?id=<?= htmlspecialchars($movimentacao['ProcedimentoID'] ?? '') ?>" 
                       class="btn btn-primary btn-sm">
                        Ver Procedimento
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center">Nenhuma movimentação encontrada.</td>
        </tr>
    <?php endif; ?>
</tbody>

        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
