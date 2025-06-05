<?php
// Desabilitar qualquer saída de erro
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Garantir que não haja saída antes do JSON
ob_start();

// Iniciar sessão
session_start();

// Incluir configurações
include_once __DIR__ . '/../config/config.php';
include_once 'functions.php';

// Função para retornar resposta em JSON
function retornarResposta($sucesso, $mensagem, $codigo = 200) {
    // Limpar qualquer saída anterior
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Definir headers
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    
    // Retornar JSON
    echo json_encode([
        'success' => $sucesso,
        'message' => $mensagem
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    retornarResposta(false, 'Usuário não autenticado', 401);
}
?> 