<?php
// Desabilitar qualquer saída de erro
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Garantir que não haja saída antes do JSON
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Iniciar sessão
session_start();

// Incluir configurações
include_once __DIR__ . '/../config/config.php';
include_once __DIR__ . '/../includes/functions.php';

// Função para retornar resposta em JSON
function retornarResposta($dados, $codigo = 200) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    retornarResposta(['error' => 'Usuário não autenticado'], 401);
}

// Verificar se o ID do procedimento foi fornecido
if (!isset($_GET['procedimento_id'])) {
    retornarResposta(['error' => 'ID do procedimento não fornecido']);
}

$procedimentoId = filter_var($_GET['procedimento_id'], FILTER_VALIDATE_INT);

if (!$procedimentoId) {
    retornarResposta(['error' => 'ID do procedimento inválido']);
}

try {
    // Buscar anotações
    $query = "
        SELECT 
            a.ID as id,
            a.Anotacao as anotacao,
            DATE_FORMAT(a.DataCriacao, '%d/%m/%Y %H:%i') as data,
            u.Nome as usuario
        FROM Anotacoes a
        INNER JOIN Usuarios u ON a.UsuarioCriadorID = u.ID
        WHERE a.ProcedimentoID = :procedimento_id
        ORDER BY a.DataCriacao DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['procedimento_id' => $procedimentoId]);
    $anotacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Processar as anotações para limpar o conteúdo
    foreach ($anotacoes as &$anotacao) {
        // Limpar e formatar o conteúdo da anotação
        $anotacao['anotacao'] = trim($anotacao['anotacao']); // Remove espaços no início e fim
        
        // Converte quebras de linha para HTML (se necessário)
        $anotacao['anotacao'] = nl2br(htmlspecialchars($anotacao['anotacao'], ENT_QUOTES, 'UTF-8'));
        
        // Remove múltiplas quebras de linha consecutivas e espaços extras
        $anotacao['anotacao'] = preg_replace('/\s+/', ' ', $anotacao['anotacao']);
        $anotacao['anotacao'] = preg_replace('/(<br\s*\/?>){3,}/', '<br><br>', $anotacao['anotacao']);
        
        // Garantir que não há espaços no início
        $anotacao['anotacao'] = ltrim($anotacao['anotacao']);
    }

    // Retornar as anotações
    retornarResposta($anotacoes);

} catch (PDOException $e) {
    error_log("Erro PDO ao buscar anotações: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    retornarResposta(['error' => 'Erro ao buscar anotações: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    error_log("Erro geral ao buscar anotações: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    retornarResposta(['error' => 'Erro ao buscar anotações: ' . $e->getMessage()], 500);
}
?>