<?php

// Função para obter uma movimentação por ID
function getMovimentacaoById($pdo, $movimentacaoID, $procedimentoID)
{
    $query = "
        SELECT m.*, o.NumeroOficio, o.DataOficio, o.Destino, o.SEI
        FROM Movimentacoes m
        LEFT JOIN Oficios o ON m.ID = o.MovimentacaoID
        WHERE m.ID = :movimentacao_id AND m.ProcedimentoID = :procedimento_id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['movimentacao_id' => $movimentacaoID, 'procedimento_id' => $procedimentoID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para obter todos os tipos de movimentação
function getTiposMovimentacao($pdo)
{
    $query = "SELECT ID, Nome FROM TiposMovimentacao";
    return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter todos os responsáveis
function getResponsaveis($pdo)
{
    $query = "SELECT ID, Nome FROM Usuarios";
    return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter as situações de procedimento
function getSituacoesProcedimento($pdo)
{
    $query = "SELECT ID, Nome FROM SituacoesProcedimento";
    return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

function processMovimentacao($pdo, $data, $movimentacaoID = null)
{
    try {
        // Obtém o ID do usuário logado
        $usuarioID = $_SESSION['usuario_id'] ?? null;

        if (!$usuarioID) {
            return ['success' => false, 'error' => "Usuário não identificado."];
        }

        // Inicia a transação
        $pdo->beginTransaction();

        // Dados comuns entre adição e edição
        $params = [
            'tipo_id' => $data['tipo_id'],
            'assunto' => trim($data['assunto']),
            'detalhes' => trim($data['detalhes'] ?? ''),
            'situacao' => $data['situacao'],
            'data_vencimento' => $data['data_vencimento'],
            'responsavel_id' => $data['responsavel_id'],
            'data_conclusao' => empty($data['data_conclusao']) ? null : $data['data_conclusao'],
            'data_requisicao' => empty($data['data_requisicao']) ? null : $data['data_requisicao'],
            'usuario_id' => $usuarioID,
        ];

        if ($movimentacaoID) {
            // Adiciona o ID da movimentação ao array de parâmetros
            $params['movimentacao_id'] = $movimentacaoID;

            // Query para atualizar movimentação existente
            $query = "
                UPDATE Movimentacoes
                SET TipoID = :tipo_id,
                    Assunto = :assunto,
                    Detalhes = :detalhes,
                    Situacao = :situacao,
                    DataVencimento = :data_vencimento,
                    ResponsavelID = :responsavel_id,
                    DataConclusao = :data_conclusao,
                    UsuarioID = :usuario_id,
                    DataRequisicao = :data_requisicao
                WHERE ID = :movimentacao_id
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
        } else {
            // Adiciona o ProcedimentoID ao array de parâmetros para inserção
            $params['procedimento_id'] = $data['procedimento_id'];

            // Query para inserir nova movimentação
            $query = "
                INSERT INTO Movimentacoes 
                (TipoID, Assunto, Detalhes, Situacao, DataVencimento, ProcedimentoID, ResponsavelID, DataConclusao, UsuarioID, DataRequisicao)
                VALUES (:tipo_id, :assunto, :detalhes, :situacao, :data_vencimento, :procedimento_id, :responsavel_id, :data_conclusao, :usuario_id, :data_requisicao)
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            // Obter o ID da movimentação inserida
            $movimentacaoID = $pdo->lastInsertId();
        }

        // Atualizar o campo SituacaoID do Procedimento, se aplicável
        if (!empty($data['situacao_procedimento'])) {
            $queryUpdateProcedimento = "
                UPDATE Procedimentos
                SET SituacaoID = :situacao_id
                WHERE ID = :procedimento_id
            ";
            $stmtUpdate = $pdo->prepare($queryUpdateProcedimento);
            $stmtUpdate->execute([
                'situacao_id' => $data['situacao_procedimento'],
                'procedimento_id' => $data['procedimento_id']
            ]);
        }

        // Processar upload de arquivos
        if (!empty($_FILES['documentos'])) {
            $uploadResult = handleFileUpload($pdo, $movimentacaoID, $_FILES['documentos']);
            if (!$uploadResult['success']) {
                return ['success' => false, 'error' => $uploadResult['error']];
            }
        }

        // Processar tipos específicos de movimentação
        if ($data['tipo_id'] == 5) { // Remessa de IP
            processProcessoJudicial($pdo, $movimentacaoID, $data);
        } elseif ($data['tipo_id'] == 9) { // Ofício
            // Processar ofício usando a função específica
            processOficio($pdo, $movimentacaoID, $data);
        }

        $pdo->commit();
        return ['success' => true];
    } catch (PDOException $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => "Erro ao processar movimentação: " . $e->getMessage()];
    }
}

// Função para processar processo judicial
function processProcessoJudicial($pdo, $movimentacaoID, $data)
{
    $numeroProcesso = trim($data['numero_processo'] ?? '');
    if (!empty($numeroProcesso)) {
        $queryVerificar = "
            SELECT COUNT(*)
            FROM ProcessosJudiciais
            WHERE Numero = :numero AND ProcedimentoID = :procedimento_id
        ";
        $stmtVerificar = $pdo->prepare($queryVerificar);
        $stmtVerificar->execute([
            'numero' => $numeroProcesso,
            'procedimento_id' => $data['procedimento_id'],
        ]);

        if ($stmtVerificar->fetchColumn() == 0) {
            $query = "
                INSERT INTO ProcessosJudiciais (ProcedimentoID, Numero, Descricao)
                VALUES (:procedimento_id, :numero, 'IP')
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'procedimento_id' => $data['procedimento_id'],
                'numero' => $numeroProcesso,
            ]);
        }
    }
}

// Função para processar ofício
function processOficio($pdo, $movimentacaoID, $data)
{
    // Buscar o assunto da movimentação
    $queryAssunto = "SELECT Assunto FROM Movimentacoes WHERE ID = :movimentacao_id";
    $stmtAssunto = $pdo->prepare($queryAssunto);
    $stmtAssunto->execute(['movimentacao_id' => $movimentacaoID]);
    $assuntoMovimentacao = $stmtAssunto->fetchColumn();

    // Se não encontrar o assunto ou estiver vazio, usar um valor padrão
    if (empty($assuntoMovimentacao)) {
        $assuntoMovimentacao = "Ofício sem assunto especificado";
    }

    // Verificar se já existe um registro de ofício para esta movimentação
    $queryVerificar = "SELECT COUNT(*) FROM Oficios WHERE MovimentacaoID = :movimentacao_id";
    $stmtVerificar = $pdo->prepare($queryVerificar);
    $stmtVerificar->execute(['movimentacao_id' => $movimentacaoID]);
    $existeOficio = $stmtVerificar->fetchColumn() > 0;

    // Preparar os dados do ofício
    $params = [
        'movimentacao_id' => $movimentacaoID,
        'numero_oficio' => trim($data['numero_oficio'] ?? ''),
        'data_oficio' => $data['data_oficio'] ?? null,
        'destino' => trim($data['destino'] ?? ''),
        'sei' => trim($data['sei'] ?? ''),
        'assunto' => $assuntoMovimentacao
    ];

    if ($existeOficio) {
        // Se já existe, atualiza o registro
        $query = "
            UPDATE Oficios 
            SET NumeroOficio = :numero_oficio,
                DataOficio = :data_oficio,
                Destino = :destino,
                SEI = :sei,
                Assunto = :assunto
            WHERE MovimentacaoID = :movimentacao_id
        ";
    } else {
        // Se não existe, insere um novo registro
        $query = "
            INSERT INTO Oficios (MovimentacaoID, NumeroOficio, DataOficio, Destino, SEI, Assunto)
            VALUES (:movimentacao_id, :numero_oficio, :data_oficio, :destino, :sei, :assunto)
        ";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
}

function handleFileUpload($pdo, $movimentacaoID, $files, $uploadDir = '../uploads/movimentacoes/')
{
    // Verifica se existem arquivos para upload
    if (empty($files['name'][0])) {
        return ['success' => true]; // Nenhum arquivo para processar
    }

    // Cria o diretório de upload, se necessário
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'error' => "Erro ao criar o diretório de upload."];
        }
    }

    // Itera sobre os arquivos enviados
    foreach ($files['name'] as $key => $filename) {
        $fileTmpPath = $files['tmp_name'][$key];
        $safeFilename = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $filename); // Sanitiza o nome do arquivo
        $destination = $uploadDir . uniqid() . '_' . $safeFilename; // Gera um nome único para o arquivo

        // Tenta mover o arquivo para o diretório de destino
        if (move_uploaded_file($fileTmpPath, $destination)) {
            // Insere as informações do arquivo no banco de dados
            $queryDocumento = "
                INSERT INTO DocumentosMovimentacao (MovimentacaoID, NomeArquivo, Caminho)
                VALUES (:movimentacao_id, :nome_arquivo, :caminho)
            ";
            $stmtDocumento = $pdo->prepare($queryDocumento);
            $stmtDocumento->execute([
                'movimentacao_id' => $movimentacaoID,
                'nome_arquivo' => $filename,
                'caminho' => $destination,
            ]);
        } else {
            return ['success' => false, 'error' => "Erro ao fazer o upload do arquivo: $filename"];
        }
    }

    return ['success' => true];
}

function getSituacoesPorCategoria($pdo, $tipoProcedimento)
{
    $categoria = $tipoProcedimento === 1 ? 'IP' : 'VPI'; // Define a categoria com base no TipoID
    $query = "
        SELECT ID, Nome
        FROM SituacoesProcedimento
        WHERE Categoria = :categoria
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['categoria' => $categoria]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTipoProcedimento($procedimentoID) {
    global $pdo;

    $query = "
        SELECT tp.ID
        FROM Procedimentos p
        INNER JOIN TiposProcedimento tp ON p.TipoID = tp.ID
        WHERE p.ID = :procedimento_id
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['procedimento_id' => $procedimentoID]);
    return $stmt->fetchColumn(); // Retorna o TipoID (1 ou 2)
}

