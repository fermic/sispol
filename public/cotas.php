<?php
include '../config/db.php'; // Inclua a conexão com o banco de dados
include '../includes/header.php';
header('Content-Type: text/html; charset=UTF-8');

// Obter a lista de responsáveis associados às movimentações do tipo "Requisição MP" (TipoID = 1)
$queryUsuarios = "
    SELECT DISTINCT u.ID, u.Nome
    FROM Usuarios u
    INNER JOIN Movimentacoes m ON u.ID = m.ResponsavelID
    WHERE m.TipoID = 1 AND m.Situacao = 'Em andamento'
";
try {
    $stmtUsuarios = $pdo->query($queryUsuarios);
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar usuários: " . $e->getMessage());
}

// Filtrar por responsável, se fornecido
$responsavelID = $_GET['responsavel_id'] ?? null;

// Consulta para buscar as movimentações do tipo "Requisição MP" (TipoID = 1) e Situacao = 'Em andamento'
$query = "
    SELECT 
        m.ID as MovimentacaoID,
        m.ProcedimentoID,
        p.NumeroProcedimento,
        m.Assunto,
        m.DataRequisicao,
        m.ResponsavelID,
        DATEDIFF(CURDATE(), m.DataRequisicao) AS DiasDesdeRequisicao
    FROM Movimentacoes m
    INNER JOIN Procedimentos p ON m.ProcedimentoID = p.ID
    WHERE m.TipoID = 1 AND m.Situacao = 'Em andamento'
";

// Adicionar filtro por responsável, se aplicável
if ($responsavelID) {
    $query .= " AND m.ResponsavelID = :responsavel_id";
}

$query .= " ORDER BY DiasDesdeRequisicao DESC";

try {
    $stmt = $pdo->prepare($query);
    if ($responsavelID) {
        $stmt->execute(['responsavel_id' => $responsavelID]);
    } else {
        $stmt->execute();
    }
    $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar movimentações: " . $e->getMessage());
}
?>

<div class="container mt-5">
    <h1 class="text-center">Movimentações - Requisição MP</h1>
    
    <!-- Filtro de busca -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="responsavel_id" class="form-label">Filtrar por Responsável</label>
                <select name="responsavel_id" id="responsavel_id" class="form-select">
                    <option value="">Todos os Responsáveis</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= htmlspecialchars($usuario['ID']) ?>" <?= $responsavelID == $usuario['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($usuario['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>
    
    <table class="table table-striped table-bordered mt-4">
        <thead class="table-dark">
            <tr>
                <th>Procedimento</th>
                <th>Assunto</th>
                <th>Data de Requisição</th>
                <th>Dias Desde Requisição</th>
                <th>Status</th>
                <th>Ações</th> <!-- Coluna para ações -->
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($movimentacoes)): ?>
                <?php foreach ($movimentacoes as $mov): ?>
                    <tr>
                        <!-- Número do Procedimento como link -->
                        <td>
                            <a href="ver_procedimento.php?id=<?= htmlspecialchars($mov['ProcedimentoID']) ?>">
                                <?= htmlspecialchars($mov['NumeroProcedimento']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($mov['Assunto'] ?? '') ?></td>
                        <td>
                            <?php if (!empty($mov['DataRequisicao'])): ?>
                                <?= htmlspecialchars(date('d/m/Y', strtotime($mov['DataRequisicao']))) ?>
                            <?php else: ?>
                                <span style="color: red; font-weight: bold;">Data não informada</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($mov['DiasDesdeRequisicao'] ?? '0') ?></td>
                        <!-- Status com círculo de cor -->
                        <td>
                            <?php
                            $dias = $mov['DiasDesdeRequisicao'] ?? null;
                            $cor = 'red'; // Vermelho como padrão para quando DataRequisicao não é informada
                            if (!empty($mov['DataRequisicao'])) {
                                if ($dias < 40) {
                                    $cor = 'green';
                                } elseif ($dias >= 40 && $dias < 60) {
                                    $cor = 'yellow';
                                }
                            }
                            ?>
                            <span style="display: inline-block; width: 15px; height: 15px; border-radius: 50%; background-color: <?= $cor ?>;"></span>
                        </td>
                        <!-- Botão de Editar -->
                        <td>
<a href="mov.php?id=<?= htmlspecialchars($mov['MovimentacaoID']) ?>&procedimento_id=<?= htmlspecialchars($mov['ProcedimentoID']) ?>&origem=relatorio" class="btn btn-warning btn-sm">
    Editar
</a>

                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhuma movimentação encontrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>

