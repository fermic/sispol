<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_rai'])) {
    $rai = $_POST['check_rai'];

    // Consulta ao banco para verificar se o RAI já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Desaparecidos WHERE RAI = :RAI");
    $stmt->execute(['RAI' => $rai]);
    $exists = $stmt->fetchColumn();

    // Retorna o resultado (0 ou 1)
    echo json_encode(['exists' => $exists > 0]);
    exit;
}
?>
