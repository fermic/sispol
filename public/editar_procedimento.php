<?php
include '../includes/header.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para editar um procedimento.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter o ID do procedimento
$procedimentoID = $_GET['id'] ?? null;

if (!$procedimentoID) {
    echo "<p class='text-center text-danger'>Procedimento não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

// Buscar dados do procedimento
$stmt = $pdo->prepare("SELECT * FROM Procedimentos WHERE ID = :ID");
$stmt->execute([':ID' => $procedimentoID]);
$procedimento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$procedimento) {
    echo "<p class='text-center text-danger'>Procedimento não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

// Carregar dados associados
$queryProcessos = "SELECT ID, Numero, Descricao FROM ProcessosJudiciais WHERE ProcedimentoID = :ID";
$queryRAIs = "SELECT ID, Numero, Descricao FROM RAIs WHERE ProcedimentoID = :ID";
$queryVitimas = "SELECT ID, Nome, Idade FROM Vitimas WHERE ProcedimentoID = :ID";
$queryCrimes = "SELECT ID, VitimaID, CrimeID, Modalidade FROM Vitimas_Crimes WHERE VitimaID IN (SELECT ID FROM Vitimas WHERE ProcedimentoID = :ID)";
$queryInvestigados = "SELECT * FROM Investigados WHERE ProcedimentoID = :ID";
$queryMeios = "SELECT MeioEmpregadoID FROM ProcedimentosMeiosEmpregados WHERE ProcedimentoID = :ID";

// Preparação das queries
$stmtProcessos = $pdo->prepare($queryProcessos);
$stmtRAIs = $pdo->prepare($queryRAIs);
$stmtVitimas = $pdo->prepare($queryVitimas);
$stmtCrimes = $pdo->prepare($queryCrimes);
$stmtInvestigados = $pdo->prepare($queryInvestigados);
$stmtMeios = $pdo->prepare($queryMeios);

// Execução das queries
$stmtProcessos->execute([':ID' => $procedimentoID]);
$stmtRAIs->execute([':ID' => $procedimentoID]);
$stmtVitimas->execute([':ID' => $procedimentoID]);
$stmtCrimes->execute([':ID' => $procedimentoID]);
$stmtInvestigados->execute([':ID' => $procedimentoID]);
$stmtMeios->execute([':ID' => $procedimentoID]);

// Captura dos dados
$processos = $stmtProcessos->fetchAll(PDO::FETCH_ASSOC);
$raisSalvos = $stmtRAIs->fetchAll(PDO::FETCH_ASSOC);
$vitimas = $stmtVitimas->fetchAll(PDO::FETCH_ASSOC);
$investigadosSalvos = $stmtInvestigados->fetchAll(PDO::FETCH_ASSOC);
$meiosEmpregados = $stmtMeios->fetchAll(PDO::FETCH_COLUMN);
$crimes = $stmtCrimes->fetchAll(PDO::FETCH_ASSOC);


// Mapear crimes por VitimaID
$vitimasCrimesMap = [];
foreach ($crimes as $crime) {
    $vitimasCrimesMap[$crime['VitimaID']][] = [
        'ID' => $crime['ID'],
        'CrimeID' => $crime['CrimeID'],
        'Modalidade' => $crime['Modalidade']
    ];
}

// Associar crimes às vítimas
$vitimasSalvas = [];
foreach ($vitimas as $vitima) {
    $vitima['Crimes'] = isset($vitimasCrimesMap[$vitima['ID']]) ? $vitimasCrimesMap[$vitima['ID']] : [];
    $vitimasSalvas[] = $vitima;
}



// Processamento do formulário POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $pdo->beginTransaction();

        // Atualizar Procedimento
        $stmt = $pdo->prepare("
            UPDATE Procedimentos SET 
            SituacaoID = :SituacaoID, OrigemID = :OrigemID, TipoID = :TipoID, NumeroProcedimento = :NumeroProcedimento, 
            DataFato = :DataFato, DataInstauracao = :DataInstauracao, MotivoAparente = :MotivoAparente, 
            EscrivaoID = :EscrivaoID, DelegadoID = :DelegadoID, DelegaciaID = :DelegaciaID
            WHERE ID = :ID
        ");
        $stmt->execute([
            ':SituacaoID' => $_POST['situacao_id'],
            ':OrigemID' => $_POST['origem_id'],
            ':TipoID' => $_POST['tipo_id'],
            ':NumeroProcedimento' => $_POST['numero_procedimento'],
            ':DataFato' => $_POST['data_fato'],
            ':DataInstauracao' => $_POST['data_instauracao'],
            ':MotivoAparente' => strtoupper(trim($_POST['motivo_aparente'])),
            ':EscrivaoID' => $_POST['escrivao_id'],
            ':DelegadoID' => $_POST['delegado_id'],
            ':DelegaciaID' => $_POST['delegacia_id'],
            ':ID' => $procedimentoID
        ]);

if (!empty($_POST['rais']) || !isset($_POST['rais'])) {
    atualizarRAIs($pdo, $procedimentoID, $_POST['rais'] ?? []);
}


        // Processar Meios Empregados
        if (isset($_POST['meios_empregados'])) {
            atualizarMeiosEmpregados($pdo, $procedimentoID, $_POST['meios_empregados']);
        }
        
if (!empty($_POST['processos']) || !isset($_POST['processos'])) {
    AtualizaProcessosJudiciais($pdo, $procedimentoID, $_POST['processos'] ?? []);
}




// Processar Vítimas e Crimes
if (isset($_POST['vitimas'])) {
    
    atualizarVitimasECrimes($pdo, $procedimentoID, $_POST['vitimas']);

    $idsExistentes = []; // IDs das vítimas existentes no formulário

    foreach ($_POST['vitimas'] as $vitima) {
        if (!empty($vitima['id'])) {
            // Atualizar vítima existente
            $stmt = $pdo->prepare("
                UPDATE Vitimas 
                SET Nome = :Nome, Idade = :Idade 
                WHERE ID = :ID AND ProcedimentoID = :ProcedimentoID
            ");
            $stmt->execute([
                ':Nome' => $vitima['nome'],
                ':Idade' => $vitima['idade'] ?? null,
                ':ID' => $vitima['id'],
                ':ProcedimentoID' => $procedimentoID,
            ]);
            $vitimaID = $vitima['id'];
        } else {
            // Inserir nova vítima
            $stmt = $pdo->prepare("
                INSERT INTO Vitimas (Nome, Idade, ProcedimentoID) 
                VALUES (:Nome, :Idade, :ProcedimentoID)
            ");
            $stmt->execute([
                ':Nome' => $vitima['nome'],
                ':Idade' => $vitima['idade'] ?? null,
                ':ProcedimentoID' => $procedimentoID,
            ]);
            $vitimaID = $pdo->lastInsertId();
        }

        $idsExistentes[] = $vitimaID;

        // --- Código ajustado: Processar crimes associados à vítima ---
if (!empty($vitima['crimes']) && is_array($vitima['crimes'])) {
    foreach ($vitima['crimes'] as $crime) {
        // Log para depurar os dados recebidos

        if (!empty($crime['id']) && isset($crime['modalidade'])) {
            $stmt = $pdo->prepare("
                INSERT INTO Vitimas_Crimes (VitimaID, CrimeID, Modalidade) 
                VALUES (:VitimaID, :CrimeID, :Modalidade)
            ");
            $stmt->execute([
                ':VitimaID' => $vitimaID,
                ':CrimeID' => $crime['id'],
                ':Modalidade' => ucfirst($crime['modalidade']),

            ]);
        }
    }
}

        // -----------------------------------------------------------
    }

    // Excluir vítimas removidas do formulário
    $stmt = $pdo->prepare("SELECT ID FROM Vitimas WHERE ProcedimentoID = :ProcedimentoID");
    $stmt->execute([':ProcedimentoID' => $procedimentoID]);
    $idsAtuais = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $idsParaRemover = array_diff($idsAtuais, $idsExistentes);
if (!empty($idsParaRemover)) {
    // Reinicia os índices do array e filtra apenas valores numéricos
    $idsParaRemover = array_values(array_filter($idsParaRemover, 'is_numeric'));

    // Gera placeholders baseados no número de IDs
    $placeholders = implode(',', array_fill(0, count($idsParaRemover), '?'));

    // Log para depuração
    error_log("IDs para Remover: " . print_r($idsParaRemover, true));
    error_log("Placeholders: $placeholders");

    try {
        // Excluir vítimas
        $stmt = $pdo->prepare("DELETE FROM Vitimas WHERE ID IN ($placeholders)");
        error_log("Query DELETE FROM Vitimas preparada com placeholders: $placeholders");
        $stmt->execute($idsParaRemover);
        error_log("Query Vitimas executada com sucesso.");

        // Excluir crimes associados às vítimas
        $stmt = $pdo->prepare("DELETE FROM Vitimas_Crimes WHERE VitimaID IN ($placeholders)");
        error_log("Query DELETE FROM Vitimas_Crimes preparada com placeholders: $placeholders");
        $stmt->execute($idsParaRemover);
        error_log("Query Vitimas_Crimes executada com sucesso.");
    } catch (PDOException $e) {
        error_log("Erro ao excluir registros: " . $e->getMessage());
        echo "Erro SQL: " . $e->getMessage(); // Opcional: Exibir o erro para depuração
        throw $e; // Re-lança o erro para rastrear a origem
    }
}

}


        // Processar Investigados
        if (isset($_POST['investigados'])) {
            atualizarInvestigados($pdo, $procedimentoID, $_POST['investigados']);
        }

        $pdo->commit();

        // Redirecionamento após sucesso
        header("Location: ver_procedimento.php?id=$procedimentoID");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erro ao atualizar procedimento: " . $e->getMessage());
        echo "<p class='text-danger text-center'>Erro ao atualizar: {$e->getMessage()}</p>";
    }
}


// Funções auxiliares
function atualizarRAIs($pdo, $procedimentoID, $rais) {
    // Obter IDs atuais no banco de dados
    $stmt = $pdo->prepare("SELECT ID FROM RAIs WHERE ProcedimentoID = :ProcedimentoID");
    $stmt->execute([':ProcedimentoID' => $procedimentoID]);
    $idsAtuais = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Obter IDs enviados no formulário
    $idsEnviados = array_filter(array_column($rais, 'id')); // IDs enviados no formulário

    // Determinar IDs para excluir (presentes no banco, mas não enviados no formulário)
    $idsParaExcluir = array_diff($idsAtuais, $idsEnviados);

    // Excluir RAIs que foram removidos do formulário
    if (!empty($idsParaExcluir)) {
        $placeholders = implode(',', array_fill(0, count($idsParaExcluir), '?'));
        $stmt = $pdo->prepare("DELETE FROM RAIs WHERE ID IN ($placeholders)");

        // Vincular os parâmetros dinamicamente
        foreach (array_values($idsParaExcluir) as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }

        // Executar a query de exclusão
        $stmt->execute();
        error_log("RAIs removidos: " . implode(', ', $idsParaExcluir)); // Log para depuração
    }

    // Atualizar ou inserir os RAIs enviados no formulário
    foreach ($rais as $rai) {
        if (!empty($rai['id'])) {
            // Atualizar RAI existente
            $stmt = $pdo->prepare("
                UPDATE RAIs
                SET Numero = :Numero, Descricao = :Descricao
                WHERE ID = :ID AND ProcedimentoID = :ProcedimentoID
            ");
            $stmt->execute([
                ':Numero' => $rai['numero'],
                ':Descricao' => $rai['descricao'],
                ':ID' => $rai['id'],
                ':ProcedimentoID' => $procedimentoID,
            ]);
        } else {
            // Inserir novo RAI
            $stmt = $pdo->prepare("
                INSERT INTO RAIs (Numero, Descricao, ProcedimentoID)
                VALUES (:Numero, :Descricao, :ProcedimentoID)
            ");
            $stmt->execute([
                ':Numero' => $rai['numero'],
                ':Descricao' => $rai['descricao'],
                ':ProcedimentoID' => $procedimentoID,
            ]);
        }
    }
}




function AtualizaProcessosJudiciais($pdo, $procedimentoID, $processos) {
    // Obter IDs atuais no banco de dados
    $stmt = $pdo->prepare("SELECT ID FROM ProcessosJudiciais WHERE ProcedimentoID = :ProcedimentoID");
    $stmt->execute([':ProcedimentoID' => $procedimentoID]);
    $idsAtuais = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Obter IDs enviados no formulário (array vazio no caso de remoção total)
    $idsEnviados = array_filter(array_column($processos, 'id')); // IDs enviados no formulário

    // Determinar IDs para excluir (presentes no banco, mas não enviados no formulário)
    $idsParaExcluir = array_diff($idsAtuais, $idsEnviados);

    // Excluir processos que foram removidos do formulário
    if (!empty($idsParaExcluir)) {
        $placeholders = implode(',', array_fill(0, count($idsParaExcluir), '?'));
        $stmt = $pdo->prepare("DELETE FROM ProcessosJudiciais WHERE ID IN ($placeholders)");

        // Vincular os parâmetros dinamicamente
        foreach (array_values($idsParaExcluir) as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }

        // Executar a query de exclusão
        $stmt->execute();
        error_log("Processos removidos: " . implode(', ', $idsParaExcluir)); // Log para depuração
    }

    // Atualizar ou inserir os processos enviados no formulário
    foreach ($processos as $processo) {
        if (!empty($processo['id'])) {
            // Atualizar processo existente
            $stmt = $pdo->prepare("
                UPDATE ProcessosJudiciais
                SET Numero = :Numero, Descricao = :Descricao
                WHERE ID = :ID AND ProcedimentoID = :ProcedimentoID
            ");
            $stmt->execute([
                ':Numero' => $processo['numero'],
                ':Descricao' => $processo['descricao'],
                ':ID' => $processo['id'],
                ':ProcedimentoID' => $procedimentoID,
            ]);
        } else {
            // Inserir novo processo
            $stmt = $pdo->prepare("
                INSERT INTO ProcessosJudiciais (Numero, Descricao, ProcedimentoID)
                VALUES (:Numero, :Descricao, :ProcedimentoID)
            ");
            $stmt->execute([
                ':Numero' => $processo['numero'],
                ':Descricao' => $processo['descricao'],
                ':ProcedimentoID' => $procedimentoID,
            ]);
        }
    }
}








function atualizarMeiosEmpregados($pdo, $procedimentoID, $meios) {
    // Buscar os IDs atuais no banco de dados
    $stmt = $pdo->prepare("SELECT MeioEmpregadoID FROM ProcedimentosMeiosEmpregados WHERE ProcedimentoID = :ProcedimentoID");
    $stmt->execute([':ProcedimentoID' => $procedimentoID]);
    $meiosExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN); // Array de IDs existentes

    $idsMantidos = []; // IDs que devem ser mantidos no banco

    foreach ($meios as $meio) {
        if (!empty($meio['id'])) {
            if (in_array($meio['id'], $meiosExistentes)) {
                // O meio empregado já existe, adiciona ao array de mantidos
                $idsMantidos[] = $meio['id'];
            } else {
                // Inserir novo meio empregado, pois não existe
                $stmt = $pdo->prepare("INSERT INTO ProcedimentosMeiosEmpregados (ProcedimentoID, MeioEmpregadoID) VALUES (:ProcedimentoID, :MeioEmpregadoID)");
                $stmt->execute([':ProcedimentoID' => $procedimentoID, ':MeioEmpregadoID' => $meio['id']]);
                $idsMantidos[] = $meio['id'];
            }
        }
    }

    // Excluir meios empregados que foram removidos do formulário
    $idsParaExcluir = array_diff($meiosExistentes, $idsMantidos);
    if (!empty($idsParaExcluir)) {
        $placeholders = implode(',', array_fill(0, count($idsParaExcluir), '?'));
        $stmt = $pdo->prepare("DELETE FROM ProcedimentosMeiosEmpregados WHERE ProcedimentoID = ? AND MeioEmpregadoID IN ($placeholders)");
        $stmt->execute(array_merge([$procedimentoID], $idsParaExcluir));
    }
}


function atualizarVitimasECrimes($pdo, $procedimentoID, $vitimas) {
    // Buscar IDs atuais das vítimas associadas ao procedimento
    $stmt = $pdo->prepare("SELECT ID FROM Vitimas WHERE ProcedimentoID = :ProcedimentoID");
    $stmt->execute([':ProcedimentoID' => $procedimentoID]);
    $idsExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $idsMantidos = []; // IDs das vítimas que serão mantidas

    foreach ($vitimas as $vitima) {
        // Verifica se é uma atualização ou inserção
        if (!empty($vitima['id']) && in_array($vitima['id'], $idsExistentes)) {
            // Atualizar vítima existente
            $stmt = $pdo->prepare("
                UPDATE Vitimas 
                SET Nome = :Nome, Idade = :Idade 
                WHERE ID = :ID
            ");
            $stmt->execute([
                ':Nome' => $vitima['nome'],
                ':Idade' => $vitima['idade'],
                ':ID' => $vitima['id']
            ]);
            $vitimaID = $vitima['id'];
        } else {
            // Inserir nova vítima
            $stmt = $pdo->prepare("
                INSERT INTO Vitimas (ProcedimentoID, Nome, Idade) 
                VALUES (:ProcedimentoID, :Nome, :Idade)
            ");
            $stmt->execute([
                ':ProcedimentoID' => $procedimentoID,
                ':Nome' => $vitima['nome'],
                ':Idade' => $vitima['idade']
            ]);
            $vitimaID = $pdo->lastInsertId();
        }

        $idsMantidos[] = $vitimaID;

        // Atualizar os crimes associados à vítima
        if (!empty($vitima['crimes'])) {
            atualizarCrimes($pdo, $vitimaID, $vitima['crimes']);
        }
    }

    // Excluir vítimas que foram removidas do formulário
    $idsParaRemover = array_diff($idsExistentes, $idsMantidos);
if (!empty($idsParaRemover)) {
    // Verificar IDs antes de excluir
    error_log("Excluindo IDs de vítimas: " . implode(',', $idsParaRemover));
    
    $stmt = $pdo->prepare("DELETE FROM Vitimas WHERE ID = :ID");
    $stmtCrime = $pdo->prepare("DELETE FROM Vitimas_Crimes WHERE VitimaID = :ID");

    foreach ($idsParaRemover as $id) {
        $stmt->execute([':ID' => $id]);
        $stmtCrime->execute([':ID' => $id]);
        error_log("ID excluído: $id");
    }
}

}

function atualizarCrimes($pdo, $vitimaID, $crimes) {
    // Excluir crimes antigos para esta vítima
    $stmt = $pdo->prepare("DELETE FROM Vitimas_Crimes WHERE VitimaID = :VitimaID");
    $stmt->execute([':VitimaID' => $vitimaID]);

    // Reinsere os crimes da vítima
    foreach ($crimes as $crime) {
        if (!empty($crime['crime_id']) && !empty($crime['modalidade'])) {
            // Insere o crime com modalidade no banco
            $stmt = $pdo->prepare("
                INSERT INTO Vitimas_Crimes (VitimaID, CrimeID, Modalidade) 
                VALUES (:VitimaID, :CrimeID, :Modalidade)
            ");
            $stmt->execute([
                ':VitimaID' => $vitimaID,
                ':CrimeID' => $crime['crime_id'],
                ':Modalidade' => ucfirst($crime['modalidade']), // Garantir a primeira letra maiúscula
            ]);
        }
    }
}







function atualizarInvestigados($pdo, $procedimentoID, $investigados) {
    // Buscar os IDs atuais no banco de dados
    $stmt = $pdo->prepare("SELECT ID, Nome FROM Investigados WHERE ProcedimentoID = :ProcedimentoID");
    $stmt->execute([':ProcedimentoID' => $procedimentoID]);
    $investigadosExistentes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ID => Nome

    $idsMantidos = []; // IDs dos investigados que devem ser mantidos

    foreach ($investigados as $investigado) {
        $nome = !empty($investigado['ignorado']) && $investigado['ignorado'] == '1' ? 'IGNORADO' : ($investigado['nome'] ?? '');

        if (!empty($investigado['id']) && isset($investigadosExistentes[$investigado['id']])) {
            // Atualizar investigado existente
            $stmt = $pdo->prepare("
                UPDATE Investigados 
                SET Nome = :Nome 
                WHERE ID = :ID AND ProcedimentoID = :ProcedimentoID
            ");
            $stmt->execute([
                ':Nome' => $nome,
                ':ID' => $investigado['id'],
                ':ProcedimentoID' => $procedimentoID,
            ]);
            $idsMantidos[] = $investigado['id'];
        } else {
            // Inserir novo investigado
            $stmt = $pdo->prepare("
                INSERT INTO Investigados (Nome, ProcedimentoID) 
                VALUES (:Nome, :ProcedimentoID)
            ");
            $stmt->execute([
                ':Nome' => $nome,
                ':ProcedimentoID' => $procedimentoID,
            ]);
            $idsMantidos[] = $pdo->lastInsertId();
        }
    }

    // Excluir investigados que foram removidos do formulário
    $idsParaExcluir = array_diff(array_keys($investigadosExistentes), $idsMantidos);
    if (!empty($idsParaExcluir)) {
        $placeholders = implode(',', array_fill(0, count($idsParaExcluir), '?'));
        $stmt = $pdo->prepare("DELETE FROM Investigados WHERE ID IN ($placeholders)");
        $stmt->execute($idsParaExcluir);
    }
}




?>



<div class="container mt-5">
    <h2 class="text-left text-secondary mb-4">Editar Procedimento</h2>
    <form method="post" id="form-cadastrar-procedimento" novalidate>
        <!-- Seção: Informações Gerais -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-light">
                <h5 class="mb-0">Informações Gerais</h5>
            </div>
            <div class="card-body bg-light">
                <div class="row g-3">
<?php

// Gerar o select para "Tipo do Procedimento" com valor salvo
echo gerar_select(
    'tipo_id', 
    'Tipo do Procedimento', 
    buscar_opcoes($pdo, 'TiposProcedimento', 'ID', 'Nome'), 
    $procedimento['TipoID'] ?? null, 
    'Selecione'
);

// Gerar o select para "Situação" com valor salvo

// Obter o Tipo do Procedimento selecionado
$tipoID = $procedimento['TipoID'] ?? null;

// Filtrar as situações com base no Tipo do Procedimento
$categoria = '';
if ($tipoID == 1) {
    $categoria = 'IP'; // Substitua com a categoria correspondente no banco
} 

if ($tipoID == 2) {
    $categoria = 'VPI'; // Substitua com a categoria correspondente no banco
}
if ($tipoID == 3) {
    $categoria = 'Desaparecimento'; // Substitua com a categoria correspondente no banco
}


$situacoes = [];
if ($categoria) {
    $stmtSituacoes = $pdo->prepare("SELECT ID, Nome FROM SituacoesProcedimento WHERE Categoria = :categoria");
    $stmtSituacoes->execute([':categoria' => $categoria]);
    $situacoes = $stmtSituacoes->fetchAll(PDO::FETCH_ASSOC);
}

// Gerar o select com as opções filtradas
echo '
<div class="col-md-6">
    <label for="situacao_id" class="form-label">Situação</label>
    <select id="situacao_id" name="situacao_id" class="form-select" required>
        <option value="">Selecione</option>';
foreach ($situacoes as $situacao) {
    $selected = ($procedimento['SituacaoID'] ?? '') == $situacao['ID'] ? 'selected' : '';
    echo "<option value=\"{$situacao['ID']}\" $selected>{$situacao['Nome']}</option>";
}
echo '
    </select>
</div>
';



// Gerar o select para "Origem" com valor salvo
echo gerar_select(
    'origem_id', 
    'Origem', 
    buscar_opcoes($pdo, 'OrigensProcedimentos', 'ID', 'Nome'), 
    $procedimento['OrigemID'] ?? null, 
    'Selecione'
);

?>

                    <div class="col-md-6">
                        <label for="numero_procedimento" class="form-label">Número do Procedimento</label>
                        <input type="text" id="numero_procedimento" name="numero_procedimento" 
                               class="form-control" value="<?= htmlspecialchars($procedimento['NumeroProcedimento'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="data_fato" class="form-label">Data do Fato</label>
                        <input type="date" id="data_fato" name="data_fato" 
                               class="form-control" value="<?= htmlspecialchars($procedimento['DataFato'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="data_instauracao" class="form-label">Data de Instauração</label>
                        <input type="date" id="data_instauracao" name="data_instauracao" 
                               class="form-control" value="<?= htmlspecialchars($procedimento['DataInstauracao'] ?? '') ?>">
                    </div>
                    <div class="col-md-12">
                        <label for="motivo_aparente" class="form-label">Motivo Aparente</label>
                        <textarea id="motivo_aparente" name="motivo_aparente" 
                                  class="form-control" rows="3"><?= htmlspecialchars($procedimento['MotivoAparente'] ?? '') ?></textarea>
                    </div>

                </div>
            </div>
        </div>
        

<!-- Seção: RAIs -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-dark text-light">
        <h5 class="mb-0">RAIs</h5>
    </div>
    <div class="card-body bg-light">
        <div id="rais-container">
            <?php if (!empty($raisSalvos)) : ?>
                <?php foreach ($raisSalvos as $index => $rai) : ?>
                    <div class="mb-3 border p-3 rounded bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="rai-<?= $index ?>" class="form-label">RAI</label>
                                <input type="text" id="rai-<?= $index ?>" name="rais[<?= $index ?>][numero]" 
                                       class="form-control" value="<?= htmlspecialchars($rai['Numero']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rai-descricao-<?= $index ?>" class="form-label">Descrição</label>
                                <input type="text" id="rai-descricao-<?= $index ?>" name="rais[<?= $index ?>][descricao]" 
                                       class="form-control" value="<?= htmlspecialchars($rai['Descricao']) ?>" required>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removerElemento(this)">Remover</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum RAI cadastrado.</p>
            <?php endif; ?>
        </div>
        <button type="button" class="btn btn-outline-dark btn-sm mt-3" onclick="adicionarRAI()">Adicionar RAI</button>
    </div>
</div>





<!-- Seção: Processos Judiciais -->
<!-- Seção: Processos Judiciais -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-dark text-light">
        <h5 class="mb-0">Processos Judiciais</h5>
    </div>
    <div class="card-body bg-light">
        <div id="processos-container">
            <?php if (!empty($processos)) : ?>
<?php foreach ($processos as $index => $processo) : ?>
    <div class="mb-3 border p-3 rounded bg-light">
        <!-- Campo ID Oculto -->
        <input type="hidden" name="processos[<?= $index ?>][id]" value="<?= htmlspecialchars($processo['ID'] ?? '') ?>">

        <div class="row">
            <!-- Número Judicial -->
            <div class="col-md-6">
                <label for="processo-<?= $index ?>" class="form-label">Número Judicial</label>
                <input type="text" id="processo-<?= $index ?>" 
                       name="processos[<?= $index ?>][numero]" 
                       class="form-control" 
                       value="<?= htmlspecialchars($processo['Numero'] ?? '') ?>" 
                       <?= !empty($processo['ID']) ? 'readonly' : '' ?> required>
            </div>

            <!-- Descrição -->
            <div class="col-md-6">
                <label for="processo-descricao-<?= $index ?>" class="form-label">Descrição</label>
                <input type="text" id="processo-descricao-<?= $index ?>" 
                       name="processos[<?= $index ?>][descricao]" 
                       class="form-control" 
                       value="<?= htmlspecialchars($processo['Descricao'] ?? '') ?>" required>
            </div>
        </div>

        <!-- Botão Remover -->
        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removerElemento(this)">Remover</button>
    </div>
<?php endforeach; ?>
           <?php endif; ?>
        </div>
        <button type="button" class="btn btn-outline-dark btn-sm mt-3" onclick="adicionarProcesso()">Adicionar Processo Judicial</button>
    </div>
</div>






<!-- Seção: Meios Empregados -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-dark text-light">
        <h5 class="mb-0">Meios Empregados</h5>
    </div>
    <div class="card-body bg-light">
        <div id="meios-empregados-container">
            <?php if (!empty($meiosEmpregados)) : ?>
                <?php foreach ($meiosEmpregados as $index => $meioID) : ?>
<div class="row mb-2 align-items-center">
    <input type="hidden" name="meios_empregados[<?= $index ?>][id_hidden]" value="<?= htmlspecialchars($meioID) ?>">
    <div class="col-md-10">
        <select name="meios_empregados[<?= $index ?>][id]" class="form-control">
            <option value="">Selecione um Meio Empregado</option>
            <?php foreach (buscar_opcoes($pdo, 'MeiosEmpregados', 'ID', 'Nome') as $opcao) : ?>
                <option value="<?= $opcao['id'] ?>" <?= $meioID == $opcao['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opcao['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-danger w-100" onclick="removerMeioEmpregado(this)">Remover</button>
    </div>
</div>

                <?php endforeach; ?>
            <?php else : ?>
                <p class="text-muted">Nenhum meio empregado adicionado.</p>
            <?php endif; ?>
        </div>
        <button type="button" class="btn btn-outline-dark btn-sm mt-3" onclick="adicionarMeioEmpregado()">Adicionar Meio Empregado</button>
        
    </div>
</div>



<!-- Seção: Vítimas -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-dark text-light">
        <h5 class="mb-0">Vítimas</h5>
    </div>
    <div class="card-body bg-light">
        <div id="vitimas-container">
            <?php if (!empty($vitimasSalvas)) : ?>
                <?php foreach ($vitimasSalvas as $index => $vitima) : ?>
                    <div class="mb-3 p-3 border rounded bg-light">
                        <!-- Nome e Idade -->
                        <div class="row">
    <input type="hidden" name="vitimas[<?= $index ?>][id]" value="<?= htmlspecialchars($vitima['ID'] ?? '') ?>">
    <div class="col-md-8">
        <label>Nome da Vítima</label>
        <input type="text" name="vitimas[<?= $index ?>][nome]" class="form-control" 
               value="<?= htmlspecialchars($vitima['Nome'] ?? '') ?>" required>
    </div>
    <div class="col-md-2">
        <label>Idade</label>
        <input type="number" name="vitimas[<?= $index ?>][idade]" class="form-control" 
               value="<?= htmlspecialchars($vitima['Idade'] ?? '') ?>" required>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="button" class="btn btn-danger w-100" onclick="removerVitima(this)">Remover</button>
    </div>
</div>


                        <!-- Crimes -->
<!-- Crimes -->
<h6 class="mt-3">Crimes</h6>
<div id="crimes-container-<?= $index ?>" class="mb-2">
    <?php if (!empty($vitima['Crimes'])) : ?>
        <?php foreach ($vitima['Crimes'] as $crimeIndex => $crime) : ?>
            <div class="row mb-2 crime-row">
                <!-- Dropdown de Crime -->
                <div class="col-md-6">
                    <select name="vitimas[<?= $index ?>][crimes][<?= $crimeIndex ?>][crime_id]" class="form-control" required>
                        <option value="">Selecione um Crime</option>
                        <?php foreach (buscar_opcoes($pdo, 'Crimes', 'ID', 'Nome') as $opcao) : ?>
                            <option value="<?= $opcao['id'] ?>"
                                <?= (isset($crime['CrimeID']) && $crime['CrimeID'] == $opcao['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($opcao['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Dropdown de Modalidade -->
                <div class="col-md-6">
<select name="vitimas[<?= $index ?>][crimes][<?= $crimeIndex ?>][modalidade]" class="form-control" required>
    <option value="Consumado" <?= (isset($crime['Modalidade']) && $crime['Modalidade'] == 'Consumado') ? 'selected' : '' ?>>Consumado</option>
    <option value="Tentado" <?= (isset($crime['Modalidade']) && $crime['Modalidade'] == 'Tentado') ? 'selected' : '' ?>>Tentado</option>
</select>


                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>



                        <!-- Botões de Adicionar -->
                        <div class="btn-group mt-3" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adicionarCrime(<?= $index ?>)">Adicionar Crime</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrarCrime">Cadastrar Novo Crime</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- Botão de Adicionar Vítima -->
        <button type="button" class="btn btn-outline-dark btn-sm mt-3" onclick="adicionarVitima()">Adicionar Vítima</button>
    </div>
</div>








<!-- Seção: Investigados -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-dark text-light">
        <h5 class="mb-0">Investigados</h5>
    </div>
    <div class="card-body bg-light">
        <div id="investigados-container">
            <?php 
            // Verificar se existem investigados salvos
            if (!empty($investigadosSalvos)) : ?>
                <?php foreach ($investigadosSalvos as $index => $investigado) : ?>
                    <div class="row mb-3 investigado-row">
                        <input type="hidden" name="investigados[<?= $index ?>][id]" value="<?= htmlspecialchars($investigado['ID']) ?>">
                        <div class="col-md-8">
                            <label for="investigado-nome-<?= $index ?>" class="form-label">Nome do Investigado</label>
                            <input type="text" id="investigado-nome-<?= $index ?>" 
                                   name="investigados[<?= $index ?>][nome]" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($investigado['Nome']) ?>" 
                                   <?= $investigado['Nome'] === 'Ignorado' ? 'disabled' : '' ?> required>
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input"
                                       id="ignorado-<?= $index ?>" 
                                       name="investigados[<?= $index ?>][ignorado]" 
                                       value="1" 
                                       <?= $investigado['Nome'] === 'Ignorado' ? 'checked' : '' ?> 
                                       onchange="toggleIgnorado(this, <?= $index ?>)">
                                <label for="ignorado-<?= $index ?>" class="form-check-label ms-2">Ignorado</label>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <button type="button" class="btn btn-danger w-100" onclick="removerInvestigado(this)">Remover</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <!-- Caso não haja investigados salvos, exibir um formulário vazio -->
                <div class="row mb-3 investigado-row">
                    <input type="hidden" name="investigados[0][id]" value="">
                    <div class="col-md-8">
                        <label for="investigado-nome-0" class="form-label">Nome do Investigado</label>
                        <input type="text" id="investigado-nome-0" name="investigados[0][nome]" 
                               class="form-control" value="" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="ignorado-0" 
                                   name="investigados[0][ignorado]" value="1" 
                                   onchange="toggleIgnorado(this, 0)">
                            <label for="ignorado-0" class="form-check-label ms-2">Ignorado</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <button type="button" class="btn btn-danger w-100" onclick="removerInvestigado(this)">Remover</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <button type="button" class="btn btn-outline-dark btn-sm mt-3" onclick="adicionarInvestigado()">Adicionar Investigado</button>
    </div>
</div>


        
        <!-- Seção: Responsáveis -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-light">
                <h5 class="mb-0">Responsáveis</h5>
            </div>
            <div class="card-body bg-light">
<div class="row g-3">
<?php
// Buscar valores existentes
$escrivaoSelecionado = $procedimento['EscrivaoID'] ?? null;
$delegadoSelecionado = $procedimento['DelegadoID'] ?? null;
$delegaciaSelecionada = $procedimento['DelegaciaID'] ?? null;

// Campo Escrivão
echo gerar_select(
    'escrivao_id',
    'Escrivão',
    buscar_opcoes($pdo, 'Usuarios u INNER JOIN Cargos c ON u.CargoID = c.ID', 'u.ID', 'u.Nome', "c.Nome = 'Escrivão de Polícia'"),
    $escrivaoSelecionado,
    'Selecione'
);

// Campo Delegado
echo gerar_select(
    'delegado_id',
    'Delegado',
    buscar_opcoes($pdo, 'Usuarios u INNER JOIN Cargos c ON u.CargoID = c.ID', 'u.ID', 'u.Nome', "c.Nome = 'Delegado'"),
    $delegadoSelecionado,
    'Selecione'
);

// Campo Delegacia
echo gerar_select(
    'delegacia_id',
    'Delegacia',
    buscar_opcoes($pdo, 'Delegacias', 'ID', 'Nome'),
    $delegaciaSelecionada,
    'Selecione'
);
?>
</div>
            </div>
        </div>

        <!-- Botão de Envio -->
        <div class="text-center mb-5">
            <button type="submit" class="btn btn-primary btn-lg">Salvar Procedimento</button>
        </div>
    </form>
</div>





<!-- Modal para cadastrar novo crime -->
<div class="modal fade" id="modalCadastrarCrime" tabindex="-1" aria-labelledby="modalCadastrarCrimeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCadastrarCrime">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastrarCrimeLabel">Cadastrar Novo Crime</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label>Nome do Crime</label>
                    <input type="text" id="novo-crime-nome" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovoCrime()">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Modal para cadastrar novo meio empregado -->
<div class="modal fade" id="modalCadastrarMeioEmpregado" tabindex="-1" aria-labelledby="modalCadastrarMeioEmpregadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCadastrarMeioEmpregado">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastrarMeioEmpregadoLabel">Cadastrar Novo Meio Empregado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="novo-meio-nome">Nome do Meio Empregado</label>
                    <input type="text" id="novo-meio-nome" class="form-control" required placeholder="Digite o nome do meio empregado">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovoMeioEmpregado()">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    const BASE_URL = "<?php echo BASE_URL; ?>";



function adicionarVitima() {
    const container = document.getElementById('vitimas-container');
    const vitimaIndex = container.children.length;

    const vitimaDiv = document.createElement('div');
    vitimaDiv.className = 'mb-3 p-3 border rounded bg-light';

    vitimaDiv.innerHTML = `
        <input type="hidden" name="vitimas[${vitimaIndex}][id]" value="">
        <div class="row">
            <div class="col-md-8">
                <label>Nome da Vítima</label>
                <input type="text" name="vitimas[${vitimaIndex}][nome]" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>Idade</label>
                <input type="number" name="vitimas[${vitimaIndex}][idade]" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger w-100" onclick="removerVitima(this)">Remover</button>
            </div>
        </div>

        <!-- Seção de Crimes -->
        <h6 class="mt-3">Crimes</h6>
        <div id="crimes-container-${vitimaIndex}" class="mb-2">
            <div class="row">
                <div class="col-md-6">
                    <select name="vitimas[${vitimaIndex}][crimes][0][id]" class="form-control">
                        <option value="">Selecione um Crime</option>
                        <?php foreach (buscar_opcoes($pdo, 'Crimes', 'ID', 'Nome') as $crime): ?>
                            <option value="<?= $crime['id'] ?>"><?= $crime['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <select name="vitimas[${vitimaIndex}][crimes][0][modalidade]" class="form-control">
                        <option value="consumado">Consumado</option>
                        <option value="Tentado">Tentado</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Botões para Adicionar e Cadastrar Novo Crime -->
        <div class="btn-group mt-3" role="group">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adicionarCrime(${vitimaIndex})">Adicionar Crime</button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrarCrime">Cadastrar Novo Crime</button>
        </div>
    `;

    container.appendChild(vitimaDiv);
}


// Função para remover uma vítima específica
function removerVitima(button) {
    const vitimaDiv = button.closest('.mb-3');
    vitimaDiv.remove();
}

function adicionarCrime(vitimaIndex) {
    const container = document.getElementById(`crimes-container-${vitimaIndex}`);
    const crimeIndex = container.children.length;

    const crimeDiv = document.createElement('div');
    crimeDiv.className = 'row mb-2 crime-row';

    crimeDiv.innerHTML = `
        <!-- Dropdown de Crime -->
        <div class="col-md-6">
            <label for="crime-${vitimaIndex}-${crimeIndex}">Crime</label>
            <select id="crime-${vitimaIndex}-${crimeIndex}" 
                    name="vitimas[${vitimaIndex}][crimes][${crimeIndex}][id]" 
                    class="form-control" required>
                <option value="">Selecione um Crime</option>
                <?php foreach (buscar_opcoes($pdo, 'Crimes', 'ID', 'Nome') as $crime): ?>
                    <option value="<?= $crime['id'] ?>"><?= htmlspecialchars($crime['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Dropdown de Modalidade -->
        <div class="col-md-6">
            <label for="modalidade-${vitimaIndex}-${crimeIndex}">Modalidade</label>
            <select id="modalidade-${vitimaIndex}-${crimeIndex}" 
                    name="vitimas[${vitimaIndex}][crimes][${crimeIndex}][modalidade]" 
                    class="form-control" required>
                <option value="consumado">Consumado</option>
                <option value="Tentado">Tentado</option>
            </select>
        </div>
    `;

    container.appendChild(crimeDiv);
}






function salvarNovoCrime() {
    const crimeNome = document.getElementById('novo-crime-nome').value;

    if (crimeNome) {
        fetch(`${BASE_URL}/public/cadastrar_crime.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nome: crimeNome }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(json => {
            if (json.success) {
                // Adiciona e seleciona o novo crime no dropdown
                adicionarNovoCrimeAoDropdown(json.crime);

                // Limpa o campo e fecha o modal
                document.getElementById('novo-crime-nome').value = '';
                const modalElement = document.getElementById('modalCadastrarCrime');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }

                alert(json.message); // Exibe mensagem de sucesso
            } else {
                throw new Error(json.message || 'Erro desconhecido no servidor.');
            }
        })
        .catch(err => {
            console.error('Erro ao cadastrar crime:', err);
            alert(`Erro ao cadastrar crime: ${err.message}`);
        });
    } else {
        alert('O campo "Nome do Crime" não pode estar vazio.');
    }
}


function removerCrime(button) {
    const crimeDiv = button.closest('.crime-row');
    crimeDiv.remove();
}


// Função para adicionar o novo crime ao dropdown
function adicionarNovoCrimeAoDropdown(crime) {
    // Localiza todos os selects de crimes existentes
    const crimeSelects = document.querySelectorAll('select[name^="vitimas"][name$="[id]"]');

    crimeSelects.forEach(select => {
        const option = document.createElement('option');
        option.value = crime.id;
        option.textContent = crime.nome;
        select.appendChild(option);
    });

    // Seleciona automaticamente o novo crime no último select adicionado
    const ultimoSelect = crimeSelects[crimeSelects.length - 1];
    if (ultimoSelect) {
        ultimoSelect.value = crime.id;
    }
}


// Função para adicionar investigado
function adicionarInvestigado() {
    const container = document.getElementById('investigados-container');
    const index = container.children.length;

    const div = document.createElement('div');
    div.className = 'row mb-3 investigado-row';

    div.innerHTML = `
        <input type="hidden" name="investigados[${index}][id]" value="">
        <div class="col-md-8">
            <label for="investigado-nome-${index}" class="form-label">Nome do Investigado</label>
            <input type="text" id="investigado-nome-${index}" name="investigados[${index}][nome]" 
                   class="form-control" value="" required>
        </div>
        <div class="col-md-2 d-flex align-items-center">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="ignorado-${index}" 
                       name="investigados[${index}][ignorado]" value="1" 
                       onchange="toggleIgnorado(this, ${index})">
                <label for="ignorado-${index}" class="form-check-label ms-2">Ignorado</label>
            </div>
        </div>
        <div class="col-md-2 d-flex align-items-center">
            <button type="button" class="btn btn-danger w-100" onclick="removerInvestigado(this)">Remover</button>
        </div>
    `;

    container.appendChild(div);
}

// Função para alternar entre "Ignorado" e entrada manual
function toggleIgnorado(checkbox, index) {
    const investigadoInput = document.querySelector(`input[name="investigados[${index}][nome]"]`);
    if (checkbox.checked) {
        investigadoInput.value = "Ignorado";
        investigadoInput.disabled = true;
    } else {
        investigadoInput.value = "";
        investigadoInput.disabled = false;
    }
}

// Função para remover um investigado específico
function removerInvestigado(button) {
    const investigadoDiv = button.closest('.mb-3');
    investigadoDiv.remove();
}

// Ignorado Checkbox
function toggleIgnorado(checkbox, index) {
    const investigadoInput = document.querySelector(`input[name="investigados[${index}][nome]"]`);
    if (checkbox.checked) {
        investigadoInput.value = "Ignorado";
        investigadoInput.disabled = true;
    } else {
        investigadoInput.value = "";
        investigadoInput.disabled = false;
    }
}

function adicionarMeioEmpregado() {
    const container = document.getElementById('meios-empregados-container');
    const meioIndex = container.children.length;

    const meioDiv = document.createElement('div');
    meioDiv.className = 'row mb-2 align-items-center';

 meioDiv.innerHTML = `
    <input type="hidden" name="meios_empregados[${meioIndex}][id_hidden]" value="">
    <div class="col-md-10">
        <select name="meios_empregados[${meioIndex}][id]" class="form-control">
            <option value="">Selecione um Meio Empregado</option>
            <?php foreach (buscar_opcoes($pdo, 'MeiosEmpregados', 'ID', 'Nome') as $meio): ?>
                <option value="<?= $meio['id'] ?>"><?= $meio['nome'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-danger w-100" onclick="removerMeioEmpregado(this)">Remover</button>
    </div>
`;


    container.appendChild(meioDiv);

    // Tornar o botão de "Cadastrar Novo Meio Empregado" visível
    const botaoCadastrar = document.getElementById('botaoCadastrarMeioEmpregado');
    botaoCadastrar.classList.remove('d-none');
}


function removerMeioEmpregado(button) {
    const meioDiv = button.closest('.row');
    meioDiv.remove();
}

function salvarNovoMeioEmpregado() {
    const meioNome = document.getElementById('novo-meio-nome').value;

    if (meioNome) {
        fetch(`${BASE_URL}/public/cadastrar_meio_empregado.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nome: meioNome }),
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }
                return response.json(); // Retorna a resposta JSON
            })
            .then(json => {
                if (json.success) {
                    // Atualiza o dropdown com o novo meio empregado
                    adicionarNovoMeioEmpregadoAoDropdown(json.meio);

                    // Resetar o campo
                    document.getElementById('novo-meio-nome').value = '';

                    // Fechar o modal usando Bootstrap 5 API
                    const modalElement = document.getElementById('modalCadastrarMeioEmpregado');
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide(); // Fecha o modal
                    }

                    alert(json.message); // Exibe mensagem de sucesso
                } else {
                    throw new Error(json.message || 'Erro desconhecido no servidor.');
                }
            })
            .catch(err => {
                console.error('Erro ao cadastrar meio empregado:', err);
                alert(`Erro ao cadastrar meio empregado: ${err.message}`);
            });
    } else {
        alert('O campo "Nome do Meio Empregado" não pode estar vazio.');
    }
}

// Função para adicionar o novo meio empregado ao dropdown
function adicionarNovoMeioEmpregadoAoDropdown(meio) {
    const selects = document.querySelectorAll('select[name^="meios_empregados"]');

    selects.forEach(select => {
        const option = document.createElement('option');
        option.value = meio.id;
        option.textContent = meio.nome;
        select.appendChild(option);
    });
}


function adicionarNovoMeioEmpregadoAoDropdown(meio) {
    const selects = document.querySelectorAll('select[name^="meios_empregados"]');

    selects.forEach(select => {
        const option = document.createElement('option');
        option.value = meio.id;
        option.textContent = meio.nome;
        select.appendChild(option);
    });

    // Adiciona o novo meio empregado selecionado no último select adicionado
    const ultimoSelect = selects[selects.length - 1];
    if (ultimoSelect) {
        ultimoSelect.value = meio.id;
    }
}


document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-cadastrar-procedimento');
    
        // Inicializar uma vítima ao carregar a página
    function inicializarVítima() {
        const container = document.getElementById('vitimas-container');
        if (container.children.length === 0) {
            adicionarVitima(); // Adiciona automaticamente uma vítima ao carregar
        }
    }

    // Inicializar um investigado ao carregar a página
    function inicializarInvestigado() {
        const container = document.getElementById('investigados-container');
        if (container.children.length === 0) {
            adicionarInvestigado(); // Adiciona automaticamente um investigado ao carregar
        }
    }
    
    // Inicializar um meio empregado ao carregar a página
    function inicializarMeioEmpregado() {
        const container = document.getElementById('meios-empregados-container');
        if (container.children.length === 0) {
            adicionarMeioEmpregado(); // Adiciona automaticamente um meio empregado ao carregar
        }
    }

    // Inicializar um processo judicial ao carregar a página
    function inicializarProcessoJudicial() {
        const container = document.getElementById('processos-container');
        if (container.children.length === 0) {
            adicionarProcesso(); // Adiciona automaticamente um processo judicial ao carregar
        }
    }

    // Inicializar um RAI ao carregar a página
    function inicializarRAI() {
        const container = document.getElementById('rais-container');
        if (container.children.length === 0) {
            adicionarRAI(); // Adiciona automaticamente um RAI ao carregar
        }
    }

// Função principal para validar o formulário
function validarFormulario(event) {
    let isValid = true;
    const mensagensErro = [];

    // Campos obrigatórios específicos
    const camposEspecificos = [
        { id: 'situacao_id', nome: 'Situação' },
        { id: 'origem_id', nome: 'Origem' },
        { id: 'tipo_id', nome: 'Tipo do Procedimento' },
        { id: 'numero_procedimento', nome: 'Número do Procedimento' },
        { id: 'data_fato', nome: 'Data do Fato' },
        { id: 'data_instauracao', nome: 'Data de Instauração' },
        { id: 'escrivao_id', nome: 'Escrivão' },
        { id: 'delegado_id', nome: 'Delegado' },
        { id: 'delegacia_id', nome: 'Delegacia' },
    ];

    // Validação de campos específicos
    camposEspecificos.forEach(campo => {
        const elemento = document.getElementById(campo.id);
        if (!elemento || !elemento.value.trim()) {
            isValid = false;
            mensagensErro.push(`O campo ${campo.nome} é obrigatório.`);
            elemento?.classList.add('is-invalid');
        } else {
            elemento.classList.remove('is-invalid');
        }
    });

    // Validação de RAIs
    const rais = document.querySelectorAll('#rais-container .mb-3');
    rais.forEach(rai => {
        const numero = rai.querySelector('input[name$="[numero]"]');
        const descricao = rai.querySelector('input[name$="[descricao]"]');

        if (numero && numero.value.trim() && (!descricao || !descricao.value.trim())) {
            isValid = false;
            mensagensErro.push('O campo Descrição é obrigatório para cada RAI preenchido.');
            descricao?.classList.add('is-invalid');
        } else {
            descricao?.classList.remove('is-invalid');
        }
    });

    // Validação de Processos Judiciais
    const processos = document.querySelectorAll('#processos-container .mb-3');
    processos.forEach(processo => {
        const numero = processo.querySelector('input[name$="[numero]"]');
        const descricao = processo.querySelector('input[name$="[descricao]"]');

        if (numero && numero.value.trim() && (!descricao || !descricao.value.trim())) {
            isValid = false;
            mensagensErro.push('O campo Descrição é obrigatório para cada Processo Judicial preenchido.');
            descricao?.classList.add('is-invalid');
        } else {
            descricao?.classList.remove('is-invalid');
        }
    });

    // Validação de Meios Empregados
    const meiosEmpregados = form.querySelectorAll('#meios-empregados-container select');
    if (meiosEmpregados.length === 0 || Array.from(meiosEmpregados).every(meio => !meio.value.trim())) {
        isValid = false;
        mensagensErro.push('É necessário adicionar pelo menos um Meio Empregado.');
    }

    // Validação de pelo menos uma vítima
    const vitimas = form.querySelectorAll('#vitimas-container .mb-3');
    if (vitimas.length === 0) {
        isValid = false;
        mensagensErro.push('É necessário adicionar pelo menos uma vítima.');
    } else {
        vitimas.forEach(vitima => {
            const nome = vitima.querySelector('input[name$="[nome]"]');
            const idade = vitima.querySelector('input[name$="[idade]"]');

            if (!nome || !nome.value.trim()) {
                isValid = false;
                mensagensErro.push('Uma vítima deve ter um nome válido.');
                nome?.classList.add('is-invalid');
            } else {
                nome.classList.remove('is-invalid');
            }

            if (!idade || !idade.value.trim() || isNaN(idade.value) || idade.value <= 0) {
                isValid = false;
                mensagensErro.push('Uma vítima deve ter uma idade válida.');
                idade?.classList.add('is-invalid');
            } else {
                idade.classList.remove('is-invalid');
            }
        });
    }

    // Validação de pelo menos um investigado
    const investigados = form.querySelectorAll('#investigados-container .row');
    if (investigados.length === 0) {
        isValid = false;
        mensagensErro.push('É necessário adicionar pelo menos um investigado.');
    } else {
        investigados.forEach(investigado => {
            const nome = investigado.querySelector('input[name$="[nome]"]');
            const ignorado = investigado.querySelector('input[name$="[ignorado]"]:checked');
            if (!ignorado && (!nome || !nome.value.trim())) {
                isValid = false;
                mensagensErro.push('Um investigado deve ter um nome válido ou ser marcado como ignorado.');
                nome?.classList.add('is-invalid');
            } else {
                nome?.classList.remove('is-invalid');
            }
        });
    }

    // Exibe mensagem de erro, se houver
    if (!isValid) {
        event?.preventDefault();
        alert(`Erro no formulário:\n\n${mensagensErro.join('\n')}`);
    }

    return isValid;
}




    // Adicionar eventos a novos elementos dinâmicos
    function adicionarValidacaoAoNovoElemento(elemento) {
        const inputs = elemento.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                input.classList.remove('is-invalid');
            });
        });
    }

    // Reescrever funções para adicionar elementos dinâmicos
    const adicionarVitimaOriginal = window.adicionarVitima;
    window.adicionarVitima = function (...args) {
        adicionarVitimaOriginal(...args);
        const vitimasContainer = document.getElementById('vitimas-container');
        const novaVitima = vitimasContainer.lastElementChild;
        adicionarValidacaoAoNovoElemento(novaVitima);
    };

    const adicionarInvestigadoOriginal = window.adicionarInvestigado;
    window.adicionarInvestigado = function (...args) {
        adicionarInvestigadoOriginal(...args);
        const investigadosContainer = document.getElementById('investigados-container');
        const novoInvestigado = investigadosContainer.lastElementChild;
        adicionarValidacaoAoNovoElemento(novoInvestigado);
    };

    const adicionarMeioEmpregadoOriginal = window.adicionarMeioEmpregado;
    window.adicionarMeioEmpregado = function (...args) {
        adicionarMeioEmpregadoOriginal(...args);
        const meiosContainer = document.getElementById('meios-empregados-container');
        const novoMeio = meiosContainer.lastElementChild;
        adicionarValidacaoAoNovoElemento(novoMeio);
    };

    // Adicionar eventos de validação para elementos iniciais
    const elementosIniciais = form.querySelectorAll('input, select, textarea');
    elementosIniciais.forEach(input => {
        input.addEventListener('change', () => {
            input.classList.remove('is-invalid');
        });
    });

    // Validação no envio do formulário
    form.addEventListener('submit', validarFormulario);
    
    // Inicializa os formulários de vítimas, investigados, meios empregados, processos judiciais e RAIs
    inicializarVítima();
    inicializarInvestigado();
    inicializarMeioEmpregado();
    inicializarProcessoJudicial();
    inicializarRAI();
});


    // Adicionar processo judicial dinamicamente
    function adicionarProcesso() {
        const container = document.getElementById('processos-container');
        const index = container.children.length;

        const div = document.createElement('div');
        div.className = 'mb-3 border p-3 rounded bg-light';

        div.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label for="processo-judicial-${index}" class="form-label">Número Judicial</label>
                    <input type="text" id="processo-judicial-${index}" name="processos[${index}][numero]" class="form-control numero-judicial" required>
                </div>
                <div class="col-md-6">
                    <label for="processo-descricao-${index}" class="form-label">Descrição</label>
                    <input type="text" id="processo-descricao-${index}" name="processos[${index}][descricao]" class="form-control" required>
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removerElemento(this)">Remover</button>
        `;

        container.appendChild(div);

        // Aplica a máscara ao campo recém-criado
        Inputmask("9999999-99.9999.9.99.9999").mask(`#processo-judicial-${index}`);
    }

    // Adicionar RAI dinamicamente
    function adicionarRAI() {
        const container = document.getElementById('rais-container');
        const index = container.children.length;

        const div = document.createElement('div');
        div.className = 'mb-3 border p-3 rounded bg-light';

        div.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label for="rai-${index}" class="form-label">RAI</label>
                    <input type="text" id="rai-${index}" name="rais[${index}][numero]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="rai-descricao-${index}" class="form-label">Descrição</label>
                    <input type="text" id="rai-descricao-${index}" name="rais[${index}][descricao]" class="form-control" required>
                </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removerElemento(this)">Remover</button>
        `;

        container.appendChild(div);
    }

    // Remover um elemento
    function removerElemento(button) {
        const div = button.closest('.mb-3');
        div.remove();
    }

    // Aplica a máscara ao carregar a página
    $(document).ready(function () {
        // Aplica a máscara ao campo Número Judicial
        $('#numero_judicial').inputmask('9999999-99.9999.9.99.9999', {
            placeholder: ' ', // Define um espaço em branco como placeholder
            clearIncomplete: true, // Limpa o valor se a entrada estiver incompleta
        });
    });
    
 
 
 document.getElementById('tipo_id').addEventListener('change', function () {
    const tipoId = this.value;
    const situacaoSelect = document.getElementById('situacao_id');
    situacaoSelect.innerHTML = '<option value="">Carregando...</option>';

    let categoria;
    if (tipoId === '1') { // IP
        categoria = 'IP';
    } else if (tipoId === '2') { // VPI
        categoria = 'VPI';
    } else if (tipoId === '3') { // Desaparecimento
        categoria = 'Desaparecimento';
    } else {
        situacaoSelect.innerHTML = '<option value="">Selecione um Tipo de Procedimento</option>';
        return;
    }

    fetch(`situacoes.php?categoria=${categoria}`)
        .then(response => response.json())
        .then(situacoes => {
            situacaoSelect.innerHTML = '<option value="">Selecione</option>';
            situacoes.forEach(situacao => {
                const option = document.createElement('option');
                option.value = situacao.ID;
                option.textContent = situacao.Nome;
                situacaoSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erro ao carregar situações:', error);
            situacaoSelect.innerHTML = '<option value="">Erro ao carregar</option>';
        });
});



document.addEventListener('input', function (event) {
    // Identificar o campo "Motivo Aparente"
    if (event.target.matches('textarea[name="motivo_aparente"]')) {
        const textarea = event.target;
        const cursorPos = textarea.selectionStart; // Captura a posição atual do cursor

        // Atualiza o valor para maiúsculas
        textarea.value = textarea.value.toUpperCase();

        // Restaura a posição do cursor
        textarea.setSelectionRange(cursorPos, cursorPos);
    }
});




  

</script>

<?php include '../includes/footer.php'; ?>
