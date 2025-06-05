<?php
include '../includes/header.php';
require_once '../config/db.php';

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $objetoID = $_POST['objeto_id'] ?? null;
        $tipoMovimentacaoID = $_POST['tipo_movimentacao_id'] ?? null;
        $destino = $_POST['destino'] ?? null;
        $responsavel = $_POST['responsavel'] ?? null;
        $observacao = $_POST['observacao'] ?? null;
        $usuarioID = $_SESSION['usuario_id'] ?? null;

        if (!$objetoID || !$tipoMovimentacaoID || !$destino || !$responsavel || !$usuarioID) {
            throw new Exception('Todos os campos obrigatórios devem ser preenchidos.');
        }

        // Inicia a transação
        $pdo->beginTransaction();

        // Insere a movimentação
        $stmt = $pdo->prepare("
            INSERT INTO MovimentacoesObjeto 
            (ObjetoID, TipoMovimentacaoID, Destino, Responsavel, Observacao, UsuarioID) 
            VALUES (:objetoID, :tipoMovimentacaoID, :destino, :responsavel, :observacao, :usuarioID)
        ");

        $stmt->execute([
            ':objetoID' => $objetoID,
            ':tipoMovimentacaoID' => $tipoMovimentacaoID,
            ':destino' => $destino,
            ':responsavel' => $responsavel,
            ':observacao' => $observacao,
            ':usuarioID' => $usuarioID
        ]);

        // Atualiza a situação do objeto se necessário
        if (isset($_POST['atualizar_situacao']) && $_POST['atualizar_situacao'] == '1') {
            $novaSituacaoID = $_POST['nova_situacao_id'] ?? null;
            if ($novaSituacaoID) {
                $stmt = $pdo->prepare("UPDATE Objetos SET SituacaoID = :situacaoID WHERE ID = :objetoID");
                $stmt->execute([
                    ':situacaoID' => $novaSituacaoID,
                    ':objetoID' => $objetoID
                ]);
            }
        }

        $pdo->commit();
        $_SESSION['success_message'] = 'Movimentação registrada com sucesso!';
        
        // Redireciona de volta para a página do procedimento
        $procedimentoID = $_POST['procedimento_id'] ?? null;
        header("Location: ver_procedimento.php?id=" . $procedimentoID);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'Erro ao registrar movimentação: ' . $e->getMessage();
        header("Location: ver_procedimento.php?id=" . ($_POST['procedimento_id'] ?? ''));
        exit;
    }
}

// Se não for POST, exibe o formulário
$objetoID = $_GET['objeto_id'] ?? null;
$procedimentoID = $_GET['procedimento_id'] ?? null;

if (!$objetoID || !$procedimentoID) {
    $_SESSION['error_message'] = 'Parâmetros inválidos.';
    header("Location: ver_procedimento.php?id=" . $procedimentoID);
    exit;
}

// Busca informações do objeto
$stmt = $pdo->prepare("
    SELECT o.*, t.Nome as TipoObjeto, s.Nome as Situacao 
    FROM Objetos o 
    LEFT JOIN TiposObjeto t ON o.TipoObjetoID = t.ID 
    LEFT JOIN SituacoesObjeto s ON o.SituacaoID = s.ID 
    WHERE o.ID = :objetoID
");
$stmt->execute([':objetoID' => $objetoID]);
$objeto = $stmt->fetch(PDO::FETCH_ASSOC);

// Busca tipos de movimentação
$stmt = $pdo->query("SELECT * FROM TiposMovimentacaoObjeto ORDER BY Nome");
$tiposMovimentacao = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca situações disponíveis
$stmt = $pdo->query("SELECT * FROM SituacoesObjeto ORDER BY Nome");
$situacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Registrar Movimentação de Objeto</h5>
                </div>
                <div class="card-body">
                    <!-- Informações do Objeto -->
                    <div class="mb-4">
                        <h6>Informações do Objeto</h6>
                        <p><strong>Tipo:</strong> <?= htmlspecialchars($objeto['TipoObjeto']) ?></p>
                        <p><strong>Descrição:</strong> <?= htmlspecialchars($objeto['Descricao']) ?></p>
                        <p><strong>Situação Atual:</strong> <?= htmlspecialchars($objeto['Situacao']) ?></p>
                    </div>

                    <form method="POST" action="registrar_movimentacao_objeto.php">
                        <input type="hidden" name="objeto_id" value="<?= htmlspecialchars($objetoID) ?>">
                        <input type="hidden" name="procedimento_id" value="<?= htmlspecialchars($procedimentoID) ?>">

                        <div class="mb-3">
                            <label for="tipo_movimentacao_id" class="form-label">Tipo de Movimentação *</label>
                            <select class="form-select" id="tipo_movimentacao_id" name="tipo_movimentacao_id" required>
                                <option value="">Selecione o tipo de movimentação</option>
                                <?php foreach ($tiposMovimentacao as $tipo): ?>
                                    <option value="<?= htmlspecialchars($tipo['ID']) ?>">
                                        <?= htmlspecialchars($tipo['Nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="destino" class="form-label">Destino *</label>
                            <input type="text" class="form-control" id="destino" name="destino" required>
                        </div>

                        <div class="mb-3">
                            <label for="responsavel" class="form-label">Responsável *</label>
                            <input type="text" class="form-control" id="responsavel" name="responsavel" required>
                        </div>

                        <div class="mb-3">
                            <label for="observacao" class="form-label">Observação</label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="atualizar_situacao" name="atualizar_situacao" value="1">
                                <label class="form-check-label" for="atualizar_situacao">
                                    Atualizar situação do objeto
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="nova_situacao_container" style="display: none;">
                            <label for="nova_situacao_id" class="form-label">Nova Situação</label>
                            <select class="form-select" id="nova_situacao_id" name="nova_situacao_id">
                                <option value="">Selecione a nova situação</option>
                                <?php foreach ($situacoes as $situacao): ?>
                                    <option value="<?= htmlspecialchars($situacao['ID']) ?>">
                                        <?= htmlspecialchars($situacao['Nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Registrar Movimentação</button>
                            <a href="ver_procedimento.php?id=<?= htmlspecialchars($procedimentoID) ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('atualizar_situacao');
    const container = document.getElementById('nova_situacao_container');
    const select = document.getElementById('nova_situacao_id');

    checkbox.addEventListener('change', function() {
        container.style.display = this.checked ? 'block' : 'none';
        select.required = this.checked;
    });
});
</script>

<?php include '../includes/footer.php'; ?> 