<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? null; // Obter o ID do comentário
$desaparecidoID = $_GET['desaparecido_id'] ?? null; // Obter o ID do desaparecido

if (!$id || !$desaparecidoID) {
    // Exibir mensagem de erro e sair
    echo '<div class="alert alert-danger">Parâmetros inválidos fornecidos.</div>';
    exit;
}

try {
    // Remover o comentário do banco de dados
    $stmt = $pdo->prepare("DELETE FROM ComentariosDesaparecimentos WHERE ID = ?");
    $stmt->execute([$id]);

    // Redirecionar de volta à página de comentários
    header("Location: comentarios.php?id=" . urlencode($desaparecidoID));
    exit;
} catch (PDOException $e) {
    // Mostrar mensagem de erro para depuração
    echo '<div class="alert alert-danger">Erro ao excluir o comentário: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}
