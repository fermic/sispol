<?php
session_start();
include '../includes/header.php'; // Inclui a navbar e configurações globais

// Verificar se o usuário está logado e obter o ID do usuário
$usuarioID = $_SESSION['usuario_id'] ?? null;
if (!$usuarioID) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para editar uma movimentação.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter o ID da movimentação e do procedimento a partir da URL
$movimentacaoID = $_GET['id'] ?? null;
$procedimentoID = $_GET['procedimento_id'] ?? null;

if (!$movimentacaoID || !$procedimentoID) {
    echo "<p class='text-center text-danger'>Movimentação ou Procedimento não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter os dados da movimentação existente
$queryMovimentacao = "
    SELECT TipoID, Assunto, Detalhes, Situacao, DATE_FORMAT(DataVencimento, '%Y-%m-%d') AS DataVencimento, 
           ResponsavelID, DATE_FORMAT(DataConclusao, '%Y-%m-%d') AS DataConclusao
    FROM Movimentacoes 
    WHERE ID = :id AND ProcedimentoID = :procedimento_id
";
$stmtMovimentacao = $pdo->prepare($queryMovimentacao);
$stmtMovimentacao->execute(['id' => $movimentacaoID, 'procedimento_id' => $procedimentoID]);
$movimentacao = $stmtMovimentacao->fetch(PDO::FETCH_ASSOC);


if (!$movimentacao) {
    echo "<p class='text-center text-danger'>Movimentação não encontrada.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter os tipos de movimentações para o dropdown
$queryTiposMovimentacao = "SELECT ID, Nome FROM TiposMovimentacao ORDER BY Nome ASC";
$stmtTiposMovimentacao = $pdo->prepare($queryTiposMovimentacao);
$stmtTiposMovimentacao->execute();
$tiposMovimentacao = $stmtTiposMovimentacao->fetchAll(PDO::FETCH_ASSOC);

// Obter os usuários responsáveis para o dropdown
$queryResponsaveis = "SELECT ID, Nome FROM Usuarios ORDER BY Nome ASC";
$stmtResponsaveis = $pdo->prepare($queryResponsaveis);
$stmtResponsaveis->execute();
$responsaveis = $stmtResponsaveis->fetchAll(PDO::FETCH_ASSOC);

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
    $dataConclusao = empty($_POST['data_conclusao']) ? null : $_POST['data_conclusao']; // Define como NULL se vazio
    $novaSituacaoProcedimento = $_POST['nova_situacao_procedimento'] ?? null;
    

    

    // Validação do campo de Nova Situação do Procedimento
    if ($movimentacao['TipoID'] == 5 && $situacao === 'Finalizado' && !$novaSituacaoProcedimento) {
        $error = "Por favor, selecione a nova situação do procedimento.";
    }
    
    // Validação simples
    if (!$tipoID || !$assunto || !$situacao || !$dataVencimento || !$responsavelID || ($situacao === 'Finalizado' && !$dataConclusao)) {
        $error = "Todos os campos obrigatórios devem ser preenchidos.";
    } else {
        try {
            // Atualizar os dados da movimentação no banco de dados
            $queryUpdate = "
                        UPDATE Movimentacoes
                        SET TipoID = :tipo_id,
                            Assunto = :assunto,
                            Detalhes = :detalhes, -- Adicionado campo Detalhes
                            Situacao = :situacao,
                            DataVencimento = :data_vencimento,
                            ResponsavelID = :responsavel_id,
                            DataConclusao = :data_conclusao
                        WHERE ID = :id AND ProcedimentoID = :procedimento_id
                    ";
                    $stmtUpdate = $pdo->prepare($queryUpdate);
                    $stmtUpdate->execute([
                        'tipo_id' => $tipoID,
                        'assunto' => $assunto,
                        'detalhes' => trim($_POST['detalhes'] ?? ''), // Captura e trata o campo Detalhes
                        'situacao' => $situacao,
                        'data_vencimento' => $dataVencimento,
                        'responsavel_id' => $responsavelID,
                        'data_conclusao' => $dataConclusao,
                        'id' => $movimentacaoID,
                        'procedimento_id' => $procedimentoID,
                    ]);


            // Atualizar a Situação do Procedimento, se necessário
            if ($novaSituacaoProcedimento) {
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
            
 
 
    // Processar Upload de Arquivos
    if (!empty($_FILES['documentos']['name'][0])) {
        $uploadDir = '../uploads/movimentacoes/'; // Certifique-se de criar esta pasta com permissões adequadas
        $permitidos = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ods'];
        $erros = [];

        foreach ($_FILES['documentos']['name'] as $index => $nomeOriginal) {
            $arquivoTemp = $_FILES['documentos']['tmp_name'][$index];
            $tamanhoArquivo = $_FILES['documentos']['size'][$index];
            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

            // Validar extensão e tamanho
            if (!in_array($extensao, $permitidos)) {
                $erros[] = "O arquivo $nomeOriginal tem uma extensão inválida.";
                continue;
            }
            if ($tamanhoArquivo > 5 * 1024 * 1024) {
                $erros[] = "O arquivo $nomeOriginal excede o tamanho máximo de 5MB.";
                continue;
            }

            // Gerar nome único para evitar conflitos
            $nomeUnico = uniqid() . '.' . $extensao;
            $destino = $uploadDir . $nomeUnico;

            // Mover o arquivo para o diretório de uploads
            if (move_uploaded_file($arquivoTemp, $destino)) {
                // Inserir referência no banco de dados
                $queryInsertDocumento = "
                    INSERT INTO DocumentosMovimentacao (MovimentacaoID, NomeArquivo, Caminho)
                    VALUES (:movimentacao_id, :nome_arquivo, :caminho)
                ";
                $stmtDocumento = $pdo->prepare($queryInsertDocumento);
                $stmtDocumento->execute([
                    'movimentacao_id' => $movimentacaoID,
                    'nome_arquivo' => $nomeOriginal,
                    'caminho' => $destino,
                ]);
            } else {
                $erros[] = "Erro ao fazer upload do arquivo $nomeOriginal.";
            }
        }

        // Exibir erros, se houver
        if (!empty($erros)) {
            foreach ($erros as $erro) {
                echo "<p class='text-danger'>$erro</p>";
            }
        }
    } 
            

// Processar apenas se TipoID for 9 (Ofício)
if ($tipoID == 9) {
    $numeroOficio = trim($_POST['numero_oficio'] ?? '');
    $dataOficio = $_POST['data_oficio'] ?? null;
    $destino = trim($_POST['destino'] ?? '');
    $sei = trim($_POST['sei'] ?? '');

    // Assunto do Ofício será o mesmo do Assunto da Movimentação
    $assuntoOficio = $assunto; // Herdando o assunto da movimentação

    // Atualizar o ofício existente
    $queryUpdateOficio = "
        UPDATE Oficios
        SET NumeroOficio = :numero_oficio,
            DataOficio = :data_oficio,
            Destino = :destino,
            SEI = :sei,
            Assunto = :assunto
        WHERE MovimentacaoID = :movimentacao_id
    ";
    $stmtUpdateOficio = $pdo->prepare($queryUpdateOficio);
    $stmtUpdateOficio->execute([
        'numero_oficio' => $numeroOficio,
        'data_oficio' => $dataOficio,
        'destino' => $destino,
        'sei' => $sei,
        'assunto' => $assuntoOficio,
        'movimentacao_id' => $movimentacaoID,
    ]);
} else {
    // Se o TipoID não for 9, remova os valores de ofício para evitar erros
    $queryDeleteOficio = "DELETE FROM Oficios WHERE MovimentacaoID = :movimentacao_id";
    $stmtDeleteOficio = $pdo->prepare($queryDeleteOficio);
    $stmtDeleteOficio->execute(['movimentacao_id' => $movimentacaoID]);
}





            // Redirecionar para a página do procedimento
            header("Location: ver_procedimento.php?id=$procedimentoID");
            exit;
        } catch (PDOException $e) {
            $error = "Erro ao editar a movimentação: " . $e->getMessage();
        }
    }
}

// Obter os dados do ofício relacionado à movimentação
$queryOficio = "SELECT * FROM Oficios WHERE MovimentacaoID = :movimentacao_id";
$stmtOficio = $pdo->prepare($queryOficio);
$stmtOficio->execute(['movimentacao_id' => $movimentacaoID]);
$oficio = $stmtOficio->fetch(PDO::FETCH_ASSOC);




$queryDocumentos = "SELECT ID, NomeArquivo, Caminho FROM DocumentosMovimentacao WHERE MovimentacaoID = :movimentacao_id";
$stmtDocumentos = $pdo->prepare($queryDocumentos);
$stmtDocumentos->execute(['movimentacao_id' => $movimentacaoID]);
$documentosExistentes = $stmtDocumentos->fetchAll(PDO::FETCH_ASSOC);



?>

<div class="container mt-5">
    
<?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

    <h1 class="text-center">Editar Movimentação</h1>

    <!-- Exibir mensagens de erro -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

   <form method="POST" enctype="multipart/form-data" class="mt-4">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tipo_id" class="form-label">Tipo de Movimentação</label>
                <select name="tipo_id" id="tipo_id" class="form-select" required>
                    <option value="">Selecione o Tipo</option>
                    <?php foreach ($tiposMovimentacao as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo['ID']) ?>" <?= $tipo['ID'] == $movimentacao['TipoID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="situacao" class="form-label">Situação</label>
                <select name="situacao" id="situacao" class="form-select" required onchange="toggleDataConclusao(this.value)">
                    <option value="Em andamento" <?= $movimentacao['Situacao'] === 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                    <option value="Finalizado" <?= $movimentacao['Situacao'] === 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
                </select>
            </div>
        </div>
<div class="mb-3">
    <label for="assunto" class="form-label">Assunto</label>
    <input type="text" name="assunto" id="assunto" class="form-control" value="<?= htmlspecialchars($movimentacao['Assunto']) ?>" required>
</div>

<div class="mb-3">
    <label for="detalhes" class="form-label">Detalhes</label>
    <textarea name="detalhes" id="detalhes" class="form-control" rows="4" placeholder="Descreva os detalhes adicionais"><?= htmlspecialchars($movimentacao['Detalhes'] ?? '') ?></textarea>
</div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="data_vencimento" class="form-label">Data de Vencimento</label>
                <input type="date" name="data_vencimento" id="data_vencimento" class="form-control" value="<?= htmlspecialchars($movimentacao['DataVencimento']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="responsavel_id" class="form-label">Responsável</label>
                <select name="responsavel_id" id="responsavel_id" class="form-select" required>
                    <option value="">Selecione o Responsável</option>
                    <?php foreach ($responsaveis as $responsavel): ?>
                        <option value="<?= htmlspecialchars($responsavel['ID']) ?>" <?= $responsavel['ID'] == $movimentacao['ResponsavelID'] ? 'selected' : '' ?>>
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
        <input 
            type="date" 
            name="data_conclusao" 
            id="data_conclusao" 
            class="form-control"
            value="<?= htmlspecialchars($movimentacao['DataConclusao'] ?? '') ?>"
        >
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

    <!-- Campo de Upload de Arquivos -->
    <fieldset style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <legend style="font-size: 1.2rem; font-weight: bold; width: auto;">Upload de Documentos</legend>
        <div class="mb-3">
            <label for="documentos" class="form-label">Selecione Arquivos</label>
            <input type="file" name="documentos[]" id="documentos" class="form-control" multiple>
            <small class="text-muted">Você pode enviar múltiplos arquivos (PDF, Word, Excel, ODS, até 5MB cada).</small>
        </div>
    </fieldset>
    
    <!-- Exibir Documentos Existentes -->
<?php if (!empty($documentosExistentes)): ?>
    <fieldset style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <legend style="font-size: 1.2rem; font-weight: bold; width: auto;">Documentos Existentes</legend>
        <ul class="list-group">
            <?php foreach ($documentosExistentes as $documento): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= htmlspecialchars($documento['NomeArquivo']) ?>
                    <a href="<?= htmlspecialchars($documento['Caminho']) ?>" class="btn btn-sm btn-primary" download>Download</a>
<a href="excluir_documento.php?id=<?= htmlspecialchars($documento['ID']) ?>&movimentacao_id=<?= htmlspecialchars($movimentacaoID) ?>&procedimento_id=<?= htmlspecialchars($procedimentoID) ?>" 
   class="btn btn-sm btn-danger"
   onclick="return confirm('Tem certeza que deseja excluir este documento?');">
   Excluir
</a>

                </li>
            <?php endforeach; ?>
        </ul>
    </fieldset>
<?php endif; ?>


<fieldset id="oficios-fields" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <legend style="font-size: 1.2rem; font-weight: bold; width: auto;">Informações do Ofício</legend>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="numero_oficio" class="form-label">Número do Ofício</label>
            <input type="text" name="numero_oficio" id="numero_oficio" class="form-control" 
                   value="<?= htmlspecialchars($oficio['NumeroOficio'] ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label for="data_oficio" class="form-label">Data do Ofício</label>
            <input type="date" name="data_oficio" id="data_oficio" class="form-control" 
                   value="<?= htmlspecialchars($oficio['DataOficio'] ?? date('Y-m-d')) ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="destino" class="form-label">Destino</label>
            <input type="text" name="destino" id="destino" class="form-control" 
                   value="<?= htmlspecialchars($oficio['Destino'] ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label for="sei" class="form-label">SEI</label>
            <input type="text" name="sei" id="sei" class="form-control" 
                   value="<?= htmlspecialchars($oficio['SEI'] ?? '') ?>">
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

    if (situacao === 'Finalizado') {
        dataConclusaoRow.style.display = 'flex'; // Exibe o campo Data de Conclusão
        document.getElementById('data_conclusao').setAttribute('required', 'required');
        
        if (tipoMovimentacao == 5) {
            novaSituacaoRow.style.display = 'block'; // Exibe o campo Nova Situação apenas para tipo 5
            novaSituacaoSelect.setAttribute('required', 'required');
        } else {
            novaSituacaoRow.style.display = 'none'; // Oculta o campo Nova Situação para outros tipos
            novaSituacaoSelect.removeAttribute('required');
        }
    } else {
        dataConclusaoRow.style.display = 'none'; // Oculta o campo Data de Conclusão
        novaSituacaoRow.style.display = 'none'; // Oculta o campo Nova Situação
        novaSituacaoSelect.removeAttribute('required');
        document.getElementById('data_conclusao').removeAttribute('required');
    }
}

// Adiciona a verificação ao carregar a página
document.addEventListener('DOMContentLoaded', function () {
    const situacaoAtual = document.getElementById('situacao').value;
    toggleDataConclusao(situacaoAtual); // Chama a função com a situação atual
});

// Adiciona os listeners aos campos para alterar dinamicamente
document.getElementById('situacao').addEventListener('change', function () {
    toggleDataConclusao(this.value);
});

document.getElementById('tipo_id').addEventListener('change', function () {
    toggleDataConclusao(document.getElementById('situacao').value);
});


// Adiciona os listeners aos campos de seleção
document.getElementById('situacao').addEventListener('change', function () {
    toggleDataConclusao(this.value);
});

document.getElementById('tipo_id').addEventListener('change', function () {
    toggleDataConclusao(document.getElementById('situacao').value);
});


document.addEventListener('DOMContentLoaded', function () {
    const tipoId = document.getElementById('tipo_id').value;
    const oficiosFields = document.getElementById('oficios-fields');

    // Mostrar ou ocultar os campos de Ofícios com base no tipo
    if (tipoId == 9) {
        oficiosFields.style.display = 'block';
    } else {
        oficiosFields.style.display = 'none';
    }
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


function toggleDataConclusao(situacao) {
    const dataConclusaoRow = document.getElementById('data-conclusao-nova-situacao-row');
    const novaSituacaoRow = document.getElementById('nova-situacao-row');
    const novaSituacaoSelect = document.getElementById('nova_situacao_procedimento');
    const tipoMovimentacao = document.getElementById('tipo_id').value;

    // Exibir ou ocultar o campo Data de Conclusão e Nova Situação com base na situação
    if (situacao === 'Finalizado') {
        dataConclusaoRow.style.display = 'flex'; // Exibe o campo Data de Conclusão
        document.getElementById('data_conclusao').setAttribute('required', 'required');

        // Exibir o campo "Nova Situação" apenas para os tipos específicos
        if (tipoMovimentacao == 7 || tipoMovimentacao == 5) { // 7: Localização de Desaparecido, 6: Remessa de IP
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
    } else {
        dataConclusaoRow.style.display = 'none'; // Oculta Data de Conclusão
        novaSituacaoRow.style.display = 'none'; // Oculta Nova Situação
        novaSituacaoSelect.removeAttribute('required');
        document.getElementById('data_conclusao').removeAttribute('required');
    }
}

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

