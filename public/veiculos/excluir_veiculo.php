<?php
require_once '../../config/db.php';
$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM Veiculos WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

header('Location: listar_veiculos.php');
exit;
?>
