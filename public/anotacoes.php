<?php
include '../includes/header.php';
require_once '../config/db.php';

// Obter o ID do procedimento
$procedimentoID = $_GET['id'] ?? null;
if (!$procedimentoID) {
    echo '<div class="alert alert-danger">Procedimento não informado.</div>';
    include '../includes/footer.php';
    exit;
}

// Obter o número do procedimento
$stmt = $pdo->prepare("SELECT NumeroProcedimento FROM Procedimentos WHERE ID = :ProcedimentoID");
$stmt->execute(['ProcedimentoID' => $procedimentoID]);
$procedimento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$procedimento) {
    echo '<div class="alert alert-danger">Procedimento não encontrado.</div>';
    include '../includes/footer.php';
    exit;
}

$numeroProcedimento = $procedimento['NumeroProcedimento'];

// Obter o ID do usuário logado
$usuarioCriadorID = $_SESSION['usuario_id'];

// Inserir nova anotação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['anotacao'])) {
    $anotacao = $_POST['anotacao'];
    $stmt = $pdo->prepare("
        INSERT INTO Anotacoes (ProcedimentoID, UsuarioCriadorID, Anotacao)
        VALUES (:ProcedimentoID, :UsuarioCriadorID, :Anotacao)
    ");
    $stmt->execute([
        'ProcedimentoID' => $procedimentoID,
        'UsuarioCriadorID' => $usuarioCriadorID,
        'Anotacao' => $anotacao
    ]);
    header("Location: anotacoes.php?id=$procedimentoID");
    exit;
}

// Excluir anotação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    $anotacaoID = $_POST['excluir_id'];
    $stmt = $pdo->prepare("DELETE FROM Anotacoes WHERE ID = :ID");
    $stmt->execute(['ID' => $anotacaoID]);
    header("Location: anotacoes.php?id=$procedimentoID");
    exit;
}

// Buscar informações das anotações
$stmt = $pdo->prepare("
    SELECT a.ID, a.Anotacao, a.DataCriacao, u.Nome AS UsuarioCriador
    FROM Anotacoes a
    JOIN Usuarios u ON a.UsuarioCriadorID = u.ID
    WHERE a.ProcedimentoID = :ProcedimentoID
    ORDER BY a.DataCriacao DESC
");
$stmt->execute(['ProcedimentoID' => $procedimentoID]);
$anotacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2>Anotações para o Procedimento: <?= htmlspecialchars($numeroProcedimento) ?></h2>

    <div class="mb-4">
        <form method="post" class="w-100">
            <textarea name="anotacao" id="anotacao" class="form-control mb-3" placeholder="Escreva uma nova anotação..." rows="5" required></textarea>
            <div class="d-flex justify-content-start">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Adicionar Anotação
                </button>
                <a href="ver_procedimento.php?id=<?= htmlspecialchars($procedimentoID) ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar ao Procedimento
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Anotações -->
    <h3 class="mb-3">Anotações Existentes</h3>
    <?php if (empty($anotacoes)): ?>
        <div class="alert alert-warning">Nenhuma anotação encontrada.</div>
    <?php else: ?>
        <div class="row gy-4">
            <?php foreach ($anotacoes as $anotacao): ?>
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Informações do usuário e data -->
                                <div>
                                    <i class="bi bi-person-fill"></i> <strong><?= htmlspecialchars($anotacao['UsuarioCriador']) ?></strong>
                                    <br>
                                    <i class="bi bi-calendar-clock"></i> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($anotacao['DataCriacao']))) ?>
                                </div>
                                <!-- Botão de Excluir -->
                                <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $anotacao['ID'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Conteúdo da anotação -->
                            <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($anotacao['Anotacao'])) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Script para Confirmar Exclusão -->
<script>
function confirmarExclusao(id) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Essa ação não poderá ser desfeita!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar formulário para excluir anotação
            const form = document.createElement('form');
            form.method = 'post';
            form.action = '';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'excluir_id';
            input.value = id;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
