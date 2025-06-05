<?php
session_start();
include '../includes/header.php'; // Inclui a navbar e configurações globais

// Verificar se o usuário está logado e obter o ID do usuário
$usuarioID = $_SESSION['usuario_id'] ?? null;
if (!$usuarioID) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para adicionar uma movimentação.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter o ID do procedimento a partir da URL
$procedimentoID = $_GET['procedimento_id'] ?? null;
if (!$procedimentoID) {
    echo "<p class='text-center text-danger'>Procedimento não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter as situações do procedimento para o dropdown
$querySituacoes = "SELECT ID, Nome FROM SituacoesProcedimento";
$stmtSituacoes = $pdo->query($querySituacoes);
$situacoes = $stmtSituacoes->fetchAll(PDO::FETCH_ASSOC);

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e obter os dados do formulário
    $tipoID = $_POST['tipo_id'] ?? null;
    $assunto = trim($_POST['assunto'] ?? '');
    $situacao = $_POST['situacao'] ?? null;
    $dataVencimento = $_POST['data_vencimento'] ?? null;
    $responsavelID = $_POST['responsavel_id'] ?? $usuarioID;
    $dataConclusao = empty($_POST['data_conclusao']) ? null : $_POST['data_conclusao'];
    $novaSituacaoProcedimento = $_POST['nova_situacao_procedimento'] ?? null;

    // Dados específicos para Ofícios
    $numeroOficio = $_POST['numero_oficio'] ?? null;
    $dataOficio = $_POST['data_oficio'] ?? null;
    $destino = $_POST['destino'] ?? null;
    $sei = $_POST['sei'] ?? null;

    try {
        // Inserir a nova movimentação no banco de dados
// Inserir a nova movimentação no banco de dados
$query = "
    INSERT INTO Movimentacoes (TipoID, Assunto, Detalhes, Situacao, DataVencimento, ProcedimentoID, UsuarioID, ResponsavelID, DataConclusao)
    VALUES (:tipo_id, :assunto, :detalhes, :situacao, :data_vencimento, :procedimento_id, :usuario_id, :responsavel_id, :data_conclusao)
";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'tipo_id' => $tipoID,
    'assunto' => $assunto,
    'detalhes' => trim($_POST['detalhes'] ?? ''),
    'situacao' => $situacao,
    'data_vencimento' => $dataVencimento,
    'procedimento_id' => $procedimentoID,
    'usuario_id' => $usuarioID,
    'responsavel_id' => $responsavelID,
    'data_conclusao' => $dataConclusao,
]);

// Obter o último ID da movimentação inserida
$movimentacaoID = $pdo->lastInsertId();

// Verifica se o processo existe antes de inserir
if ($tipoID == 5) {
    $numeroProcesso = trim($_POST['numero_processo'] ?? '');
    if (!empty($numeroProcesso)) {
        // Verificar se o processo já existe
        $queryVerificar = "
            SELECT COUNT(*) 
            FROM ProcessosJudiciais 
            WHERE Numero = :numero AND ProcedimentoID = :procedimento_id
        ";
        $stmtVerificar = $pdo->prepare($queryVerificar);
        $stmtVerificar->execute([
            'numero' => $numeroProcesso,
            'procedimento_id' => $procedimentoID,
        ]);
        $existe = $stmtVerificar->fetchColumn();

        // Inserir apenas se não existir
        if ($existe == 0) {
            $queryProcessos = "
                INSERT INTO ProcessosJudiciais (ProcedimentoID, Numero, Descricao)
                VALUES (:procedimento_id, :numero, :descricao)
            ";
            $stmtProcessos = $pdo->prepare($queryProcessos);
            $stmtProcessos->execute([
                'procedimento_id' => $procedimentoID,
                'numero' => $numeroProcesso,
                'descricao' => 'IP',
            ]);
        }
    }
}


