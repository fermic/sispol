<?php
include '../config/db.php'; // Substitua pelo arquivo de conexão com o banco de dados
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');

    if (empty($nome)) {
        echo json_encode(['success' => false, 'message' => 'O nome da espécie é obrigatório.']);
        exit;
    }

    try {
        // Verificar se já existe uma espécie com o mesmo nome
        $query = "SELECT ID FROM ArmaEspecie WHERE Nome = :nome LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['nome' => $nome]);
        $especieExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($especieExistente) {
            echo json_encode(['success' => true, 'id' => $especieExistente['ID'], 'nome' => $nome]);
            exit;
        }

        // Inserir nova espécie
        $query = "INSERT INTO ArmaEspecie (Nome) VALUES (:nome)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['nome' => $nome]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'nome' => $nome]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar a espécie: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $term = $_GET['term'] ?? '';
    try {
        $query = "SELECT ID as id, Nome as nome FROM ArmaEspecie WHERE Nome LIKE :term ORDER BY Nome ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['term' => '%' . $term . '%']);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($result);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
}
