<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Caso GET, retornar os dados da tabela SituacoesObjeto para o Select2
    $term = trim($_GET['term'] ?? ''); // Termo de busca
    $query = "SELECT ID, Nome FROM SituacoesObjeto WHERE Nome LIKE :term ORDER BY Nome ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['term' => "%$term%"]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar os dados no formato esperado pelo Select2
    $response = array_map(function ($item) {
        return [
            'id' => $item['ID'],
            'text' => $item['Nome'],
        ];
    }, $result);

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Caso POST, adicionar um novo valor à tabela SituacoesObjeto
    $nome = trim($_POST['nome'] ?? '');

    if (!empty($nome)) {
        $stmt = $pdo->prepare("INSERT INTO SituacoesObjeto (Nome) VALUES (:nome)");
        $stmt->execute(['nome' => $nome]);
        $id = $pdo->lastInsertId();

        echo json_encode(['success' => true, 'id' => $id, 'nome' => $nome]);
    } else {
        echo json_encode(['success' => false]);
    }
}
