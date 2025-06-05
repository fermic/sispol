<?php
require_once '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        $stmt = $pdo->prepare("
            UPDATE Desaparecidos
            SET Situacao = 'Encontrado', DataLocalizacao = :dataAtual
            WHERE ID = :id
        ");
        $stmt->execute([
            'id' => $id,
            'dataAtual' => date('Y-m-d')
        ]);

        header('Location: desaparecimentos.php?msg=Registro marcado como Encontrado');
        exit;
    } catch (PDOException $e) {
        header('Location: desaparecimentos.php?error=Erro ao marcar como Encontrado');
        exit;
    }
} else {
    header('Location: desaparecimentos.php?error=ID inválido');
    exit;
}
?>
