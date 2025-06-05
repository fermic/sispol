<?php
require_once 'funcoes.php';
require_once 'db.php'; // Conexão com o banco de dados

session_start();

// Verifica se o ID foi fornecido
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['mensagem'] = [
        'tipo' => 'danger',
        'conteudo' => 'ID da recompensa não fornecido!'
    ];
    header('Location: index.php');
    exit;
}

try {
    // Atualiza o status da recompensa para "Aprovada"
    $stmt = $pdo->prepare("UPDATE rec_recompensas SET status = 'Aprovada' WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Verifica se alguma linha foi afetada
    if ($stmt->rowCount() > 0) {
        $_SESSION['mensagem'] = [
            'tipo' => 'success',
            'conteudo' => 'Recompensa aprovada com sucesso!'
        ];
    } else {
        $_SESSION['mensagem'] = [
            'tipo' => 'warning',
            'conteudo' => 'Nenhuma recompensa encontrada com o ID fornecido!'
        ];
    }

} catch (Exception $e) {
    $_SESSION['mensagem'] = [
        'tipo' => 'danger',
        'conteudo' => 'Erro ao aprovar a recompensa: ' . $e->getMessage()
    ];
}

// Redireciona de volta para o index.php
header('Location: index.php');
exit;
