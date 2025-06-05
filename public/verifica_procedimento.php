<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero_procedimento'])) {
    $numero = $_POST['numero_procedimento'];

    // Consulta ao banco para verificar se o número já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Procedimentos WHERE NumeroProcedimento = :numero");
    $stmt->execute([':numero' => $numero]);
    $exists = $stmt->fetchColumn();

    // Retorna o resultado (0 ou 1)
    echo json_encode(['exists' => $exists > 0]);
    exit;
}
?>
