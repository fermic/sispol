<?php
require_once '../config/db.php'; // Conexão com o banco de dados

$procedimentoID = intval($_GET['procedimento_id'] ?? 0);

if ($procedimentoID) {
    $stmt = $pdo->prepare("SELECT ID, Numero AS numero, Descricao AS descricao FROM ProcessosJudiciais WHERE ProcedimentoID = :procedimento_id");
    $stmt->execute(['procedimento_id' => $procedimentoID]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
}
?>
