<?php
include '../includes/header.php'; // Inclui a navbar e configurações globais
require_once '../includes/helpers.php'; // Inclui as funções helper

// Obter o ID do procedimento a partir da URL
$procedimentoID = $_GET['id'] ?? null;
if (!$procedimentoID) {
    echo "<p class='text-center text-danger'>Procedimento não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

// Consulta para buscar os detalhes do procedimento
$queryProcedimento = "
    SELECT 
        p.ID,
        p.NumeroProcedimento,
        p.DataFato,
        p.DataInstauracao,
        p.TipoID,
        p.MotivoAparente,
        sp.Nome AS Situacao,
        sp.Cor AS Cor, -- Adiciona a cor associada à situação
        (
            SELECT GROUP_CONCAT(
                DISTINCT CONCAT(
                    v.Nome, ' (', 
                    (SELECT GROUP_CONCAT(CONCAT(c.Nome, ' - ', vc.Modalidade) SEPARATOR '), (')
                     FROM Vitimas_Crimes vc
                     LEFT JOIN Crimes c ON vc.CrimeID = c.ID
                     WHERE vc.VitimaID = v.ID
                    ), ')'
                ) SEPARATOR ', '
            )
            FROM Vitimas v
            WHERE v.ProcedimentoID = p.ID
        ) AS Vitimas,
        (
            SELECT GROUP_CONCAT(DISTINCT i.Nome SEPARATOR ', ')
            FROM Investigados i
            WHERE i.ProcedimentoID = p.ID
        ) AS Investigados,
        (
            SELECT GROUP_CONCAT(DISTINCT m.Nome SEPARATOR ', ')
            FROM ProcedimentosMeiosEmpregados pme
            LEFT JOIN MeiosEmpregados m ON pme.MeioEmpregadoID = m.ID
            WHERE pme.ProcedimentoID = p.ID
        ) AS MeiosEmpregados,
        uDelegado.Nome AS Delegado,
        uEscrivao.Nome AS Escrivao,
        d.Nome AS Delegacia,
        EXISTS (
            SELECT 1 
            FROM FavoritosUsuarios fu 
            WHERE fu.ProcedimentoID = p.ID AND fu.UsuarioID = :usuarioLogadoID
        ) AS Favorito
    FROM Procedimentos p
    LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID -- Junta a tabela para obter a cor
    LEFT JOIN Usuarios uDelegado ON uDelegado.ID = p.DelegadoID
    LEFT JOIN Usuarios uEscrivao ON uEscrivao.ID = p.EscrivaoID
    LEFT JOIN Delegacias d ON d.ID = p.DelegaciaID
    WHERE p.ID = :id
";
$usuarioLogadoID = $_SESSION['usuario_id'] ?? null;
$params['usuarioLogadoID'] = $usuarioLogadoID;

$stmt = $pdo->prepare($queryProcedimento);
$stmt->execute([
    'id' => $procedimentoID,
    'usuarioLogadoID' => $usuarioLogadoID
]);

$procedimento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$procedimento) {
    echo "<p class='text-center text-danger'>Procedimento não encontrado.</p>";
    include '../includes/footer.php';
    exit;
}

$queryMovimentacoes = "
    SELECT 
        mv.ID, 
        mv.Assunto, 
        mv.DataCriacao, 
        mv.DataVencimento,
        mv.Situacao,
        DATEDIFF(mv.DataVencimento, CURDATE()) AS dias_restantes,
        tm.Nome AS Tipo
    FROM Movimentacoes mv
    LEFT JOIN TiposMovimentacao tm ON mv.TipoID = tm.ID
    WHERE mv.ProcedimentoID = :id
    ORDER BY 
        CASE 
            WHEN mv.Situacao = 'Finalizado' THEN 1
            ELSE 0
        END ASC,
        mv.DataVencimento ASC
";



$stmtMovimentacoes = $pdo->prepare($queryMovimentacoes);
$stmtMovimentacoes->execute(['id' => $procedimentoID]);
$movimentacoes = $stmtMovimentacoes->fetchAll(PDO::FETCH_ASSOC);


// Consulta para buscar RAIs
$queryRAIs = "
    SELECT Numero, Descricao 
    FROM RAIs 
    WHERE ProcedimentoID = :id
";
$stmtRAIs = $pdo->prepare($queryRAIs);
$stmtRAIs->execute(['id' => $procedimentoID]);
$rais = $stmtRAIs->fetchAll(PDO::FETCH_ASSOC);

// Consulta para buscar Processos Judiciais
$queryProcessosJudiciais = "
    SELECT Numero, Descricao 
    FROM ProcessosJudiciais 
    WHERE ProcedimentoID = :id
";
$stmtProcessosJudiciais = $pdo->prepare($queryProcessosJudiciais);
$stmtProcessosJudiciais->execute(['id' => $procedimentoID]);
$processosJudiciais = $stmtProcessosJudiciais->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-5" style="max-width: 95%; margin: 0 auto;">


<?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

    
    
<div>
<!-- Título e botões -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Detalhes do Procedimento</h1>
    <div>
        <a href="editar_procedimento.php?id=<?= $procedimentoID ?>" class="btn btn-warning me-2">
            <i class="bi bi-pencil-fill me-1"></i> Editar Procedimento
        </a>
        <a href="anotacoes.php?id=<?= $procedimentoID ?>" class="btn btn-info me-2">
            <i class="bi bi-journal-text me-1"></i> Ver Anotações
        </a>
