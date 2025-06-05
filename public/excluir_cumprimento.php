<?php
require '../config/db.php'; // Conexão com o banco de dados

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM CumprimentosCautelares WHERE ID = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['success_message'] = "Cumprimento excluído com sucesso!";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erro ao excluir cumprimento: " . $e->getMessage();
    }
}

header("Location: listar_cumprimentos.php");
exit;
