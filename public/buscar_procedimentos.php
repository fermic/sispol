<?php
require_once '../config/db.php';

$query = trim($_GET['q'] ?? '');

if ($query) {
    $stmt = $pdo->prepare("SELECT ID, NumeroProcedimento FROM Procedimentos WHERE NumeroProcedimento LIKE :query LIMIT 10");
    $stmt->execute(['query' => "%$query%"]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar os dados no formato esperado pelo Select2
    $formattedResult = array_map(function ($item) {
        return [
            'id' => $item['ID'],
            'text' => $item['NumeroProcedimento']
        ];
    }, $result);

    echo json_encode($formattedResult);
}
?>
