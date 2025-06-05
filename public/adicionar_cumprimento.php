<?php
include '../includes/header.php';
require '../config/db.php'; // Conexão com o banco de dados

// Capturar a origem da URL
$origem = $_GET['origem'] ?? null;
$procedimentoID = $_GET['procedimento_id'] ?? null;

// Mapeamento das origens para URLs de redirecionamento
$redirectMap = [
    'procedimentos' => $procedimentoID ? "ver_procedimento.php?id={$procedimentoID}" : "listar_cautelares.php",
    'listagem_cumprimentos' => "listar_cumprimentos.php",
    'default' => "listar_cautelares.php",
];

// Definir a URL de redirecionamento
$redirectUrl = $redirectMap[$origem] ?? $redirectMap['default'];

// Obter o ID da solicitação da URL (opcional)
$solicitacaoID = $_GET['solicitacao_id'] ?? null;
$detalhesSolicitacao = [];

// Buscar os detalhes da solicitação, se o ID for informado
if ($solicitacaoID) {
    $queryDetalhes = "
        SELECT 
            tc.Nome AS TipoCautelar,
            isc.QuantidadeSolicitada,
            IFNULL(SUM(cc.QuantidadeCumprida), 0) AS QuantidadeCumprida
        FROM 
            ItensSolicitacaoCautelar isc
        LEFT JOIN 
            TiposCautelar tc ON isc.TipoCautelarID = tc.ID
        LEFT JOIN 
            CumprimentosCautelares cc ON cc.SolicitacaoCautelarID = isc.SolicitacaoCautelarID AND cc.TipoCautelarID = isc.TipoCautelarID
        WHERE 
            isc.SolicitacaoCautelarID = :SolicitacaoID
        GROUP BY 
            tc.Nome, isc.QuantidadeSolicitada, isc.ID
    ";

    $stmtDetalhes = $pdo->prepare($queryDetalhes);
    $stmtDetalhes->execute([':SolicitacaoID' => $solicitacaoID]);
    $detalhesSolicitacao = $stmtDetalhes->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Verificar se "Diligência sem RAI" está marcada
        $diligenciaSemRAI = isset($_POST['diligencia_sem_rai']) && $_POST['diligencia_sem_rai'] === 'on';

        // Obter valores de RAI e descrição ou definir como vazio se "Diligência sem RAI" estiver marcada
        $rai = $diligenciaSemRAI ? '' : ($_POST['rai'] ?? '');
        $descricaoRAI = $diligenciaSemRAI ? '' : ($_POST['descricao_rai'] ?? '');

        // Inserir o RAI na tabela RAIs somente se houver valores válidos
        if (!empty($procedimentoID) && !$diligenciaSemRAI && !empty($rai)) {
            $stmtRAI = $pdo->prepare("
                INSERT INTO RAIs (ProcedimentoID, Numero, Descricao)
                VALUES (:ProcedimentoID, :Numero, :Descricao)
            ");
            $stmtRAI->execute([
                ':ProcedimentoID' => $procedimentoID,
                ':Numero' => $rai,
                ':Descricao' => $descricaoRAI
            ]);
        }

        foreach ($_POST['cumprimentos'] as $cumprimento) {
            // Aplicar validação apenas se não for avulso (existe uma solicitação vinculada)
            if ($solicitacaoID) {
                $stmtNomeCautelar = $pdo->prepare("SELECT Nome FROM TiposCautelar WHERE ID = :TipoCautelarID");
                $stmtNomeCautelar->execute([':TipoCautelarID' => $cumprimento['tipo_cautelar_id']]);
                $nomeCautelar = $stmtNomeCautelar->fetchColumn();

                $stmtValidarQuantidade = $pdo->prepare("
                    SELECT 
                        isc.QuantidadeSolicitada,
                        IFNULL(SUM(cc.QuantidadeCumprida), 0) AS QuantidadeCumprida
                    FROM ItensSolicitacaoCautelar isc
                    LEFT JOIN CumprimentosCautelares cc 
                        ON cc.SolicitacaoCautelarID = isc.SolicitacaoCautelarID 
                        AND cc.TipoCautelarID = isc.TipoCautelarID
                    WHERE isc.SolicitacaoCautelarID = :SolicitacaoID AND isc.TipoCautelarID = :TipoCautelarID
                    GROUP BY isc.QuantidadeSolicitada
                ");
                $stmtValidarQuantidade->execute([
                    ':SolicitacaoID' => $solicitacaoID,
                    ':TipoCautelarID' => $cumprimento['tipo_cautelar_id']
                ]);
                $dadosQuantidade = $stmtValidarQuantidade->fetch(PDO::FETCH_ASSOC);

                if ($dadosQuantidade) {
                    $quantidadeRestante = $dadosQuantidade['QuantidadeSolicitada'] - $dadosQuantidade['QuantidadeCumprida'];
                    if ($cumprimento['quantidade_cumprida'] > $quantidadeRestante) {
                        throw new Exception(
                            "Quantidade cumprida para o tipo de cautelar '{$nomeCautelar}' excede a quantidade restante ({$quantidadeRestante})."
                        );
                    }
                }
            }

            $stmtCumprimento = $pdo->prepare("
                INSERT INTO CumprimentosCautelares (SolicitacaoCautelarID, TipoCautelarID, RAI, DescricaoRAI, DataCumprimento, QuantidadeCumprida)
                VALUES (:SolicitacaoCautelarID, :TipoCautelarID, :RAI, :DescricaoRAI, :DataCumprimento, :QuantidadeCumprida)
            ");
            $stmtCumprimento->execute([
                ':SolicitacaoCautelarID' => $solicitacaoID ?: null,
                ':TipoCautelarID' => $cumprimento['tipo_cautelar_id'],
                ':RAI' => $rai,
                ':DescricaoRAI' => $descricaoRAI,
                ':DataCumprimento' => $_POST['data_cumprimento'],
                ':QuantidadeCumprida' => $cumprimento['quantidade_cumprida']
            ]);

            $cumprimentoID = $pdo->lastInsertId();

            if (!empty($_POST['envolvidos'])) {
                $stmtEnvolvido = $pdo->prepare("
                    INSERT INTO EnvolvidosCumprimentoCautelar (CumprimentoCautelarID, Nome)
                    VALUES (:CumprimentoCautelarID, :Nome)
                ");

                foreach ($_POST['envolvidos'] as $envolvido) {
                    $stmtEnvolvido->execute([
                        ':CumprimentoCautelarID' => $cumprimentoID,
                        ':Nome' => $envolvido
                    ]);
                }
            }
        }

        $pdo->commit();
        $_SESSION['success_message'] = "Cumprimento e envolvidos adicionados com sucesso!";
        header("Location: " . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8'));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Erro ao processar dados: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
    }
}

?>



<div class="container mt-5">
    <h2 class="mb-4"><?= $solicitacaoID ? 'Cumprir Cautelar Vinculada' : 'Cumprimento Avulso' ?></h2>

    <?php if ($solicitacaoID && count($detalhesSolicitacao) > 0): ?>
        <div class="mb-4">
            <h5>Detalhes da Solicitação</h5>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tipo de Cautelar</th>
                        <th>Quantidade Solicitada</th>
                        <th>Quantidade Cumprida</th>
                        <th>Quantidade Restante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalhesSolicitacao as $detalhe): ?>
                        <?php $quantidadeRestante = $detalhe['QuantidadeSolicitada'] - $detalhe['QuantidadeCumprida']; ?>
                        <tr>
                            <td><?= htmlspecialchars($detalhe['TipoCautelar'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($detalhe['QuantidadeSolicitada'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($detalhe['QuantidadeCumprida'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $quantidadeRestante ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($solicitacaoID): ?>
    <!-- Botão para exibir/ocultar detalhes -->
    <div class="mb-3">
        <button class="btn btn-info btn-sm" onclick="toggleDetails()">Exibir Detalhes</button>
    </div>

    <!-- Detalhes cadastrados (inicialmente ocultos) -->
    <div id="detalhes-cadastrados" class="d-none">
        <h5>Registros Cadastrados</h5>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tipo de Cautelar</th>
                    <th>Data de Cumprimento</th>
                    <th>RAI</th>
                    <th>Descrição do RAI</th>
                    <th>Envolvido</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta para buscar os registros já cadastrados
                $queryCadastrados = "
                    SELECT 
                        tc.Nome AS TipoCautelar,
                        cc.DataCumprimento,
                        cc.RAI,
                        cc.DescricaoRAI,
                        GROUP_CONCAT(ec.Nome SEPARATOR ', ') AS Envolvidos
                    FROM CumprimentosCautelares cc
                    LEFT JOIN TiposCautelar tc ON cc.TipoCautelarID = tc.ID
                    LEFT JOIN EnvolvidosCumprimentoCautelar ec ON cc.ID = ec.CumprimentoCautelarID
                    WHERE cc.SolicitacaoCautelarID = :SolicitacaoID
                    GROUP BY tc.Nome, cc.DataCumprimento, cc.RAI, cc.DescricaoRAI
                ";
                $stmtCadastrados = $pdo->prepare($queryCadastrados);
                $stmtCadastrados->execute([':SolicitacaoID' => $solicitacaoID]);
                $registrosCadastrados = $stmtCadastrados->fetchAll(PDO::FETCH_ASSOC);

                if (count($registrosCadastrados) > 0):
                    foreach ($registrosCadastrados as $registro): ?>
                        <tr>
                            <td><?= htmlspecialchars($registro['TipoCautelar'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= date('d/m/Y', strtotime($registro['DataCumprimento'])) ?></td>
                            <td><?= htmlspecialchars($registro['RAI'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($registro['DescricaoRAI'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($registro['Envolvidos'] ?? 'Nenhum envolvido', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhum registro cadastrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
    <!-- Formulário -->
    <form method="post">
        <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') ?>">

<div class="row mb-3">
    <!-- Campo de RAI -->
    <div class="col-md-4">
        <label for="rai" class="form-label">RAI</label>
        <input type="text" name="rai" id="rai" class="form-control" placeholder="Digite o RAI">
    </div>

    <!-- Campo de Descrição do RAI -->
    <div class="col-md-4">
        <label for="descricao_rai" class="form-label">Descrição do RAI</label>
        <input type="text" name="descricao_rai" id="descricao_rai" class="form-control" placeholder="Descrição do RAI">
    </div>

    <!-- Checkbox Diligência sem RAI -->
    <div class="col-md-4 d-flex align-items-center">
        <div class="form-check">
            <input type="checkbox" id="diligencia_sem_rai" class="form-check-input" onclick="toggleRAIFields()">
            <label for="diligencia_sem_rai" class="form-check-label">Diligência sem RAI</label>
        </div>
    </div>
</div>

        <!-- Campo de Envolvidos -->
        <div class="mb-3">
            <label for="envolvidos" class="form-label">Envolvidos</label>
            <div id="envolvidos-container"></div>
            <button type="button" class="btn btn-outline-dark btn-sm mt-2" onclick="adicionarEnvolvido()">Adicionar Envolvido</button>
        </div>

        <!-- Cumprimentos -->
        <div class="mb-3">
            <label for="cumprimentos" class="form-label">Cumprimentos</label>
            <div id="cumprimentos-container"></div>
            <button type="button" class="btn btn-outline-dark btn-sm mt-2" onclick="adicionarCumprimento()">Adicionar Cumprimento</button>
        </div>

        <!-- Data do Cumprimento -->
<div class="row mb-3">
    <div class="col-md-4">
        <label for="data_cumprimento" class="form-label">Data do Cumprimento</label>
        <input type="date" name="data_cumprimento" id="data_cumprimento" class="form-control" required>
    </div>
</div>


        <!-- Botões -->
        <div class="d-flex justify-content-start gap-2 mb-4">
             <a href="<?= htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>

<script>
// Função para adicionar novos campos de envolvidos dinamicamente
function adicionarEnvolvido() {
    const container = document.getElementById('envolvidos-container');
    const index = container.children.length;

    const div = document.createElement('div');
    div.className = 'row mb-3';

    div.innerHTML = `
        <div class="col-md-12">
            <input type="text" name="envolvidos[${index}]" class="form-control" placeholder="Nome do Envolvido" required>
        </div>
    `;
    container.appendChild(div);
}
</script>


<script>
// Função para adicionar novos campos de cumprimento dinamicamente
function adicionarCumprimento() {
    const container = document.getElementById('cumprimentos-container');
    const index = container.children.length;

    const div = document.createElement('div');
    div.className = 'row mb-3';

    div.innerHTML = `
        <div class="col-md-8">
<select name="cumprimentos[${index}][tipo_cautelar_id]" class="form-control" required>
    <option value="">Selecione o Tipo de Cautelar</option>
    <?php
    if ($solicitacaoID) {
        $queryTiposValidos = "
            SELECT DISTINCT tc.ID, tc.Nome, 
                isc.QuantidadeSolicitada - IFNULL(SUM(cc.QuantidadeCumprida), 0) AS QuantidadeRestante
            FROM ItensSolicitacaoCautelar isc
            LEFT JOIN TiposCautelar tc ON isc.TipoCautelarID = tc.ID
            LEFT JOIN CumprimentosCautelares cc 
                ON cc.SolicitacaoCautelarID = isc.SolicitacaoCautelarID AND cc.TipoCautelarID = isc.TipoCautelarID
            WHERE isc.SolicitacaoCautelarID = :SolicitacaoID
            GROUP BY isc.TipoCautelarID
        ";
        $stmtTiposValidos = $pdo->prepare($queryTiposValidos);
        $stmtTiposValidos->execute([':SolicitacaoID' => $solicitacaoID]);
        $tiposValidos = $stmtTiposValidos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tiposValidos as $tipo) {
            echo "<option value='{$tipo['ID']}' data-restante='{$tipo['QuantidadeRestante']}'>" 
                . htmlspecialchars($tipo['Nome'], ENT_QUOTES, 'UTF-8') 
                . " (Restante: {$tipo['QuantidadeRestante']})</option>";
        }
    } else {
        // Carregar todos os tipos de cautelares para cumprimento avulso
        $queryTipos = "SELECT ID, Nome FROM TiposCautelar";
        $stmtTipos = $pdo->query($queryTipos);
        $tiposCautelares = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tiposCautelares as $tipo) {
            echo "<option value='{$tipo['ID']}'>" 
                . htmlspecialchars($tipo['Nome'], ENT_QUOTES, 'UTF-8') . "</option>";
        }
    }
    ?>
</select>


        </div>
        <div class="col-md-4">
            <input type="number" name="cumprimentos[${index}][quantidade_cumprida]" class="form-control" placeholder="Quantidade" required>
        </div>
    `;
    container.appendChild(div);
}
</script>

<script>
document.querySelector('form').addEventListener('submit', function (e) {
    const solicitacaoID = <?= json_encode($solicitacaoID) ?>; // PHP insere o valor de solicitacaoID
    let isValid = true;
    const errors = [];

    if (solicitacaoID) {
        document.querySelectorAll('#cumprimentos-container .row').forEach(function (row) {
            const tipoCautelar = row.querySelector('select').selectedOptions[0].text;
            const quantidadeCumprida = parseInt(row.querySelector('input[type="number"]').value, 10);
            const quantidadeRestante = parseInt(row.querySelector('select').dataset.restante, 10);

            if (quantidadeCumprida > quantidadeRestante) {
                isValid = false;
                errors.push(`A quantidade cumprida para ${tipoCautelar} excede a quantidade restante (${quantidadeRestante}).`);
            }
        });
    }

    if (!isValid) {
        e.preventDefault(); // Impedir envio do formulário
        alert(errors.join('\n'));
    }
});

</script>


<script>
// Alternar exibição de detalhes
function toggleDetails() {
    const detalhes = document.getElementById('detalhes-cadastrados');
    const button = document.querySelector('button[onclick="toggleDetails()"]');
    if (detalhes.classList.contains('d-none')) {
        detalhes.classList.remove('d-none');
        button.textContent = 'Ocultar Detalhes';
    } else {
        detalhes.classList.add('d-none');
        button.textContent = 'Exibir Detalhes';
    }
}
</script>

<script>
function toggleRAIFields() {
    const isChecked = document.getElementById('diligencia_sem_rai').checked;
    const raiField = document.getElementById('rai');
    const descricaoRAIField = document.getElementById('descricao_rai');

    raiField.disabled = isChecked;
    descricaoRAIField.disabled = isChecked;

    // Limpar valores ao desabilitar
    if (isChecked) {
        raiField.value = '';
        descricaoRAIField.value = '';
    }
}
</script>
<?php include '../includes/footer.php'; ?>
