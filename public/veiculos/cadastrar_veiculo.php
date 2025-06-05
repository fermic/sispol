<?php
include_once '../../includes/header.php'; // Inclui a navbar e configurações globais
$id = $placa = $marca = $modelo = $cor = $observacoes = '';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM Veiculos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($veiculo) {
        $placa = $veiculo['placa'];
        $marca = $veiculo['marca'];
        $modelo = $veiculo['modelo'];
        $cor = $veiculo['cor'];
        $observacoes = $veiculo['observacoes'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $placa = $_POST['placa'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $cor = $_POST['cor'];
    $observacoes = $_POST['observacoes'];

    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE Veiculos SET placa = :placa, marca = :marca, modelo = :modelo, cor = :cor, observacoes = :observacoes 
            WHERE id = :id
        ");
        $stmt->execute([
            'placa' => $placa, 'marca' => $marca, 'modelo' => $modelo,
            'cor' => $cor, 'observacoes' => $observacoes, 'id' => $id
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO Veiculos (placa, marca, modelo, cor, observacoes) 
            VALUES (:placa, :marca, :modelo, :cor, :observacoes)
        ");
        $stmt->execute([
            'placa' => $placa, 'marca' => $marca, 'modelo' => $modelo,
            'cor' => $cor, 'observacoes' => $observacoes
        ]);
    }

    header('Location: listar_veiculos.php');
    exit;
}
?>


<div class="container mt-4">
    <h2><?= $id ? 'Editar' : 'Cadastrar' ?> Veículo</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <div class="mb-3">
            <label for="placa" class="form-label">Placa</label>
            <input type="text" name="placa" id="placa" class="form-control" value="<?= htmlspecialchars($placa) ?>" required>
        </div>
        <div class="mb-3">
            <label for="marca" class="form-label">Marca</label>
            <input type="text" name="marca" id="marca" class="form-control" value="<?= htmlspecialchars($marca) ?>" required>
        </div>
        <div class="mb-3">
            <label for="modelo" class="form-label">Modelo</label>
            <input type="text" name="modelo" id="modelo" class="form-control" value="<?= htmlspecialchars($modelo) ?>" required>
        </div>
        <div class="mb-3">
            <label for="cor" class="form-label">Cor</label>
            <input type="text" name="cor" id="cor" class="form-control" value="<?= htmlspecialchars($cor) ?>" required>
        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" class="form-control"><?= htmlspecialchars($observacoes) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="listar_veiculos.php" class="btn btn-secondary">Voltar</a>
    </form>
</div>
</body>
</html>
