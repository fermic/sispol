<?php
require_once 'db.php';

function obterPoliciais($pdo) {
    $stmt = $pdo->query("SELECT * FROM rec_policiais WHERE situacao = 'Ativo'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function obterRecompensas($pdo) {
    $stmt = $pdo->query("SELECT r.*, p.nome AS policial_nome 
                         FROM rec_recompensas r 
                         LEFT JOIN rec_policiais p ON r.policial_id = p.id
                         ORDER BY r.data_solicitacao DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obterProximoPolicial($pdo) {
    $policiais = obterPoliciais($pdo); // Apenas policiais ativos
    $ultimoId = obterUltimoPolicialAtribuido($pdo);

    if (count($policiais) === 0) return null;

    $proximoIndex = $ultimoId !== null ? array_search($ultimoId, array_column($policiais, 'id')) + 1 : 0;
    return $policiais[$proximoIndex % count($policiais)];
}


function obterUltimoPolicialAtribuido($pdo) {
    $stmt = $pdo->query("SELECT policial_id FROM rec_recompensas WHERE policial_id IS NOT NULL ORDER BY id DESC LIMIT 1");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado ? $resultado['policial_id'] : null;
}

function obterNomePolicial($pdo, $policialId) {
    if (!$policialId) return null;

    $stmt = $pdo->prepare("SELECT nome FROM rec_policiais WHERE id = :id");
    $stmt->execute([':id' => $policialId]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    return $resultado ? $resultado['nome'] : null;
}

function sendWhatsAppMessageToPolice($policialId, $message, $pdo) {
    // Obtém o telefone do policial pelo ID
    $stmt = $pdo->prepare("SELECT telefone, nome FROM rec_policiais WHERE id = :id");
    $stmt->execute([':id' => $policialId]);
    $policial = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$policial) {
        throw new Exception("Policial com ID $policialId não encontrado.");
    }

    $number = $policial['telefone'];
    $policialNome = $policial['nome'];

    // Valida o número de telefone
    if (empty($number)) {
        throw new Exception("O policial $policialNome não possui um número de telefone válido.");
    }

    // Ajusta o número para o formato correto
    $number = preg_replace('/[^0-9]/', '', $number); // Remove caracteres não numéricos
    if (strlen($number) >= 13) {
        $number = substr($number, 0, 4) . substr($number, 5); // Remove o quinto dígito se necessário
    }

    // URL e configuração para envio
    $url = 'http://85.239.238.30:3000/send-message';
    $data = [
        'number' => $number,
        'message' => $message
    ];
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    // Envia a mensagem
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        throw new Exception("Erro ao enviar a mensagem para o policial $policialNome ($number).");
    }

    return "Mensagem enviada para $policialNome com sucesso!";
}


?>
