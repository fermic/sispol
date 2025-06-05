<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicionar novo registro
    $nome = trim($_POST['nome'] ?? '');

    if (!empty($nome)) {
        $stmt = $pdo->prepare("INSERT INTO LocaisArmazenagem (Nome) VALUES (:nome)");
        $stmt->execute(['nome' => $nome]);
        $id = $pdo->lastInsertId();

        echo json_encode(['success' => true, 'id' => $id, 'nome' => $nome]);
        exit;
    } else {
        echo json_encode(['success' => false]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Buscar resultados para exibir no Select2
    $term = trim($_GET['term'] ?? ''); // Termo de busca enviado pelo Select2
    $query = "SELECT ID, Nome FROM LocaisArmazenagem";

    if (!empty($term)) {
        $query .= " WHERE Nome LIKE :term ORDER BY Nome ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['term' => '%' . $term . '%']);
    } else {
        $query .= " ORDER BY Nome ASC";
        $stmt = $pdo->query($query);
    }

    $locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $results = array_map(function ($local) {
        return [
            'id' => $local['ID'],
            'text' => $local['Nome'],
        ];
    }, $locais);

    echo json_encode($results);
    exit;
}

// Caso nenhuma rota seja válida
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request']);