<?php $isFavorito = !empty($procedimento['Favorito']); ?>

<a href="#" 
   class="btn btn <?= $isFavorito ? 'btn-danger' : 'btn-secondary me-2' ?>" 
   onclick="gerenciarFavorito(<?= $procedimentoID?>, <?= $isFavorito ? "'remover'" : "'adicionar'" ?>)">
   <i class="bi <?= $isFavorito ? 'bi-star-fill' : 'bi-star' ?>"></i> 
   <?= $isFavorito ? 'Remover Favorito' : 'Adicionar aos Favoritos' ?>
</a>

    </div>
</div>




<!-- Detalhes do Procedimento -->
<!-- Detalhes do Procedimento -->


<div class="card mb-4">
    <div class="card-header bg-dark text-white">
       Detalhes do Procedimento
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Coluna 1 -->
            <div class="col-md-6">
                <!-- Seção: Informações Gerais -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">Informações Gerais</h5>
                    <p><strong>Tipo:</strong> <?= htmlspecialchars($procedimento['TipoID'] == 1 ? 'Inquérito Policial' : 'VPI') ?></p>
<p><strong>Número do Procedimento:</strong> 
    <a href="https://spp.ssp.go.gov.br/documentos?procedimentoId=<?= htmlspecialchars($procedimento['NumeroProcedimento']) ?>" 
       target="_blank" 
       class="text-decoration-none">
        <?= htmlspecialchars($procedimento['NumeroProcedimento']) ?>
    </a>
    <!-- Ícone de cópia para o número do procedimento -->
    <button type="button" 
            class="btn btn-link btn-sm p-0 ms-1 copy-btn" 
            data-copy-text="<?= htmlspecialchars($procedimento['NumeroProcedimento']) ?>"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="Copiar número do procedimento">
        <i class="bi bi-clipboard"></i>
    </button>
</p>

                    <p><strong>Data da Instauração:</strong> <?= htmlspecialchars(formatarDataBrasileira($procedimento['DataInstauracao'] ?? null)) ?></p>
                </div>

                <!-- Seção: Envolvidos -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">Envolvidos</h5>
                    <p><strong>Vítimas:</strong> <?= htmlspecialchars($procedimento['Vitimas'] ?? 'Nenhuma') ?></p>
                    <p><strong>Investigados:</strong> <?= htmlspecialchars($procedimento['Investigados'] ?? 'Ignorado(a)') ?></p>
                    <p><strong>Motivo Aparente:</strong> <?= htmlspecialchars($procedimento['MotivoAparente'] ?? 'Não informado') ?></p>
                </div>
            </div>

            <!-- Coluna 2 -->
            <div class="col-md-6">
                <!-- Seção: Delegacia e Situação -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">Delegacia e Situação</h5>
                    <p>
                        <strong>Situação:</strong>
                        <span class="badge <?= htmlspecialchars($procedimento['Cor'] ?? 'badge-secondary') ?>">
                            <?= htmlspecialchars($procedimento['Situacao'] ?? 'Não informada') ?>
                        </span>
                    </p>
                    <p><strong>Meios Empregados:</strong> <?= htmlspecialchars($procedimento['MeiosEmpregados'] ?? 'Nenhum') ?></p>
                    <p><strong>Delegado:</strong> <?= htmlspecialchars($procedimento['Delegado'] ?? 'Não informado') ?></p>
                    <p><strong>Escrivão:</strong> <?= htmlspecialchars($procedimento['Escrivao'] ?? 'Não informado') ?></p>
                    <p><strong>Delegacia:</strong> <?= htmlspecialchars($procedimento['Delegacia'] ?? 'Não informado') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- RAIs e Processos Judiciais -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white">Processos Judiciais e RAIs</div>
    <div class="card-body">
        <div class="row">
            <!-- Coluna: Processos Judiciais -->
            <div class="col-md-6">
                <h5>Processos Judiciais</h5>
                <?php if (empty($processosJudiciais)): ?>
                    <p class="text-muted">Nenhum Processo Judicial registrado.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($processosJudiciais as $processo): ?>
                            <li class="list-group-item">
                                <strong><?= formatar_numero_processo(htmlspecialchars($processo['Numero']) ?? 'Não informado') ?></strong>
                                <!-- Ícone de cópia para o processo judicial -->
                                <button type="button" 
                                        class="btn btn-link btn-sm p-0 ms-1 copy-btn" 
                                        data-copy-text="<?= htmlspecialchars($processo['Numero']) ?>"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Copiar número do processo">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                                <br> <?= htmlspecialchars($processo['Descricao'] ?? 'Sem descrição') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Coluna: RAIs -->
            <div class="col-md-6">
                <h5>RAIs</h5>
                <?php if (empty($rais)): ?>
                    <p class="text-muted">Nenhum RAI registrado.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($rais as $rai): ?>
                            <li class="list-group-item">
                                <a href="https://atendimento.ssp.go.gov.br/#/atendimento/<?= urlencode($rai['Numero']) ?>" 
                                   target="_blank" 
                                   class="text-decoration-none">
                                    <strong><?= htmlspecialchars($rai['Numero']) ?></strong>
                                </a>
                                <!-- Ícone de cópia para o RAI -->
                                <button type="button" 
                                        class="btn btn-link btn-sm p-0 ms-1 copy-btn" 
                                        data-copy-text="<?= htmlspecialchars($rai['Numero']) ?>"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Copiar número do RAI">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                                - <?= htmlspecialchars($rai['Descricao'] ?? 'Sem descrição') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


    <!-- Prazos -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            Movimentações
            <a href="mov.php?procedimento_id=<?= $procedimentoID ?>" 
               class="btn btn-success btn-sm float-end border border-white">
               Adicionar Movimentação
            </a>

        </div>
