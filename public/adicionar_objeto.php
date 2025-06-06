<?php
include '../includes/header.php';

// Função para validar dados do objeto
function validarDadosObjeto($dados) {
    $erros = [];
    
    // Validação do tipo de objeto
    if (empty($dados['tipo_id']) || $dados['tipo_id'] <= 0) {
        $erros[] = "Tipo de objeto é obrigatório";
    }
    
    // Validação da descrição
    if (empty($dados['descricao'])) {
        $erros[] = "Descrição é obrigatória";
    } elseif (strlen(trim($dados['descricao'])) < 10) {
        $erros[] = "Descrição deve ter pelo menos 10 caracteres";
    }
    
    // Validação da quantidade
    if (!isset($dados['quantidade']) || $dados['quantidade'] <= 0) {
        $erros[] = "Quantidade deve ser maior que zero";
    }
    
    // Validação da data de apreensão
    if (empty($dados['data_apreensao'])) {
        $erros[] = "Data de apreensão é obrigatória";
    } else {
        $data = DateTime::createFromFormat('Y-m-d', $dados['data_apreensao']);
        if (!$data || $data->format('Y-m-d') !== $dados['data_apreensao']) {
            $erros[] = "Data de apreensão inválida";
        }
    }
    
    // Validação específica para armas de fogo
    if ($dados['tipo_id'] == 4) {
        // Validar espécie
        if (empty($dados['arma_especie_id']) || $dados['arma_especie_id'] <= 0) {
            $erros[] = "Espécie da arma é obrigatória";
        }
        
        // Validar calibre
        if (empty($dados['arma_calibre_id']) || $dados['arma_calibre_id'] <= 0) {
            $erros[] = "Calibre da arma é obrigatório";
        }
        
        // Validar número de série
        if (empty($dados['numero_serie'])) {
            $erros[] = "Número de série é obrigatório para armas de fogo";
        } elseif (strlen(trim($dados['numero_serie'])) < 3) {
            $erros[] = "Número de série deve ter pelo menos 3 caracteres";
        }
        
        // Validar marca (opcional, mas se preenchido deve ser válido)
        if (!empty($dados['arma_marca_id']) && $dados['arma_marca_id'] <= 0) {
            $erros[] = "Marca da arma inválida";
        }
        
        // Validar modelo (opcional, mas se preenchido deve ser válido)
        if (!empty($dados['arma_modelo_id']) && $dados['arma_modelo_id'] <= 0) {
            $erros[] = "Modelo da arma inválido";
        }
        
        // Validar processo judicial (opcional, mas se preenchido deve ser válido)
        if (!empty($dados['processo_judicial_id']) && $dados['processo_judicial_id'] <= 0) {
            $erros[] = "Processo judicial inválido";
        }
    }
    
    return $erros;
}

// Função para log de ações
function logAcaoUsuario($pdo, $acao, $objeto_id = null, $detalhes = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO LogAcoes (usuario_id, acao, objeto_id, detalhes, data_acao)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$_SESSION['usuario_id'], $acao, $objeto_id, $detalhes]);
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

// Gerar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar se é adição ou edição
$objetoID = $_GET['objeto_id'] ?? null;
$isEditing = !empty($objetoID);

// Verificar se o usuário está logado
$usuarioID = $_SESSION['usuario_id'] ?? null;
if (!$usuarioID) {
    echo "<div class='alert alert-danger text-center'>Você precisa estar logado para realizar esta ação.</div>";
    include '../includes/footer.php';
    exit;
}

// Obter o ID do procedimento
$procedimentoID = $_GET['procedimento_id'] ?? null;
if (!$procedimentoID) {
    echo "<div class='alert alert-danger text-center'>Procedimento não encontrado.</div>";
    include '../includes/footer.php';
    exit;
}

// Inicializar variáveis
$tipoID = $descricao = $quantidade = $situacaoID = $dataApreensao = $lacreAtual = $localID = '';
$especieID = $calibreID = $numeroSerie = $marcaID = $modeloID = $processoJudicialID = null;
$tipoNome = $especieNome = $calibreNome = $marcaNome = $modeloNome = '';
$errosValidacao = [];

// Cache para dados estáticos (simulação - implementar com Redis/Memcached em produção)
function getCachedData($key, $callback) {
    // Em produção, usar Redis ou Memcached
    static $cache = [];
    
    if (!isset($cache[$key])) {
        $cache[$key] = $callback();
    }
    
    return $cache[$key];
}

// Obter dados com cache
$tiposObjeto = getCachedData('tipos_objeto', function() use ($pdo) {
    $query = "SELECT ID, Nome FROM TiposObjeto ORDER BY Nome ASC";
    return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
});

$situacoesObjeto = getCachedData('situacoes_objeto', function() use ($pdo) {
    $query = "SELECT ID, Nome FROM SituacoesObjeto ORDER BY Nome ASC";
    return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
});

// Obter os processos judiciais relacionados ao procedimento
$queryProcessos = "SELECT ID, Numero FROM ProcessosJudiciais WHERE ProcedimentoID = :procedimento_id ORDER BY Numero ASC";
$stmtProcessos = $pdo->prepare($queryProcessos);
$stmtProcessos->execute(['procedimento_id' => $procedimentoID]);
$processosJudiciais = $stmtProcessos->fetchAll(PDO::FETCH_ASSOC);

