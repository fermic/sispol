<?php
require_once 'funcoes.php';

$mensagem = '';
$id = $_GET['id'] ?? null;

// Verifica se o ID foi fornecido
if (!$id) {
    header('Location: index.php');
    exit;
}

// Obtém os dados da recompensa para exibir no formulário
$stmt = $pdo->prepare("SELECT * FROM rec_recompensas WHERE id = :id");
$stmt->execute([':id' => $id]);
$recompensa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recompensa) {
    header('Location: index.php');
    exit;
}

// Obtém a lista de policiais para os selects
$policiais = obterPoliciais($pdo);

// Atualiza os dados da recompensa no banco de dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataSolicitacao = $_POST['data_solicitacao'];
    $rai = $_POST['rai'];
    $observacoes = $_POST['observacoes'];
    $status = $_POST['status'];
    $destino = $_POST['destino'];
    $policialSolicitanteId = $_POST['policial_solicitante'];

    try {
        $policialId = ($destino === 'Delegacia') ? null : $destino;

        $stmt = $pdo->prepare("UPDATE rec_recompensas 
                               SET data_solicitacao = :data_solicitacao, 
                                   rai = :rai, 
                                   policial_id = :policial_id, 
                                   policial_solicitante_id = :policial_solicitante_id, 
                                   observacoes = :observacoes, 
                                   status = :status 
                               WHERE id = :id");
        $stmt->execute([
            ':data_solicitacao' => $dataSolicitacao,
            ':rai' => $rai,
            ':policial_id' => $policialId,
            ':policial_solicitante_id' => $policialSolicitanteId,
            ':observacoes' => $observacoes,
            ':status' => $status,
            ':id' => $id,
        ]);

        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $mensagem = "Erro ao atualizar recompensa: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Recompensa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Editar Recompensa</h1>
    <?php if ($mensagem): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="data_solicitacao" class="form-label">Data da Solicitação</label>
            <input type="date" class="form-control" id="data_solicitacao" name="data_solicitacao" value="<?= htmlspecialchars($recompensa['data_solicitacao']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="rai" class="form-label">RAI</label>
            <input type="text" class="form-control" id="rai" name="rai" value="<?= htmlspecialchars($recompensa['rai']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="destino" class="form-label">Policial Beneficiário</label>
            <select class="form-select" id="destino" name="destino" required>
                <option value="Delegacia" <?= $recompensa['policial_id'] === null ? 'selected' : '' ?>>Delegacia</option>
                <?php foreach ($policiais as $policial): ?>
                    <option value="<?= htmlspecialchars($policial['id']) ?>" <?= $policial['id'] == $recompensa['policial_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($policial['nome']) ?> 
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="policial_solicitante" class="form-label">Policial Solicitante</label>
            <select class="form-select" id="policial_solicitante" name="policial_solicitante" required>
                <option value="">Selecione</option>
                <?php foreach ($policiais as $policial): ?>
                    <option value="<?= htmlspecialchars($policial['id']) ?>" <?= $policial['id'] == $recompensa['policial_solicitante_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($policial['nome']) ?> (<?= htmlspecialchars($policial['telefone'] ?? 'Não informado') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea class="form-control" id="observacoes" name="observacoes"><?= htmlspecialchars($recompensa['observacoes']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="Solicitada" <?= $recompensa['status'] === 'Solicitada' ? 'selected' : '' ?>>Solicitada</option>
                <option value="Aprovada" <?= $recompensa['status'] === 'Aprovada' ? 'selected' : '' ?>>Aprovada</option>
                <option value="Recusada" <?= $recompensa['status'] === 'Recusada' ? 'selected' : '' ?>>Recusada</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="index.php" class="btn btn-secondary">Voltar</a>
    </form>
</div>
</body>
</html>
