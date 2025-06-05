<?php
include_once '../../includes/header.php';

// Inicialização de variáveis
$id = $nome = $cargo = $cpf = $funcional = $telefone = '';

// Verifica se é edição
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM Policiais WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $policial = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($policial) {
        $nome = $policial['nome'];
        $cargo = $policial['cargo'];
        $cpf = $policial['cpf'];
        $funcional = $policial['funcional'];
        $telefone = $policial['telefone'];
    }
}

// Salvar ou atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nome = $_POST['nome'];
    $cargo = $_POST['cargo'];
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $funcional = $_POST['funcional'];
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);

    $anexoPath = null;

    // Verifica se é uma edição e o usuário deseja manter o anexo
    if ($id && isset($_POST['manter_anexo']) && !empty($policial['anexo'])) {
        $anexoPath = $policial['anexo']; // Mantém o anexo atual
    }

    // Verifica se um novo arquivo foi enviado
    if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/policiais/';
        $fileName = uniqid() . '-' . basename($_FILES['anexo']['name']);
        $uploadFile = $uploadDir . $fileName;

        // Cria o diretório, se necessário
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move o arquivo enviado para o diretório de destino
        if (move_uploaded_file($_FILES['anexo']['tmp_name'], $uploadFile)) {
            $anexoPath = $fileName; // Atualiza o caminho do novo anexo
        } else {
            echo "<div class='alert alert-danger'>Erro ao fazer upload do arquivo.</div>";
        }
    }

    if ($id) {
        // Atualizar registro
        $stmt = $pdo->prepare("
            UPDATE Policiais 
            SET nome = :nome, cargo = :cargo, cpf = :cpf, funcional = :funcional, telefone = :telefone, anexo = :anexo
            WHERE id = :id
        ");
        $stmt->execute([
            'nome' => $nome, 'cargo' => $cargo, 'cpf' => $cpf,
            'funcional' => $funcional, 'telefone' => $telefone,
            'anexo' => $anexoPath, 'id' => $id
        ]);
    } else {
        // Inserir novo registro
        $stmt = $pdo->prepare("
            INSERT INTO Policiais (nome, cargo, cpf, funcional, telefone, anexo) 
            VALUES (:nome, :cargo, :cpf, :funcional, :telefone, :anexo)
        ");
        $stmt->execute([
            'nome' => $nome, 'cargo' => $cargo, 'cpf' => $cpf,
            'funcional' => $funcional, 'telefone' => $telefone,
            'anexo' => $anexoPath
        ]);
    }

    header('Location: listar_policiais.php');
    exit;
}
?>

<div class="container mt-5">
    <h1><?= $id ? 'Editar Policial' : 'Cadastrar Policial' ?></h1>
    <form method="POST" enctype="multipart/form-data"> <!-- Adicione enctype -->
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($nome) ?>" required>
        </div>
        <div class="mb-3">
            <label for="cargo" class="form-label">Cargo</label>
            <select name="cargo" id="cargo" class="form-select" required>
                <option value="">Selecione o cargo</option>
                <option value="Delegado" <?= $cargo === 'Delegado' ? 'selected' : '' ?>>Delegado</option>
                <option value="Escrivão" <?= $cargo === 'Escrivão' ? 'selected' : '' ?>>Escrivão</option>
                <option value="Agente" <?= $cargo === 'Agente' ? 'selected' : '' ?>>Agente</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="cpf" class="form-label">CPF</label>
            <input type="text" name="cpf" id="cpf" class="form-control" value="<?= htmlspecialchars($cpf) ?>" required>
        </div>
        <div class="mb-3">
            <label for="funcional" class="form-label">Funcional</label>
            <input type="text" name="funcional" id="funcional" class="form-control" value="<?= htmlspecialchars($funcional) ?>" required>
        </div>
        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" name="telefone" id="telefone" class="form-control" value="<?= htmlspecialchars($telefone) ?>">
        </div>
<div class="mb-3">
    <label for="anexo" class="form-label">Arquivo Anexo (opcional)</label>
    <?php if ($id && !empty($policial['anexo'])): ?>
        <div class="mb-2">
            <a href="../../uploads/policiais/<?= htmlspecialchars($policial['anexo']) ?>" 
               target="_blank" 
               class="btn btn-info btn-sm">
                Ver Anexo Atual
            </a>
        </div>
        <div class="form-check">
            <input type="checkbox" name="manter_anexo" id="manter_anexo" class="form-check-input" checked>
            <label for="manter_anexo" class="form-check-label">Manter anexo existente</label>
        </div>
    <?php endif; ?>
    <input type="file" name="anexo" id="anexo" class="form-control">
</div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="listar_policiais.php" class="btn btn-secondary">Voltar</a>
    </form>
</div>


<script>
    // Aplica as máscaras ao carregar a página
    document.addEventListener('DOMContentLoaded', () => {
        // Máscara para CPF
        Inputmask({
            mask: "999.999.999-99",
            placeholder: "_",
            clearIncomplete: true
        }).mask("#cpf");

        // Máscara para Telefone
        Inputmask({
            mask: "(99) 99999-9999",
            placeholder: "_",
            clearIncomplete: true
        }).mask("#telefone");
    });
</script>
