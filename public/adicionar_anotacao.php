<?php
/**
 * Script para adicionar anotações via AJAX
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
    $logMessage = "[$timestamp] ADICIONAR_ANOTACAO: $message";
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
    logError("Iniciando processo de adição de anotação", [
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

    // Verificar se $pdo está disponível
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

    // Validar dados do formulário
    if (!isset($_POST['procedimento_id']) || !isset($_POST['anotacao'])) {
        logError("Dados do formulário incompletos", [
            'post_data' => $_POST
        ]);
        retornarResposta(false, 'Dados incompletos. Procedimento e anotação são obrigatórios.');
    }

    // Validar e sanitizar dados
    $procedimentoId = filter_var($_POST['procedimento_id'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    
    $anotacao = trim($_POST['anotacao']);
    $usuarioId = filter_var($_SESSION['usuario_id'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    if (!$procedimentoId || !$usuarioId || empty($anotacao)) {
        logError("Dados inválidos após validação", [
            'procedimento_id' => $_POST['procedimento_id'],
            'usuario_id' => $_SESSION['usuario_id'],
            'anotacao_length' => strlen($anotacao)
        ]);
        retornarResposta(false, 'Dados inválidos. Verifique os campos e tente novamente.');
    }

    // Iniciar transação
    $pdo->beginTransaction();
    logError("Transação iniciada");

    try {
        // Verificar se o procedimento existe
        $stmtVerificar = $pdo->prepare("
            SELECT ID, NumeroProcedimento 
            FROM Procedimentos 
            WHERE ID = ?
        ");
        
        $stmtVerificar->execute([$procedimentoId]);
        $procedimento = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
        
        if (!$procedimento) {
            $pdo->rollBack();
            logError("Procedimento não encontrado", [
                'procedimento_id' => $procedimentoId
            ]);
            retornarResposta(false, 'Procedimento não encontrado.');
        }

        // Inserir a anotação
        $stmtInserir = $pdo->prepare("
            INSERT INTO Anotacoes (
                ProcedimentoID,
                UsuarioCriadorID,
                Anotacao,
                DataCriacao
            ) VALUES (?, ?, ?, NOW())
        ");

        $resultado = $stmtInserir->execute([
            $procedimentoId,
            $usuarioId,
            $anotacao
        ]);

        if (!$resultado) {
            $pdo->rollBack();
            logError("Erro ao inserir anotação", [
                'error_info' => $stmtInserir->errorInfo()
            ]);
            retornarResposta(false, 'Erro ao salvar anotação.');
        }

        $anotacaoId = $pdo->lastInsertId();

        // Registrar log da ação (opcional)
        try {
            $functionsPath = __DIR__ . '/../includes/functions.php';
            if (file_exists($functionsPath)) {
                include_once $functionsPath;
                if (function_exists('logAcaoUsuario')) {
                    logAcaoUsuario($pdo, 'adicionar_anotacao', $procedimentoId, 
                        "Nova anotação adicionada ao procedimento {$procedimento['NumeroProcedimento']}");
                    logError("Log de ação registrado");
                }
            }
        } catch (Exception $e) {
            logError("Erro ao registrar log de ação", [
                'error' => $e->getMessage()
            ]);
            // Não falha a operação principal se o log falhar
        }

        // Commit da transação
        $pdo->commit();
        logError("Transação commitada com sucesso");

        // Retornar sucesso com dados da anotação
        retornarResposta(true, 'Anotação adicionada com sucesso.', [
            'anotacao_id' => $anotacaoId,
            'procedimento_id' => $procedimentoId,
            'data_criacao' => date('Y-m-d H:i:s')
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        logError("Erro na transação - rollback executado", [
            'error' => $e->getMessage()
        ]);
        throw $e;
    }

} catch (PDOException $e) {
    logError("Erro PDO detalhado", [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'sql_state' => $e->errorInfo[0] ?? 'desconhecido',
        'driver_code' => $e->errorInfo[1] ?? 'desconhecido',
        'driver_message' => $e->errorInfo[2] ?? 'desconhecido'
    ]);
    
    $mensagemErro = 'Erro no banco de dados.';
    
    switch ($e->errorInfo[0] ?? $e->getCode()) {
        case '23000':
            $mensagemErro = 'Erro de integridade de dados.';
            break;
        case '42S02':
            $mensagemErro = 'Tabela Anotacoes não encontrada.';
            break;
        case '42S22':
            $mensagemErro = 'Estrutura da tabela Anotacoes está incorreta.';
            break;
        case 'HY000':
            $mensagemErro = 'Erro geral no banco de dados: ' . ($e->errorInfo[2] ?? $e->getMessage());
            break;
        case '08S01':
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
        'line' => $e->getLine()
    ]);
    
    retornarResposta(false, 'Erro interno do servidor: ' . $e->getMessage(), [
        'error_type' => get_class($e)
    ], 500);
}
?> 