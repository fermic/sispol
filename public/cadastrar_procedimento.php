<?php
include '../includes/header.php'; // Inclui configurações globais e funções reutilizáveis

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para cadastrar um procedimento.</p>";
    include '../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Insere o procedimento
 $stmt = $pdo->prepare("
    INSERT INTO Procedimentos 
    (SituacaoID, OrigemID, TipoID, NumeroProcedimento, DataFato, DataInstauracao, MotivoAparente, 
    EscrivaoID, DelegadoID, DelegaciaID, DataCriacao) 
    VALUES (:SituacaoID, :OrigemID, :TipoID, :NumeroProcedimento, :DataFato, :DataInstauracao, :MotivoAparente, 
    :EscrivaoID, :DelegadoID, :DelegaciaID, NOW())
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
]);


        $procedimentoID = $pdo->lastInsertId(); // Obtém o ID do procedimento recém-inserido
        
        // Capturar valor do campo "Houve Local de Crime?"
        $houveLocalCrime = $_POST['houve_local_crime'] ?? 'Nao';
        $dataFato = $_POST['data_fato']; // Assumindo que há um campo para a data do fato no formulário
        $usuarioID = $_SESSION['usuario_id']; // Assumindo que o ID do usuário logado está na sessão
        
        if ($houveLocalCrime === 'Sim') {
            // Inserir na tabela Movimentacoes
            $movQuery = "INSERT INTO Movimentacoes (TipoID, Assunto, Situacao, DataVencimento, DataConclusao, ProcedimentoID, UsuarioID, ResponsavelID)
                         VALUES (:tipoID, :assunto, :situacao, :dataVencimento, :dataConclusao, :procedimentoID, :usuarioID, :responsavelID)";
            $movStmt = $pdo->prepare($movQuery);
            $movStmt->execute([
                ':tipoID' => 8,
                ':assunto' => "Acompanhamento em Local de Crime",
                ':situacao' => "Finalizado",
                ':dataVencimento' => $dataFato,
                ':dataConclusao' => $dataFato,
                ':procedimentoID' => $procedimentoID,
                ':usuarioID' => $usuarioID,
                ':responsavelID' => $usuarioID,
            ]);
        }

        
        // Insere os processos judiciais
        if (!empty($_POST['processos']) && is_array($_POST['processos'])) {
            $stmtProcessos = $pdo->prepare("
                INSERT INTO ProcessosJudiciais (ProcedimentoID, Numero, Descricao) 
                VALUES (:ProcedimentoID, :Numero, :Descricao)
            ");
            foreach ($_POST['processos'] as $processo) {
                if (!empty($processo['numero']) && !empty($processo['descricao'])) {
                    $stmtProcessos->execute([
                        ':ProcedimentoID' => $procedimentoID,
                        ':Numero' => $processo['numero'],
                        ':Descricao' => $processo['descricao'],
                    ]);
                }
            }
        }

        // Insere os RAIs
        if (!empty($_POST['rais']) && is_array($_POST['rais'])) {
            $stmtRAIs = $pdo->prepare("
                INSERT INTO RAIs (ProcedimentoID, Numero, Descricao) 
                VALUES (:ProcedimentoID, :Numero, :Descricao)
            ");
            foreach ($_POST['rais'] as $rai) {
                if (!empty($rai['numero']) && !empty($rai['descricao'])) {
                    $stmtRAIs->execute([
                        ':ProcedimentoID' => $procedimentoID,
                        ':Numero' => $rai['numero'],
                        ':Descricao' => $rai['descricao'],
                    ]);
                }
            }
        }


        // Insere os meios empregados associados
        foreach ($_POST['meios_empregados'] as $meioEmpregado) {
            if (!empty($meioEmpregado['id'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO ProcedimentosMeiosEmpregados (ProcedimentoID, MeioEmpregadoID) 
                    VALUES (:ProcedimentoID, :MeioEmpregadoID)
                ");
                $stmt->execute([
                    ':ProcedimentoID' => $procedimentoID,
                    ':MeioEmpregadoID' => $meioEmpregado['id'],
                ]);
            }
        }

        // Verificar se existem vítimas
        if (empty($_POST['vitimas']) || !is_array($_POST['vitimas'])) {
            throw new Exception("É obrigatório cadastrar pelo menos uma vítima.");
        }

        // Insere as vítimas e seus crimes
        $vitimaInserida = false;
        foreach ($_POST['vitimas'] as $vitima) {
            if (!empty($vitima['nome'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO Vitimas (Nome, Idade, ProcedimentoID) 
                    VALUES (:Nome, :Idade, :ProcedimentoID)
                ");
                $stmt->execute([
                    ':Nome' => $vitima['nome'],
                    ':Idade' => $vitima['idade'],
                    ':ProcedimentoID' => $procedimentoID,
                ]);

                $vitimaID = $pdo->lastInsertId(); // Obtém o ID da vítima recém-inserida

                // Insere os vínculos com crimes
                if (!empty($vitima['crimes'])) {
                    foreach ($vitima['crimes'] as $crime) {
                        if (!empty($crime['id']) && !empty($crime['modalidade'])) {
                            $stmt = $pdo->prepare("
                                INSERT INTO Vitimas_Crimes (VitimaID, CrimeID, Modalidade) 
                                VALUES (:VitimaID, :CrimeID, :Modalidade)
                            ");
                            $stmt->execute([
                                ':VitimaID' => $vitimaID,
                                ':CrimeID' => $crime['id'],
                                ':Modalidade' => $crime['modalidade'],
                            ]);
                        }
                    }
                }
                $vitimaInserida = true;
            }
        }
        if (!$vitimaInserida) {
            throw new Exception("É obrigatório cadastrar pelo menos uma vítima.");
        }

// Verificar se existem investigados
if (empty($_POST['investigados']) || !is_array($_POST['investigados'])) {
    throw new Exception("É obrigatório cadastrar pelo menos um investigado.");
}

// Insere os investigados
$investigadoInserido = false;
foreach ($_POST['investigados'] as $investigado) {
    // Verifica se o campo "Ignorado" foi marcado
    if (!empty($investigado['nome']) || (isset($investigado['ignorado']) && $investigado['ignorado'] === '1')) {
                    $nomeInvestigado = isset($investigado['ignorado']) && $investigado['ignorado'] === '1' 
                        ? 'IGNORADO' 
                        : strtoupper(trim($investigado['nome']));
                    $stmt = $pdo->prepare("
                        INSERT INTO Investigados (Nome, ProcedimentoID) 
                        VALUES (:Nome, :ProcedimentoID)
                    ");
                    $stmt->execute([
                        ':Nome' => $nomeInvestigado,
                        ':ProcedimentoID' => $procedimentoID,
                    ]);

        $investigadoInserido = true;
    }
}
if (!$investigadoInserido) {
    throw new Exception("É obrigatório cadastrar pelo menos um investigado.");
}


        $pdo->commit();
        header("Location: ver_procedimento.php?id=$procedimentoID");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p class='text-danger text-center'>Erro ao cadastrar procedimento: {$e->getMessage()}</p>";
    }
}



?>
<div class="container mt-5">
    <h2 class="text-left text-secondary mb-4">Cadastrar Procedimento</h2>
    <form method="post" id="form-cadastrar-procedimento" novalidate>
        <!-- Seção: Informações Gerais -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-light">
                <h5 class="mb-0">Informações Gerais</h5>
            </div>
            <div class="card-body bg-light">
                <div class="row g-3">
<?php

// Gerar o select para "Tipo do Procedimento" com um identificador único
echo gerar_select(
    'tipo_id', 
    'Tipo do Procedimento', 
    buscar_opcoes($pdo, 'TiposProcedimento', 'ID', 'Nome'), 
    null, 
    'Selecione'
);

// O campo "Situação" será gerado vazio para que as opções sejam preenchidas dinamicamente via JavaScript
echo '
<div class="col-md-6">
    <label for="situacao_id" class="form-label">Situação</label>
    <select id="situacao_id" name="situacao_id" class="form-select" required>
        <option value="">Selecione</option>
    </select>
</div>
';

// Gerar o select para "Origem"
echo gerar_select(
    'origem_id', 
    'Origem', 
    buscar_opcoes($pdo, 'OrigensProcedimentos', 'ID', 'Nome'), 
    null, 
    'Selecione'
);

?>



                    <div class="col-md-6">
                        <label for="numero_procedimento" class="form-label">Número do Procedimento</label>
                        <input type="text" id="numero_procedimento" name="numero_procedimento" class="form-control" required>
                        <small id="numero-feedback" class="text-danger" style="display: none;"></small>
                    </div>


                    <div class="col-md-6">
                        <label for="data_fato" class="form-label">Data do Fato</label>
                        <input type="date" id="data_fato" name="data_fato" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="data_instauracao" class="form-label">Data de Instauração</label>
                        <input type="date" id="data_instauracao" name="data_instauracao" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label for="motivo_aparente" class="form-label">Motivo Aparente</label>
                        <textarea id="motivo_aparente" name="motivo_aparente" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row mb-3 mt-3">
                        <div class="col-md-6">
                            <label for="houve_local_crime" class="form-label">Houve Local de Crime?</label>
                            <div class="form-check">
                                <input type="radio" name="houve_local_crime" id="local_crime_sim" value="Sim" class="form-check-input">
                                <label for="local_crime_sim" class="form-check-label">Sim</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="houve_local_crime" id="local_crime_nao" value="Nao" class="form-check-input" checked>
                                <label for="local_crime_nao" class="form-check-label">Não</label>
                            </div>
                        </div>
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
        <div id="rais-container"></div>
        <button type="button" class="btn btn-outline-dark btn-sm mt-3" onclick="adicionarRAI()">Adicionar RAI</button>
    </div>
</div>


        
        
<!-- Seção: Processos Judiciais -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-dark text-light">
        <h5 class="mb-0">Processos Judiciais</h5>
    </div>
    <div class="card-body bg-light">
        <div id="processos-container"></div>
        <button type="button" class="btn btn-outline-dark btn-sm mt-3" onclick="adicionarProcesso()">Adicionar Processo Judicial</button>
    </div>
</div>






        <!-- Seção: Meios Empregados -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-light">
                <h5 class="mb-0">Meios Empregados</h5>
            </div>
<div class="card-body bg-light">
    <div id="meios-empregados-container"></div>
    <button type="button" class="btn btn-outline-dark btn-sm mt-3" onclick="adicionarMeioEmpregado()">Adicionar Meio Empregado</button>
    <button 
        type="button" 
        class="btn btn-outline-primary btn-sm mt-3 d-none" 
        id="botaoCadastrarMeioEmpregado" 
        data-bs-toggle="modal" 
        data-bs-target="#modalCadastrarMeioEmpregado"
    >
        Cadastrar Novo Meio Empregado
    </button>
</div>


        </div>

        <!-- Seção: Vítimas -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-light">
                <h5 class="mb-0">Vítimas</h5>
            </div>
            <div class="card-body bg-light">
                <div id="vitimas-container"></div>
                <button type="button" class="btn btn-outline-dark btn-sm mt-3" onclick="adicionarVitima()">Adicionar Vítima</button>
            </div>
        </div>

        <!-- Seção: Investigados -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-dark text-light">
                <h5 class="mb-0">Investigados</h5>
            </div>
            <div class="card-body bg-light">
                <div id="investigados-container"></div>
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
    // Obter o ID do usuário logado
    $usuarioLogadoID = $_SESSION['usuario_id'];

    // Buscar o DelegaciaID associado ao usuário logado
    $stmtDelegacia = $pdo->prepare("SELECT DelegaciaID FROM Usuarios WHERE ID = :usuarioID");
    $stmtDelegacia->execute([':usuarioID' => $usuarioLogadoID]);
    $delegaciaPadrao = $stmtDelegacia->fetchColumn();

    // Campo Escrivão
    echo gerar_select(
        'escrivao_id',
        'Escrivão',
        buscar_opcoes($pdo, 'Usuarios u INNER JOIN Cargos c ON u.CargoID = c.ID', 'u.ID', 'u.Nome', "c.Nome = 'Escrivão de Polícia'"),
        $usuarioLogadoID, // Define o usuário logado como padrão
        'Selecione'
    );

    // Campo Delegado com o ID padrão 5
    echo gerar_select(
        'delegado_id',
        'Delegado',
        buscar_opcoes($pdo, 'Usuarios u INNER JOIN Cargos c ON u.CargoID = c.ID', 'u.ID', 'u.Nome', "c.Nome = 'Delegado'"),
        5, // Define o delegado padrão com ID 5
        'Selecione'
    );

    // Campo Delegacia com a delegacia padrão do usuário logado
    echo gerar_select(
        'delegacia_id',
        'Delegacia',
        buscar_opcoes($pdo, 'Delegacias', 'ID', 'Nome'),
        $delegaciaPadrao, // Define a delegacia padrão do usuário logado
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
<h6 class="mt-3">Crimes</h6>
<div id="crimes-container-${vitimaIndex}" class="mb-2">
    <div class="row">
<div class="col-md-6 mb-2">
    <select name="vitimas[${vitimaIndex}][crimes][0][id]" class="form-control select2">
        <option value="">Selecione um Crime</option>
        <?php foreach (buscar_opcoes($pdo, 'Crimes', 'ID', 'Nome') as $crime): ?>
            <option value="<?= $crime['id'] ?>"><?= htmlspecialchars($crime['nome']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
        <div class="col-md-6">
            <select name="vitimas[${vitimaIndex}][crimes][0][modalidade]" class="form-control">
                <option value="consumado">Consumado</option>
                <option value="tentado">Tentado</option>
            </select>
        </div>
    </div>
</div>
<div class="btn-group mt-3" role="group">
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adicionarCrime(${vitimaIndex})">Adicionar Crime</button>
    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrarCrime">Cadastrar Novo Crime</button>
</div>

    `;

    container.appendChild(vitimaDiv);
    inicializarSelect2();
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
    crimeDiv.className = 'row mb-2';

    crimeDiv.innerHTML = `
        <div class="col-md-6">
            <select name="vitimas[${vitimaIndex}][crimes][${crimeIndex}][id]" class="form-control select2">
                <option value="">Selecione um Crime</option>
                <?php foreach (buscar_opcoes($pdo, 'Crimes', 'ID', 'Nome') as $crime): ?>
                    <option value="<?= $crime['id'] ?>"><?= htmlspecialchars($crime['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <select name="vitimas[${vitimaIndex}][crimes][${crimeIndex}][modalidade]" class="form-control">
                <option value="consumado">Consumado</option>
                <option value="tentado">Tentado</option>
            </select>
        </div>
    `;

    container.appendChild(crimeDiv);

    // Inicializa o Select2 no novo campo
    inicializarSelect2();
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


function adicionarInvestigado() {
    const container = document.getElementById('investigados-container');
    const index = container.children.length;

    const div = document.createElement('div');
    div.innerHTML = `
        <div class="row mb-3">
            <div class="col-md-8">
                <label>Nome do Investigado</label>
                <input type="text" name="investigados[${index}][nome]" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <input type="checkbox" id="ignorado-${index}" name="investigados[${index}][ignorado]" value="1" onchange="toggleIgnorado(this, ${index})">
                <label for="ignorado-${index}" class="ms-2">Ignorado</label>
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <button type="button" class="btn btn-danger w-100" onclick="removerInvestigado(this)">Remover</button>
            </div>
        </div>
    `;
    container.appendChild(div);
}

// Função para alternar entre "Ignorado" e entrada manual
function toggleIgnorado(checkbox, index) {
    const investigadoInput = document.querySelector(`input[name="investigados[${index}][nome]"]`);
    if (checkbox.checked) {
        investigadoInput.value = "IGNORADO";
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
        investigadoInput.value = "IGNORADO";
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
            const crimes = vitima.querySelectorAll('select[name^="vitimas"][name$="[id]"]');

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

            // Validação de pelo menos um crime selecionado
            if (crimes.length === 0 || Array.from(crimes).every(crime => !crime.value.trim())) {
                isValid = false;
                mensagensErro.push('Cada vítima deve ter pelo menos um crime associado.');
                crimes.forEach(crime => crime.classList.add('is-invalid'));
            } else {
                crimes.forEach(crime => crime.classList.remove('is-invalid'));
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



function inicializarSelect2() {
    $('.select2').select2({
        placeholder: "Selecione um Crime",
        allowClear: true, // Permite limpar a seleção
        width: '100%', // Ajusta a largura
    });
}

// Chame a função após adicionar o elemento
document.addEventListener('DOMContentLoaded', () => {
    inicializarSelect2();
});



document.addEventListener('DOMContentLoaded', function () {
    const numeroProcedimentoField = document.getElementById('numero_procedimento');
    const numeroFeedback = document.createElement('small');
    numeroFeedback.id = 'numero-feedback';
    numeroFeedback.className = 'text-danger';
    numeroProcedimentoField.parentNode.appendChild(numeroFeedback);

    // Verificar "Número do Procedimento" ao perder o foco
    numeroProcedimentoField.addEventListener('blur', function () {
        const numeroValue = numeroProcedimentoField.value.trim();

        if (numeroValue === '') {
            numeroFeedback.textContent = '';
            return;
        }

        // Fazer a requisição AJAX
        fetch('verifica_procedimento.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ numero_procedimento: numeroValue }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.exists) {
                    numeroFeedback.textContent = 'Este número de procedimento já está cadastrado.';
                    if (!confirm('Este número de procedimento já está cadastrado. Deseja continuar?')) {
                        numeroProcedimentoField.value = '';
                        numeroProcedimentoField.focus();
                    }
                } else {
                    numeroFeedback.textContent = '';
                }
            })
            .catch((error) => {
                console.error('Erro na verificação do número do procedimento:', error);
                numeroFeedback.textContent = 'Erro ao verificar o número do procedimento.';
            });
    });
});

</script>





<?php include '../includes/footer.php'; ?>
