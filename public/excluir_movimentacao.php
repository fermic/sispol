<?php
include '../includes/header.php'; // Inclui configurações globais e conexão com o banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para realizar esta ação.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter os parâmetros da URL
$movimentacaoID = $_GET['id'] ?? null;
$procedimentoID = $_GET['procedimento_id'] ?? null;

// Validar os parâmetros
if (!$movimentacaoID || !$procedimentoID) {
    echo "<p class='text-center text-danger'>Movimentação ou procedimento inválido.</p>";
    include '../includes/footer.php';
    exit;
}

try {
    // Preparar e executar a exclusão
    $stmt = $pdo->prepare("DELETE FROM Movimentacoes WHERE ID = :id");
    $stmt->execute(['id' => $movimentacaoID]);

    // Verificar se a exclusão foi bem-sucedida
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Movimentação excluída com sucesso.";
    } else {
        $_SESSION['error_message'] = "Não foi possível excluir a movimentação. Verifique os dados e tente novamente.";
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erro ao excluir movimentação: " . $e->getMessage();
}

// Redirecionar para a página do procedimento
header("Location: ver_procedimento.php?id=$procedimentoID");
exit;
?>
