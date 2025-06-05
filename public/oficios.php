<?php
session_start();
include '../includes/header.php'; // Inclui a navbar e configurações globais
require_once '../config/db.php'; // Inclui a conexão com o banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}

// Campo de busca
$searchTerm = trim($_GET['search'] ?? '');

// Query base para buscar os ofícios
$query = "
    SELECT 
        o.NumeroOficio,
        o.DataOficio,
        o.Assunto,
        p.NumeroProcedimento AS NumeroProcedimento,
        o.Destino,
        o.SEI,
        u.Nome AS Responsavel,
        m.Detalhes
    FROM Oficios o
    LEFT JOIN Procedimentos p ON o.ProcedimentoID = p.ID
    LEFT JOIN Usuarios u ON o.ResponsavelID = u.ID
    LEFT JOIN Movimentacoes m ON o.MovimentacaoID = m.ID
    WHERE 1=1
";

// Adiciona condições de busca se o termo estiver presente
if ($searchTerm) {
    $query .= " AND (
        o.NumeroOficio LIKE :searchTerm OR
        o.Assunto LIKE :searchTerm OR
        o.Destino LIKE :searchTerm OR
        o.SEI LIKE :searchTerm OR
        p.NumeroProcedimento LIKE :searchTerm OR
        m.Detalhes LIKE :searchTerm
    )";
}

$query .= " ORDER BY o.DataOficio DESC";

$stmt = $pdo->prepare($query);

if ($searchTerm) {
    $stmt->bindValue(':searchTerm', "%$searchTerm%", PDO::PARAM_STR);
}

$stmt->execute();
$oficios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5" style="max-width: 95%; margin: 0 auto;">
    <h1 class="text-center">Ofícios</h1>

    <!-- Campo de busca -->
    <form method="GET" class="d-flex justify-content-center mb-4">
        <input type="text" name="search" class="form-control w-50 me-2" placeholder="Buscar por Número, Assunto, Destino, SEI, Número do Procedimento ou Detalhes" value="<?= htmlspecialchars($searchTerm) ?>">
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>

    <!-- Tabela de resultados -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Número do Ofício</th>
                <th>Data</th>
                <th>Assunto</th>
                <th>Número do Procedimento</th>
                <th>Destino</th>
                <th>SEI</th>
                <th>Responsável</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($oficios): ?>
                <?php foreach ($oficios as $oficio): ?>
                    <tr>
                        <td><?= htmlspecialchars($oficio['NumeroOficio']) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($oficio['DataOficio']))) ?></td>
                        <td><?= htmlspecialchars($oficio['Assunto']) ?></td>
                        <td><?= htmlspecialchars($oficio['NumeroProcedimento']) ?></td>
                        <td><?= htmlspecialchars($oficio['Destino']) ?></td>
                        <td><?= !empty($oficio['SEI']) ? htmlspecialchars($oficio['SEI']) : 'Sem Informações' ?></td>
                        <td><?= htmlspecialchars($oficio['Responsavel']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Nenhum ofício encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
