<?php
session_start();
include '../includes/header.php'; // Inclui a navbar e configurações globais
require_once '../includes/movimentacao_functions.php'; // Funções de movimentação


// Verificar se o usuário está logado
$usuarioID = $_SESSION['usuario_id'] ?? null;
if (!$usuarioID) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}

// Determinar se é adição ou edição
$movimentacaoID = $_GET['id'] ?? null; // ID da movimentação (nulo para adição)
$procedimentoID = $_GET['procedimento_id'] ?? null;
if (!$procedimentoID) {
    echo "<p class='text-center text-danger'>Procedimento não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

// Dados para edição, se necessário
$movimentacao = null;
if ($movimentacaoID) {
    $movimentacao = getMovimentacaoById($pdo, $movimentacaoID, $procedimentoID);
    if (!$movimentacao) {
        echo "<p class='text-center text-danger'>Movimentação não encontrada.</p>";
        include '../includes/footer.php';
        exit;
    }
}

// Dados globais para o formulário
$tiposMovimentacao = getTiposMovimentacao($pdo);
$responsaveis = getResponsaveis($pdo);
$situacoes = getSituacoesProcedimento($pdo);

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = processMovimentacao($pdo, $_POST, $movimentacaoID);
    if ($result['success']) {
        // Verificar o parâmetro "origem"
        if (isset($_GET['origem']) && $_GET['origem'] === 'relatorio') {
            header("Location: cotas.php"); // Redirecionar para cotas.php se origem for "relatorio"
        } else {
            header("Location: ver_procedimento.php?id=$procedimentoID");
        }
        exit;
    } else {
        $error = $result['error'];
    }
}


$query = "
    SELECT tp.ID AS TipoID
    FROM Procedimentos p
    INNER JOIN TiposProcedimento tp ON p.TipoID = tp.ID
    WHERE p.ID = :procedimento_id
";
$stmt = $pdo->prepare($query);
$stmt->execute(['procedimento_id' => $procedimentoID]);
$tipoProcedimentoID = $stmt->fetchColumn();

if (!$tipoProcedimentoID) {
    echo "<p class='text-center text-danger'>Tipo de Procedimento não encontrado.</p>";
    exit;
}

?>

