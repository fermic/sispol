<?php
require '../config/db.php'; // Conexão com o banco de dados
include '../includes/header.php'; // Inclui a navbar e configurações globais

// Capturar os parâmetros da URL
$solicitacaoID = $_GET['solicitacao_id'] ?? null;
$procedimentoID = $_GET['procedimento_id'] ?? null;

// Verificar se a solicitação existe
if (!$solicitacaoID || !$procedimentoID) {
    $_SESSION['error_message'] = "Parâmetros inválidos. Não foi possível excluir a cautelar.";
    header("Location: ver_procedimento.php?id=$procedimentoID");
    exit;
}

try {
    $pdo->beginTransaction();

    // Excluir os registros relacionados na tabela `CumprimentosCautelares`
    $stmtCumprimentos = $pdo->prepare("DELETE FROM CumprimentosCautelares WHERE SolicitacaoCautelarID = :solicitacaoID");
    $stmtCumprimentos->execute([':solicitacaoID' => $solicitacaoID]);

    // Excluir os registros relacionados na tabela `ItensSolicitacaoCautelar`
    $stmtItens = $pdo->prepare("DELETE FROM ItensSolicitacaoCautelar WHERE SolicitacaoCautelarID = :solicitacaoID");
    $stmtItens->execute([':solicitacaoID' => $solicitacaoID]);

    // Excluir a solicitação da tabela `SolicitacoesCautelares`
    $stmtSolicitacao = $pdo->prepare("DELETE FROM SolicitacoesCautelares WHERE ID = :solicitacaoID");
    $stmtSolicitacao->execute([':solicitacaoID' => $solicitacaoID]);

    $pdo->commit();

    // Mensagem de sucesso
    $_SESSION['success_message'] = "Cautelar excluída com sucesso.";
    header("Location: ver_procedimento.php?id=$procedimentoID");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();

    // Mensagem de erro
    $_SESSION['error_message'] = "Erro ao excluir a cautelar: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    header("Location: ver_procedimento.php?id=$procedimentoID");
    exit;
}
