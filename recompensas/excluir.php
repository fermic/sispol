<?php
require_once 'funcoes.php';
session_start();

// Verifica se o ID foi fornecido
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        // Prepara a exclusão no banco de dados
        $stmt = $pdo->prepare("DELETE FROM rec_recompensas WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Define mensagem de sucesso na session
        $_SESSION['mensagem'] = [
            'tipo' => 'success',
            'conteudo' => 'Recompensa excluída com sucesso!'
        ];

        // Redireciona para a página inicial
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        // Define mensagem de erro na session
        $_SESSION['mensagem'] = [
            'tipo' => 'danger',
            'conteudo' => 'Erro ao excluir a recompensa: ' . $e->getMessage()
        ];

        // Redireciona para a página inicial
        header('Location: index.php');
        exit;
    }
} else {
    // Define mensagem de erro caso o ID não seja fornecido
    $_SESSION['mensagem'] = [
        'tipo' => 'warning',
        'conteudo' => 'ID da recompensa não foi fornecido.'
    ];

    // Redireciona para a página inicial
    header('Location: index.php');
    exit;
}
