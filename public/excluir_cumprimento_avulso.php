<?php
require '../config/db.php'; // Conexão com o banco de dados

// Obter o ID do cumprimento avulso
$id = $_GET['id'] ?? null;

if (!$id || $id === 'null') {
    $_SESSION['error_message'] = "ID inválido ou não informado.";
    header("Location: listar_cautelares.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // Verificar se o cumprimento é realmente avulso
    $stmtVerificar = $pdo->prepare("
        SELECT ID 
        FROM CumprimentosCautelares 
        WHERE ID = :id AND SolicitacaoCautelarID IS NULL
    ");
    $stmtVerificar->execute([':id' => $id]);

    if (!$stmtVerificar->fetch()) {
        throw new Exception("O cumprimento informado não é avulso ou não existe.");
    }

    // Excluir os envolvidos associados ao cumprimento
    $stmtEnvolvidos = $pdo->prepare("DELETE FROM EnvolvidosCumprimentoCautelar WHERE CumprimentoCautelarID = :id");
    $stmtEnvolvidos->execute([':id' => $id]);

    // Excluir o cumprimento avulso
    $stmtCumprimento = $pdo->prepare("DELETE FROM CumprimentosCautelares WHERE ID = :id AND SolicitacaoCautelarID IS NULL");
    $stmtCumprimento->execute([':id' => $id]);

    $pdo->commit();
    $_SESSION['success_message'] = "Cumprimento avulso excluído com sucesso.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Erro ao excluir cumprimento avulso: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

header("Location: listar_cautelares.php");
exit;