<div class="card-body">
<?php if (empty($movimentacoes)): ?>
    <p class="text-muted">Nenhuma movimentação registrada.</p>
<?php else: ?>
    <div class="card-body table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Assunto</th>
                <th>Situação</th>
                <th>Data do Vencimento</th>
                <th>Prazo (dias)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movimentacoes as $movimentacao): ?>
                <tr>
<td>
    <?php 
    // Define as cores fixas para tipos específicos
    $tipoCoresEspecificos = [
        'Prisões' => 'badge-danger',
        'Remessa de IP' => 'badge-danger',
        'Requisição MP' => 'badge-info'
    ];

    // Verifica se o tipo está na lista específica ou aplica a cor padrão
    $corTipo = $tipoCoresEspecificos[$movimentacao['Tipo']] ?? 'badge-warning';
    ?>
    <span class="badge <?= $corTipo ?>">
        <?= htmlspecialchars($movimentacao['Tipo'] ?? 'Não informado') ?>
    </span>
</td>


                    <td><?= htmlspecialchars($movimentacao['Assunto'] ?? 'Não informado') ?></td>
                    <td><?= htmlspecialchars($movimentacao['Situacao'] ?? 'Não informada') ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($movimentacao['DataVencimento'] ?? ''))) ?></td>

                    <td>
                        <?php if ($movimentacao['Situacao'] === 'Finalizado'): ?>
                            <span class="text-success">
                                <i class="fas fa-check"></i> Finalizado
                            </span>
                        <?php else: ?>
                            <?php 
                            $diasRestantes = $movimentacao['dias_restantes'];
                            $corBadge = 'text-bg-success'; // Cor padrão

                            // Ajusta a cor do badge com base nos dias restantes
                            if ($diasRestantes <= PRAZO_LARANJA) {
                                $corBadge = 'text-bg-danger'; // Vermelho
                            } elseif ($diasRestantes <= PRAZO_AMARELO) {
                                $corBadge = 'text-bg-warning'; // Amarelo
                            } elseif ($diasRestantes <= PRAZO_VERDE) {
                                $corBadge = 'text-bg-success'; // Verde
                            }
                            ?>
                            <span class="badge <?= $corBadge ?>">
                                <?= htmlspecialchars($diasRestantes) ?>
                            </span>
                        <?php endif; ?>
                    </td>
<td>
    <a href="mov.php?id=<?= $movimentacao['ID'] ?>&procedimento_id=<?= $procedimentoID ?>" 
       class="text-primary me-2" 
       title="Editar">
        <i class="fas fa-edit"></i>
    </a>
    <a href="excluir_movimentacao.php?id=<?= $movimentacao['ID'] ?>&procedimento_id=<?= $procedimentoID ?>" 
       class="text-danger me-2" 
       title="Excluir" 
       onclick="return confirm('Tem certeza que deseja excluir esta movimentação?');">
        <i class="fas fa-trash-alt"></i>
    </a>
    <a href="ver_movimentacao.php?id=<?= $movimentacao['ID'] ?>" 
       class="text-primary" 
       title="Ver Movimentação">
        <i class="fas fa-eye"></i>
    </a>
</td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
</div>


    </div>






