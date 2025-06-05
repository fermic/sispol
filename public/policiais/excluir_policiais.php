<?php
include_once '../../includes/header.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM Policiais WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

header('Location: listar_policiais.php');
exit;
?>
