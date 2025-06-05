<?php
session_start();
require '../config/db.php';

$usuarioID = $_SESSION['usuario_id'] ?? null;
$procedimentoID = $_POST['procedimento_id'] ?? null;
$acao = $_POST['acao'] ?? null;

if (!$usuarioID || !$procedimentoID || !$acao) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

try {
    if ($acao === 'adicionar') {
        $stmt = $pdo->prepare("
            INSERT INTO FavoritosUsuarios (UsuarioID, ProcedimentoID) 
            VALUES (:usuarioID, :procedimentoID)
            ON DUPLICATE KEY UPDATE DataFavoritado = CURRENT_TIMESTAMP
        ");
    } elseif ($acao === 'remover') {
        $stmt = $pdo->prepare("
            DELETE FROM FavoritosUsuarios 
            WHERE UsuarioID = :usuarioID AND ProcedimentoID = :procedimentoID
        ");
    } else {
        throw new Exception('Ação inválida.');
    }

    $stmt->execute([
        ':usuarioID' => $usuarioID,
        ':procedimentoID' => $procedimentoID,
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
