<?php
include '../includes/header.php'; // Inclui configurações globais

// Fetch data for dropdowns
$situacoes = $pdo->query("SELECT ID, Nome FROM SituacoesProcedimento")->fetchAll(PDO::FETCH_ASSOC);
$tipos = $pdo->query("SELECT ID, Nome FROM TiposProcedimento")->fetchAll(PDO::FETCH_ASSOC);
$origens = $pdo->query("SELECT ID, Nome FROM OrigensProcedimentos")->fetchAll(PDO::FETCH_ASSOC);
$escrivaos = $pdo->query("SELECT u.ID, u.Nome FROM Usuarios u JOIN Cargos c ON u.CargoID = c.ID WHERE c.Nome = 'Escrivão de Polícia'")->fetchAll(PDO::FETCH_ASSOC);
$delegados = $pdo->query("SELECT ID, Nome FROM Usuarios WHERE CargoID = (SELECT ID FROM Cargos WHERE Nome = 'Delegado')")->fetchAll(PDO::FETCH_ASSOC);
$crimes = $pdo->query("SELECT ID, Nome FROM Crimes")->fetchAll(PDO::FETCH_ASSOC);
$meiosEmpregados = $pdo->query("SELECT ID, Nome FROM MeiosEmpregados ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="container mt-5">
    <h1 class="text-center">Pesquisa Avançada de Procedimentos</h1>
    <form method="GET" action="resultados_pesquisa_avancada.php" class="mt-4">
        <!-- Informações Básicas -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Informações Básicas</div>
            <div class="card-body">
                <div class="row g-3">
<!-- Campo de Tipo -->
<div class="col-md-4">
    <label for="tipo" class="form-label">Tipo</label>
    <select name="tipo" id="tipo" class="form-select">
        <option value="">Todos os Tipos</option>
        <option value="IP" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'IP') ? 'selected' : '' ?>>IP</option>
        <option value="VPI" <?= (isset($_GET['tipo']) && $_GET['tipo'] === 'VPI') ? 'selected' : '' ?>>VPI</option>
    </select>
</div>

