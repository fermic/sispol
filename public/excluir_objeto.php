<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $objetoID = $_POST['objeto_id'] ?? null;
    $procedimentoID = $_POST['procedimento_id'] ?? null;

    if (!$objetoID || !$procedimentoID) {
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos para exclusão.']);
        exit;
    }

    try {
        // Excluir a entrada relacionada a armas de fogo, se existir
        $queryArma = "DELETE FROM ArmasFogo WHERE ObjetoID = :objeto_id";
        $stmtArma = $pdo->prepare($queryArma);
        $stmtArma->execute(['objeto_id' => $objetoID]);

        // Excluir o objeto
        $queryObjeto = "DELETE FROM Objetos WHERE ID = :objeto_id";
        $stmtObjeto = $pdo->prepare($queryObjeto);
        $stmtObjeto->execute(['objeto_id' => $objetoID]);

        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir o objeto: ' . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
    exit;
}
?>
