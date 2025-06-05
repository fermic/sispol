<?php
/**
 * Formata uma data no formato brasileiro (DD/MM/YYYY HH:MM:SS).
 *
 * @param string|null $data Data no formato do banco (Y-m-d H:i:s ou Y-m-d).
 * @return string Data formatada ou "Sem data" se o valor for nulo.
 */
function formatarDataBrasileira($data) {
    if ($data) {
        $dataFormatada = DateTime::createFromFormat('Y-m-d H:i:s', $data);
        if ($dataFormatada) {
            return $dataFormatada->format('d/m/Y H:i:s');
        }
        $dataFormatada = DateTime::createFromFormat('Y-m-d', $data);
        return $dataFormatada ? $dataFormatada->format('d/m/Y') : 'Formato Inválido';
    }
    return 'Sem data';
}

// Buscar opções genéricas de tabelas
function buscar_opcoes($pdo, $tabela, $idColuna, $nomeColuna, $condicao = '') {
    $query = "SELECT {$idColuna} AS id, {$nomeColuna} AS nome FROM {$tabela}";
    if (!empty($condicao)) {
        $query .= " WHERE {$condicao}";
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Gerar selects de opções
function gerar_select($name, $label, $opcoes, $selecionado = null, $placeholder = "Selecione") {
    $html = "<div class='col-md-6'>"; // Ajustado para melhor compatibilidade com Bootstrap
    $html .= "<label for='{$name}' class='form-label'>{$label}</label>"; // Usando a classe padrão do Bootstrap para labels
    $html .= "<select name='{$name}' id='{$name}' class='form-select'>"; // Usando a classe Bootstrap "form-select"
    $html .= "<option value=''>{$placeholder}</option>"; // Adiciona "Selecione"
    
    foreach ($opcoes as $opcao) {
        $selected = ($opcao['id'] == $selecionado) ? 'selected' : '';
        $html .= "<option value='{$opcao['id']}' {$selected}>{$opcao['nome']}</option>";
    }
    
    $html .= "</select>";
    $html .= "</div>";
    
    return $html;
}




// Função para cadastrar procedimento no banco
function cadastrar_procedimento($pdo, $data) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO Procedimentos 
            (SituacaoID, OrigemID, TipoID, RAI, NumeroProcedimento, NumeroJudicial, DataFato, DataInstauracao, MotivoAparente, EnderecoFato, 
            EscrivaoID, DelegadoID, DelegaciaID, DataCriacao, MeioEmpregadoID) 
            VALUES (:SituacaoID, :OrigemID, :TipoID, :RAI, :NumeroProcedimento, :NumeroJudicial, :DataFato, :DataInstauracao, :MotivoAparente, 
            :EnderecoFato, :EscrivaoID, :DelegadoID, :DelegaciaID, NOW(), :MeioEmpregadoID)
        ");
        $stmt->execute($data);

        // Certifique-se de obter o ID corretamente
        $procedimentoID = $pdo->lastInsertId();
        if (!$procedimentoID) {
            throw new Exception('Falha ao inserir o Procedimento. Nenhum ID gerado.');
        }

        $pdo->commit();
        return $procedimentoID;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}


// Função para criar um crime caso não exista
function criar_crime($pdo, $nome) {
    $stmt = $pdo->prepare("INSERT INTO Crimes (Nome) VALUES (:Nome)");
    $stmt->execute([':Nome' => $nome]);
    return $pdo->lastInsertId();
}

function cadastrar_vitimas($pdo, $procedimentoID, $vitimas) {
    foreach ($vitimas as $vitima) {
        $stmt = $pdo->prepare("INSERT INTO Vitimas (Nome, Idade, ProcedimentoID) VALUES (:Nome, :Idade, :ProcedimentoID)");
        $stmt->execute([
            ':Nome' => $vitima['nome'],
            ':Idade' => $vitima['idade'],
            ':ProcedimentoID' => $procedimentoID,
        ]);

        $vitimaID = $pdo->lastInsertId();

        foreach ($vitima['crimes'] as $crime) {
            if (!empty($crime['id'])) {
                $stmt = $pdo->prepare("INSERT INTO Vitimas_Crimes (VitimaID, CrimeID, Modalidade) VALUES (:VitimaID, :CrimeID, :Modalidade)");
                $stmt->execute([
                    ':VitimaID' => $vitimaID,
                    ':CrimeID' => $crime['id'],
                    ':Modalidade' => $crime['modalidade'],
                ]);
            }
        }
    }
}

function cadastrar_investigados($pdo, $procedimentoID, $investigados) {
    foreach ($investigados as $investigado) {
        $stmt = $pdo->prepare("INSERT INTO Investigados (Nome, ProcedimentoID) VALUES (:Nome, :ProcedimentoID)");
        $stmt->execute([
            ':Nome' => $investigado['nome'],
            ':ProcedimentoID' => $procedimentoID,
        ]);
    }
}


function sendWhatsAppMessage($number, $message) {
    // Ajusta o número para o formato correto
    if (strlen($number) >= 13) {
        $number = substr($number, 0, 4) . substr($number, 5); // Remove o quinto dígito
    }
    
    // URL e configuração para envio
    $url = 'http://85.239.238.30:3000/send-message';
    $data = ['number' => $number, 'message' => $message];
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
        // Tratamento de erro
        throw new Exception("Erro ao enviar a mensagem para o número $number");
    }
    
    return $result;
}


