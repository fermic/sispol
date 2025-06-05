<?php
include '../includes/header.php';
require '../config/db.php'; // Conexão com o banco de dados

// Consulta para listar as cautelares solicitadas com seus cumprimentos, agrupando por Solicitação
$query = "
(
    SELECT 
        sc.ID AS SolicitacaoID,
        sc.ProcedimentoID,
        p.NumeroProcedimento,
        sc.ProcessoJudicial,
        sc.DataSolicitacao,
        GROUP_CONCAT(DISTINCT tc.Nome ORDER BY tc.Nome ASC SEPARATOR '<br>') AS TiposCautelares,
        (SELECT SUM(isc.QuantidadeSolicitada) 
         FROM ItensSolicitacaoCautelar isc 
         WHERE isc.SolicitacaoCautelarID = sc.ID) AS QuantidadeSolicitada,
        (SELECT IFNULL(SUM(cc.QuantidadeCumprida), 0) 
         FROM CumprimentosCautelares cc 
         WHERE cc.SolicitacaoCautelarID = sc.ID) AS QuantidadeCumprida,
        MAX(cc.DataCumprimento) AS UltimoCumprimento,
        NULL AS CumprimentoID
    FROM SolicitacoesCautelares sc
    LEFT JOIN Procedimentos p ON sc.ProcedimentoID = p.ID
    LEFT JOIN ItensSolicitacaoCautelar isc ON sc.ID = isc.SolicitacaoCautelarID
    LEFT JOIN TiposCautelar tc ON isc.TipoCautelarID = tc.ID
    LEFT JOIN CumprimentosCautelares cc ON sc.ID = cc.SolicitacaoCautelarID
    GROUP BY sc.ID, p.NumeroProcedimento
)
UNION ALL
(
    SELECT 
        NULL AS SolicitacaoID,
        NULL AS ProcedimentoID,
        NULL AS NumeroProcedimento,
        NULL AS ProcessoJudicial,
        cc.DataCumprimento AS DataSolicitacao,
        tc.Nome AS TiposCautelares,
        NULL AS QuantidadeSolicitada,
        cc.QuantidadeCumprida AS QuantidadeCumprida,
        cc.DataCumprimento AS UltimoCumprimento,
        cc.ID AS CumprimentoID
    FROM CumprimentosCautelares cc
    LEFT JOIN TiposCautelar tc ON cc.TipoCautelarID = tc.ID
    WHERE cc.SolicitacaoCautelarID IS NULL
)
ORDER BY DataSolicitacao DESC;
";



$stmt = $pdo->query($query);
$cautelares = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="container mt-5">
    <h2 class="mb-4">Lista de Cautelares Solicitadas</h2>

    <!-- Botões de ação -->
    <div class="mb-3">
        <a href="adicionar_cautelar.php" class="btn btn-primary">Adicionar Cautelar</a>
        <a href="adicionar_cumprimento.php" class="btn btn-success">Cumprimento Avulso</a>
    </div>

    <!-- Tabela de cautelares -->

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Procedimento</th>
                <th>Processo Judicial</th>
                <th>Data da Solicitação</th>
                <th>Tipos de Cautelares</th>
                <th>Quantidade Solicitada</th>
                <th>Quantidade Cumprida</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($cautelares) > 0): ?>
                <?php foreach ($cautelares as $cautelar): ?>
                    <tr>
                        <td>
                            <?php if ($cautelar['ProcedimentoID']): ?>
                                <a href="ver_procedimento.php?id=<?= $cautelar['ProcedimentoID'] ?>">
                                    <?= htmlspecialchars($cautelar['NumeroProcedimento'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php else: ?>
                                Avulso
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($cautelar['ProcessoJudicial'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= date('d/m/Y', strtotime($cautelar['DataSolicitacao'])) ?></td>
                        <td><?= $cautelar['TiposCautelares'] ?></td>
                        <td><?= htmlspecialchars($cautelar['QuantidadeSolicitada'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($cautelar['QuantidadeCumprida'], ENT_QUOTES, 'UTF-8') ?></td>
<td>
    <?php if ($cautelar['SolicitacaoID']): ?>
        <!-- Cautelar Vinculada -->
        <a href="adicionar_cumprimento.php?solicitacao_id=<?= $cautelar['SolicitacaoID'] ?>" class="btn btn-sm btn-success">Cumprir</a>
        <a href="javascript:void(0);" onclick="confirmarExclusao(<?= $cautelar['SolicitacaoID'] ?>, false);" class="btn btn-sm btn-danger">Excluir</a>
    <?php elseif ($cautelar['CumprimentoID']): ?>
        <!-- Cumprimento Avulso -->
        <a href="javascript:void(0);" onclick="confirmarExclusao(<?= $cautelar['CumprimentoID'] ?>, true);" class="btn btn-sm btn-danger">Excluir</a>
    <?php else: ?>
        Avulso
    <?php endif; ?>
</td>


                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Nenhuma cautelar solicitada encontrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmarExclusao(id, isAvulso) {
    if (!id || id === 'null') {
        alert('ID inválido para exclusão.');
        return;
    }

    let mensagem = isAvulso 
        ? 'Tem certeza que deseja excluir este cumprimento avulso?' 
        : 'Tem certeza que deseja excluir esta cautelar e seus cumprimentos associados?';
    
    if (confirm(mensagem)) {
        let url = isAvulso 
            ? 'excluir_cumprimento_avulso.php?id=' + id 
            : 'excluir_cautelar.php?id=' + id;

        window.location.href = url;
    }
}

</script>
<?php include '../includes/footer.php'; ?>
