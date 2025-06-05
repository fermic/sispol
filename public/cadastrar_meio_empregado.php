<?php
require_once '../config/db.php'; // Inclua o cabeçalho com a configuração do banco de dados

// Configura o cabeçalho para resposta JSON
header('Content-Type: application/json');

try {
    // Verifique se o método da solicitação é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Método não permitido
        echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
        exit;
    }

    // Recebe os dados enviados em JSON
    $data = json_decode(file_get_contents('php://input'), true);

    // Valida o campo nome
    if (empty($data['nome'])) {
        http_response_code(400); // Requisição inválida
        echo json_encode(['success' => false, 'message' => 'O campo nome é obrigatório.']);
        exit;
    }

    // Insere o novo meio empregado no banco de dados
    $stmt = $pdo->prepare("INSERT INTO MeiosEmpregados (Nome) VALUES (:nome)");
    $stmt->execute([':nome' => $data['nome']]);

    // Obtém o ID do meio empregado inserido
    $meioID = $pdo->lastInsertId();

    // Retorna sucesso com os dados do novo meio empregado
    echo json_encode([
        'success' => true,
        'message' => 'Meio empregado cadastrado com sucesso!',
        'meio' => [
            'id' => $meioID,
            'nome' => $data['nome']
        ]
    ]);
} catch (Exception $e) {
    // Captura e retorna erros
    http_response_code(500); // Erro interno do servidor
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar meio empregado: ' . $e->getMessage()]);
}