<!-- Campo de Situação -->
<div class="col-md-4">
    <label for="situacao_id" class="form-label">Situação</label>
    <select name="situacao_id" id="situacao_id" class="form-select">
        <option value="">Todas as Situações</option>
        <?php foreach ($situacoes as $situacao): ?>
            <option value="<?= htmlspecialchars($situacao['ID']) ?>" <?= (isset($_GET['situacao_id']) && $_GET['situacao_id'] == $situacao['ID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($situacao['Nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>



                    <div class="col-md-4">
                        <label for="origem_id" class="form-label">Origem do Procedimento</label>
                        <select name="origem_id" id="origem_id" class="form-select">
                            <option value="">Todas as Origens</option>
                            <?php foreach ($origens as $origem): ?>
                                <option value="<?= $origem['ID'] ?>"><?= htmlspecialchars($origem['Nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datas -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Datas</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="data_fato_inicio" class="form-label">Data do Fato (Início)</label>
                        <input type="date" name="data_fato_inicio" id="data_fato_inicio" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="data_fato_fim" class="form-label">Data do Fato (Término)</label>
                        <input type="date" name="data_fato_fim" id="data_fato_fim" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="data_instauracao_inicio" class="form-label">Data da Instauração (Início)</label>
                        <input type="date" name="data_instauracao_inicio" id="data_instauracao_inicio" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="data_instauracao_fim" class="form-label">Data da Instauração (Término)</label>
                        <input type="date" name="data_instauracao_fim" id="data_instauracao_fim" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Envolvidos -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Envolvidos</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="escrivao_id" class="form-label">Escrivão</label>
                        <select name="escrivao_id" id="escrivao_id" class="form-select">
                            <option value="">Todos os Escrivães</option>
                            <?php foreach ($escrivaos as $escrivao): ?>
                                <option value="<?= $escrivao['ID'] ?>"><?= htmlspecialchars($escrivao['Nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="delegado_id" class="form-label">Delegado</label>
                        <select name="delegado_id" id="delegado_id" class="form-select">
                            <option value="">Todos os Delegados</option>
                            <?php foreach ($delegados as $delegado): ?>
                                <option value="<?= $delegado['ID'] ?>"><?= htmlspecialchars($delegado['Nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Crimes e Modalidades -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Crimes e Modalidades</div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Crimes (Múltiplos) -->
                    <div class="col-md-6">
                        <label for="crimes" class="form-label">Crimes</label>
                        <select name="crimes[]" id="crimes" class="form-select select2" multiple>
                            <?php foreach ($crimes as $crime): ?>
                                <option value="<?= $crime['ID'] ?>"><?= htmlspecialchars($crime['Nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Selecione um ou mais crimes</small>
                    </div>

                    <!-- Modalidade -->
                    <div class="col-md-6">
                        <label for="modalidade" class="form-label">Modalidade</label>
                        <select name="modalidade[]" id="modalidade" class="form-select select2" multiple>
                            <option value="Consumado">Consumado</option>
                            <option value="Tentado">Tentado</option>
                        </select>
                        <small class="text-muted">Selecione uma ou mais modalidades</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meios Empregados -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Meios Empregados</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="meios_empregados" class="form-label">Meios Empregados</label>
                        <select name="meios_empregados[]" id="meios_empregados" class="form-select select2" multiple>
                            <?php foreach ($meiosEmpregados as $meio): ?>
                                <option value="<?= $meio['ID'] ?>"><?= htmlspecialchars($meio['Nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Selecione um ou mais meios empregados</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg px-5 mb-4">
                <i class="bi bi-search"></i> Pesquisar
            </button>
        </div>
    </form>
</div>



<script>
    // Ativar Select2 nos campos de crimes e modalidade
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Selecione",
            allowClear: true,
            width: '100%'
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.getElementById('tipo'); // Campo Tipo
    const situacaoSelect = document.getElementById('situacao_id'); // Campo Situação

    // Salvar todas as opções iniciais de "Situação" para uso posterior
    const situacoesOriginais = Array.from(situacaoSelect.options);

    // Mapeamento de tipos para situações correspondentes
    const situacoesPorTipo = {
        IP: [1, 2, 3, 4, 5, 6, 7], // IDs de situações correspondentes ao tipo "IP"
        VPI: [8, 9, 10, 11]       // IDs de situações correspondentes ao tipo "VPI"
    };

    // Função para atualizar opções do campo "Situação"
    function atualizarSituacoes() {
        const tipoSelecionado = tipoSelect.value; // Obter o valor do tipo selecionado

        // Verificar se o tipo foi selecionado
        if (!tipoSelecionado) {
            situacaoSelect.innerHTML = '<option value="">Informe o tipo</option>'; // Exibir mensagem padrão
            situacaoSelect.disabled = true; // Desabilitar o campo
            return;
        }

        // Obter as opções correspondentes ao tipo selecionado
        const situacoesPermitidas = situacoesPorTipo[tipoSelecionado] || [];

        // Atualizar o select com as opções válidas
        situacaoSelect.innerHTML = '<option value="">Todos</option>'; // Opção padrão inicial
        situacoesOriginais.forEach(option => {
            if (situacoesPermitidas.includes(parseInt(option.value))) {
                situacaoSelect.appendChild(option.cloneNode(true)); // Adicionar a opção ao select
            }
        });

        situacaoSelect.disabled = false; // Habilitar o campo
    }

    // Ouvir mudanças no campo "Tipo de Procedimento"
    tipoSelect.addEventListener('change', atualizarSituacoes);

    // Inicializar opções na carga da página
    atualizarSituacoes();
});

</script>


<?php include '../includes/footer.php'; ?>
