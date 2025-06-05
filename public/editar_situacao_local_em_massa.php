<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $objetos = $_POST['objetos'] ?? [];
    $novaSituacao = $_POST['nova_situacao'] ?? null;
    $novoLocal = $_POST['novo_local'] ?? null;
    $procedimentoID = $_POST['procedimento_id'] ?? null;

    if (empty($objetos) || !$novaSituacao || !$novoLocal || !$procedimentoID) {
        $_SESSION['error_message'] = 'Nenhum objeto selecionado ou dados inválidos.';
        header('Location: ver_procedimento.php?id=' . $procedimentoID);
        exit;
    }

    try {
        // Atualiza a situação e o local dos objetos
        $query = "
            UPDATE Objetos 
            SET SituacaoID = :novaSituacao, LocalArmazenagemID = :novoLocal 
            WHERE ID IN (" . implode(',', array_map('intval', $objetos)) . ")
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'novaSituacao' => $novaSituacao,
            'novoLocal' => $novoLocal,
        ]);

        $_SESSION['success_message'] = 'Situação e local de armazenagem atualizados com sucesso!';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao atualizar: ' . $e->getMessage();
    }

    header('Location: ver_procedimento.php?id=' . $procedimentoID);
    exit;
}
