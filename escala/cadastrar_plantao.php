<?php
require_once 'funcoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataInicio = $_POST['data_inicio'];
    $dataFim = $_POST['data_fim'];
    $policial1 = $_POST['policial1'];
    $policial2 = $_POST['policial2'];

    try {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO escala (data_inicio, data_fim, policial1_id, policial2_id) VALUES (:data_inicio, :data_fim, :policial1, :policial2)");
        $stmt->execute([
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'policial1' => $policial1,
            'policial2' => $policial2,
        ]);
        $mensagem = "Plantão cadastrado com sucesso!";
    } catch (Exception $e) {
        $mensagem = "Erro ao cadastrar plantão: " . $e->getMessage();
    }
}

// Obter a lista de policiais para exibição no formulário
try {
    global $conn;
    $policiais = $conn->query("SELECT id, nome FROM policiais ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erro ao carregar policiais: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Cadastrar Plantão</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Cadastrar Plantão</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-info text-center"><?= $mensagem ?></div>
        <?php endif; ?>

        <!-- Botão Voltar -->
        <div class="d-flex justify-content-start mb-4">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar para Escalas
            </a>
        </div>

        <!-- Formulário de Cadastro -->
        <form method="POST">
            <div class="mb-3">
                <label for="data_inicio" class="form-label">Data de Início</label>
                <input type="date" id="data_inicio" name="data_inicio" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="data_fim" class="form-label">Data de Fim</label>
                <input type="date" id="data_fim" name="data_fim" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="policial1" class="form-label">Policial 1</label>
                <select id="policial1" name="policial1" class="form-select" required>
                    <option value="">Selecione um policial</option>
                    <?php foreach ($policiais as $policial): ?>
                        <option value="<?= $policial['id'] ?>"><?= $policial['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="policial2" class="form-label">Policial 2</label>
                <select id="policial2" name="policial2" class="form-select" required>
                    <option value="">Selecione um policial</option>
                    <?php foreach ($policiais as $policial): ?>
                        <option value="<?= $policial['id'] ?>"><?= $policial['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
