<?php
include '../includes/header.php'; // Inclui a navbar e configurações globais

// Verificar se o usuário está logado e obter o ID do usuário
$usuarioID = $_SESSION['usuario_id'] ?? null;
if (!$usuarioID) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para editar um prazo.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter o ID do prazo a partir da URL
$prazoID = $_GET['id'] ?? null;
if (!$prazoID) {
    echo "<p class='text-center text-danger'>Prazo não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

// Buscar os dados do prazo existente
$query = "
    SELECT 
        pz.ID, 
        pz.TipoID, 
        pz.Assunto, 
        pz.Situacao, 
        pz.DataVencimento, 
        pz.ProcedimentoID
    FROM Prazos pz
    WHERE pz.ID = :id
";
$stmt = $pdo->prepare($query);
$stmt->execute(['id' => $prazoID]);
$prazo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prazo) {
    echo "<p class='text-center text-danger'>Prazo não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter os tipos de prazos para o dropdown
$queryTiposPrazo = "SELECT ID, Nome FROM TiposPrazo";
$stmtTiposPrazo = $pdo->query($queryTiposPrazo);
$tiposPrazo = $stmtTiposPrazo->fetchAll(PDO::FETCH_ASSOC);

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e obter os dados do formulário
    $tipoID = $_POST['tipo_id'] ?? null;
    $assunto = trim($_POST['assunto'] ?? '');
    $situacao = $_POST['situacao'] ?? null;
    $dataVencimento = $_POST['data_vencimento'] ?? null;

    // Validação simples
    if (!$tipoID || !$assunto || !$situacao || !$dataVencimento) {
        $error = "Todos os campos são obrigatórios.";
    } else {
        try {
            // Atualizar os dados do prazo no banco de dados
            $queryUpdate = "
                UPDATE Prazos
                SET TipoID = :tipo_id,
                    Assunto = :assunto,
                    Situacao = :situacao,
                    DataVencimento = :data_vencimento
                WHERE ID = :id
            ";
            $stmtUpdate = $pdo->prepare($queryUpdate);
            $stmtUpdate->execute([
                'tipo_id' => $tipoID,
                'assunto' => $assunto,
                'situacao' => $situacao,
                'data_vencimento' => $dataVencimento,
                'id' => $prazoID,
            ]);

            // Redirecionar para a página do procedimento
            header("Location: ver_procedimento.php?id=" . $prazo['ProcedimentoID']);
            exit;
        } catch (PDOException $e) {
            $error = "Erro ao atualizar o prazo: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center">Editar Prazo</h1>

    <!-- Exibir mensagens de erro -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="tipo_id" class="form-label">Tipo de Prazo</label>
            <select name="tipo_id" id="tipo_id" class="form-select" required>
                <option value="">Selecione o Tipo</option>
                <?php foreach ($tiposPrazo as $tipo): ?>
                    <option value="<?= htmlspecialchars($tipo['ID']) ?>" <?= $tipo['ID'] == $prazo['TipoID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tipo['Nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="assunto" class="form-label">Assunto</label>
            <input type="text" name="assunto" id="assunto" class="form-control" value="<?= htmlspecialchars($prazo['Assunto']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="situacao" class="form-label">Situação</label>
            <select name="situacao" id="situacao" class="form-select" required>
                <option value="Em andamento" <?= $prazo['Situacao'] === 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                <option value="Finalizado" <?= $prazo['Situacao'] === 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="data_vencimento" class="form-label">Data de Vencimento</label>
<?php
$dataVencimento = htmlspecialchars(date('Y-m-d', strtotime($prazo['DataVencimento'])));
?>
<input type="date" name="data_vencimento" id="data_vencimento" class="form-control" value="<?= $dataVencimento ?>" required>


        </div>
        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="ver_procedimento.php?id=<?= htmlspecialchars($prazo['ProcedimentoID']) ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
