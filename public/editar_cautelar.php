<?php
include '../includes/header.php';
require '../config/db.php'; // Conexão com o banco de dados

// Obter o ID da solicitação da URL
$solicitacaoID = $_GET['solicitacao_id'] ?? null;
$procedimentoID = $_GET['procedimento_id'] ?? null;

if (!$solicitacaoID || !$procedimentoID) {
    echo "<p class='text-center text-danger'>Cautelar ou Procedimento não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

// Consulta para buscar os dados da solicitação cautelar
$querySolicitacao = "
    SELECT 
        sc.ID AS SolicitacaoID,
        sc.ProcessoJudicial,
        sc.DataSolicitacao,
        sc.Observacoes,
        GROUP_CONCAT(DISTINCT isc.TipoCautelarID ORDER BY isc.TipoCautelarID ASC) AS TiposCautelares
    FROM SolicitacoesCautelares sc
    LEFT JOIN ItensSolicitacaoCautelar isc ON sc.ID = isc.SolicitacaoCautelarID
    WHERE sc.ID = :solicitacaoID
    GROUP BY sc.ID
";

$stmtSolicitacao = $pdo->prepare($querySolicitacao);
$stmtSolicitacao->execute([':solicitacaoID' => $solicitacaoID]);
$solicitacao = $stmtSolicitacao->fetch(PDO::FETCH_ASSOC);

if (!$solicitacao) {
    echo "<p class='text-center text-danger'>Cautelar não encontrada.</p>";
    include '../includes/footer.php';
    exit;
}

// Buscar os tipos de cautelares disponíveis
$queryTiposCautelares = "SELECT ID, Nome FROM TiposCautelar";
$tiposCautelares = $pdo->query($queryTiposCautelares)->fetchAll(PDO::FETCH_ASSOC);

// Processar o formulário ao ser enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Atualizar a solicitação cautelar
        $stmtUpdateSolicitacao = $pdo->prepare("
            UPDATE SolicitacoesCautelares
            SET ProcessoJudicial = :ProcessoJudicial,
                DataSolicitacao = :DataSolicitacao,
                Observacoes = :Observacoes
            WHERE ID = :SolicitacaoID
        ");
        $stmtUpdateSolicitacao->execute([
            ':ProcessoJudicial' => $_POST['processo_judicial'],
            ':DataSolicitacao' => $_POST['data_solicitacao'],
            ':Observacoes' => $_POST['observacoes'],
            ':SolicitacaoID' => $solicitacaoID
        ]);

        // Atualizar os itens da solicitação cautelar
        $stmtDeleteItens = $pdo->prepare("DELETE FROM ItensSolicitacaoCautelar WHERE SolicitacaoCautelarID = :SolicitacaoID");
        $stmtDeleteItens->execute([':SolicitacaoID' => $solicitacaoID]);

        $stmtInsertItens = $pdo->prepare("
            INSERT INTO ItensSolicitacaoCautelar (SolicitacaoCautelarID, TipoCautelarID, QuantidadeSolicitada)
            VALUES (:SolicitacaoCautelarID, :TipoCautelarID, :QuantidadeSolicitada)
        ");
        foreach ($_POST['tipos_cautelares'] as $tipoCautelar) {
            if (!empty($tipoCautelar['id']) && !empty($tipoCautelar['quantidade'])) {
                $stmtInsertItens->execute([
                    ':SolicitacaoCautelarID' => $solicitacaoID,
                    ':TipoCautelarID' => $tipoCautelar['id'],
                    ':QuantidadeSolicitada' => $tipoCautelar['quantidade']
                ]);
            }
        }

        $pdo->commit();
        $_SESSION['success_message'] = "Cautelar atualizada com sucesso!";
        header("Location: ver_procedimento.php?id={$procedimentoID}");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Erro ao atualizar cautelar: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
    }
}
?>

<div class="container mt-5">
    <h2 class="mb-4">Editar Cautelar</h2>

    <form method="post">
        <!-- Processo Judicial -->
        <div class="mb-3">
            <label for="processo_judicial" class="form-label">Número do Processo Judicial</label>
            <input type="text" name="processo_judicial" id="processo_judicial" class="form-control" 
                   value="<?= htmlspecialchars($solicitacao['ProcessoJudicial'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <!-- Data da Solicitação -->
        <div class="mb-3">
            <label for="data_solicitacao" class="form-label">Data da Solicitação</label>
            <input type="date" name="data_solicitacao" id="data_solicitacao" class="form-control" 
                   value="<?= htmlspecialchars($solicitacao['DataSolicitacao'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <!-- Observações -->
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" class="form-control" rows="3"><?= htmlspecialchars($solicitacao['Observacoes'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- Tipos de Cautelares -->
        <div class="mb-3">
            <label class="form-label">Tipos de Cautelares</label>
<div id="tipos-cautelares-container">
    <?php
    // Consulta para obter os detalhes dos itens da solicitação
    $queryItens = "
        SELECT 
            isc.TipoCautelarID,
            isc.QuantidadeSolicitada,
            tc.Nome AS TipoCautelarNome
        FROM ItensSolicitacaoCautelar isc
        LEFT JOIN TiposCautelar tc ON isc.TipoCautelarID = tc.ID
        WHERE isc.SolicitacaoCautelarID = :SolicitacaoID
    ";
    $stmtItens = $pdo->prepare($queryItens);
    $stmtItens->execute([':SolicitacaoID' => $solicitacaoID]);
    $itensSolicitacao = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    foreach ($itensSolicitacao as $index => $item):
    ?>
        <div class="row mb-3">
            <div class="col-md-8">
                <select name="tipos_cautelares[<?= $index ?>][id]" class="form-select" required>
                    <option value="">Selecione o Tipo de Cautelar</option>
                    <?php foreach ($tiposCautelares as $tipo): ?>
                        <option value="<?= $tipo['ID'] ?>" <?= $tipo['ID'] == $item['TipoCautelarID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['Nome'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" name="tipos_cautelares[<?= $index ?>][quantidade]" 
                       class="form-control" placeholder="Quantidade Solicitada" 
                       value="<?= htmlspecialchars($item['QuantidadeSolicitada'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
        </div>
    <?php endforeach; ?>
</div>

            <button type="button" class="btn btn-outline-dark btn-sm mt-2" onclick="adicionarTipoCautelar()">Adicionar Tipo de Cautelar</button>
        </div>

        <!-- Botões -->
        <div class="d-flex justify-content-start gap-2 mb-4">
            <a href="ver_procedimento.php?id=<?= htmlspecialchars($procedimentoID, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>

<script>
function adicionarTipoCautelar() {
    const container = document.getElementById('tipos-cautelares-container');
    const index = container.children.length;

    const div = document.createElement('div');
    div.className = 'row mb-3';

    div.innerHTML = `
        <div class="col-md-8">
            <select name="tipos_cautelares[${index}][id]" class="form-select" required>
                <option value="">Selecione o Tipo de Cautelar</option>
                <?php foreach ($tiposCautelares as $tipo): ?>
                    <option value="<?= $tipo['ID'] ?>"><?= htmlspecialchars($tipo['Nome'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <input type="number" name="tipos_cautelares[${index}][quantidade]" 
                   class="form-control" placeholder="Quantidade Solicitada" required>
        </div>
    `;
    container.appendChild(div);
}
</script>

<?php include '../includes/footer.php'; ?>