// Se for TipoID = 9 (Ofícios), salvar também na tabela Oficios
if ($tipoID == 9) {
    $queryOficios = "
        INSERT INTO Oficios (NumeroOficio, Assunto, Destino, SEI, DataOficio, ProcedimentoID, MovimentacaoID, ResponsavelID)
        VALUES (:numero_oficio, :assunto, :destino, :sei, :data_oficio, :procedimento_id, :movimentacao_id, :responsavel_id)
    ";
    $stmtOficios = $pdo->prepare($queryOficios);
    $stmtOficios->execute([
        'numero_oficio' => $numeroOficio,
        'assunto' => $assunto,
        'destino' => $destino,
        'sei' => $sei,
        'data_oficio' => $dataOficio,
        'procedimento_id' => $procedimentoID,
        'movimentacao_id' => $movimentacaoID, // Relaciona ao ID da movimentação
        'responsavel_id' => $responsavelID,
    ]);
}


        // Atualizar situação do procedimento se necessário
        if (($tipoID == 5 || $tipoID == 7) && $situacao === 'Finalizado' && $novaSituacaoProcedimento) {
            $queryUpdateProcedimento = "
                UPDATE Procedimentos
                SET SituacaoID = :nova_situacao
                WHERE ID = :procedimento_id
            ";
            $stmtUpdateProcedimento = $pdo->prepare($queryUpdateProcedimento);
            $stmtUpdateProcedimento->execute([
                'nova_situacao' => $novaSituacaoProcedimento,
                'procedimento_id' => $procedimentoID,
            ]);
        }
        
        
if (!empty($_FILES['documentos']['name'][0])) {
    $uploadDir = '../uploads/movimentacoes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Cria o diretório, se não existir
    }

    foreach ($_FILES['documentos']['name'] as $key => $filename) {
        $fileTmpPath = $_FILES['documentos']['tmp_name'][$key];
        $safeFilename = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $filename);
        $destination = $uploadDir . uniqid() . '_' . $safeFilename;

        if (move_uploaded_file($fileTmpPath, $destination)) {
            // Salvar o arquivo no banco de dados
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
            $error = "Erro ao fazer o upload do arquivo: $filename";
            break;
        }
    }
}


        // Redirecionar para a página do procedimento
        header("Location: ver_procedimento.php?id=$procedimentoID");
        exit;
    } catch (PDOException $e) {
        $error = "Erro ao adicionar a movimentação: " . $e->getMessage();
    }
}


// Obter os tipos de movimentações para o dropdown
$queryTiposMovimentacao = "SELECT ID, Nome FROM TiposMovimentacao";
$stmtTiposMovimentacao = $pdo->query($queryTiposMovimentacao);
$tiposMovimentacao = $stmtTiposMovimentacao->fetchAll(PDO::FETCH_ASSOC);

