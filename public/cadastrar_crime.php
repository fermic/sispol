<?php
require_once '../config/db.php';

// Configura o cabeçalho para resposta JSON
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Método não permitido
        echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['nome'])) {
        http_response_code(400); // Requisição inválida
        echo json_encode(['success' => false, 'message' => 'O campo nome é obrigatório.']);
        exit;
    }

    // Insere o novo crime no banco de dados
    $stmt = $pdo->prepare("INSERT INTO Crimes (Nome) VALUES (:nome)");
    $stmt->execute([':nome' => $data['nome']]);

    $crimeID = $pdo->lastInsertId(); // Obtém o ID do crime inserido

    echo json_encode(['success' => true, 'message' => 'Crime cadastrado com sucesso!', 'crime' => ['id' => $crimeID, 'nome' => $data['nome']]]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar crime: ' . $e->getMessage()]);
}