<div class="container mt-5">
    <h1 class="text-center"><?= $movimentacaoID ? 'Editar' : 'Adicionar' ?> Movimentação</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="procedimento_id" value="<?= htmlspecialchars($procedimentoID) ?>">
        <input type="hidden" name="movimentacao_id" value="<?= htmlspecialchars($movimentacaoID) ?>">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tipo_id" class="form-label">Tipo de Movimentação</label>
                <select name="tipo_id" id="tipo_id" class="form-select" required>
                    <option value="">Selecione o Tipo</option>
                    <?php foreach ($tiposMovimentacao as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo['ID']) ?>" <?= $movimentacao && $tipo['ID'] == $movimentacao['TipoID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="situacao" class="form-label">Situação</label>
                <select name="situacao" id="situacao" class="form-select" required>
                    <option value="Em andamento" <?= $movimentacao && $movimentacao['Situacao'] === 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                    <option value="Finalizado" <?= $movimentacao && $movimentacao['Situacao'] === 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="assunto" class="form-label">Assunto</label>
            <input type="text" name="assunto" id="assunto" class="form-control" value="<?= htmlspecialchars($movimentacao['Assunto'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="detalhes" class="form-label">Detalhes</label>
            <textarea name="detalhes" id="detalhes" class="form-control" rows="4"><?= htmlspecialchars($movimentacao['Detalhes'] ?? '') ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="data_vencimento" class="form-label">Data de Vencimento</label>
                <?php
                $dataVencimento = isset($movimentacao['DataVencimento']) ? date('Y-m-d', strtotime($movimentacao['DataVencimento'])) : '';
                ?>
                <input type="date" name="data_vencimento" id="data_vencimento" class="form-control" value="<?= htmlspecialchars($dataVencimento) ?>" required>
            </div>

            <div class="col-md-6 mb-3" id="data_conclusao_container" style="display: none;">
                <label for="data_conclusao" class="form-label">Data de Conclusão</label>
                <input type="date" name="data_conclusao" id="data_conclusao" class="form-control" value="<?= htmlspecialchars($movimentacao['DataConclusao'] ?? '') ?>">
            </div>
        </div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="responsavel_id" class="form-label">Responsável</label>
        <select name="responsavel_id" id="responsavel_id" class="form-select" required>
            <option value="">Selecione o Responsável</option>
            <?php foreach ($responsaveis as $responsavel): ?>
                <option value="<?= htmlspecialchars($responsavel['ID']) ?>" <?= $movimentacao && $responsavel['ID'] == $movimentacao['ResponsavelID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($responsavel['Nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-6 mb-3" id="data_requisicao_container" style="display: none;">
        <label for="data_requisicao" class="form-label">Data da Requisição</label>
        <input type="date" name="data_requisicao" id="data_requisicao" class="form-control" value="<?= htmlspecialchars($movimentacao['DataRequisicao'] ?? '') ?>">
    </div>
</div>

        <div class="mb-3">
            <label for="documentos" class="form-label">Documentos</label>
            <input type="file" name="documentos[]" id="documentos" class="form-control" multiple>
        </div>

<fieldset id="campos-da-remessa" style="display: none; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <legend style="font-size: 1.2rem; font-weight: bold; width: auto;">Campos da Remessa</legend>
    <div class="row">
        <!-- Campo Número do Processo Judicial -->
        <div class="col-md-6 mb-3" id="numero_processo_container">
            <label for="numero_processo" class="form-label">Número do Processo</label>
            <input type="text" name="numero_processo" id="numero_processo" class="form-control">
        </div>

        <!-- Campo Situação do Procedimento -->
        <div class="col-md-6 mb-3" id="situacao_procedimento_container">
            <label for="situacao_procedimento" class="form-label">Situação do Procedimento</label>
            <select name="situacao_procedimento" id="situacao_procedimento" class="form-select">
                <option value="">Selecione a Situação</option>
                <!-- Opções serão preenchidas dinamicamente -->
            </select>
        </div>
    </div>
</fieldset>


        <fieldset id="oficio_container" style="display: none; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <legend style="font-size: 1.2rem; font-weight: bold; width: auto;">Informações do Ofício</legend>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="numero_oficio" class="form-label">Número do Ofício</label>
                    <input type="text" name="numero_oficio" id="numero_oficio" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="data_oficio" class="form-label">Data do Ofício</label>
                    <input type="date" name="data_oficio" id="data_oficio" class="form-control">
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
        
        <input type="hidden" id="procedimento_tipo_id" name="procedimento_tipo_id" value="<?= htmlspecialchars($tipoProcedimentoID) ?>">

    </form>
</div>
<script>

document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.getElementById('tipo_id');
    const situacaoSelect = document.getElementById('situacao');
    const assuntoInput = document.getElementById('assunto');
    const numeroProcessoContainer = document.getElementById('numero_processo_container');
    const oficioContainer = document.getElementById('oficio_container');
    const camposDaRemessa = document.getElementById('campos-da-remessa');
    const dataConclusaoContainer = document.getElementById('data_conclusao_container');
    const dataRequisicaoContainer = document.getElementById('data_requisicao_container');
    const situacaoProcedimentoContainer = document.getElementById('situacao_procedimento_container');
    const situacaoProcedimentoSelect = document.getElementById('situacao_procedimento');
    const procedimentoTipoID = document.getElementById('procedimento_tipo_id');

    async function fetchSituacoes(categoria) {
        const response = await fetch(`situacoes.php?categoria=${categoria}`);
        if (!response.ok) throw new Error('Erro ao buscar situações');
        return await response.json();
    }

    async function toggleFields() {
        const tipo = parseInt(tipoSelect.value);
        const situacao = situacaoSelect.value;

        // Lógica para preencher o campo "Assunto" apenas se estiver vazio
        if (tipo === 5 && !assuntoInput.value) {
            assuntoInput.value = "Remessa de IP";
        }

        // Lógica para exibir o campo "Número do Processo"
        if (tipo === 5 && situacao === 'Finalizado') {
            numeroProcessoContainer.style.display = 'block';
        } else {
            numeroProcessoContainer.style.display = 'none';
            document.getElementById('numero_processo').value = ''; // Limpa o campo
        }

        // Lógica para exibir o campo "Ofício"
        if (tipo === 9) {
            oficioContainer.style.display = 'block';
            document.querySelectorAll('#oficio_container input').forEach(field => {
                field.required = false;
            });
        } else {
            oficioContainer.style.display = 'none';
            document.querySelectorAll('#oficio_container input').forEach(field => {
                field.value = '';
            });
        }

        // Lógica para exibir o campo "Data de Conclusão"
        if (situacao === 'Finalizado') {
            dataConclusaoContainer.style.display = 'block';
        } else {
            dataConclusaoContainer.style.display = 'none';
        }

        // Lógica para exibir "Data de Requisição"
        if (tipo === 1) {
            dataRequisicaoContainer.style.display = 'block';
        } else {
            dataRequisicaoContainer.style.display = 'none';
            document.getElementById('data_requisicao').value = '';
        }

        // Lógica para exibir "Situação do Procedimento"
        if (tipo === 5 && situacao === 'Finalizado') {
            camposDaRemessa.style.display = 'block';

            // Determinar categoria com base no Tipo de Procedimento
            const categoria = procedimentoTipoID.value == 1 ? 'IP' : (procedimentoTipoID.value == 2 ? 'VPI' : null);

            if (!categoria) {
                situacaoProcedimentoSelect.innerHTML = '<option value="">Categoria inválida</option>';
                return;
            }

            try {
                const situacoes = await fetchSituacoes(categoria);
                situacaoProcedimentoSelect.innerHTML = '<option value="">Selecione a Situação</option>';
                situacoes.forEach(situacao => {
                    const option = document.createElement('option');
                    option.value = situacao.ID;
                    option.textContent = situacao.Nome;
                    situacaoProcedimentoSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Erro ao carregar situações:', error);
                situacaoProcedimentoSelect.innerHTML = '<option value="">Erro ao carregar situações</option>';
            }
        } else {
            camposDaRemessa.style.display = 'none';
            situacaoProcedimentoSelect.innerHTML = ''; // Limpa o select
        }
    }

    tipoSelect.addEventListener('change', toggleFields);
    situacaoSelect.addEventListener('change', toggleFields);

    // Inicializa a visibilidade correta ao carregar a página
    toggleFields();
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





<?php include '../includes/footer.php'; ?>
