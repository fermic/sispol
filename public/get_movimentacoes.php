<?php
include '../config/db.php'; // Inclua a conexão ao banco de dados

try {
    // Consulta para buscar os dados de Movimentações
    $query = $pdo->query("
        SELECT Assunto, DataVencimento 
        FROM Movimentacoes
        WHERE DataVencimento IS NOT NULL
    ");
    $movimentacoes = $query->fetchAll(PDO::FETCH_ASSOC);

    // Formatar os dados para o FullCalendar
    $events = array_map(function ($movimentacao) {
        return [
            'title' => $movimentacao['Assunto'],
            'start' => $movimentacao['DataVencimento']
        ];
    }, $movimentacoes);

    // Retornar os dados como JSON
    echo json_encode($events);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
