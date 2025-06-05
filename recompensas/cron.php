<?php
require_once 'funcoes.php'; // Contém a função sendWhatsAppMessageToPolice e outras utilitárias.
require_once 'db.php'; // Conexão com o banco de dados.

try {
    // 1. Solicitações com 1 dia
    $stmt1 = $pdo->prepare("
        SELECT r.id, r.rai, r.data_solicitacao, r.policial_solicitante_id
        FROM rec_recompensas r
        WHERE DATE(r.data_solicitacao) = DATE(NOW() - INTERVAL 1 DAY)
    ");
    $stmt1->execute();
    $solicitacoes1Dia = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    foreach ($solicitacoes1Dia as $sol) {
        $mensagem = "🟠 Prezado Policial, lembre-se de encaminhar o pedido de recompensa do RAI {$sol['rai']} para a Regional!";
        sendWhatsAppMessageToPolice($sol['policial_solicitante_id'], $mensagem, $pdo);
    }

    // 2. Solicitações com 3 dias
    $stmt3 = $pdo->prepare("
        SELECT r.id, r.rai, r.data_solicitacao, r.policial_id, r.policial_solicitante_id
        FROM rec_recompensas r
        WHERE DATE(r.data_solicitacao) = DATE(NOW() - INTERVAL 3 DAY)
    ");
    $stmt3->execute();
    $solicitacoes3Dias = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    foreach ($solicitacoes3Dias as $sol) {
        $solicitanteNome = obterNomePolicial($pdo, $sol['policial_solicitante_id']);
        $mensagem = "🟠 Prezado Policial, confirme com o policial $solicitanteNome se a recompensa foi enviada para a Regional.";
        sendWhatsAppMessageToPolice($sol['policial_id'], $mensagem, $pdo);
    }

    // 3. Solicitações com 7 dias
    $stmt7 = $pdo->prepare("
        SELECT r.id, r.rai, r.data_solicitacao, r.policial_id, r.policial_solicitante_id
        FROM rec_recompensas r
        WHERE DATE(r.data_solicitacao) = DATE(NOW() - INTERVAL 7 DAY)
    ");
    $stmt7->execute();
    $solicitacoes7Dias = $stmt7->fetchAll(PDO::FETCH_ASSOC);

    foreach ($solicitacoes7Dias as $sol) {
        $mensagemSolicitante = "🟠 Prezado Policial, confirme se a recompensa do RAI *{$sol['rai']}* foi aprovada ou reprovada.";
        $mensagemDestino = "🟠 Prezado Policial, confirme se a recompensa do RAI *{$sol['rai']}* foi aprovada ou reprovada.";
        
        // Envia para o solicitante
        sendWhatsAppMessageToPolice($sol['policial_solicitante_id'], $mensagemSolicitante, $pdo);
        
        // Envia para o destino
        sendWhatsAppMessageToPolice($sol['policial_id'], $mensagemDestino, $pdo);
    }

} catch (Exception $e) {
    error_log("Erro ao executar o script cron.php: " . $e->getMessage());
}
