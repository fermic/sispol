<?php
session_start();
include '../includes/header.php';
require_once '../config/db.php';

// Verificar se o ID do procedimento foi passado
$procedimentoID = $_GET['id'] ?? null;
if (!$procedimentoID) {
    echo "<p class='text-center text-danger'>Procedimento não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

// Verificar se o formulário de confirmação foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transação
        $pdo->beginTransaction();

        // Excluir registros relacionados na tabela Vitimas
        $queryDeleteVitimas = "DELETE FROM Vitimas WHERE ProcedimentoID = :procedimento_id";
        $stmtDeleteVitimas = $pdo->prepare($queryDeleteVitimas);
        $stmtDeleteVitimas->execute(['procedimento_id' => $procedimentoID]);

        // Excluir registros relacionados na tabela Investigados
        $queryDeleteInvestigados = "DELETE FROM Investigados WHERE ProcedimentoID = :procedimento_id";
        $stmtDeleteInvestigados = $pdo->prepare($queryDeleteInvestigados);
        $stmtDeleteInvestigados->execute(['procedimento_id' => $procedimentoID]);

        // Excluir registros relacionados na tabela RAIs
        $queryDeleteRAIs = "DELETE FROM RAIs WHERE ProcedimentoID = :procedimento_id";
        $stmtDeleteRAIs = $pdo->prepare($queryDeleteRAIs);
        $stmtDeleteRAIs->execute(['procedimento_id' => $procedimentoID]);

        // Excluir registros relacionados na tabela ProcessosJudiciais
        $queryDeleteProcessosJudiciais = "DELETE FROM ProcessosJudiciais WHERE ProcedimentoID = :procedimento_id";
        $stmtDeleteProcessosJudiciais = $pdo->prepare($queryDeleteProcessosJudiciais);
        $stmtDeleteProcessosJudiciais->execute(['procedimento_id' => $procedimentoID]);

        // Excluir documentos de movimentações relacionadas
        $queryDeleteDocumentos = "
            DELETE FROM DocumentosMovimentacao
            WHERE MovimentacaoID IN (
                SELECT ID FROM Movimentacoes WHERE ProcedimentoID = :procedimento_id
            )
        ";
        $stmtDeleteDocumentos = $pdo->prepare($queryDeleteDocumentos);
        $stmtDeleteDocumentos->execute(['procedimento_id' => $procedimentoID]);

        // Excluir movimentações relacionadas
        $queryDeleteMovimentacoes = "DELETE FROM Movimentacoes WHERE ProcedimentoID = :procedimento_id";
        $stmtDeleteMovimentacoes = $pdo->prepare($queryDeleteMovimentacoes);
        $stmtDeleteMovimentacoes->execute(['procedimento_id' => $procedimentoID]);

        // Excluir registros de ofícios relacionados
        $queryDeleteOficios = "DELETE FROM Oficios WHERE ProcedimentoID = :procedimento_id";
        $stmtDeleteOficios = $pdo->prepare($queryDeleteOficios);
        $stmtDeleteOficios->execute(['procedimento_id' => $procedimentoID]);

        // Excluir o procedimento
        $queryDeleteProcedimento = "DELETE FROM Procedimentos WHERE ID = :procedimento_id";
        $stmtDeleteProcedimento = $pdo->prepare($queryDeleteProcedimento);
        $stmtDeleteProcedimento->execute(['procedimento_id' => $procedimentoID]);

        // Confirmar transação
        $pdo->commit();

        // Redirecionar para a lista de procedimentos com mensagem de sucesso
        $_SESSION['message'] = "Procedimento excluído com sucesso.";
        header("Location: procedimentos.php");
        exit;

    } catch (PDOException $e) {
        // Reverter transação em caso de erro
        $pdo->rollBack();
        $error = "Erro ao excluir o procedimento: " . $e->getMessage();
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center text-danger">Excluir Procedimento</h1>

    <!-- Exibir mensagem de erro -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="alert alert-warning">
        <strong>Atenção:</strong> Esta ação irá excluir o procedimento e todos os registros relacionados (investigados, RAIs, processos judiciais, movimentações, ofícios, documentos). Esta ação não pode ser desfeita.
    </div>

    <form method="POST">
        <div class="text-center">
            <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
            <a href="procedimentos.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
