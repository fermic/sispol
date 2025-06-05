<?php
/**
 * Script para excluir anotações via AJAX
 * Versão final corrigida
 */

// Configurações de erro para produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão primeiro
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir apenas o arquivo de configuração correto
include_once __DIR__ . '/../config/db.php';

// Limpar qualquer buffer de saída existente
while (ob_get_level()) {
    ob_end_clean();
}

// Configurar headers antes de qualquer saída
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Função para log personalizado
function logError($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] EXCLUIR_ANOTACAO: $message";
    if (!empty($context)) {
        $logMessage .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    error_log($logMessage);
}

// Função para retornar resposta padronizada
function retornarResposta($sucesso, $mensagem, $dados = null, $codigo = 200) {
    // Garantir que não há saída anterior
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code($codigo);
    
    $resposta = [
        'success' => $sucesso,
        'message' => $mensagem,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($dados !== null) {
        $resposta['data'] = $dados;
    }
    
    echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    logError("Iniciando processo de exclusão de anotação", [
        'method' => $_SERVER['REQUEST_METHOD'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    // Verificar se o usuário está logado
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        logError("Tentativa de acesso sem autenticação", [
            'session_data' => array_keys($_SESSION ?? [])
        ]);
        retornarResposta(false, 'Usuário não autenticado. Faça login novamente.', null, 401);
    }

    // Verificar método da requisição
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logError("Método de requisição incorreto", [
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
        retornarResposta(false, 'Método não permitido. Use POST.', null, 405);
    }

    // Verificar se $pdo está disponível (definido em db.php)
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        logError("Conexão com banco de dados não disponível", [
            'pdo_exists' => isset($pdo),
            'pdo_type' => isset($pdo) ? gettype($pdo) : 'não definido'
        ]);
        retornarResposta(false, 'Erro de conexão com o banco de dados.', null, 500);
    }

    // Testar conexão PDO
    try {
        $pdo->query("SELECT 1");
        logError("Conexão PDO testada com sucesso");
    } catch (PDOException $e) {
        logError("Erro na conexão PDO", [
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]);
        retornarResposta(false, 'Falha na conexão com banco de dados.', null, 500);
    }

    // Obter dados da requisição
    $rawInput = file_get_contents('php://input');
    logError("Dados recebidos", [
        'raw_input' => $rawInput,
        'content_length' => strlen($rawInput)
    ]);
    
    if (empty($rawInput)) {
        retornarResposta(false, 'Nenhum dado foi enviado na requisição.');
    }

    $dados = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logError("Erro ao decodificar JSON", [
            'json_error' => json_last_error_msg(),
            'raw_input' => $rawInput
        ]);
        retornarResposta(false, 'Dados JSON inválidos: ' . json_last_error_msg());
    }

    // Validar estrutura dos dados
    if (!is_array($dados)) {
        logError("Dados não são um array", ['dados_type' => gettype($dados)]);
        retornarResposta(false, 'Formato de dados inválido.');
    }

    if (!isset($dados['anotacao_id']) || !isset($dados['procedimento_id'])) {
        logError("Campos obrigatórios ausentes", [
            'dados_keys' => array_keys($dados),
            'dados' => $dados
        ]);
        retornarResposta(false, 'Campos obrigatórios ausentes (anotacao_id, procedimento_id).');
    }

    // Validar e sanitizar IDs
    $anotacaoId = filter_var($dados['anotacao_id'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    
    $procedimentoId = filter_var($dados['procedimento_id'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    
    $usuarioId = filter_var($_SESSION['usuario_id'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    if (!$anotacaoId || !$procedimentoId || !$usuarioId) {
        logError("IDs inválidos após validação", [
            'anotacao_id_original' => $dados['anotacao_id'],
            'procedimento_id_original' => $dados['procedimento_id'],
            'usuario_id_original' => $_SESSION['usuario_id'],
            'anotacao_id_validado' => $anotacaoId,
            'procedimento_id_validado' => $procedimentoId,
            'usuario_id_validado' => $usuarioId
        ]);
        retornarResposta(false, 'IDs fornecidos são inválidos.');
    }

    logError("IDs validados com sucesso", [
        'anotacao_id' => $anotacaoId,
        'procedimento_id' => $procedimentoId,
        'usuario_id' => $usuarioId
    ]);

    // Iniciar transação
    $pdo->beginTransaction();
    logError("Transação iniciada");
    
    try {
        // Verificar se a anotação existe e obter detalhes
        $stmtVerificar = $pdo->prepare("
            SELECT 
                a.ID,
                a.UsuarioCriadorID,
                a.Anotacao,
                a.DataCriacao,
                u.Nome as UsuarioNome,
                p.NumeroProcedimento
            FROM Anotacoes a
            LEFT JOIN Usuarios u ON a.UsuarioCriadorID = u.ID
            LEFT JOIN Procedimentos p ON a.ProcedimentoID = p.ID
            WHERE a.ID = ? AND a.ProcedimentoID = ?
        ");
        
        logError("Query de verificação preparada");
        
        $executeResult = $stmtVerificar->execute([$anotacaoId, $procedimentoId]);
        $anotacao = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
        
        logError("Resultado da verificação", [
            'execute_result' => $executeResult,
            'anotacao_encontrada' => $anotacao !== false,
            'error_info' => $stmtVerificar->errorInfo()
        ]);
        
        if (!$anotacao) {
            $pdo->rollBack();
            logError("Anotação não encontrada", [
                'anotacao_id' => $anotacaoId,
                'procedimento_id' => $procedimentoId
            ]);
            retornarResposta(false, 'Anotação não encontrada ou não pertence ao procedimento especificado.');
        }

        logError("Anotação encontrada", [
            'anotacao_id' => $anotacao['ID'],
            'usuario_proprietario' => $anotacao['UsuarioCriadorID'],
            'usuario_solicitante' => $usuarioId,
            'numero_procedimento' => $anotacao['NumeroProcedimento']
        ]);

        // Verificar permissões (usuário só pode excluir suas próprias anotações ou ser admin)
        $podeExcluir = false;
        
        if ($anotacao['UsuarioCriadorID'] == $usuarioId) {
            $podeExcluir = true;
            logError("Permissão concedida: próprio usuário");
        } else {
            // Verificar se é administrador ou supervisor
            $stmtCargo = $pdo->prepare("
                SELECT c.Nome as CargoNome
                FROM Usuarios u
                LEFT JOIN Cargos c ON u.CargoID = c.ID
                WHERE u.ID = ?
            ");
            $stmtCargo->execute([$usuarioId]);
            $cargoUsuario = $stmtCargo->fetch(PDO::FETCH_ASSOC);
            
            logError("Cargo do usuário verificado", [
                'cargo' => $cargoUsuario['CargoNome'] ?? 'não encontrado'
            ]);
            
            if ($cargoUsuario && in_array($cargoUsuario['CargoNome'], ['Administrador', 'Supervisor', 'Delegado'])) {
                $podeExcluir = true;
                logError("Permissão concedida: cargo administrativo", [
                    'cargo' => $cargoUsuario['CargoNome']
                ]);
            }
        }

        if (!$podeExcluir) {
            $pdo->rollBack();
            logError("Permissão negada para exclusão", [
                'usuario_proprietario' => $anotacao['UsuarioCriadorID'],
                'usuario_solicitante' => $usuarioId,
                'nome_proprietario' => $anotacao['UsuarioNome']
            ]);
            retornarResposta(false, 'Você não tem permissão para excluir esta anotação. Apenas o autor ou administradores podem excluir anotações.');
        }

        // Excluir a anotação
        $stmtExcluir = $pdo->prepare("
            DELETE FROM Anotacoes 
            WHERE ID = ? AND ProcedimentoID = ?
        ");
        
        logError("Query de exclusão preparada");
        
        $resultadoExclusao = $stmtExcluir->execute([$anotacaoId, $procedimentoId]);
        $rowsAffected = $stmtExcluir->rowCount();
        
        logError("Resultado da exclusão", [
            'execute_result' => $resultadoExclusao,
            'rows_affected' => $rowsAffected,
            'error_info' => $stmtExcluir->errorInfo()
        ]);
        
        if (!$resultadoExclusao || $rowsAffected === 0) {
            $pdo->rollBack();
            logError("Falha na exclusão da anotação", [
                'resultado' => $resultadoExclusao,
                'rows_affected' => $rowsAffected,
                'error_info' => $stmtExcluir->errorInfo()
            ]);
            retornarResposta(false, 'Erro ao excluir anotação do banco de dados.');
        }

        // Registrar log da ação (opcional - só se função existir)
        try {
            $functionsPath = __DIR__ . '/../includes/functions.php';
            if (file_exists($functionsPath)) {
                include_once $functionsPath;
                if (function_exists('logAcaoUsuario')) {
                    logAcaoUsuario($pdo, 'excluir_anotacao', $procedimentoId, 
                        "Anotação ID {$anotacaoId} excluída do procedimento {$anotacao['NumeroProcedimento']}");
                    logError("Log de ação registrado");
                } else {
                    logError("Função logAcaoUsuario não encontrada");
                }
            } else {
                logError("Arquivo functions.php não encontrado", ['path' => $functionsPath]);
            }
        } catch (Exception $e) {
            // Log do erro, mas não falha a operação principal
            logError("Erro ao registrar log de ação", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        // Commit da transação
        $pdo->commit();
        logError("Transação commitada com sucesso");
        
        logError("Anotação excluída com sucesso", [
            'anotacao_id' => $anotacaoId,
            'procedimento_id' => $procedimentoId,
            'usuario_id' => $usuarioId
        ]);

        retornarResposta(true, 'Anotação excluída com sucesso.', [
            'anotacao_id' => $anotacaoId,
            'procedimento_id' => $procedimentoId
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        logError("Erro na transação - rollback executado", [
            'error' => $e->getMessage()
        ]);
        throw $e; // Re-lançar para ser capturado pelo catch externo
    }

} catch (PDOException $e) {
    logError("Erro PDO detalhado", [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'sql_state' => $e->errorInfo[0] ?? 'desconhecido',
        'driver_code' => $e->errorInfo[1] ?? 'desconhecido',
        'driver_message' => $e->errorInfo[2] ?? 'desconhecido',
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Mensagens de erro mais específicas baseadas no código do erro
    $mensagemErro = 'Erro no banco de dados.';
    
    // Códigos de erro SQL mais comuns
    switch ($e->errorInfo[0] ?? $e->getCode()) {
        case '23000': // Constraint violation
            $mensagemErro = 'Erro de integridade de dados - a anotação pode estar sendo referenciada em outro local.';
            break;
        case '42S02': // Table not found
            $mensagemErro = 'Tabela Anotacoes não encontrada no banco de dados.';
            break;
        case '42S22': // Column not found
            $mensagemErro = 'Estrutura da tabela Anotacoes está incorreta.';
            break;
        case 'HY000': // General error
            $mensagemErro = 'Erro geral no banco de dados: ' . ($e->errorInfo[2] ?? $e->getMessage());
            break;
        case '08S01': // Communication link failure
            $mensagemErro = 'Falha na comunicação com o banco de dados.';
            break;
        default:
            $mensagemErro = 'Erro no banco: ' . $e->getMessage();
    }
    
    retornarResposta(false, $mensagemErro, [
        'error_code' => $e->getCode(),
        'sql_state' => $e->errorInfo[0] ?? null
    ], 500);

} catch (Exception $e) {
    logError("Erro geral capturado", [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    retornarResposta(false, 'Erro interno do servidor: ' . $e->getMessage(), [
        'error_type' => get_class($e)
    ], 500);
}
?>

<!-- TESTE RÁPIDO: cole no console do navegador -->
<!-- 
// Substitua com IDs reais do seu banco
fetch('excluir_anotacao.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        anotacao_id: 1,  // ID real
        procedimento_id: 1  // ID real
    })
}).then(response => response.json()).then(data => {
    console.log('Resposta:', data);
}).catch(error => {
    console.error('Erro:', error);
});
-->

<!-- VERIFICAR ESTRUTURA DAS TABELAS -->
<!--
Execute no MySQL para verificar se as tabelas estão corretas:

DESCRIBE Anotacoes;
DESCRIBE Usuarios;
DESCRIBE Procedimentos;
DESCRIBE Cargos;

SELECT COUNT(*) FROM Anotacoes;
SELECT COUNT(*) FROM Usuarios;
SELECT COUNT(*) FROM Procedimentos;

-- Teste de dados
SELECT a.*, u.Nome, p.NumeroProcedimento 
FROM Anotacoes a 
LEFT JOIN Usuarios u ON a.UsuarioID = u.ID 
LEFT JOIN Procedimentos p ON a.ProcedimentoID = p.ID 
LIMIT 3;
-->