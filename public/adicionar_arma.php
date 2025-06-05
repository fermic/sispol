<?php
session_start();
include '../includes/header.php'; // Inclui a navbar e configurações globais
require_once '../config/db.php'; // Inclui a conexão com o banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}



// Obter listas para os campos de seleção
$especies = $pdo->query("SELECT ID, Nome FROM EspeciesDeArmas")->fetchAll(PDO::FETCH_ASSOC);
$calibres = $pdo->query("SELECT ID, Nome FROM CalibresDeArmas")->fetchAll(PDO::FETCH_ASSOC);
$marcas = $pdo->query("SELECT ID, Nome FROM MarcasDeArmas")->fetchAll(PDO::FETCH_ASSOC);

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter os dados do formulário
    $especieID = $_POST['especie_id'] ?? null;
    $calibreID = $_POST['calibre_id'] ?? null;
    $numeroSerie = trim($_POST['numero_serie'] ?? '');
    $marcaID = $_POST['marca_id'] ?? null;
    $modelo = trim($_POST['modelo'] ?? '');
    $numeroProcedimento = trim($_POST['numero_procedimento'] ?? '');
    $dataApreensao = $_POST['data_apreensao'] ?? null;
    $processoJudicialID = empty($_POST['numero_processo_judicial']) ? null : intval($_POST['numero_processo_judicial']);


    $lacre = trim($_POST['lacre'] ?? '');
    $cor = trim($_POST['cor'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    $localizacao = trim($_POST['localizacao'] ?? '');
    $situacao = $_POST['situacao'] ?? 'Acautelada';

    if ($processoJudicialID) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ProcessosJudiciais WHERE ID = :id");
        $stmt->execute(['id' => $processoJudicialID]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("O Processo Judicial selecionado não existe.");
        }
    }

    // Validação básica
    if ($especieID && $calibreID && $numeroSerie && $marcaID && $dataApreensao) {
        try {
            // Inserir no banco de dados
$query = "
    INSERT INTO Armas 
    (EspecieID, CalibreID, NumeroSerie, MarcaID, Modelo, NumeroProcedimento, DataApreensao, ProcessoJudicialID, Lacre, Cor, Observacoes, Localizacao, Situacao)
    VALUES
    (:especie_id, :calibre_id, :numero_serie, :marca_id, :modelo, :numero_procedimento, :data_apreensao, :processo_judicial_id, :lacre, :cor, :observacoes, :localizacao, :situacao)
";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'especie_id' => $especieID,
    'calibre_id' => $calibreID,
    'numero_serie' => $numeroSerie,
    'marca_id' => $marcaID,
    'modelo' => $modelo,
    'numero_procedimento' => $numeroProcedimento,
    'data_apreensao' => $dataApreensao,
    'processo_judicial_id' => $processoJudicialID, // Somente valores válidos ou NULL
    'lacre' => $lacre,
    'cor' => $cor,
    'observacoes' => $observacoes,
    'localizacao' => $localizacao,
    'situacao' => $situacao,
]);


            echo "<p class='text-center text-success'>Arma cadastrada com sucesso!</p>";
        } catch (PDOException $e) {
            echo "<p class='text-center text-danger'>Erro ao cadastrar arma: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='text-center text-danger'>Preencha todos os campos obrigatórios.</p>";
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center">Adicionar Nova Arma</h1>

    <form method="POST" class="mt-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="especie_id" class="form-label">Espécie</label>
                <select name="especie_id" id="especie_id" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($especies as $especie): ?>
                        <option value="<?= htmlspecialchars($especie['ID']) ?>"><?= htmlspecialchars($especie['Nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="calibre_id" class="form-label">Calibre</label>
                <select name="calibre_id" id="calibre_id" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($calibres as $calibre): ?>
                        <option value="<?= htmlspecialchars($calibre['ID']) ?>"><?= htmlspecialchars($calibre['Nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="marca_id" class="form-label">Marca</label>
                <select name="marca_id" id="marca_id" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($marcas as $marca): ?>
                        <option value="<?= htmlspecialchars($marca['ID']) ?>"><?= htmlspecialchars($marca['Nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-6">
                <label for="numero_serie" class="form-label">Número de Série</label>
                <input type="text" name="numero_serie" id="numero_serie" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="modelo" class="form-label">Modelo</label>
                <input type="text" name="modelo" id="modelo" class="form-control">
            </div>
        </div>

        <div class="row g-3 mt-3">
<div class="row g-3">
    <div class="col-md-6">
        <label for="numero_procedimento" class="form-label">Número do Procedimento</label>
        <select id="numero_procedimento" name="numero_procedimento" class="form-select">
            <option value="">Selecione...</option>
            <!-- Populado dinamicamente via Select2 -->
        </select>
    </div>

    <div class="col-md-6">
        <label for="numero_processo_judicial" class="form-label">Número do Processo Judicial</label>
        <select id="numero_processo_judicial" name="numero_processo_judicial" class="form-select" disabled>
            <option value="">Selecione...</option>
            <!-- Populado dinamicamente via AJAX -->
        </select>
    </div>
</div>



            <div class="col-md-6">
                <label for="data_apreensao" class="form-label">Data da Apreensão</label>
                <input type="date" name="data_apreensao" id="data_apreensao" class="form-control" required>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-6">
                <label for="lacre" class="form-label">Lacre</label>
                <input type="text" name="lacre" id="lacre" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="cor" class="form-label">Cor</label>
                <input type="text" name="cor" id="cor" class="form-control">
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-6">
                <label for="localizacao" class="form-label">Localização</label>
                <input type="text" name="localizacao" id="localizacao" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="situacao" class="form-label">Situação</label>
                <select name="situacao" id="situacao" class="form-select">
                    <option value="Acautelada">Acautelada</option>
                    <option value="Baixada">Baixada</option>
                    <option value="Outros">Outros</option>
                </select>
            </div>
        </div>

        <div class="mt-4">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" class="form-control" rows="4"></textarea>
        </div>

        <div class="mt-4 text-center">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="armas.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
$(document).ready(function () {
    $('#numero_procedimento').select2({
        ajax: {
            url: 'buscar_procedimentos.php', // Endereço do back-end
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term // Termo de busca
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return {
                            id: item.id,
                            text: item.text
                        };
                    })
                };
            }
        },
        placeholder: 'Digite para buscar...',
        minimumInputLength: 2 // Mínimo de caracteres para iniciar a busca
    });

    // Atualizar processos judiciais ao selecionar um procedimento
    $('#numero_procedimento').on('select2:select', function (e) {
        const procedimentoID = e.params.data.id; // ID do procedimento selecionado

        if (procedimentoID) {
            // Habilitar o campo de processo judicial
            $('#numero_processo_judicial').prop('disabled', false);

            // Buscar os processos judiciais vinculados
            fetch(`buscar_processos_judiciais.php?procedimento_id=${procedimentoID}`)
                .then(response => response.json())
                .then(data => {
                    // Limpar o campo de processos judiciais
                    $('#numero_processo_judicial').empty().append('<option value="">Selecione...</option>');

                    // Preencher com os processos judiciais
                    data.forEach(processo => {
                        $('#numero_processo_judicial').append(
                            `<option value="${processo.id}">${processo.numero} - ${processo.descricao}</option>`
                        );
                    });
                })
                .catch(error => console.error('Erro ao buscar processos judiciais:', error));
        }
    });
});



</script>
<?php include '../includes/footer.php'; ?>