// Buscar o ID do tipo de movimentação "Entrada"
$stmtTipoEntrada = $pdo->prepare("SELECT ID FROM TiposMovimentacaoObjeto WHERE UPPER(Nome) LIKE '%ENTRADA%'");
$stmtTipoEntrada->execute();
$tipoEntradaID = $stmtTipoEntrada->fetchColumn();

if (!$tipoEntradaID) {
    error_log("ERRO: Tipo de movimentação 'Entrada' não encontrado no banco de dados");
    $_SESSION['error_message'] = 'Erro: Tipo de movimentação "Entrada" não encontrado no sistema.';
    header('Location: ver_procedimento.php?id=' . $procedimentoID);
    exit;
}

// Carregar dados para edição
if ($isEditing) {
    $queryObjeto = "
        SELECT o.*, t.Nome AS TipoNome, s.Nome AS SituacaoNome
        FROM Objetos o
        LEFT JOIN TiposObjeto t ON o.TipoObjetoID = t.ID
        LEFT JOIN SituacoesObjeto s ON o.SituacaoID = s.ID
        WHERE o.ID = :objeto_id AND o.ProcedimentoID = :procedimento_id
    ";
    $stmtObjeto = $pdo->prepare($queryObjeto);
    $stmtObjeto->execute(['objeto_id' => $objetoID, 'procedimento_id' => $procedimentoID]);
    $objeto = $stmtObjeto->fetch(PDO::FETCH_ASSOC);

    if ($objeto) {
        $tipoID = $objeto['TipoObjetoID'];
        $tipoNome = $objeto['TipoNome'];
        $descricao = $objeto['Descricao'];
        $quantidade = $objeto['Quantidade'];
        $situacaoID = $objeto['SituacaoID'];
        $dataApreensao = $objeto['DataApreensao'];
        $lacreAtual = $objeto['LacreAtual'];

        if ($tipoID == 4) {
            // Carregar detalhes de arma de fogo
            $queryArma = "
                SELECT 
                    af.*,
                    e.Nome AS EspecieNome,
                    c.Nome AS CalibreNome,
                    m.Nome AS MarcaNome,
                    mo.Nome AS ModeloNome
                FROM ArmasFogo af
                LEFT JOIN ArmaEspecie e ON af.EspecieID = e.ID
                LEFT JOIN ArmaCalibre c ON af.CalibreID = c.ID
                LEFT JOIN ArmaMarca m ON af.MarcaID = m.ID
                LEFT JOIN ArmaModelo mo ON af.ModeloID = mo.ID
                WHERE af.ObjetoID = :objeto_id
            ";
            $stmtArma = $pdo->prepare($queryArma);
            $stmtArma->execute(['objeto_id' => $objetoID]);
            $arma = $stmtArma->fetch(PDO::FETCH_ASSOC);

            if ($arma) {
                $especieID = $arma['EspecieID'];
                $calibreID = $arma['CalibreID'];
                $numeroSerie = $arma['NumeroSerie'];
                $marcaID = $arma['MarcaID'];
                $modeloID = $arma['ModeloID'];
                $processoJudicialID = $arma['ProcessoJudicialID'];

                $especieNome = $arma['EspecieNome'];
                $calibreNome = $arma['CalibreNome'];
                $marcaNome = $arma['MarcaNome'];
                $modeloNome = $arma['ModeloNome'];
            }
        }
        
        logAcaoUsuario($pdo, 'visualizar_edicao_objeto', $objetoID);
    } else {
        echo "<div class='alert alert-danger text-center'>Objeto não encontrado ou acesso negado.</div>";
        include '../includes/footer.php';
        exit;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Erro de segurança. Tente novamente.';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    try {
        // Log dos dados recebidos
        error_log("Dados POST recebidos: " . print_r($_POST, true));
        
        // Sanitizar dados
        $dadosFormulario = [
            'tipo_id' => filter_var($_POST['tipo_id'] ?? 0, FILTER_VALIDATE_INT),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'quantidade' => filter_var($_POST['quantidade'] ?? 0, FILTER_VALIDATE_INT),
            'data_apreensao' => $_POST['data_apreensao'] ?? '',
            'lacre_atual' => trim($_POST['lacre_atual'] ?? ''),
            'arma_especie_id' => filter_var($_POST['arma_especie_id'] ?? 0, FILTER_VALIDATE_INT),
            'arma_calibre_id' => filter_var($_POST['arma_calibre_id'] ?? 0, FILTER_VALIDATE_INT),
            'numero_serie' => trim($_POST['numero_serie'] ?? ''),
            'arma_marca_id' => filter_var($_POST['arma_marca_id'] ?? 0, FILTER_VALIDATE_INT),
            'arma_modelo_id' => filter_var($_POST['arma_modelo_id'] ?? 0, FILTER_VALIDATE_INT),
            'processo_judicial_id' => filter_var($_POST['processo_judicial_id'] ?? 0, FILTER_VALIDATE_INT),
        ];
        
        // Converter zeros para null nos campos opcionais
        $camposOpcionais = ['arma_marca_id', 'arma_modelo_id', 'processo_judicial_id'];
        foreach ($camposOpcionais as $campo) {
            if ($dadosFormulario[$campo] === 0) {
                $dadosFormulario[$campo] = null;
            }
        }
        
        // Log dos dados sanitizados
        error_log("Dados sanitizados: " . print_r($dadosFormulario, true));

        // Validar dados
        $errosValidacao = validarDadosObjeto($dadosFormulario);
        error_log("Erros de validação: " . print_r($errosValidacao, true));

        if (!empty($errosValidacao)) {
            $_SESSION['error_message'] = 'Erros de validação encontrados:';
            $_SESSION['validation_errors'] = $errosValidacao;
            error_log("Erros de validação: " . print_r($errosValidacao, true));
        } else {
            $pdo->beginTransaction();
            error_log("Iniciando transação");

            try {
                if ($isEditing) {
                    error_log("Modo: Edição - Objeto ID: " . $objetoID);
                    // Atualizar objeto existente
                    $queryUpdateObjeto = "
                        UPDATE Objetos 
                        SET TipoObjetoID = :tipo_id, 
                            Descricao = :descricao, 
                            Quantidade = :quantidade, 
                            DataApreensao = :data_apreensao, 
                            LacreAtual = :lacre_atual
                        WHERE ID = :objeto_id AND ProcedimentoID = :procedimento_id
                    ";
                    $stmtUpdateObjeto = $pdo->prepare($queryUpdateObjeto);
                    $stmtUpdateObjeto->execute([
                        'tipo_id' => $dadosFormulario['tipo_id'],
                        'descricao' => $dadosFormulario['descricao'],
                        'quantidade' => $dadosFormulario['quantidade'],
                        'data_apreensao' => $dadosFormulario['data_apreensao'],
                        'lacre_atual' => $dadosFormulario['lacre_atual'],
                        'objeto_id' => $objetoID,
                        'procedimento_id' => $procedimentoID,
                    ]);

                    if ($dadosFormulario['tipo_id'] == 4) {
                        // Verificar se já existe registro de arma
                        $stmtCheckArma = $pdo->prepare("SELECT ID FROM ArmasFogo WHERE ObjetoID = ?");
                        $stmtCheckArma->execute([$objetoID]);
                        
                        if ($stmtCheckArma->fetchColumn()) {
                            // Atualizar arma existente
                            $queryUpdateArma = "
                                UPDATE ArmasFogo 
                                SET EspecieID = :especie_id, 
                                    CalibreID = :calibre_id, 
                                    NumeroSerie = :numero_serie, 
                                    MarcaID = :marca_id, 
                                    ModeloID = :modelo_id, 
                                    ProcessoJudicialID = :processo_judicial_id 
                                WHERE ObjetoID = :objeto_id
                            ";
                            $stmtUpdateArma = $pdo->prepare($queryUpdateArma);
                            $stmtUpdateArma->execute([
                                'especie_id' => $dadosFormulario['arma_especie_id'],
                                'calibre_id' => $dadosFormulario['arma_calibre_id'],
                                'numero_serie' => $dadosFormulario['numero_serie'],
                                'marca_id' => $dadosFormulario['arma_marca_id'],
                                'modelo_id' => $dadosFormulario['arma_modelo_id'],
                                'processo_judicial_id' => $dadosFormulario['processo_judicial_id'],
                                'objeto_id' => $objetoID,
                            ]);
                        } else {
                            // Inserir nova arma
                            $queryInsertArma = "
                                INSERT INTO ArmasFogo (
                                    ObjetoID, ProcedimentoID, EspecieID, CalibreID, 
                                    NumeroSerie, MarcaID, ModeloID, ProcessoJudicialID
                                ) VALUES (
                                    :objeto_id, :procedimento_id, :especie_id, :calibre_id, 
                                    :numero_serie, :marca_id, :modelo_id, :processo_judicial_id
                                )
                            ";
                            $stmtInsertArma = $pdo->prepare($queryInsertArma);
                            $stmtInsertArma->execute([
                                'objeto_id' => $objetoID,
                                'procedimento_id' => $procedimentoID,
                                'especie_id' => $dadosFormulario['arma_especie_id'],
                                'calibre_id' => $dadosFormulario['arma_calibre_id'],
                                'numero_serie' => $dadosFormulario['numero_serie'],
                                'marca_id' => $dadosFormulario['arma_marca_id'],
                                'modelo_id' => $dadosFormulario['arma_modelo_id'],
                                'processo_judicial_id' => $dadosFormulario['processo_judicial_id'],
                            ]);
                        }
                    }

                    logAcaoUsuario($pdo, 'editar_objeto', $objetoID, 'Objeto atualizado');
                    
                } else {
                    error_log("Modo: Inserção");
                    // Inserir novo objeto
                    $queryObjeto = "
                        INSERT INTO Objetos (
                            TipoObjetoID, Descricao, Quantidade, DataApreensao, LacreAtual, 
                            ProcedimentoID, UsuarioID
                        ) VALUES (
                            :tipo_id, :descricao, :quantidade, :data_apreensao, :lacre_atual, 
                            :procedimento_id, :usuario_id
                        )
                    ";
                    
                    $paramsObjeto = [
                        'tipo_id' => $dadosFormulario['tipo_id'],
                        'descricao' => $dadosFormulario['descricao'],
                        'quantidade' => $dadosFormulario['quantidade'],
                        'data_apreensao' => $dadosFormulario['data_apreensao'],
                        'lacre_atual' => $dadosFormulario['lacre_atual'],
                        'procedimento_id' => $procedimentoID,
                        'usuario_id' => $usuarioID
                    ];
                    
                    error_log("Query Objeto: " . $queryObjeto);
                    error_log("Parâmetros Objeto: " . print_r($paramsObjeto, true));
                    
                    $stmtObjeto = $pdo->prepare($queryObjeto);
                    $stmtObjeto->execute($paramsObjeto);
                    
                    $objetoID = $pdo->lastInsertId();
                    error_log("Novo Objeto ID: " . $objetoID);

                    // Registrar movimentação inicial
                    $queryMovimentacao = "
                        INSERT INTO MovimentacoesObjeto (
                            ObjetoID, TipoMovimentacaoID, Observacao, DataMovimentacao, UsuarioID
                        ) VALUES (
                            :objeto_id, :tipo_movimentacao_id, 'Entrada inicial no sistema', NOW(), :usuario_id
                        )
                    ";
                    
                    $paramsMovimentacao = [
                        'objeto_id' => $objetoID,
                        'tipo_movimentacao_id' => $tipoEntradaID,
                        'usuario_id' => $usuarioID
                    ];
                    
                    error_log("Query Movimentação: " . $queryMovimentacao);
                    error_log("Parâmetros Movimentação: " . print_r($paramsMovimentacao, true));
                    
                    $stmtMovimentacao = $pdo->prepare($queryMovimentacao);
                    $stmtMovimentacao->execute($paramsMovimentacao);

                    if ($dadosFormulario['tipo_id'] == 4) {
                        error_log("Inserindo dados de arma");
                        
                        // Validar campos obrigatórios de arma
                        if (empty($dadosFormulario['arma_especie_id']) || 
                            empty($dadosFormulario['arma_calibre_id']) || 
                            empty($dadosFormulario['numero_serie'])) {
                            throw new Exception("Campos obrigatórios da arma não preenchidos");
                        }
                        
                        $queryArma = "
                            INSERT INTO ArmasFogo (
                                ObjetoID, ProcedimentoID, EspecieID, CalibreID, 
                                NumeroSerie, MarcaID, ModeloID, ProcessoJudicialID
                            ) VALUES (
                                :objeto_id, :procedimento_id, :especie_id, :calibre_id, 
                                :numero_serie, :marca_id, :modelo_id, :processo_judicial_id
                            )
                        ";
                        
                        $paramsArma = [
                            'objeto_id' => $objetoID,
                            'procedimento_id' => $procedimentoID,
                            'especie_id' => $dadosFormulario['arma_especie_id'],
                            'calibre_id' => $dadosFormulario['arma_calibre_id'],
                            'numero_serie' => $dadosFormulario['numero_serie'],
                            'marca_id' => $dadosFormulario['arma_marca_id'],
                            'modelo_id' => $dadosFormulario['arma_modelo_id'],
                            'processo_judicial_id' => $dadosFormulario['processo_judicial_id']
                        ];
                        
                        error_log("Query Arma: " . $queryArma);
                        error_log("Parâmetros Arma: " . print_r($paramsArma, true));
                        
                        $stmtArma = $pdo->prepare($queryArma);
                        $stmtArma->execute($paramsArma);
                        error_log("Dados da arma inseridos com sucesso");
                    }

                    logAcaoUsuario($pdo, 'criar_objeto', $objetoID, 'Novo objeto criado');
                }

                $pdo->commit();
                error_log("Transação commitada com sucesso");
                $_SESSION['success_message'] = $isEditing ? "Objeto atualizado com sucesso!" : "Objeto adicionado com sucesso!";

                // Redirecionar baseado no botão clicado
                if (isset($_POST['salvar_adicionar_novo'])) {
                    $_SESSION['success_message'] = "Objeto adicionado com sucesso! Pronto para adicionar outro.";
                    header("Location: adicionar_objeto.php?procedimento_id=$procedimentoID");
                } else {
                    header("Location: ver_procedimento.php?id=$procedimentoID");
                }
                exit;

            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Erro PDO ao salvar objeto: " . $e->getMessage());
                error_log("SQL State: " . $e->getCode());
                error_log("Stack trace: " . $e->getTraceAsString());
                error_log("Dados do formulário: " . print_r($dadosFormulario, true));
                $_SESSION['error_message'] = "Erro ao salvar os dados. Por favor, verifique os campos e tente novamente.";
                logAcaoUsuario($pdo, 'erro_salvar_objeto', $objetoID ?? null, $e->getMessage());
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Erro geral ao salvar objeto: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                $_SESSION['error_message'] = $e->getMessage();
                logAcaoUsuario($pdo, 'erro_salvar_objeto', $objetoID ?? null, $e->getMessage());
            }
        }
    } catch (Exception $e) {
        error_log("Erro crítico: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $_SESSION['error_message'] = "Erro interno do sistema. Por favor, tente novamente.";
        logAcaoUsuario($pdo, 'erro_sistema', $objetoID ?? null, $e->getMessage());
    }
}

// Determinar se deve mostrar o card de arma inicialmente
$mostrarArmaCard = ($isEditing && $tipoID == 4);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEditing ? 'Editar Objeto' : 'Adicionar Objeto' ?> - Sistema Policial</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        .progress-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: rgba(0,0,0,0.1);
            z-index: 9999;
        }
        
        .progress-bar-custom {
            height: 100%;
            background: linear-gradient(90deg, #007bff, #28a745);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .card-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
        }
        
        .btn-floating {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .form-floating .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .invalid-feedback {
            display: block;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .spinner-custom {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .auto-save-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 8px 16px;
            background: #28a745;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            display: none;
            z-index: 1000;
        }
        
        .field-hint {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        .required-field {
            position: relative;
        }
        
        .required-field::after {
            content: "*";
            color: #dc3545;
            font-weight: bold;
            margin-left: 4px;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }
            
            .btn-floating {
                bottom: 10px;
                right: 10px;
                width: 50px;
                height: 50px;
            }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-bar-custom" id="progress-bar"></div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="text-center">
            <div class="spinner-custom"></div>
            <p class="mt-3">Salvando...</p>
        </div>
    </div>
    
    <!-- Auto-save Indicator -->
    <div class="auto-save-indicator" id="auto-save-indicator">
        <i class="fas fa-check-circle"></i> Salvo automaticamente
    </div>

    <div class="container mt-5 fade-in">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="display-6 text-center">
                    <i class="fas fa-<?= $isEditing ? 'edit' : 'plus-circle' ?> me-2"></i>
                    <?= $isEditing ? 'Editar Objeto' : 'Adicionar Objeto' ?>
                </h1>
                <p class="text-muted text-center">
                    Procedimento ID: <?= htmlspecialchars($procedimentoID) ?>
                </p>
            </div>
        </div>

        <!-- Mensagens -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['validation_errors'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Erros de validação:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($_SESSION['validation_errors'] as $erro): ?>
                        <li><?= htmlspecialchars($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['validation_errors']); ?>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate id="form-objeto">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <!-- Card 1: Informações Básicas -->
            <div class="card mb-4 shadow">
                <div class="card-header text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informações Básicas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_id" class="form-label required-field">Tipo de Objeto</label>
                            <select name="tipo_id" id="tipo_id" class="form-select select2" required>
                                <option value="">Selecione o Tipo</option>
                                <?php foreach ($tiposObjeto as $tipo): ?>
                                    <option value="<?= htmlspecialchars($tipo['ID']) ?>" 
                                        <?= isset($tipoID) && $tipoID == $tipo['ID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['Nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="field-hint">
                                <i class="fas fa-info-circle"></i>
                                Selecione o tipo do objeto apreendido
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="lacre_atual" class="form-label">Lacre Atual</label>
                            <input type="text" name="lacre_atual" id="lacre_atual" class="form-control" 
                                   value="<?= htmlspecialchars($lacreAtual ?? '') ?>" 
                                   placeholder="Número do lacre">
                            <div class="field-hint">
                                <i class="fas fa-tag"></i>
                                Número de identificação do lacre (opcional)
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="number" name="quantidade" id="quantidade" class="form-control" 
                                       min="1" value="<?= htmlspecialchars($isEditing ? $quantidade : 1) ?>" 
                                       required placeholder="Quantidade">
                                <label for="quantidade" class="required-field">Quantidade</label>
                                <div class="field-hint">
                                    <i class="fas fa-hashtag"></i>
                                    Número de unidades do objeto
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="date" name="data_apreensao" id="data_apreensao" class="form-control" 
                                       value="<?= htmlspecialchars($dataApreensao ?? date('Y-m-d')) ?>" 
                                       required placeholder="Data de Apreensão">
                                <label for="data_apreensao" class="required-field">Data de Apreensão</label>
                                <div class="field-hint">
                                    <i class="fas fa-calendar"></i>
                                    Data em que o objeto foi apreendido
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-floating">
                            <textarea name="descricao" id="descricao" class="form-control" 
                                      rows="4" style="height: 120px;" required 
                                      placeholder="Descreva o objeto"><?= htmlspecialchars($descricao ?? '') ?></textarea>
                            <label for="descricao" class="required-field">Descrição</label>
                            <div class="field-hint">
                                <i class="fas fa-file-text"></i>
                                Descrição detalhada do objeto (mínimo 10 caracteres)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Detalhes da Arma de Fogo -->
            <div class="card mb-4 shadow" id="arma-card" style="display: <?= $mostrarArmaCard ? 'block' : 'none' ?>;">
                <div class="card-header text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-crosshairs me-2"></i>
                        Detalhes da Arma de Fogo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="arma_especie_id" class="form-label required-field">Espécie</label>
                            <select name="arma_especie_id" id="arma_especie_id" class="form-select select2">
                                <option value="">Selecione a Espécie</option>
                                <?php 
                                $queryEspecies = "SELECT ID, Nome FROM ArmaEspecie ORDER BY Nome ASC";
                                $stmtEspecies = $pdo->query($queryEspecies);
                                $especiesObjeto = $stmtEspecies->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($especiesObjeto as $especie): 
                                ?>
                                    <option value="<?= htmlspecialchars($especie['ID']) ?>" 
                                        <?= isset($especieID) && $especieID == $especie['ID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($especie['Nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="field-hint">
                                <i class="fas fa-list"></i>
                                Tipo específico da arma de fogo
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="arma_calibre_id" class="form-label required-field">Calibre</label>
                            <select name="arma_calibre_id" id="arma_calibre_id" class="form-select select2">
                                <option value="">Selecione o Calibre</option>
                                <?php 
                                $queryCalibres = "SELECT ID, Nome FROM ArmaCalibre ORDER BY Nome ASC";
                                $stmtCalibres = $pdo->query($queryCalibres);
                                $calibresObjeto = $stmtCalibres->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($calibresObjeto as $calibre): 
                                ?>
                                    <option value="<?= htmlspecialchars($calibre['ID']) ?>" 
                                        <?= isset($calibreID) && $calibreID == $calibre['ID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($calibre['Nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="field-hint">
                                <i class="fas fa-bullseye"></i>
                                Calibre da munição utilizada
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="numero_serie" class="form-label required-field">Número de Série</label>
                            <input type="text" name="numero_serie" id="numero_serie" class="form-control" 
                                   value="<?= htmlspecialchars($numeroSerie ?? '') ?>" 
                                   placeholder="Número de Série">
                            <div class="field-hint">
                                <i class="fas fa-barcode"></i>
                                Numeração de identificação da arma
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="arma_marca_id" class="form-label">Marca</label>
                            <select name="arma_marca_id" id="arma_marca_id" class="form-select select2">
                                <option value="">Selecione a Marca</option>
                                <?php 
                                $queryMarcas = "SELECT ID, Nome FROM ArmaMarca ORDER BY Nome ASC";
                                $stmtMarcas = $pdo->query($queryMarcas);
                                $marcasObjeto = $stmtMarcas->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($marcasObjeto as $marca): 
                                ?>
                                    <option value="<?= htmlspecialchars($marca['ID']) ?>" 
                                        <?= isset($marcaID) && $marcaID == $marca['ID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($marca['Nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="field-hint">
                                <i class="fas fa-industry"></i>
                                Fabricante da arma de fogo
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="arma_modelo_id" class="form-label">Modelo</label>
                            <select name="arma_modelo_id" id="arma_modelo_id" class="form-select select2">
                                <option value="">Selecione o Modelo</option>
                                <?php 
                                $queryModelos = "SELECT ID, Nome FROM ArmaModelo ORDER BY Nome ASC";
                                $stmtModelos = $pdo->query($queryModelos);
                                $modelosObjeto = $stmtModelos->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($modelosObjeto as $modelo): 
                                ?>
                                    <option value="<?= htmlspecialchars($modelo['ID']) ?>" 
                                        <?= isset($modeloID) && $modeloID == $modelo['ID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($modelo['Nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="field-hint">
                                <i class="fas fa-tag"></i>
                                Modelo específico da arma
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="processo_judicial_id" class="form-label">Processo Judicial</label>
                            <select name="processo_judicial_id" id="processo_judicial_id" class="form-select select2">
                                <option value="">Selecione o Processo</option>
                                <?php foreach ($processosJudiciais as $processo): ?>
                                    <option value="<?= htmlspecialchars($processo['ID']) ?>" 
                                        <?= isset($processoJudicialID) && $processoJudicialID == $processo['ID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($processo['Numero']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="field-hint">
                                <i class="fas fa-gavel"></i>
                                Processo judicial relacionado (opcional)
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <button type="button" id="copiar-detalhes-btn" class="btn btn-outline-primary">
                                <i class="fas fa-copy me-2"></i>
                                Gerar Descrição Automática
                            </button>
                            <small class="text-muted ms-2">
                                Preenche automaticamente a descrição com os dados da arma
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="card mb-4 shadow">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 justify-content-between">
                        <div class="d-flex gap-2">
                            <button type="submit" name="salvar" class="btn btn-success btn-lg" id="btn-salvar">
                                <i class="fas fa-save me-2"></i>
                                <?= $isEditing ? 'Atualizar' : 'Salvar' ?>
                            </button>
                            
                            <?php if (!$isEditing): ?>
                                <button type="submit" name="salvar_adicionar_novo" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus me-2"></i>
                                    Salvar e Adicionar Novo
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-info" id="btn-preview">
                                <i class="fas fa-eye me-2"></i>
                                Visualizar
                            </button>
                            
                            <a href="ver_procedimento.php?id=<?= htmlspecialchars($procedimentoID) ?>" 
                               class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Floating Help Button -->
    <button type="button" class="btn btn-info btn-floating" data-bs-toggle="modal" data-bs-target="#helpModal">
        <i class="fas fa-question"></i>
    </button>

    <!-- Modal de Ajuda -->
    <div class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-question-circle me-2"></i>
                        Ajuda - Cadastro de Objetos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="accordion" id="helpAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#help1">
                                    <i class="fas fa-keyboard me-2"></i>
                                    Atalhos de Teclado
                                </button>
                            </h2>
                            <div id="help1" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li><kbd>Ctrl + S</kbd> - Salvar formulário</li>
                                        <li><kbd>F2</kbd> - Focar no campo descrição</li>
                                        <li><kbd>Esc</kbd> - Cancelar e voltar</li>
                                        <li><kbd>Tab</kbd> - Navegar entre campos</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#help2">
                                    <i class="fas fa-crosshairs me-2"></i>
                                    Cadastro de Armas
                                </button>
                            </h2>
                            <div id="help2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                <div class="accordion-body">
                                    <p>Para armas de fogo, os seguintes campos são obrigatórios:</p>
                                    <ul>
                                        <li>Espécie da arma</li>
                                        <li>Calibre</li>
                                        <li>Número de série</li>
                                    </ul>
                                    <p>Use o botão "Gerar Descrição Automática" para preencher automaticamente a descrição com os dados da arma.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Variáveis globais
            const procedimentoID = <?= json_encode($procedimentoID) ?>;
            const isEditing = <?= json_encode($isEditing) ?>;
            const mostrarArmaInicial = <?= json_encode($mostrarArmaCard) ?>;
            const autoSaveInterval = 30000; // 30 segundos
            
            // Inicializar progress bar
            updateProgressBar();
            
            // Auto-save functionality
            let autoSaveTimer;
            let formChanged = false;
            
            // Detectar mudanças no formulário
            $('#form-objeto input, #form-objeto select, #form-objeto textarea').on('change input', function() {
                formChanged = true;
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(autoSave, autoSaveInterval);
            });
            
            // Função de auto-save
            function autoSave() {
                if (!formChanged) return;
                
                const formData = new FormData(document.getElementById('form-objeto'));
                const data = Object.fromEntries(formData);
                
                // Salvar no localStorage
                localStorage.setItem('form_backup_' + procedimentoID, JSON.stringify(data));
                
                // Mostrar indicador
                showAutoSaveIndicator();
                formChanged = false;
            }
            
            // Mostrar indicador de auto-save
            function showAutoSaveIndicator() {
                const indicator = $('#auto-save-indicator');
                indicator.fadeIn().delay(2000).fadeOut();
            }
            
            // Recuperar dados salvos
            function restoreFormData() {
                const savedData = localStorage.getItem('form_backup_' + procedimentoID);
                if (savedData && !isEditing) {
                    const data = JSON.parse(savedData);
                    
                    Swal.fire({
                        title: 'Dados Salvos Encontrados',
                        text: 'Deseja restaurar os dados salvos automaticamente?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, restaurar',
                        cancelButtonText: 'Não, começar novo'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Object.keys(data).forEach(key => {
                                const element = $(`[name="${key}"]`);
                                if (element.length) {
                                    element.val(data[key]).trigger('change');
                                }
                            });
                            Swal.fire('Restaurado!', 'Dados restaurados com sucesso.', 'success');
                        } else {
                            localStorage.removeItem('form_backup_' + procedimentoID);
                        }
                    });
                }
            }
            
            // Inicializar Select2 básico para campos estáticos
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true
            });
            
            // Função para controlar campos de arma
            function toggleArmaFields(show) {
                const armaCard = $('#arma-card');
                const camposObrigatorios = $('#arma_especie_id, #arma_calibre_id, #numero_serie');
                const todosCamposArma = $('#arma_especie_id, #arma_calibre_id, #arma_marca_id, #arma_modelo_id, #numero_serie, #processo_judicial_id');
                
                if (show) {
                    armaCard.slideDown(300);
                    // Tornar campos obrigatórios
                    camposObrigatorios.prop('required', true);
                    // Reinicializar Select2 para garantir que os valores estejam visíveis
                    $('#arma_especie_id, #arma_calibre_id, #arma_marca_id, #arma_modelo_id, #processo_judicial_id').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        allowClear: true
                    });
                } else {
                    armaCard.slideUp(300);
                    // Remover obrigatoriedade
                    camposObrigatorios.prop('required', false);
                    // Limpar valores apenas se não estiver em modo de edição
                    if (!isEditing) {
                        todosCamposArma.val('').trigger('change');
                        $('#arma_especie_id, #arma_calibre_id, #arma_marca_id, #arma_modelo_id, #processo_judicial_id').val(null).trigger('change');
                    }
                }
                
                updateProgressBar();
            }
            
            // Controlar exibição dos campos de arma
            $('#tipo_id').on('change', function() {
                const valorSelecionado = $(this).val();
                const isArma = (valorSelecionado === '4');
                
                console.log('Tipo selecionado:', valorSelecionado, 'É arma:', isArma);
                
                toggleArmaFields(isArma);
            });

            // Verificar o tipo inicial após o carregamento da página
            $(document).ready(function() {
                // Se estiver editando e for arma, mostrar campos
                if (mostrarArmaInicial) {
                    toggleArmaFields(true);
                }
                
                // Verificar valor inicial do select
                const tipoInicial = $('#tipo_id').val();
                if (tipoInicial === '4') {
                    toggleArmaFields(true);
                }
            });
            
            // Gerar descrição automática para armas
            $('#copiar-detalhes-btn').on('click', function() {
                const descricao = gerarDescricaoArma();
                $('#descricao').val(descricao);
                
                // Animação visual
                $('#descricao').addClass('border-success').delay(1000).queue(function() {
                    $(this).removeClass('border-success').dequeue();
                });
                
                Swal.fire({
                    icon: 'success',
                    title: 'Descrição Gerada!',
                    text: 'A descrição foi preenchida automaticamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            });

            // Função para gerar descrição da arma
            function gerarDescricaoArma() {
                const especie = $('#arma_especie_id option:selected').text().trim() || 'Não informado';
                const calibre = $('#arma_calibre_id option:selected').text().trim() || 'Não informado';
                const marca = $('#arma_marca_id option:selected').text().trim() || 'Não informado';
                const modelo = $('#arma_modelo_id option:selected').text().trim() || 'Não informado';
                const numeroSerie = $('#numero_serie').val().trim() || 'Não informado';
                
                let descricao = 'Arma de fogo';
                if (especie && especie !== 'Selecione a Espécie') {
                    descricao += `, tipo: ${especie}`;
                }
                if (calibre && calibre !== 'Selecione o Calibre') {
                    descricao += `, calibre: ${calibre}`;
                }
                if (marca && marca !== 'Selecione a Marca') {
                    descricao += `, marca: ${marca}`;
                }
                if (modelo && modelo !== 'Selecione o Modelo') {
                    descricao += `, modelo: ${modelo}`;
                }
                descricao += `, número de série: ${numeroSerie}`;
                
                return descricao;
            }
            
            // Validação em tempo real
            $('#descricao').on('input', function() {
                const value = $(this).val();
                const minLength = 10;
                const feedback = $(this).siblings('.invalid-feedback');
                
                if (value.length < minLength) {
                    $(this).addClass('is-invalid').removeClass('is-valid');
                    if (feedback.length === 0) {
                        $(this).after(`<div class="invalid-feedback">A descrição deve ter pelo menos ${minLength} caracteres.</div>`);
                    }
                } else {
                    $(this).addClass('is-valid').removeClass('is-invalid');
                    feedback.remove();
                }
                
                updateProgressBar();
            });
            
            // Validação para campos obrigatórios
            $('input[required], select[required], textarea[required]').on('blur change', function() {
                const value = $(this).val();
                if (!value || value.trim() === '') {
                    $(this).addClass('is-invalid').removeClass('is-valid');
                } else {
                    $(this).addClass('is-valid').removeClass('is-invalid');
                }
                updateProgressBar();
            });
            
            // Atualizar barra de progresso
            function updateProgressBar() {
                const totalFields = $('input[required], select[required], textarea[required]').length;
                const filledFields = $('input[required], select[required], textarea[required]').filter(function() {
                    return $(this).val() && $(this).val().trim() !== '';
                }).length;
                
                const progress = totalFields > 0 ? (filledFields / totalFields) * 100 : 0;
                $('#progress-bar').css('width', progress + '%');
            }
            
            // Preview do objeto
            $('#btn-preview').on('click', function() {
                const formData = new FormData(document.getElementById('form-objeto'));
                const data = Object.fromEntries(formData);
                
                let previewHtml = '<div class="row">';
                previewHtml += `<div class="col-md-6"><strong>Tipo:</strong> ${$('#tipo_id option:selected').text()}</div>`;
                previewHtml += `<div class="col-md-6"><strong>Quantidade:</strong> ${data.quantidade || 'Não informado'}</div>`;
                previewHtml += `<div class="col-md-6"><strong>Data Apreensão:</strong> ${data.data_apreensao || 'Não informado'}</div>`;
                previewHtml += `<div class="col-md-6"><strong>Lacre:</strong> ${data.lacre_atual || 'Não informado'}</div>`;
                previewHtml += `<div class="col-12 mt-2"><strong>Descrição:</strong><br>${data.descricao || 'Não informado'}</div>`;
                
                if (data.tipo_id === '4') {
                    previewHtml += '<div class="col-12 mt-3"><h6>Detalhes da Arma:</h6>';
                    previewHtml += `<div class="col-md-6"><strong>Espécie:</strong> ${$('#arma_especie_id option:selected').text()}</div>`;
                    previewHtml += `<div class="col-md-6"><strong>Calibre:</strong> ${$('#arma_calibre_id option:selected').text()}</div>`;
                    previewHtml += `<div class="col-md-6"><strong>Marca:</strong> ${$('#arma_marca_id option:selected').text()}</div>`;
                    previewHtml += `<div class="col-md-6"><strong>Modelo:</strong> ${$('#arma_modelo_id option:selected').text()}</div>`;
                    previewHtml += `<div class="col-md-6"><strong>Nº Série:</strong> ${data.numero_serie || 'Não informado'}</div>`;
                    previewHtml += '</div>';
                }
                
                previewHtml += '</div>';
                
                Swal.fire({
                    title: 'Pré-visualização do Objeto',
                    html: previewHtml,
                    width: '600px',
                    confirmButtonText: 'Fechar'
                });
            });

            // Atalhos de teclado
            $(document).on('keydown', function(e) {
                // Ctrl+S para salvar
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    $('#btn-salvar').click();
                }
                
                // F2 para focar na descrição
                if (e.key === 'F2') {
                    e.preventDefault();
                    $('#descricao').focus();
                }
                
                // Esc para cancelar
                if (e.key === 'Escape') {
                    e.preventDefault();
                    if (confirm('Deseja realmente cancelar? Dados não salvos serão perdidos.')) {
                        window.location.href = `ver_procedimento.php?id=${procedimentoID}`;
                    }
                }
            });
            
            // Interceptar envio do formulário
            $('#form-objeto').on('submit', function(e) {
                e.preventDefault();
                
                // Mostrar loading
                $('#loading-overlay').show();
                
                // Validar formulário
                const form = this;
                if (!form.checkValidity()) {
                    e.stopPropagation();
                    $('#loading-overlay').hide();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Formulário Inválido',
                        text: 'Por favor, preencha todos os campos obrigatórios.',
                    });
                    
                    form.classList.add('was-validated');
                    return false;
                }
                
                // Desativar o evento beforeunload
                window.onbeforeunload = null;
                
                // Enviar formulário
                setTimeout(() => {
                    // Limpar backup se sucesso
                    localStorage.removeItem('form_backup_' + procedimentoID);
                    form.submit();
                }, 1000);
            });
            
            // Prevenir perda de dados apenas se houver mudanças não salvas
            window.addEventListener('beforeunload', function(e) {
                if (formChanged && !$('#loading-overlay').is(':visible')) {
                    e.preventDefault();
                    e.returnValue = 'Você tem alterações não salvas. Deseja realmente sair?';
                }
            });
            
            // Restaurar dados ao carregar
            restoreFormData();
            
            // Inicializar progress bar
            setTimeout(updateProgressBar, 500);
            
            // Debug: adicionar logs para verificar funcionamento
            console.log('Script inicializado');
            console.log('Modo edição:', isEditing);
            console.log('Mostrar arma inicial:', mostrarArmaInicial);
            console.log('Valor inicial do tipo:', $('#tipo_id').val());
        });
    </script>
</body>
</html>