<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        Cautelares
        <a href="adicionar_cautelar.php?procedimento_id=<?= $procedimentoID ?>" 
           class="btn btn-success btn-sm float-end border border-white">
           Adicionar Cautelar
        </a>
    </div>
    <div class="card-body">
        <?php
        $queryCautelares = "
            SELECT 
                sc.ID AS SolicitacaoID,
                sc.ProcessoJudicial,
                GROUP_CONCAT(DISTINCT tc.Nome ORDER BY tc.Nome ASC SEPARATOR '<br>') AS TiposCautelares,
                (
                    SELECT SUM(isc.QuantidadeSolicitada)
                    FROM ItensSolicitacaoCautelar isc
                    WHERE isc.SolicitacaoCautelarID = sc.ID
                ) AS QuantidadeSolicitada,
                (
                    SELECT IFNULL(SUM(cc.QuantidadeCumprida), 0)
                    FROM CumprimentosCautelares cc
                    WHERE cc.SolicitacaoCautelarID = sc.ID
                ) AS QuantidadeCumprida
            FROM SolicitacoesCautelares sc
            LEFT JOIN ItensSolicitacaoCautelar isc ON sc.ID = isc.SolicitacaoCautelarID
            LEFT JOIN TiposCautelar tc ON isc.TipoCautelarID = tc.ID
            WHERE sc.ProcedimentoID = :procedimentoID
            GROUP BY sc.ID
        ";

        $stmtCautelares = $pdo->prepare($queryCautelares);
        $stmtCautelares->execute(['procedimentoID' => $procedimentoID]);
        $cautelares = $stmtCautelares->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (empty($cautelares)): ?>
            <p class="text-muted">Nenhuma cautelar registrada para este procedimento.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($cautelares as $cautelar): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header bg-light text-dark">
                                <strong>Processo Judicial:</strong><br>
                                <?= htmlspecialchars($cautelar['ProcessoJudicial'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                                <!-- Ícone de cópia para o processo judicial da cautelar -->
                                <?php if (!empty($cautelar['ProcessoJudicial'])): ?>
                                    <button type="button" 
                                            class="btn btn-link btn-sm p-0 ms-1 copy-btn" 
                                            data-copy-text="<?= htmlspecialchars($cautelar['ProcessoJudicial']) ?>"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="Copiar número do processo">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <p><strong>Tipos de Cautelares:</strong><br><?= $cautelar['TiposCautelares'] ?></p>
                                <p><strong>Quantidade Solicitada:</strong> 
                                    <?= htmlspecialchars($cautelar['QuantidadeSolicitada'] ?? '0', ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <p><strong>Quantidade Cumprida:</strong> 
                                    <?= htmlspecialchars($cautelar['QuantidadeCumprida'] ?? '0', ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
<div class="card-footer text-end">
    <a href="adicionar_cumprimento.php?solicitacao_id=<?= $cautelar['SolicitacaoID'] ?>&procedimento_id=<?= $procedimentoID ?>&origem=procedimentos" 
       class="btn btn-sm btn-success">Cumprir</a>
    <a href="editar_cautelar.php?solicitacao_id=<?= $cautelar['SolicitacaoID'] ?>&procedimento_id=<?= $procedimentoID ?>" 
       class="btn btn-sm btn-warning">Editar</a>
    <a href="excluir_cautelar.php?solicitacao_id=<?= $cautelar['SolicitacaoID'] ?>&procedimento_id=<?= $procedimentoID ?>&origem=procedimentos" 
       class="btn btn-sm btn-danger" 
       onclick="return confirm('Tem certeza que deseja excluir esta cautelar?');">Excluir</a>
</div>



                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>





<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        Objetos
        <a href="adicionar_objeto.php?procedimento_id=<?= $procedimentoID ?>" 
           class="btn btn-success btn-sm float-end border border-white">
           Adicionar Objeto
        </a>
    </div>
    <div class="card-body">
        <?php
        // Consulta para buscar os objetos relacionados ao procedimento
        $queryObjetos = "
            SELECT 
                o.ID,
                t.Nome AS TipoObjeto,
                o.Descricao,
                o.Quantidade,
                o.LacreAtual,
                (
                    SELECT mo.TipoMovimentacaoID
                    FROM MovimentacoesObjeto mo
                    WHERE mo.ObjetoID = o.ID
                    ORDER BY mo.DataMovimentacao DESC, mo.ID DESC
                    LIMIT 1
                ) AS UltimaMovimentacaoID,
                (
                    SELECT tmo.Nome
                    FROM MovimentacoesObjeto mo
                    LEFT JOIN TiposMovimentacaoObjeto tmo ON mo.TipoMovimentacaoID = tmo.ID
                    WHERE mo.ObjetoID = o.ID
                    ORDER BY mo.DataMovimentacao DESC, mo.ID DESC
                    LIMIT 1
                ) AS UltimaMovimentacao,
                (
                    SELECT tmo.Cor
                    FROM MovimentacoesObjeto mo
                    LEFT JOIN TiposMovimentacaoObjeto tmo ON mo.TipoMovimentacaoID = tmo.ID
                    WHERE mo.ObjetoID = o.ID
                    ORDER BY mo.DataMovimentacao DESC, mo.ID DESC
                    LIMIT 1
                ) AS CorMovimentacao,
                (
                    SELECT mo.Observacao
                    FROM MovimentacoesObjeto mo
                    WHERE mo.ObjetoID = o.ID
                    ORDER BY mo.DataMovimentacao DESC, mo.ID DESC
                    LIMIT 1
                ) AS UltimaObservacao
            FROM Objetos o
            LEFT JOIN TiposObjeto t ON o.TipoObjetoID = t.ID
            WHERE o.ProcedimentoID = :procedimentoID
            ORDER BY o.ID DESC
        ";

        $stmtObjetos = $pdo->prepare($queryObjetos);
        $stmtObjetos->execute(['procedimentoID' => $procedimentoID]);
        $objetos = $stmtObjetos->fetchAll(PDO::FETCH_ASSOC);

        // Consulta para listar todas as situações disponíveis
        $querySituacoes = "SELECT ID, Nome FROM SituacoesObjeto";
        $stmtSituacoes = $pdo->query($querySituacoes);
        $situacoesDisponiveis = $stmtSituacoes->fetchAll(PDO::FETCH_ASSOC);

        // Consulta para listar todos os locais disponíveis
        $queryLocais = "SELECT ID, Nome FROM LocaisArmazenagem";
        $stmtLocais = $pdo->query($queryLocais);
        $locaisDisponiveis = $stmtLocais->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (empty($objetos)): ?>
            <p class="text-muted">Nenhum objeto registrado para este procedimento.</p>
        <?php else: ?>
            <div class="mb-3">
                <button type="button" id="btn-movimentar-lote" class="btn btn-info">
                    <i class="bi bi-arrow-left-right"></i> Movimentar em Lote
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="check-all"></th>
                            <th style="white-space: nowrap;">Tipo de Objeto</th>
                            <th>Descrição</th>
                            <th>Quantidade</th>
                            <th>Última Movimentação</th>
                            <th>Observação da Movimentação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($objetos as $objeto): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="objetos[]" value="<?= htmlspecialchars($objeto['ID']) ?>">
                                </td>
                                <td style="white-space: nowrap;">
                                    <?= htmlspecialchars($objeto['TipoObjeto'] ?? 'Não informado') ?>
                                    <?php if ($objeto['LacreAtual']): ?>
                                        <span class="badge bg-dark" data-lacre-atual="<?= htmlspecialchars($objeto['LacreAtual']) ?>">
                                            Lacre: <?= htmlspecialchars($objeto['LacreAtual']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($objeto['Descricao'] ?? 'Sem descrição') ?></td>
                                <td><?= htmlspecialchars($objeto['Quantidade'] ?? '0') ?></td>
                                <td>
                                    <?php if ($objeto['UltimaMovimentacao']): ?>
                                        <span class="badge <?= getBadgeClassMovimentacao($objeto['UltimaMovimentacao'], $objeto['CorMovimentacao']) ?>">
                                            <?= htmlspecialchars($objeto['UltimaMovimentacao']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Sem movimentação</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($objeto['UltimaObservacao']): ?>
                                        <small class="text-muted"><?= htmlspecialchars($objeto['UltimaObservacao']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Sem observação</small>
                                    <?php endif; ?>
                                </td>
                                <td>
<div class="d-flex gap-2">
    <a href="adicionar_objeto.php?objeto_id=<?= htmlspecialchars($objeto['ID']) ?>&procedimento_id=<?= htmlspecialchars($procedimentoID) ?>" 
       class="btn btn-sm btn-warning flex-fill">
        <i class="bi bi-pencil"></i> Editar
    </a>
    <button 
        type="button" 
        class="btn btn-sm btn-outline-dark flex-fill" 
        data-bs-toggle="modal" 
        data-bs-target="#modalCadeiaCustodia<?= htmlspecialchars($objeto['ID']) ?>">
        <i class="bi bi-clock-history"></i> Cadeia de Custódia
    </button>
    <button 
        type="button" 
        class="btn btn-danger btn-sm btn-excluir flex-fill" 
        data-id="<?= htmlspecialchars($objeto['ID']) ?>" 
        data-procedimento-id="<?= htmlspecialchars($procedimentoID) ?>" 
        data-action="excluir_objeto.php">
        <i class="bi bi-trash"></i> Excluir
    </button>
</div>

<!-- Modal da Cadeia de Custódia -->
<div class="modal fade" id="modalCadeiaCustodia<?= htmlspecialchars($objeto['ID']) ?>" tabindex="-1" aria-labelledby="modalCadeiaCustodiaLabel<?= htmlspecialchars($objeto['ID']) ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalCadeiaCustodiaLabel<?= htmlspecialchars($objeto['ID']) ?>">
                    <i class="bi bi-shield-lock me-2"></i>
                    Cadeia de Custódia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <!-- Cabeçalho com informações do objeto -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Detalhes do Objeto</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Tipo:</strong> <?= htmlspecialchars($objeto['TipoObjeto'] ?? 'Não informado') ?></p>
                                <p class="mb-1"><strong>Descrição:</strong> <?= htmlspecialchars($objeto['Descricao'] ?? 'Sem descrição') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Quantidade:</strong> <?= htmlspecialchars($objeto['Quantidade'] ?? '0') ?></p>
                                <p class="mb-1">
                                    <strong>Lacre Atual:</strong>
                                    <?php if ($objeto['LacreAtual']): ?>
                                        <span class="badge bg-dark"><?= htmlspecialchars($objeto['LacreAtual']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Não informado</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Histórico de Movimentações -->
                <h6 class="border-bottom pb-2 mb-3">
                    <i class="bi bi-clock-history me-2"></i>
                    Histórico de Movimentações
                </h6>

                <?php
                // Busca o histórico de movimentações do objeto
                $stmt = $pdo->prepare("
                    SELECT 
                        mo.*,
                        tmo.Nome as TipoMovimentacao,
                        tmo.Cor as CorMovimentacao,
                        u.Nome as UsuarioNome
                    FROM MovimentacoesObjeto mo
                    LEFT JOIN TiposMovimentacaoObjeto tmo ON mo.TipoMovimentacaoID = tmo.ID
                    LEFT JOIN Usuarios u ON mo.UsuarioID = u.ID
                    WHERE mo.ObjetoID = :objetoID
                    ORDER BY mo.DataMovimentacao DESC
                ");
                $stmt->execute([':objetoID' => $objeto['ID']]);
                $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if (empty($movimentacoes)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Nenhuma movimentação registrada.
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($movimentacoes as $mov): ?>
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-type badge <?= getBadgeClassMovimentacao($mov['TipoMovimentacao'], $mov['CorMovimentacao']) ?>">
                                            <?= htmlspecialchars($mov['TipoMovimentacao']) ?>
                                        </span>
                                        <span class="timeline-date">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?= formatarDataBrasileira($mov['DataMovimentacao']) ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($mov['Observacao'])): ?>
                                        <div class="timeline-observation">
                                            <i class="bi bi-info-circle me-1"></i>
                                            <?= htmlspecialchars($mov['Observacao']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="timeline-footer">
                                        <i class="bi bi-person me-1"></i>
                                        Registrado por: <?= htmlspecialchars($mov['UsuarioNome']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a href="imprimir_cadeia_custodia.php?objeto_id=<?= htmlspecialchars($objeto['ID']) ?>&procedimento_id=<?= htmlspecialchars($procedimentoID) ?>" 
                   class="btn btn-dark" 
                   target="_blank">
                    <i class="bi bi-printer"></i> Imprimir
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    <?php endif; ?>
</div>
</div>




</div>
</div>

<!-- Modal de Movimentação em Lote -->
<?php
// Busca os tipos de movimentação disponíveis
$queryTiposMovimentacao = "SELECT ID, Nome FROM TiposMovimentacaoObjeto ORDER BY Nome";
$stmtTiposMovimentacao = $pdo->query($queryTiposMovimentacao);
$tiposMovimentacao = $stmtTiposMovimentacao->fetchAll(PDO::FETCH_ASSOC);

// Debug: Imprime os tipos de movimentação
error_log("Tipos de Movimentação disponíveis:");
foreach ($tiposMovimentacao as $tipo) {
    error_log("ID: {$tipo['ID']}, Nome: {$tipo['Nome']}");
}
?>

<div class="modal fade" id="modalMovimentacaoLote" tabindex="-1" aria-labelledby="modalMovimentacaoLoteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMovimentacaoLoteLabel">Movimentar Objetos em Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="form-movimentacao-lote" action="movimentar_objetos_lote.php" method="POST">
                <input type="hidden" name="procedimento_id" value="<?= htmlspecialchars($procedimentoID) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="data_movimentacao" class="form-label">Data da Movimentação <span class="text-danger">*</span></label>
                        <input type="datetime-local" 
                               name="data_movimentacao" 
                               id="data_movimentacao" 
                               class="form-control" 
                               required 
                               value="<?= date('Y-m-d\TH:i') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="tipo_movimentacao" class="form-label">Tipo de Movimentação <span class="text-danger">*</span></label>
                        <select name="tipo_movimentacao" id="tipo_movimentacao" class="form-select" required>
                            <option value="">Selecione o tipo de movimentação</option>
                            <?php foreach ($tiposMovimentacao as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo['ID']) ?>" 
                                        data-nome="<?= htmlspecialchars($tipo['Nome']) ?>"
                                        data-id="<?= htmlspecialchars($tipo['ID']) ?>">
                                    <?= htmlspecialchars($tipo['Nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campos específicos para Retorno da Perícia -->
                    <div id="campos_pericia" style="display: none;">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Para objetos que retornam da perícia, informe o novo lacre.
                            O lacre anterior será preenchido automaticamente com o lacre atual do objeto.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="lacre_anterior" class="form-label">Lacre Anterior</label>
                                <input type="text" 
                                       name="lacre_anterior" 
                                       id="lacre_anterior" 
                                       class="form-control" 
                                       readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="novo_lacre" class="form-label">Novo Lacre <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="novo_lacre" 
                                       id="novo_lacre" 
                                       class="form-control" 
                                       placeholder="Lacre atual do objeto">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observacao" class="form-label">Observação da Movimentação</label>
                        <textarea name="observacao" id="observacao" class="form-control" rows="3" placeholder="Digite uma observação sobre a movimentação (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Movimentação</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Seleciona todos os botões de exclusão
    document.querySelectorAll('.btn-excluir').forEach(button => {
        button.addEventListener('click', function () {
            const objetoId = this.getAttribute('data-id'); // ID do objeto
            const procedimentoId = this.getAttribute('data-procedimento-id'); // ID do procedimento
            const actionUrl = this.getAttribute('data-action'); // URL para exclusão

            // Exibe o alerta simples
            if (confirm('Tem certeza de que deseja excluir este objeto?')) {
                // Requisição para excluir o objeto
                fetch(actionUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        objeto_id: objetoId,
                        procedimento_id: procedimentoId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Objeto excluído com sucesso.');
                        location.reload(); // Recarrega a página para atualizar a lista
                    } else {
                        alert('Erro ao excluir o objeto: ' + (data.message || 'Erro desconhecido.'));
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    alert('Ocorreu um erro ao tentar excluir o objeto.');
                });
            }
        });
    });
});
</script>



<script>
// Função para normalizar texto (remove acentos e converte para maiúsculas)
function normalizarTexto(texto) {
    return texto.normalize('NFD')
               .replace(/[\u0300-\u036f]/g, '')
               .toUpperCase();
}

// Script para controlar o campo de Local de Armazenagem
document.getElementById('tipo_movimentacao').addEventListener('change', function() {
    const tipoSelecionado = this.options[this.selectedIndex];
    const tipoId = tipoSelecionado.getAttribute('data-id');
    const nomeTipo = tipoSelecionado.getAttribute('data-nome');
    const camposPericia = document.getElementById('campos_pericia');
    const novoLacreInput = document.getElementById('novo_lacre');
    const lacreAnteriorInput = document.getElementById('lacre_anterior');

    // Verifica se é Retorno da Perícia
    if (tipoId === '3' || normalizarTexto(nomeTipo) === 'RETORNO DA PERICIA') {
        camposPericia.style.display = 'block';
        novoLacreInput.required = true;

        // Pega o lacre atual do primeiro objeto selecionado
        const checkboxSelecionado = document.querySelector('input[name="objetos[]"]:checked');
        if (checkboxSelecionado) {
            const objetoRow = checkboxSelecionado.closest('tr');
            const lacreAtual = objetoRow.querySelector('[data-lacre-atual]')?.getAttribute('data-lacre-atual');
            lacreAnteriorInput.value = lacreAtual || 'Não informado';
        }
    } else {
        camposPericia.style.display = 'none';
        novoLacreInput.required = false;
        lacreAnteriorInput.value = '';
    }

    // Reseta os campos
    selectLocalArmazenagem.value = '';
    inputLocalArmazenagemFixo.value = '';
    selectLocalArmazenagem.required = false;

    // Define o comportamento baseado no tipo de movimentação
    switch(normalizarTexto(nomeTipo)) {
        case 'DESTRUICAO':
            divLocalArmazenagem.style.display = 'none';
            inputLocalArmazenagemFixo.value = 'NAO_SE_APLICA';
            break;

        case 'DEVOLUCAO':
            divLocalArmazenagem.style.display = 'none';
            inputLocalArmazenagemFixo.value = 'NAO_SE_APLICA';
            break;

        case 'ENCAMINHAMENTO AO DEPOSITO JUDICIAL':
            divLocalArmazenagem.style.display = 'none';
            // Busca o ID do Depósito Judicial
            const depositoJudicial = Array.from(selectLocalArmazenagem.options).find(opt => 
                normalizarTexto(opt.textContent).includes('DEPOSITO JUDICIAL')
            );
            if (depositoJudicial) {
                inputLocalArmazenagemFixo.value = depositoJudicial.value;
            }
            break;

        case 'SAIDA PARA PERICIA':
            divLocalArmazenagem.style.display = 'none';
            // Busca o ID do Encaminhado para Perícia
            const encaminhadoPericia = Array.from(selectLocalArmazenagem.options).find(opt => 
                normalizarTexto(opt.textContent).includes('ENCAMINHADO PARA PERICIA')
            );
            if (encaminhadoPericia) {
                inputLocalArmazenagemFixo.value = encaminhadoPericia.value;
            }
            break;

        case 'ENTRADA':
        case 'OUTROS':
        case 'RETORNO DA PERICIA':
        case 'TRANSFERENCIA':
            divLocalArmazenagem.style.display = 'block';
            selectLocalArmazenagem.required = true;
            break;

        default:
            console.log('Tipo de movimentação não reconhecido:', normalizarTexto(nomeTipo));
            divLocalArmazenagem.style.display = 'none';
            inputLocalArmazenagemFixo.value = '';
    }
});

// Script para movimentação em lote
document.getElementById('btn-movimentar-lote').addEventListener('click', function() {
    const checkboxesMarcados = document.querySelectorAll('input[name="objetos[]"]:checked');
    
    if (checkboxesMarcados.length === 0) {
        Swal.fire({
            title: 'Atenção',
            text: 'Por favor, selecione pelo menos um objeto para movimentar.',
            icon: 'warning',
            confirmButtonText: 'Ok'
        });
        return;
    }

    // Verifica se todos os objetos selecionados têm o mesmo lacre atual
    const lacresAtuais = new Set();
    checkboxesMarcados.forEach(checkbox => {
        const objetoId = checkbox.value;
        const objetoRow = checkbox.closest('tr');
        const lacreAtual = objetoRow.querySelector('[data-lacre-atual]')?.getAttribute('data-lacre-atual');
        if (lacreAtual) {
            lacresAtuais.add(lacreAtual);
        }
    });

    // Se houver mais de um lacre diferente, mostra um aviso
    if (lacresAtuais.size > 1) {
        Swal.fire({
            title: 'Atenção',
            text: 'Os objetos selecionados possuem lacres diferentes. Deseja continuar mesmo assim?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const modal = new bootstrap.Modal(document.getElementById('modalMovimentacaoLote'));
                modal.show();
            }
        });
    } else {
        const modal = new bootstrap.Modal(document.getElementById('modalMovimentacaoLote'));
        modal.show();
    }
});

document.getElementById('form-movimentacao-lote').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const tipoMovimentacao = document.getElementById('tipo_movimentacao');
    const nomeTipo = normalizarTexto(tipoMovimentacao.options[tipoMovimentacao.selectedIndex].getAttribute('data-nome'));
    const novoLacre = document.getElementById('novo_lacre').value;
    
    // Validação específica para Retorno da Perícia
    if (nomeTipo === 'RETORNO DA PERICIA' && !novoLacre) {
        Swal.fire({
            title: 'Erro',
            text: 'Para Retorno da Perícia, o novo lacre é obrigatório.',
            icon: 'error',
            confirmButtonText: 'Ok'
        });
        return;
    }

    const checkboxesMarcados = document.querySelectorAll('input[name="objetos[]"]:checked');
    const dataMovimentacao = document.getElementById('data_movimentacao').value;
    
    if (checkboxesMarcados.length === 0) {
        Swal.fire({
            title: 'Erro',
            text: 'Por favor, selecione pelo menos um objeto para movimentar.',
            icon: 'error',
            confirmButtonText: 'Ok'
        });
        return;
    }

    if (!dataMovimentacao) {
        Swal.fire({
            title: 'Erro',
            text: 'Por favor, informe a data da movimentação.',
            icon: 'error',
            confirmButtonText: 'Ok'
        });
        return;
    }

    // Remove objetos anteriores do formulário (se houver)
    const objetosAnteriores = this.querySelectorAll('input[name="objetos[]"]');
    objetosAnteriores.forEach(input => input.remove());

    // Adiciona os objetos selecionados ao formulário
    checkboxesMarcados.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'objetos[]';
        input.value = checkbox.value;
        this.appendChild(input);
    });

    // Formata a data para exibição
    const dataFormatada = new Date(dataMovimentacao).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    // Atualizar a mensagem de confirmação para incluir informações do lacre
    let mensagemConfirmacao = `
        <div class="text-start">
            <p><strong>Quantidade de objetos:</strong> ${checkboxesMarcados.length}</p>
            <p><strong>Data da movimentação:</strong> ${dataFormatada}</p>
            <p><strong>Tipo de movimentação:</strong> ${tipoMovimentacao.options[tipoMovimentacao.selectedIndex].text}</p>
    `;

    if (nomeTipo === 'RETORNO DA PERICIA') {
        mensagemConfirmacao += `
            <p><strong>Novo Lacre:</strong> ${novoLacre}</p>
            <p><strong>Lacre Anterior:</strong> ${document.getElementById('lacre_anterior').value || 'Não informado'}</p>
        `;
    }

    if (document.getElementById('observacao').value) {
        mensagemConfirmacao += `<p><strong>Observação:</strong> ${document.getElementById('observacao').value}</p>`;
    }

    mensagemConfirmacao += '</div>';

    Swal.fire({
        title: 'Confirmação',
        html: mensagemConfirmacao,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, movimentar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});

// Limpa o formulário quando o modal é fechado
document.getElementById('modalMovimentacaoLote').addEventListener('hidden.bs.modal', function() {
    document.getElementById('form-movimentacao-lote').reset();
    document.getElementById('data_movimentacao').value = new Date().toISOString().slice(0, 16);
    document.getElementById('campos_pericia').style.display = 'none';
    document.getElementById('novo_lacre').required = false;
});

// Script para selecionar todos os checkboxes
document.getElementById('check-all').addEventListener('change', function() {
    document.querySelectorAll('input[name="objetos[]"]').forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>



<script>

function gerenciarFavorito(procedimentoId, acao) {
    fetch('favoritos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            procedimento_id: procedimentoId,
            acao: acao
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Favorito atualizado com sucesso!');
            location.reload(); // Atualiza a página para refletir a mudança
        } else {
            alert('Erro: ' + (data.message || 'Não foi possível atualizar o favorito.'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro na comunicação com o servidor.');
    });
}

</script>



<?php include '../includes/footer.php'; ?>

<style>
/* Estilos para a Timeline */
.timeline {
    position: relative;
    padding: 20px 0;
}
.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 15px;
    height: 100%;
    width: 2px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
    margin-left: 30px;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #0d6efd;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #0d6efd;
}
.timeline-item:last-child {
    margin-bottom: 0;
}
.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.timeline-date {
    font-size: 0.85rem;
    color: #6c757d;
}
.timeline-type {
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
}
.timeline-observation {
    background: #fff;
    border-left: 3px solid #0d6efd;
    padding: 10px;
    margin-top: 10px;
    font-size: 0.9rem;
    color: #495057;
}
.timeline-footer {
    font-size: 0.85rem;
    color: #6c757d;
    border-top: 1px solid #e9ecef;
    padding-top: 10px;
    margin-top: 10px;
}

/* Ajustes para o Modal */
.modal-lg {
    max-width: 800px;
}
.modal-header {
    border-bottom: none;
}
.modal-footer {
    border-top: none;
}

/* Estilo para o botão de cópia */
.copy-btn {
    color: #6c757d;
    transition: all 0.2s ease;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    margin-left: 0.25rem;
}

.copy-btn:hover {
    color: #007bff;
    transform: scale(1.1);
}

.copy-btn.text-success {
    color: #28a745 !important;
}

.copy-btn:focus {
    outline: none;
    box-shadow: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Função para copiar texto
    function copyToClipboard(text) {
        // Criar um elemento textarea temporário
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';  // Evitar scroll
        textarea.style.opacity = '0';       // Tornar invisível
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            // Executar o comando de cópia
            const successful = document.execCommand('copy');
            if (!successful) {
                throw new Error('Falha ao copiar');
            }
            return true;
        } catch (err) {
            console.error('Erro ao copiar:', err);
            return false;
        } finally {
            // Remover o textarea temporário
            document.body.removeChild(textarea);
        }
    }

    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });

    // Adicionar evento de clique para os botões de cópia
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Prevenir comportamento padrão do botão
            
            const textToCopy = this.getAttribute('data-copy-text');
            const tooltip = bootstrap.Tooltip.getInstance(this);
            
            // Tentar copiar o texto
            if (copyToClipboard(textToCopy)) {
                // Atualizar o ícone temporariamente
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                
                // Mudar para ícone de check
                icon.className = 'bi bi-check2';
                this.classList.add('text-success');
                
                // Atualizar tooltip
                if (tooltip) {
                    tooltip.dispose();
                }
                
                const newTooltip = new bootstrap.Tooltip(this, {
                    title: 'Copiado!',
                    trigger: 'manual'
                });
                newTooltip.show();
                
                // Restaurar após 2 segundos
                setTimeout(() => {
                    icon.className = originalClass;
                    this.classList.remove('text-success');
                    if (newTooltip) {
                        newTooltip.dispose();
                    }
                    new bootstrap.Tooltip(this, {
                        title: this.getAttribute('title'),
                        trigger: 'hover'
                    });
                }, 2000);
            } else {
                // Mostrar mensagem de erro
                if (tooltip) {
                    tooltip.dispose();
                }
                
                const errorTooltip = new bootstrap.Tooltip(this, {
                    title: 'Erro ao copiar!',
                    trigger: 'manual'
                });
                errorTooltip.show();
                
                setTimeout(() => {
                    if (errorTooltip) {
                        errorTooltip.dispose();
                    }
                    new bootstrap.Tooltip(this, {
                        title: this.getAttribute('title'),
                        trigger: 'hover'
                    });
                }, 2000);
            }
        });
    });
});
</script>
