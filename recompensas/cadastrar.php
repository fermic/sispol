<?php
require_once 'funcoes.php';

$mensagem = '';
$proximoPolicial = obterProximoPolicial($pdo); // Obtém o próximo policial da vez.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataSolicitacao = $_POST['data_solicitacao'];
    $rai = $_POST['rai'];
    $observacoes = $_POST['observacoes'];
    $destino = $_POST['destino'];
    $policialSolicitanteId = $_POST['policial_solicitante'];

    try {
        // Determina o policial_id (null para Delegacia, ou ID do policial da vez)
        $policialId = ($destino === 'Delegacia') ? null : $destino;

        // Insere a recompensa no banco de dados
        $stmt = $pdo->prepare("INSERT INTO rec_recompensas (data_solicitacao, rai, policial_id, policial_solicitante_id, observacoes, status)
                               VALUES (:data_solicitacao, :rai, :policial_id, :policial_solicitante_id, :observacoes, 'Solicitada')");
        $stmt->execute([
            ':data_solicitacao' => $dataSolicitacao,
            ':rai' => $rai,
            ':policial_id' => $policialId,
            ':policial_solicitante_id' => $policialSolicitanteId,
            ':observacoes' => $observacoes,
        ]);

        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        $mensagem = "Erro ao cadastrar recompensa: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Recompensa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        .policial-destaque {
            background-color: #d4edda; /* Fundo verde claro */
            border: 2px solid #28a745; /* Bordas verdes */
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Cadastrar Recompensa</h1>
    <?php if ($mensagem): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>
    <div class="policial-destaque">
        <strong>Próximo da escala:</strong> <?= htmlspecialchars($proximoPolicial['nome'] ?? 'Nenhum policial cadastrado') ?>
    </div>
    <form method="POST">
        <div class="mb-3">
            <label for="data_solicitacao" class="form-label">Data da Solicitação</label>
            <input type="date" class="form-control" id="data_solicitacao" name="data_solicitacao" required>
        </div>
        <div class="mb-3">
            <label for="rai" class="form-label">RAI</label>
            <input type="text" class="form-control" id="rai" name="rai" required>
        </div>
        <div class="mb-3">
            <label for="destino" class="form-label">Policial Beneficiário</label>
            <select class="form-select" id="destino" name="destino" required>
                <option value="Delegacia">Delegacia</option>
                <?php if ($proximoPolicial): ?>
                    <option value="<?= htmlspecialchars($proximoPolicial['id']) ?>" selected>
                        <?= htmlspecialchars($proximoPolicial['nome']) ?>
                    </option>
                <?php endif; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="policial_solicitante" class="form-label">Policial Solicitante</label>
            <select class="form-select" id="policial_solicitante" name="policial_solicitante" required>
                <option value="">Selecione</option>
                <?php foreach (obterPoliciais($pdo) as $policial): ?>
                    <option value="<?= htmlspecialchars($policial['id']) ?>">
                        <?= htmlspecialchars($policial['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea class="form-control" id="observacoes" name="observacoes"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
        <a href="index.php" class="btn btn-secondary">Voltar</a>
    </form>
</div>
</body>
</html>