// Obter os usuários responsáveis para o dropdown
$queryResponsaveis = "SELECT ID, Nome FROM Usuarios";
$stmtResponsaveis = $pdo->query($queryResponsaveis);
$responsaveis = $stmtResponsaveis->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h1 class="text-center">Adicionar Movimentação</h1>

    <!-- Exibir mensagens de erro -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tipo_id" class="form-label">Tipo de Movimentação</label>
                <select name="tipo_id" id="tipo_id" class="form-select" required>
                    <option value="">Selecione o Tipo</option>
                    <?php foreach ($tiposMovimentacao as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo['ID']) ?>" data-nome="<?= htmlspecialchars($tipo['Nome']) ?>">
                            <?= htmlspecialchars($tipo['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="situacao" class="form-label">Situação</label>
                <select name="situacao" id="situacao" class="form-select" required onchange="toggleDataConclusao(this.value)">
                    <option value="Em andamento">Em andamento</option>
                    <option value="Finalizado">Finalizado</option>
                </select>
            </div>
        </div>
<div class="mb-3">
    <label for="assunto" class="form-label">Assunto</label>
    <input type="text" name="assunto" id="assunto" class="form-control" placeholder="Descreva o assunto" required>
</div>

<div class="mb-3">
    <label for="detalhes" class="form-label">Detalhes</label>
    <textarea name="detalhes" id="detalhes" class="form-control" rows="4" placeholder="Descreva os detalhes adicionais da movimentação"></textarea>
</div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="data_vencimento" class="form-label">Data de Vencimento</label>
                <input type="date" name="data_vencimento" id="data_vencimento" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="responsavel_id" class="form-label">Responsável</label>
                <select name="responsavel_id" id="responsavel_id" class="form-select" required>
                    <option value="">Selecione o Responsável</option>
                    <?php foreach ($responsaveis as $responsavel): ?>
                        <option value="<?= htmlspecialchars($responsavel['ID']) ?>" <?= $responsavel['ID'] == $usuarioID ? 'selected' : '' ?>>
                            <?= htmlspecialchars($responsavel['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        

<div class="row" id="data-conclusao-nova-situacao-row" style="display: none; flex-wrap: wrap;">
    <!-- Campo Data de Conclusão -->
    <div class="col-md-6 mb-3">
        <label for="data_conclusao" class="form-label">Data de Conclusão</label>
        <input type="date" name="data_conclusao" id="data_conclusao" class="form-control">
    </div>
    
    <!-- Campo Nova Situação do Procedimento -->
    <div class="col-md-6 mb-3" id="nova-situacao-row" style="display: none;">
        <label for="nova_situacao_procedimento" class="form-label">Nova Situação do Procedimento</label>
        <select name="nova_situacao_procedimento" id="nova_situacao_procedimento" class="form-select">
            <option value="" disabled selected>Selecione a Nova Situação</option>
            <?php foreach ($situacoes as $situacao): ?>
                <option value="<?= htmlspecialchars($situacao['ID']) ?>">
                    <?= htmlspecialchars($situacao['Nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<fieldset id="processo-judicial-fields" style="display: none; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <legend style="font-size: 1.2rem; font-weight: bold; width: auto;">Informações do Processo Judicial</legend>
    
<div class="mb-3">
    <label for="numero_processo" class="form-label">Número do Processo</label>
    <input type="text" name="numero_processo" id="numero_processo" class="form-control">
</div>

</fieldset>


    <div class="mb-3">
        <label for="documentos" class="form-label">Documentos</label>
        <input type="file" name="documentos[]" id="documentos" class="form-control" multiple>
    </div>



<fieldset id="oficios-fields" style="display: none; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <legend style="font-size: 1.2rem; font-weight: bold; width: auto;">Informações do Ofício</legend>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="numero_oficio" class="form-label">Número do Ofício</label>
            <input type="text" name="numero_oficio" id="numero_oficio" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="data_oficio" class="form-label">Data do Ofício</label>
            <input type="date" name="data_oficio" id="data_oficio" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="destino" class="form-label">Destino</label>
            <input type="text" name="destino" id="destino" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label for="sei" class="form-label">SEI</label>
            <input type="text" name="sei" id="sei" class="form-control">
        </div>
    </div>
</fieldset>






        <div class="mb-4">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="ver_procedimento.php?id=<?= htmlspecialchars($procedimentoID) ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function toggleDataConclusao(situacao) {
    const dataConclusaoRow = document.getElementById('data-conclusao-nova-situacao-row');
    const novaSituacaoRow = document.getElementById('nova-situacao-row');
    const novaSituacaoSelect = document.getElementById('nova_situacao_procedimento');
    const tipoMovimentacao = document.getElementById('tipo_id').value;
    const processoFields = document.getElementById('processo-judicial-fields');

    // Exibir ou ocultar o campo Data de Conclusão e Nova Situação com base na situação
    if (situacao === 'Finalizado') {
        dataConclusaoRow.style.display = 'flex'; // Exibe o campo Data de Conclusão
        document.getElementById('data_conclusao').setAttribute('required', 'required');

        // Exibir o campo "Nova Situação" apenas para os tipos específicos
        if (tipoMovimentacao == 7 || tipoMovimentacao == 5) { // 7: Localização de Desaparecido, 5: Remessa de IP
            novaSituacaoRow.style.display = 'block';
            novaSituacaoSelect.setAttribute('required', 'required');

            // Buscar as situações relacionadas com base no tipo de procedimento
            let categoria = tipoMovimentacao == 7 ? 'Desaparecimento' : 'IP'; // Categoria baseada no tipo
            fetch(`situacoes.php?categoria=${categoria}`)
                .then(response => {
                    if (!response.ok) throw new Error('Erro ao buscar situações');
                    return response.json();
                })
                .then(situacoes => {
                    // Preencher o dropdown de Nova Situação com os dados retornados
                    novaSituacaoSelect.innerHTML = '<option value="" disabled selected>Selecione a Nova Situação</option>';
                    situacoes.forEach(situacao => {
                        const option = document.createElement('option');
                        option.value = situacao.ID;
                        option.textContent = situacao.Nome;
                        novaSituacaoSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar situações:', error);
                });
        } else {
            novaSituacaoRow.style.display = 'none'; // Oculta para outros tipos
            novaSituacaoSelect.removeAttribute('required');
        }

        // Exibe o campo "Número do Processo" se a situação for Finalizado
        if (tipoMovimentacao == 5) { // 5: Remessa de IP
            processoFields.style.display = 'block';
            document.getElementById('numero_processo').setAttribute('required', 'required');
        }

    } else {
        dataConclusaoRow.style.display = 'none'; // Oculta Data de Conclusão
        novaSituacaoRow.style.display = 'none'; // Oculta Nova Situação
        novaSituacaoSelect.removeAttribute('required');

        processoFields.style.display = 'none'; // Oculta Número do Processo
        document.getElementById('numero_processo').removeAttribute('required');

        document.getElementById('data_conclusao').removeAttribute('required');
    }
}

// Event Listeners para campos de seleção
document.getElementById('situacao').addEventListener('change', function () {
    toggleDataConclusao(this.value); // Atualiza os campos com base na situação selecionada
});

document.getElementById('tipo_id').addEventListener('change', function () {
    const situacao = document.getElementById('situacao').value;
    toggleDataConclusao(situacao); // Atualiza os campos com base no tipo selecionado
});



document.getElementById('tipo_id').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const tipoNome = selectedOption.getAttribute('data-nome');
    const assuntoInput = document.getElementById('assunto');

    if (tipoNome) {
        assuntoInput.value = tipoNome;
    } else {
        assuntoInput.value = '';
    }

    const situacao = document.getElementById('situacao').value;
    toggleDataConclusao(situacao);
});


document.getElementById('tipo_id').addEventListener('change', function () {
    const selectedValue = this.value;
    const oficiosFields = document.getElementById('oficios-fields');

    // Mostrar ou ocultar campos para "Ofícios" (TipoID = 9)
    if (selectedValue == 9) {
        oficiosFields.style.display = 'block';
    } else {
        oficiosFields.style.display = 'none';
        // Limpar os campos ao ocultá-los
        document.getElementById('numero_oficio').value = '';
        document.getElementById('data_oficio').value = '';
        document.getElementById('destino').value = '';
        document.getElementById('sei').value = '';
    }
});

document.getElementById('tipo_id').addEventListener('change', function () {
    const selectedValue = this.value;
    const processoFields = document.getElementById('processo-judicial-fields');

    // Mostrar ou ocultar campos para "Remessa de IP" (TipoID = 5)
    if (selectedValue == 5) {
        processoFields.style.display = 'block';
    } else {
        processoFields.style.display = 'none';
        // Limpar os campos ao ocultá-los
        document.getElementById('numero_processo').value = '';
    }
});



</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Seleciona o campo do Número do Processo
        const numeroProcesso = document.getElementById('numero_processo');

        // Cria a máscara usando a API do Inputmask
        const mask = new Inputmask('9999999-99.9999.9.99.9999', {
            placeholder: '_', // Define o placeholder
            clearIncomplete: true // Limpa valores incompletos
        });

        // Aplica a máscara ao campo
        mask.mask(numeroProcesso);

        console.log('Máscara aplicada com sucesso.');
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Selecionar o formulário
    const form = document.querySelector('form');

    form.addEventListener('submit', function (event) {
        // Prevenir envio inicial para validar os campos
        event.preventDefault();

        // Obter os valores selecionados
        const tipoMovimentacao = document.getElementById('tipo_id').value;
        const situacao = document.getElementById('situacao').value;

        // Condição para ativar confetes
        if (tipoMovimentacao == 5 && situacao === 'Finalizado') {
            // Função para exibir confetes
            const launchConfetti = () => {
                const duration = 3 * 1000; // Duração de 3 segundos
                const end = Date.now() + duration;

                (function frame() {
                    // Lançar confetes em diferentes direções
                    confetti({
                        particleCount: 5,
                        angle: 60,
                        spread: 55,
                        origin: { x: 0 }
                    });
                    confetti({
                        particleCount: 5,
                        angle: 120,
                        spread: 55,
                        origin: { x: 1 }
                    });

                    // Continuar até o final da duração
                    if (Date.now() < end) {
                        requestAnimationFrame(frame);
                    }
                })();
            };

            // Mostrar confetes
            launchConfetti();

            // Adicionar um pequeno atraso para enviar o formulário após a animação
            setTimeout(() => {
                form.submit();
            }, 3000); // Espera 3 segundos antes de enviar
        } else {
            // Submete o formulário normalmente se não for a condição
            form.submit();
        }
    });
});
</script>



