<?php
require_once 'db.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Incrementar a contagem de acessos
    $stmt = $pdo->prepare("UPDATE links SET acessos = acessos + 1 WHERE id = ?");
    $stmt->execute([$id]);

    // Buscar a URL do link
    $stmt = $pdo->prepare("SELECT url FROM links WHERE id = ?");
    $stmt->execute([$id]);
    $link = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($link) {
        // Redirecionar para a URL
        header("Location: " . $link['url']);
        exit;
    }
}

// Caso não encontre o link
header("Location: index.php");
exit;
