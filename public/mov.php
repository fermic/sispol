<?php
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

<!-- Adicione a biblioteca confetti.js aqui se não estiver no header.php -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-exchange-alt me-2"></i>
                <?= $movimentacaoID ? 'Editar' : 'Adicionar' ?> Movimentação
            </h4>
            <a href="ver_procedimento.php?id=<?= htmlspecialchars($procedimentoID) ?>" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>

        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="procedimento_id" value="<?= htmlspecialchars($procedimentoID) ?>">
                <input type="hidden" name="movimentacao_id" value="<?= htmlspecialchars($movimentacaoID) ?>">

                <!-- Tipo e Situação -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="tipo_id" class="form-label">
                            <i class="fas fa-tag me-1"></i>Tipo de Movimentação
                        </label>
                        <select name="tipo_id" id="tipo_id" class="form-select" required>
                            <option value="">Selecione o Tipo</option>
                            <?php foreach ($tiposMovimentacao as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo['ID']) ?>" 
                                    <?= $movimentacao && $tipo['ID'] == $movimentacao['TipoID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo['Nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione o tipo de movimentação.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="situacao" class="form-label">
                            <i class="fas fa-info-circle me-1"></i>Situação
                        </label>
                        <select name="situacao" id="situacao" class="form-select" required>
                            <option value="Em andamento" <?= $movimentacao && $movimentacao['Situacao'] === 'Em andamento' ? 'selected' : '' ?>>
                                <i class="fas fa-clock"></i> Em andamento
                            </option>
                            <option value="Finalizado" <?= $movimentacao && $movimentacao['Situacao'] === 'Finalizado' ? 'selected' : '' ?>>
                                <i class="fas fa-check-circle"></i> Finalizado
                            </option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione a situação.</div>
                    </div>
                </div>

                <!-- Assunto e Detalhes -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label for="assunto" class="form-label">
                            <i class="fas fa-heading me-1"></i>Assunto
                        </label>
                        <input type="text" name="assunto" id="assunto" class="form-control" 
                               value="<?= htmlspecialchars($movimentacao['Assunto'] ?? '') ?>" required>
                        <div class="invalid-feedback">Por favor, informe o assunto.</div>
                    </div>

                    <div class="col-12">
                        <label for="detalhes" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Detalhes
                        </label>
                        <textarea name="detalhes" id="detalhes" class="form-control" rows="4"
                                  placeholder="Digite os detalhes da movimentação..."><?= htmlspecialchars($movimentacao['Detalhes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Datas -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="data_vencimento" class="form-label">
                            <i class="fas fa-calendar-alt me-1"></i>Data de Vencimento
                        </label>
                        <?php
                        $dataVencimento = isset($movimentacao['DataVencimento']) ? date('Y-m-d', strtotime($movimentacao['DataVencimento'])) : '';
                        ?>
                        <input type="date" name="data_vencimento" id="data_vencimento" 
                               class="form-control" value="<?= htmlspecialchars($dataVencimento) ?>" required>
                        <div class="invalid-feedback">Por favor, informe a data de vencimento.</div>
                    </div>

                    <div class="col-md-6" id="data_conclusao_container" style="display: none;">
                        <label for="data_conclusao" class="form-label">
                            <i class="fas fa-calendar-check me-1"></i>Data de Conclusão
                        </label>
                        <input type="date" name="data_conclusao" id="data_conclusao" 
                               class="form-control" value="<?= htmlspecialchars($movimentacao['DataConclusao'] ?? '') ?>">
                    </div>
                </div>

                <!-- Responsável e Data Requisição -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="responsavel_id" class="form-label">
                            <i class="fas fa-user me-1"></i>Responsável
                        </label>
                        <select name="responsavel_id" id="responsavel_id" class="form-select" required>
                            <option value="">Selecione o Responsável</option>
                            <?php foreach ($responsaveis as $responsavel): ?>
                                <option value="<?= htmlspecialchars($responsavel['ID']) ?>" 
                                    <?= $movimentacao && $responsavel['ID'] == $movimentacao['ResponsavelID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($responsavel['Nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione o responsável.</div>
                    </div>

                    <div class="col-md-6" id="data_requisicao_container" style="display: none;">
                        <label for="data_requisicao" class="form-label">
                            <i class="fas fa-calendar-plus me-1"></i>Data da Requisição
                        </label>
                        <input type="date" name="data_requisicao" id="data_requisicao" 
                               class="form-control" value="<?= htmlspecialchars($movimentacao['DataRequisicao'] ?? '') ?>">
                    </div>
                </div>

                <!-- Documentos -->
                <div class="mb-4">
                    <label for="documentos" class="form-label">
                        <i class="fas fa-file-upload me-1"></i>Documentos
                    </label>
                    <input type="file" name="documentos[]" id="documentos" class="form-control" multiple
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <div class="form-text">Você pode selecionar múltiplos arquivos.</div>
                </div>

                <!-- Campos da Remessa -->
                <div class="card mb-4" id="campos-da-remessa" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>Campos da Remessa
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6" id="numero_processo_container">
                                <label for="numero_processo" class="form-label">Número do Processo</label>
                                <input type="text" name="numero_processo" id="numero_processo" class="form-control">
                            </div>

                            <div class="col-md-6" id="situacao_procedimento_container">
                                <label for="situacao_procedimento" class="form-label">Situação do Procedimento</label>
                                <select name="situacao_procedimento" id="situacao_procedimento" class="form-select">
                                    <option value="">Selecione a Situação</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Campos do Ofício -->
                <div class="card mb-4" id="oficio_container" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-envelope me-2"></i>Informações do Ofício
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="numero_oficio" class="form-label">Número do Ofício</label>
                                <input type="text" name="numero_oficio" id="numero_oficio" class="form-control" 
                                       value="<?= htmlspecialchars($movimentacao['NumeroOficio'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="data_oficio" class="form-label">Data do Ofício</label>
                                <input type="date" name="data_oficio" id="data_oficio" class="form-control" 
                                       value="<?= htmlspecialchars($movimentacao['DataOficio'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="destino" class="form-label">Destino</label>
                                <input type="text" name="destino" id="destino" class="form-control" 
                                       value="<?= htmlspecialchars($movimentacao['Destino'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="sei" class="form-label">SEI</label>
                                <input type="text" name="sei" id="sei" class="form-control" 
                                       value="<?= htmlspecialchars($movimentacao['SEI'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="ver_procedimento.php?id=<?= htmlspecialchars($procedimentoID) ?>" 
                       class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Salvar
                    </button>
                </div>

                <input type="hidden" id="procedimento_tipo_id" name="procedimento_tipo_id" 
                       value="<?= htmlspecialchars($tipoProcedimentoID) ?>">
            </form>
        </div>
    </div>
</div>

<!-- Adicione os estilos personalizados -->
<style>
.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.form-control, .form-select {
    border-radius: 5px;
    border: 1px solid #ced4da;
    padding: 0.5rem 0.75rem;
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.btn {
    border-radius: 5px;
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #5c636a;
    border-color: #565e64;
}

.alert {
    border-radius: 5px;
    border: none;
}

.invalid-feedback {
    font-size: 0.875rem;
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
}

/* Animações suaves */
.card, .form-control, .form-select, .btn {
    transition: all 0.3s ease;
}

/* Estilo para campos desabilitados */
.form-control:disabled, .form-select:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

/* Estilo para campos obrigatórios */
.form-label.required::after {
    content: " *";
    color: #dc3545;
}

/* Estilo para tooltips */
.tooltip {
    font-size: 0.875rem;
}

/* Estilo para o container de documentos */
#documentos {
    padding: 0.375rem;
}

/* Estilo para os cards de campos específicos */
#campos-da-remessa .card-header,
#oficio_container .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

/* Estilo para os ícones nos labels */
.form-label i {
    width: 16px;
    text-align: center;
    margin-right: 4px;
}
</style>

<!-- Adicione o script de validação do formulário -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validação do formulário
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Inicializa tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<!-- Mantenha os scripts existentes -->
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
            // Não limpa os campos do ofício ao editar
            if (!document.querySelector('#oficio_container input').value) {
                document.querySelectorAll('#oficio_container input').forEach(field => {
                    field.required = false;
                });
            }
        } else {
            oficioContainer.style.display = 'none';
            // Não limpa os campos se estiver editando
            if (!document.querySelector('#oficio_container input').value) {
                document.querySelectorAll('#oficio_container input').forEach(field => {
                    field.value = '';
                });
            }
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

<!-- NOVO SCRIPT DE CELEBRAÇÃO SUBSTITUINDO O ANTIGO SCRIPT DE CONFETES -->
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
        const responsavel = document.getElementById('responsavel_id');
        const responsavelNome = responsavel.options[responsavel.selectedIndex].text;

        // Condição para ativar celebração
        if (tipoMovimentacao == 5 && situacao === 'Finalizado') {
            // Criar overlay para celebração
            const overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            overlay.style.zIndex = '9999';
            overlay.style.display = 'flex';
            overlay.style.flexDirection = 'column';
            overlay.style.justifyContent = 'center';
            overlay.style.alignItems = 'center';
            overlay.style.overflow = 'hidden';
            document.body.appendChild(overlay);

            // Adicionar troféu
            const trophy = document.createElement('div');
            trophy.innerHTML = `
                <svg width="150" height="150" viewBox="0 0 24 24" fill="gold">
                    <path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V19H7v2h10v-2h-4v-3.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/>
                </svg>
            `;
            trophy.style.marginBottom = '20px';
            trophy.style.animation = 'bounce 1s ease infinite';
            overlay.appendChild(trophy);

            // Adicionar mensagem personalizada
            const message = document.createElement('div');
            message.innerHTML = `<h1 style="color: white; text-align: center; font-size: 3rem; text-shadow: 0 0 10px #ff0, 0 0 20px #ff0;">${responsavelNome}, você é demais!</h1>
                               <h2 style="color: white; text-align: center; font-size: 2rem;">Mais um IP finalizado com sucesso!</h2>`;
            overlay.appendChild(message);

            // Função para criar estrelas
            const createStars = () => {
                for (let i = 0; i < 30; i++) {
                    const star = document.createElement('div');
                    star.innerHTML = '★';
                    star.style.position = 'absolute';
                    star.style.color = `hsl(${Math.random() * 360}, 100%, 50%)`;
                    star.style.fontSize = `${Math.random() * 30 + 10}px`;
                    star.style.left = `${Math.random() * 100}%`;
                    star.style.top = `${Math.random() * 100}%`;
                    star.style.animation = `twinkle ${Math.random() * 2 + 1}s ease infinite`;
                    overlay.appendChild(star);
                }
            };

            // Função para criar balões
            const createBalloons = () => {
                for (let i = 0; i < 20; i++) {
                    const balloon = document.createElement('div');
                    balloon.style.position = 'absolute';
                    balloon.style.bottom = '-50px';
                    balloon.style.left = `${Math.random() * 100}%`;
                    balloon.style.width = '40px';
                    balloon.style.height = '50px';
                    balloon.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 50%)`;
                    balloon.style.borderRadius = '50% 50% 50% 50% / 40% 40% 60% 60%';
                    balloon.style.animation = `float ${Math.random() * 5 + 5}s linear infinite`;
                    balloon.style.animationDelay = `${Math.random() * 5}s`;
                    
                    // Adicionar linha ao balão
                    const string = document.createElement('div');
                    string.style.position = 'absolute';
                    string.style.width = '1px';
                    string.style.height = '80px';
                    string.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
                    string.style.bottom = '-80px';
                    string.style.left = '50%';
                    balloon.appendChild(string);
                    
                    overlay.appendChild(balloon);
                }
            };

            // Adicionar estilos de animação
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes bounce {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-20px); }
                }
                @keyframes twinkle {
                    0%, 100% { opacity: 0.2; transform: scale(0.8); }
                    50% { opacity: 1; transform: scale(1.2); }
                }
                @keyframes float {
                    0% { transform: translateY(0); }
                    100% { transform: translateY(-100vh); }
                }
            `;
            document.head.appendChild(style);

            // Lançar confetes
            confetti({
                particleCount: 200,
                spread: 160,
                origin: { y: 0.6 }
            });

            // Criar estrelas e balões
            createStars();
            createBalloons();

            // Botão para continuar
            const button = document.createElement('button');
            button.innerText = 'Continuar';
            button.style.marginTop = '30px';
            button.style.padding = '10px 20px';
            button.style.fontSize = '18px';
            button.style.backgroundColor = '#4CAF50';
            button.style.color = 'white';
            button.style.border = 'none';
            button.style.borderRadius = '5px';
            button.style.cursor = 'pointer';
            button.addEventListener('click', function() {
                overlay.remove();
                form.submit();
            });
            overlay.appendChild(button);

            // Auto-submeter após 10 segundos
            setTimeout(() => {
                if (document.body.contains(overlay)) {
                    overlay.remove();
                    form.submit();
                }
            }, 10000);
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