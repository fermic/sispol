<?php
include '../includes/header.php';
require_once '../config/db.php';

session_start();
$usuarioCriadorID = $_SESSION['usuario_id'];
$id = $_GET['id'] ?? null; // Obter o ID para edição, se disponível
$registro = null;

// Se estiver no modo de edição, carregue os dados do registro
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM Desaparecidos WHERE ID = :id");
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registro) {
        echo '<div class="alert alert-danger">Registro não encontrado.</div>';
        include '../includes/footer.php';
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idade = isset($_POST['idade_nao_informada']) && $_POST['idade_nao_informada'] === 'on' ? null : $_POST['idade'];
    $dados = [
        'Vitima' => $_POST['vitima'],
        'Idade' => $idade,
        'DataDesaparecimento' => $_POST['data_desaparecimento'],
        'DataLocalizacao' => $_POST['data_localizacao'] ?: null,
        'RAI' => $_POST['rai'],
        'Situacao' => $_POST['situacao'],
        'UsuarioCriadorID' => $usuarioCriadorID
    ];

    if ($id) {
        // Atualizar o registro
        $stmt = $pdo->prepare("
            UPDATE Desaparecidos 
            SET Vitima = :Vitima, Idade = :Idade, DataDesaparecimento = :DataDesaparecimento, 
                DataLocalizacao = :DataLocalizacao, RAI = :RAI, Situacao = :Situacao, 
                UsuarioCriadorID = :UsuarioCriadorID
            WHERE ID = :id
        ");
        $dados['id'] = $id;
    } else {
        // Criar novo registro
        $stmt = $pdo->prepare("
            INSERT INTO Desaparecidos (Vitima, Idade, DataDesaparecimento, DataLocalizacao, RAI, Situacao, UsuarioCriadorID)
            VALUES (:Vitima, :Idade, :DataDesaparecimento, :DataLocalizacao, :RAI, :Situacao, :UsuarioCriadorID)
        ");
    }

    $stmt->execute($dados);

    header('Location: desaparecimentos.php');
    exit;
}
?>

<div class="container mt-5">
    <h2><?= $id ? 'Editar' : 'Adicionar' ?> Desaparecimento</h2>
    <form method="post">
        <!-- Campos do formulário -->
        <div class="mb-3">
            <label for="rai" class="form-label">RAI</label>
            <input type="text" name="rai" id="rai" class="form-control" required
                   value="<?= htmlspecialchars($registro['RAI'] ?? '') ?>">
            <small id="rai-feedback" class="text-danger" style="display: none;"></small>
        </div>

        <div class="mb-3">
            <label for="vitima" class="form-label">Nome</label>
            <input type="text" name="vitima" id="vitima" class="form-control" required
                   value="<?= htmlspecialchars($registro['Vitima'] ?? '') ?>">
        </div>

<div class="mb-3">
<div class="row">
    <!-- Campo de idade -->
    <div class="col-md-8">
        <label for="idade" class="form-label">Idade</label>
        <input type="number" name="idade" id="idade" class="form-control"
               value="<?= isset($registro['Idade']) && $registro['Idade'] !== null ? htmlspecialchars($registro['Idade']) : '' ?>">
    </div>

    <!-- Checkbox "Não informada" -->
    <div class="col-md-4 d-flex align-items-center">
        <div class="form-check mt-4">
            <input type="checkbox" class="form-check-input" id="idade_nao_informada" name="idade_nao_informada"
                   <?= isset($registro['Idade']) && $registro['Idade'] === null ? 'checked' : '' ?>>
            <label class="form-check-label" for="idade_nao_informada">Não informada</label>
        </div>
    </div>
</div>

</div>



        <div class="mb-3">
            <label for="data_desaparecimento" class="form-label">Data do Desaparecimento</label>
            <input type="date" name="data_desaparecimento" id="data_desaparecimento" class="form-control" required
                   value="<?= htmlspecialchars($registro['DataDesaparecimento'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="situacao" class="form-label">Situação</label>
            <select name="situacao" id="situacao" class="form-select" required>
                <option value="Desaparecido" <?= isset($registro['Situacao']) && $registro['Situacao'] === 'Desaparecido' ? 'selected' : '' ?>>Desaparecido</option>
                <option value="Encontrado" <?= isset($registro['Situacao']) && $registro['Situacao'] === 'Encontrado' ? 'selected' : '' ?>>Encontrado</option>
            </select>
        </div>

        <div class="mb-3" id="data_localizacao_group" style="<?= isset($registro['Situacao']) && $registro['Situacao'] === 'Encontrado' ? '' : 'display: none;' ?>">
            <label for="data_localizacao" class="form-label">Data da Localização</label>
            <input type="date" name="data_localizacao" id="data_localizacao" class="form-control"
                   value="<?= htmlspecialchars($registro['DataLocalizacao'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary"><?= $id ? 'Atualizar' : 'Salvar' ?></button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Elementos DOM
    const situacaoField = document.getElementById('situacao');
    const dataLocalizacaoGroup = document.getElementById('data_localizacao_group');
    const dataLocalizacaoInput = document.getElementById('data_localizacao');
    const idadeField = document.getElementById('idade');
    const idadeNaoInformadaCheckbox = document.getElementById('idade_nao_informada');
    const raiField = document.getElementById('rai');
    const raiFeedback = document.getElementById('rai-feedback');

    /**
     * Exibir ou ocultar o campo de Data da Localização com base na situação.
     */
    function toggleDataLocalizacao() {
        if (situacaoField.value === 'Encontrado') {
            dataLocalizacaoGroup.style.display = 'block';
        } else {
            dataLocalizacaoGroup.style.display = 'none';
            dataLocalizacaoInput.value = ''; // Limpar o valor ao ocultar
        }
    }

    /**
     * Habilitar ou desabilitar o campo de idade com base no estado do checkbox.
     */
    function toggleIdadeField() {
        if (idadeNaoInformadaCheckbox.checked) {
            idadeField.value = ''; // Limpar o valor do campo
            idadeField.setAttribute('disabled', 'true'); // Desabilitar o campo
        } else {
            idadeField.removeAttribute('disabled'); // Habilitar o campo
        }
    }

    /**
     * Configuração inicial dos campos ao carregar a página.
     */
    function initializeFields() {
        // Sincronizar o checkbox "Não informada" com o campo Idade
        if (idadeField.value === '' && !idadeField.hasAttribute('disabled')) {
            idadeNaoInformadaCheckbox.checked = true;
        }
        toggleIdadeField(); // Ajustar o estado inicial do campo de idade

        // Configurar o estado inicial do campo Data da Localização
        toggleDataLocalizacao();
    }

    /**
     * Verificar se o número de RAI já existe no banco de dados.
     */
    function verificarRAI() {
        const raiValue = raiField.value.trim();

        if (raiValue === '') {
            raiFeedback.style.display = 'none';
            return;
        }

        fetch('verifica_rai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ check_rai: raiValue }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.exists) {
                    raiFeedback.textContent = 'Já existe um desaparecimento com este número de RAI.';
                    raiFeedback.style.display = 'block';

                    if (!confirm('Já existe um desaparecimento com este número de RAI. Deseja continuar?')) {
                        raiField.value = '';
                        raiField.focus();
                    }
                } else {
                    raiFeedback.style.display = 'none';
                }
            })
            .catch((error) => {
                console.error('Erro na verificação do RAI:', error);
                raiFeedback.textContent = 'Erro ao verificar o RAI.';
                raiFeedback.style.display = 'block';
            });
    }

    // Inicializar os campos ao carregar a página
    initializeFields();

    // Adicionar eventos para interatividade
    situacaoField.addEventListener('change', toggleDataLocalizacao);
    idadeNaoInformadaCheckbox.addEventListener('change', toggleIdadeField);
    raiField.addEventListener('blur', verificarRAI);
});

</script>

<?php include '../includes/footer.php'; ?>
