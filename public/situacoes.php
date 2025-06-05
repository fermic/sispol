<?php
include '../config/db.php'; // Inclua sua conexão com o banco

header('Content-Type: application/json');

$categoria = $_GET['categoria'] ?? null;

if (!$categoria) {
    echo json_encode([]);
    exit;
}

try {
    // Certifique-se de que sua tabela "SituacoesProcedimento" possui a coluna "Categoria"
    $stmt = $pdo->prepare("SELECT ID, Nome FROM SituacoesProcedimento WHERE Categoria = :categoria");
    $stmt->execute([':categoria' => $categoria]);
    $situacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($situacoes);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar situações: ' . $e->getMessage()]);
}
