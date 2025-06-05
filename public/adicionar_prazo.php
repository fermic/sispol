<?php
session_start();
include '../includes/header.php'; // Inclui a navbar e configurações globais

// Verificar se o usuário está logado e obter o ID do usuário
$usuarioID = $_SESSION['usuario_id'] ?? null;
if (!$usuarioID) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para adicionar um prazo.</p>";
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

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e obter os dados do formulário
    $tipoID = $_POST['tipo_id'] ?? null;
    $assunto = trim($_POST['assunto'] ?? '');
    $situacao = $_POST['situacao'] ?? null;
    $dataVencimento = $_POST['data_vencimento'] ?? null;

    // Validação simples
    if (!$tipoID || !$assunto || !$situacao || !$dataVencimento) {
        $error = "Todos os campos são obrigatórios.";
    } else {
        try {
            // Inserir o novo prazo no banco de dados
            $query = "
                INSERT INTO Prazos (TipoID, Assunto, Situacao, DataVencimento, ProcedimentoID, UsuarioID)
                VALUES (:tipo_id, :assunto, :situacao, :data_vencimento, :procedimento_id, :usuario_id)
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'tipo_id' => $tipoID,
                'assunto' => $assunto,
                'situacao' => $situacao,
                'data_vencimento' => $dataVencimento,
                'procedimento_id' => $procedimentoID,
                'usuario_id' => $usuarioID,
            ]);

            // Redirecionar para a página do procedimento
            header("Location: ver_procedimento.php?id=$procedimentoID");
            exit;
        } catch (PDOException $e) {
            $error = "Erro ao adicionar o prazo: " . $e->getMessage();
        }
    }
}

// Obter os tipos de prazos para o dropdown
$queryTiposPrazo = "SELECT ID, Nome FROM TiposPrazo";
$stmtTiposPrazo = $pdo->query($queryTiposPrazo);
$tiposPrazo = $stmtTiposPrazo->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h1 class="text-center">Adicionar Prazo</h1>

    <!-- Exibir mensagens de erro -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="tipo_id" class="form-label">Tipo de Prazo</label>
            <select name="tipo_id" id="tipo_id" class="form-select" required>
                <option value="">Selecione o Tipo</option>
                <?php foreach ($tiposPrazo as $tipo): ?>
                    <option value="<?= htmlspecialchars($tipo['ID']) ?>">
                        <?= htmlspecialchars($tipo['Nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="assunto" class="form-label">Assunto</label>
            <input type="text" name="assunto" id="assunto" class="form-control" placeholder="Descreva o assunto" required>
        </div>
        <div class="mb-3">
            <label for="situacao" class="form-label">Situação</label>
            <select name="situacao" id="situacao" class="form-select" required>
                <option value="Em andamento">Em andamento</option>
                <option value="Finalizado">Finalizado</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="data_vencimento" class="form-label">Data de Vencimento</label>
            <input type="date" name="data_vencimento" id="data_vencimento" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="ver_procedimento.php?id=<?= htmlspecialchars($procedimentoID) ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
