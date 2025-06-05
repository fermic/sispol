<?php
require_once '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM Desaparecidos WHERE ID = :id");
        $stmt->execute(['id' => $id]);

        header('Location: desaparecimentos.php?msg=Registro excluído com sucesso');
        exit;
    } catch (PDOException $e) {
        header('Location: desaparecimentos.php?error=Erro ao excluir o registro');
        exit;
    }
} else {
    header('Location: desaparecimentos.php?error=ID inválido');
    exit;
}
?>
