<?php

define('SECRET_KEY', '147988b6e7ed8e59e044257daba0db7d'); // Troque para uma chave segura


function getAllLinks($pdo) {
    $stmt = $pdo->query("SELECT * FROM links ORDER BY acessos DESC, titulo ASC");
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Descriptografa as senhas antes de retornar
    foreach ($links as &$link) {
        if (!empty($link['senha'])) {
            $link['senha'] = decryptPassword($link['senha']);
        }
    }

    return $links;
}

function searchLinks($pdo, $search) {
    $stmt = $pdo->prepare("SELECT * FROM links WHERE titulo LIKE :search OR observacoes LIKE :search ORDER BY acessos DESC, titulo ASC");
    $stmt->execute([':search' => "%$search%"]);
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Descriptografa as senhas antes de retornar
    foreach ($links as &$link) {
        if (!empty($link['senha'])) {
            $link['senha'] = decryptPassword($link['senha']);
        }
    }

    return $links;
}

// Função para descriptografar a senha
function decryptPassword($encryptedPassword) {
    return openssl_decrypt($encryptedPassword, 'AES-256-CBC', SECRET_KEY, 0, substr(SECRET_KEY, 0, 16));
}
