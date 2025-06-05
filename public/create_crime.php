<?php
include '../config/db.php';
header('Content-Type: application/json');

$crimeName = $_POST['crime'] ?? '';

if (!empty($crimeName)) {
    try {
        // Verificar se o crime já existe
        $queryCheck = "SELECT ID FROM Crimes WHERE Nome = :nome";
        $stmtCheck = $pdo->prepare($queryCheck);
        $stmtCheck->execute(['nome' => $crimeName]);

        if ($stmtCheck->rowCount() > 0) {
            echo json_encode(['status' => 'exists', 'message' => 'Crime já existe']);
            exit;
        }

        // Inserir o novo crime
        $queryInsert = "INSERT INTO Crimes (Nome) VALUES (:nome)";
        $stmtInsert = $pdo->prepare($queryInsert);
        $stmtInsert->execute(['nome' => $crimeName]);

        echo json_encode(['status' => 'success', 'message' => 'Crime criado com sucesso', 'crime' => $crimeName]);
    } catch (PDOException $e) {
        error_log("Erro ao criar crime: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Erro ao criar crime']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Nome do crime inválido']);
}
