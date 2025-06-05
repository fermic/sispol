<?php
include '../config/db.php';
header('Content-Type: application/json');

$term = $_GET['term'] ?? '';
if (strlen($term) > 1) {
    try {
        $query = "SELECT Nome FROM Crimes WHERE Nome LIKE :term ORDER BY Nome ASC LIMIT 10";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['term' => "%$term%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results); // Retorna os dados em JSON
    } catch (PDOException $e) {
        error_log("Erro ao buscar crimes: " . $e->getMessage());
        echo json_encode([]); // Retorna array vazio em caso de erro
    }
} else {
    echo json_encode([]); // Retorna array vazio se o termo for curto
}