/**
 * Formata o número do processo judicial no padrão 9999999-99.9999.9.99.9999
 *
 * @param string $numero Processo sem formatação (somente números)
 * @return string Número do processo formatado
 */
function formatar_numero_processo($numero) {
    // Remove tudo que não for dígito
    $numero = preg_replace('/\D/', '', $numero);

    // Verifica se o número tem o tamanho correto
    if (strlen($numero) !== 20) {
        return $numero; // Retorna o número original se não tiver 20 dígitos
    }

    // Aplica a formatação com substrings
    return substr($numero, 0, 7) . '-' . substr($numero, 7, 2) . '.' . substr($numero, 9, 4) . '.' .
           substr($numero, 13, 1) . '.' . substr($numero, 14, 2) . '.' . substr($numero, 16, 4);
}



/**
 * Verifica se o usuário está logado.
 *
 * @return bool
 */
function isUserLoggedIn(): bool {
    return isset($_SESSION['usuario']) && isset($_SESSION['usuario_id']);
}

/**
 * Sanitiza uma entrada de formulário.
 *
 * @param int $type Tipo da entrada (e.g., INPUT_POST).
 * @param string $field Nome do campo.
 * @return string Entrada sanitizada.
 */
function sanitizeInput($input, $type = 'string') {
    if ($input === null || $input === '') {
        return '';
    }

    switch ($type) {
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        case 'date':
            return date('Y-m-d', strtotime($input));
        case 'string':
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}


/**
 * Autentica o usuário no banco de dados.
 *
 * @param PDO $pdo Conexão com o banco de dados.
 * @param string $usuario Nome do usuário.
 * @param string $senha Senha do usuário.
 * @return array|null Retorna os dados do usuário ou null se inválido.
 */
function autenticarUsuario(PDO $pdo, string $usuario, string $senha): ?array {
    $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE Usuario = :usuario");
    $stmt->execute(['usuario' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return ($user && password_verify($senha, $user['Senha'])) ? $user : null;
}

/**
 * Registra uma tentativa de login falha e bloqueia após limite excedido.
 */
function registrarTentativaFalha(): void {
    $maxTentativas = 5;
    $tempoBloqueio = 900; // 15 minutos

    if (isset($_SESSION['tentativas']) && $_SESSION['tentativas'] >= $maxTentativas) {
        $tempoRestante = $_SESSION['bloqueio_tempo'] - time();
        if ($tempoRestante > 0) {
            die("Muitas tentativas de login. Tente novamente em " . ceil($tempoRestante / 60) . " minutos.");
        } else {
            unset($_SESSION['tentativas'], $_SESSION['bloqueio_tempo']);
        }
    }

    $_SESSION['tentativas'] = ($_SESSION['tentativas'] ?? 0) + 1;
    if ($_SESSION['tentativas'] >= $maxTentativas) {
        $_SESSION['bloqueio_tempo'] = time() + $tempoBloqueio;
    }
}


// Define a chave de criptografia (substitua por uma chave segura)
define('ENCRYPTION_KEY', 'YW5SeXAzVGhsNE9ndWRnN3RFa2hlVnhkUTNDM0pYcGQ=');

// Função para criptografar dados
function encrypt_password($password) {
    $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc')); // Vetor de inicialização
    $encrypted = openssl_encrypt($password, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Função para descriptografar dados
function decrypt_password($encrypted_password) {
    list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_password), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
}

function delete_senha($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM Senhas WHERE id = :id");
    return $stmt->execute(['id' => $id]);
}
