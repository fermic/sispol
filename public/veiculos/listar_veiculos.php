<?php
include_once '../../includes/header.php'; // Inclui a navbar e configurações globais

$stmt = $pdo->query("SELECT * FROM Veiculos ORDER BY id DESC");
$veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2>Listagem de Veículos</h2>
    <a href="cadastrar_veiculo.php" class="btn btn-primary mb-3">Cadastrar Novo Veículo</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Placa</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Cor</th>
                <th>Observações</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($veiculos as $veiculo): ?>
                <tr>
                    <td><?= htmlspecialchars($veiculo['id']) ?></td>
                    <td><?= htmlspecialchars($veiculo['placa']) ?></td>
                    <td><?= htmlspecialchars($veiculo['marca']) ?></td>
                    <td><?= htmlspecialchars($veiculo['modelo']) ?></td>
                    <td><?= htmlspecialchars($veiculo['cor']) ?></td>
                    <td><?= htmlspecialchars($veiculo['observacoes']) ?></td>
                    <td>
                        <a href="cadastrar_veiculo.php?id=<?= $veiculo['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="excluir_veiculo.php?id=<?= $veiculo['id'] ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Deseja realmente excluir este veículo?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
