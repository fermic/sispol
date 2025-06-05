<?php
include '../includes/header.php';
require '../config/db.php'; // Conexão com o banco de dados

// Obter o ID do Procedimento da URL (se existir)
$procedimentoID = $_GET['procedimento_id'] ?? null;

// Captura a URL de origem ou define uma padrão
$redirectUrl = $_POST['redirect_url'] ?? $_SERVER['HTTP_REFERER'] ?? 'listar_cautelares.php';

// Buscar os procedimentos disponíveis para exibir no Select2
$queryProcedimentos = "SELECT ID, NumeroProcedimento FROM Procedimentos";
$stmtProcedimentos = $pdo->query($queryProcedimentos);
$procedimentos = $stmtProcedimentos->fetchAll(PDO::FETCH_ASSOC);

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Inserir a solicitação
        $stmt = $pdo->prepare("
            INSERT INTO SolicitacoesCautelares (ProcedimentoID, ProcessoJudicial, DataSolicitacao, Observacoes)
            VALUES (:ProcedimentoID, :ProcessoJudicial, :DataSolicitacao, :Observacoes)
        ");
        $stmt->execute([
            ':ProcedimentoID' => $_POST['procedimento_id'] ?? null,
            ':ProcessoJudicial' => $_POST['processo_judicial'] ?? null,
            ':DataSolicitacao' => $_POST['data_solicitacao'],
            ':Observacoes' => $_POST['observacoes'] ?? null
        ]);
        $solicitacaoID = $pdo->lastInsertId();

        // Inserir os itens da solicitação
        $stmtItens = $pdo->prepare("
            INSERT INTO ItensSolicitacaoCautelar (SolicitacaoCautelarID, TipoCautelarID, QuantidadeSolicitada)
            VALUES (:SolicitacaoCautelarID, :TipoCautelarID, :QuantidadeSolicitada)
        ");
        foreach ($_POST['tipos_cautelares'] as $item) {
            if (!empty($item['id']) && !empty($item['quantidade'])) {
                $stmtItens->execute([
                    ':SolicitacaoCautelarID' => $solicitacaoID,
                    ':TipoCautelarID' => $item['id'],
                    ':QuantidadeSolicitada' => $item['quantidade']
                ]);
            }
        }
        
        // Inserir o processo judicial na tabela ProcessosJudiciais
        if (!empty($_POST['procedimento_id']) && !empty($_POST['processo_judicial'])) {
            $stmtProcesso = $pdo->prepare("
                INSERT INTO ProcessosJudiciais (ProcedimentoID, Numero, Descricao)
                VALUES (:ProcedimentoID, :Numero, :Descricao)
            ");
            $stmtProcesso->execute([
                ':ProcedimentoID' => $_POST['procedimento_id'],
                ':Numero' => $_POST['processo_judicial'],
                ':Descricao' => 'Medidas Cautelares'
            ]);
        }

        $pdo->commit();
        $_SESSION['success_message'] = "Solicitação adicionada com sucesso!";
        header("Location: $redirectUrl");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Erro ao adicionar solicitação: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: $redirectUrl");
        exit;
    }
}
?>

<div class="container mt-5">
    <h2 class="mb-4">Adicionar Solicitação de Cautelar</h2>

    <form method="post">
        <!-- Enviar URL de redirecionamento -->
        <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') ?>">

        <!-- Campo oculto para Procedimento ID -->
        <input type="hidden" name="procedimento_id" value="<?= htmlspecialchars($procedimentoID, ENT_QUOTES, 'UTF-8') ?>">

        <!-- Campo do Processo Judicial -->
        <div class="mb-3">
            <label for="processo_judicial" class="form-label">Número do Processo Judicial</label>
            <input type="text" name="processo_judicial" id="processo_judicial" 
                   class="form-control" placeholder="9999999-99.9999.9.99.9999">
        </div>

        <!-- Campo de Data da Solicitação -->
        <div class="mb-3">
            <label for="data_solicitacao" class="form-label">Data da Solicitação</label>
            <input type="date" name="data_solicitacao" id="data_solicitacao" class="form-control" required>
        </div>

        <!-- Observações -->
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" class="form-control" rows="3"></textarea>
        </div>

        <!-- Tipos de Cautelares -->
        <div class="mb-3">
            <label class="form-label">Tipos de Cautelares</label>
            <div id="tipos-cautelares-container">
                <!-- JavaScript adicionará os campos aqui -->
            </div>
            <button type="button" class="btn btn-outline-dark btn-sm mt-2" onclick="adicionarTipoCautelar()">Adicionar Tipo de Cautelar</button>
        </div>

        <!-- Botões -->
        <div class="d-flex justify-content-start gap-2 mb-4">
            <a href="listar_cautelares.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>

<script>
// Ativar Select2 no campo de Procedimentos
document.addEventListener('DOMContentLoaded', function () {
    $('#procedimento_id').select2({
        placeholder: "Selecione um Procedimento (opcional)",
        allowClear: true,
        width: '100%'
    });
});

// Função para adicionar novos campos de tipo de cautelar dinamicamente
function adicionarTipoCautelar() {
    const container = document.getElementById('tipos-cautelares-container');
    const index = container.children.length;

    const div = document.createElement('div');
    div.className = 'row mb-3';

    div.innerHTML = `
        <div class="col-md-8">
            <select name="tipos_cautelares[${index}][id]" class="form-control" required>
                <option value="">Selecione o Tipo de Cautelar</option>
                <?php
                $queryTipos = "SELECT ID, Nome FROM TiposCautelar";
                $stmtTipos = $pdo->query($queryTipos);
                $tipos = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);

                foreach ($tipos as $tipo) {
                    echo "<option value='{$tipo['ID']}'>" . htmlspecialchars($tipo['Nome'], ENT_QUOTES, 'UTF-8') . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-4">
            <input type="number" name="tipos_cautelares[${index}][quantidade]" class="form-control" placeholder="Quantidade" required>
        </div>
    `;
    container.appendChild(div);
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
<script>
// Aplicar a máscara ao campo de processo judicial
$(document).ready(function() {
    $('#processo_judicial').inputmask('9999999-99.9999.9.99.9999', {
        placeholder: '_',
        clearIncomplete: true
    });
});
</script>

<?php include '../includes/footer.php'; ?>
