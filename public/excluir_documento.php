<?php
session_start();
require_once '../config/db.php';

// Obter os parâmetros da URL
$documentoID = $_GET['id'] ?? null;
$movimentacaoID = $_GET['movimentacao_id'] ?? null;
$procedimentoID = $_GET['procedimento_id'] ?? null;

// Validar os parâmetros
if (!$documentoID || !$movimentacaoID || !$procedimentoID) {
    die("Parâmetros inválidos.");
}

try {
    // Obter o caminho do arquivo
    $query = "SELECT Caminho FROM DocumentosMovimentacao WHERE ID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $documentoID]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($documento) {
        // Excluir o arquivo físico, se existir
        if (file_exists($documento['Caminho'])) {
            unlink($documento['Caminho']);
        }

        // Excluir o registro do banco de dados
        $queryDelete = "DELETE FROM DocumentosMovimentacao WHERE ID = :id";
        $stmtDelete = $pdo->prepare($queryDelete);
        $stmtDelete->execute(['id' => $documentoID]);

        $_SESSION['success_message'] = "Documento excluído com sucesso.";
    } else {
        $_SESSION['error_message'] = "Documento não encontrado.";
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erro ao excluir o documento: " . $e->getMessage();
}

// Redirecionar para a página de origem usando o Referer
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? "editar_movimentacao.php?id=$movimentacaoID&procedimento_id=$procedimentoID";
header("Location: $redirectUrl");
exit;
