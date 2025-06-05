<?php
include '../includes/header.php'; // Inclui a navbar e configurações globais

// Verificar e definir constantes para gestão de prazos se não existirem
if (!defined('PRAZO_VERDE')) define('PRAZO_VERDE', 15);    
if (!defined('PRAZO_AMARELO')) define('PRAZO_AMARELO', 10);
if (!defined('PRAZO_LARANJA')) define('PRAZO_LARANJA', 5);   

// Obter o ID do procedimento a partir da URL
$procedimentoID = $_GET['id'] ?? null;
if (!$procedimentoID) {
    echo "<div class='alert alert-danger shadow-sm' role='alert'>
            <i class='bi bi-exclamation-triangle-fill me-2'></i> Procedimento não encontrado.
          </div>";
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
        sp.Cor AS Cor, 
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
    LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID 
    LEFT JOIN Usuarios uDelegado ON uDelegado.ID = p.DelegadoID
    LEFT JOIN Usuarios uEscrivao ON uEscrivao.ID = p.EscrivaoID
    LEFT JOIN Delegacias d ON d.ID = p.DelegaciaID
    WHERE p.ID = :id
";
$usuarioLogadoID = $_SESSION['usuario_id'] ?? null;

$stmt = $pdo->prepare($queryProcedimento);
$stmt->execute([
    'id' => $procedimentoID,
    'usuarioLogadoID' => $usuarioLogadoID
]);

$procedimento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$procedimento) {
    echo "<div class='alert alert-danger shadow-sm' role='alert'>
            <i class='bi bi-exclamation-triangle-fill me-2'></i> Procedimento não encontrado.
          </div>";
    include '../includes/footer.php';
    exit;
}

// Demais consultas para buscar movimentações, RAIs, processos judiciais, etc...
// [Mantenha o restante das consultas SQL conforme o código original]

// Consulta para movimentações
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

// Buscar RAIs e processos judiciais
$queryRAIs = "SELECT Numero, Descricao FROM RAIs WHERE ProcedimentoID = :id";
$stmtRAIs = $pdo->prepare($queryRAIs);
$stmtRAIs->execute(['id' => $procedimentoID]);
$rais = $stmtRAIs->fetchAll(PDO::FETCH_ASSOC);

$queryProcessosJudiciais = "SELECT Numero, Descricao FROM ProcessosJudiciais WHERE ProcedimentoID = :id";
$stmtProcessosJudiciais = $pdo->prepare($queryProcessosJudiciais);
$stmtProcessosJudiciais->execute(['id' => $procedimentoID]);
$processosJudiciais = $stmtProcessosJudiciais->fetchAll(PDO::FETCH_ASSOC);

// Buscar cautelares
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

// Buscar objetos
$queryObjetos = "
    SELECT 
        o.ID,
        t.Nome AS TipoObjeto,
        o.Descricao,
        o.Quantidade,
        s.Nome AS Situacao,
        l.Nome AS LocalArmazenagem
    FROM Objetos o
    LEFT JOIN TiposObjeto t ON o.TipoObjetoID = t.ID
    LEFT JOIN SituacoesObjeto s ON o.SituacaoID = s.ID
    LEFT JOIN LocaisArmazenagem l ON o.LocalArmazenagemID = l.ID
    WHERE o.ProcedimentoID = :procedimentoID
    ORDER BY o.ID DESC
";

$stmtObjetos = $pdo->prepare($queryObjetos);
$stmtObjetos->execute(['procedimentoID' => $procedimentoID]);
$objetos = $stmtObjetos->fetchAll(PDO::FETCH_ASSOC);

// Consulta para listar situações disponíveis e locais de armazenagem
$querySituacoes = "SELECT ID, Nome FROM SituacoesObjeto ORDER BY Nome";
$stmtSituacoes = $pdo->query($querySituacoes);
$situacoesDisponiveis = $stmtSituacoes->fetchAll(PDO::FETCH_ASSOC);

$queryLocais = "SELECT ID, Nome FROM LocaisArmazenagem ORDER BY Nome";
$stmtLocais = $pdo->query($queryLocais);
$locaisDisponiveis = $stmtLocais->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Adicione CSS personalizado no cabeçalho -->
<style>
    :root {
        --primary-color: #0d6efd;
        --secondary-color: #6c757d;
        --success-color: #198754;
        --info-color: #0dcaf0;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-color: #f8f9fa;
        --dark-color: #212529;
    }
    
    .procedimento-card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }
    
    .procedimento-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        border-top-left-radius: 8px !important;
        border-top-right-radius: 8px !important;
        font-weight: 500;
    }
    
    .list-group-item-action:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    .btn {
        border-radius: 4px;
        font-weight: 500;
    }
    
    .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-icon i {
        margin-right: 6px;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: none;
        font-weight: 500;
    }
    
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .cautelar-card {
        height: 100%;
        border: none;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }
    
    .cautelar-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    
    .btn-outline-primary, .btn-outline-danger, .btn-outline-warning, .btn-outline-info {
        border-width: 1.5px;
    }
    
    .floating-action-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1030;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
    }
    
    .custom-tab {
        padding: 0.75rem 1.25rem;
        border-radius: 0.375rem;
        margin-right: 0.5rem;
        cursor: pointer;
        font-weight: 500;
    }
    
    .custom-tab.active {
        background-color: #fff;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .section-title {
        font-weight: 500;
        position: relative;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background-color: var(--primary-color);
        border-radius: 3px;
    }
    
    .info-list {
        list-style: none;
        padding-left: 0;
    }
    
    .info-list li {
        margin-bottom: 0.5rem;
        padding: 0.5rem;
        border-radius: 4px;
    }
    
    .info-list li:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .info-list-label {
        font-weight: 500;
        color: #6c757d;
    }
    
    /* Cores para badges de situação */
    .badge-finalizado {
        background-color: #198754 !important;
        color: white !important;
    }
    
    .badge-pendente {
        background-color: #0d6efd !important;
        color: white !important;
    }
    
    .badge-atrasado {
        background-color: #dc3545 !important;
        color: white !important;
    }
    
    /* Animações para elementos */
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Progress bar estilizado */
    .progress {
        height: 8px;
        border-radius: 4px;
    }
    
    /* Container principal com espaçamento melhorado */
    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1.5rem;
    }
</style>

<div class="main-container fade-in">
    <!-- Alertas de Sucesso/Erro -->
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Cabeçalho de Procedimento -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?= htmlspecialchars($procedimento['TipoID'] == 1 ? 'Inquérito Policial' : 'VPI') ?> - Detalhes</h1>
            <p class="text-muted mb-0">
                Número: <a href="https://spp.ssp.go.gov.br/documentos?procedimentoId=<?= htmlspecialchars($procedimento['NumeroProcedimento']) ?>" 
                          target="_blank" class="fw-bold text-decoration-none">
                    <?= htmlspecialchars($procedimento['NumeroProcedimento']) ?>
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </p>
        </div>
        
        <div class="d-flex gap-2">
            <?php $isFavorito = !empty($procedimento['Favorito']); ?>
            <button class="btn <?= $isFavorito ? 'btn-danger' : 'btn-outline-secondary' ?> btn-icon" 
                   onclick="gerenciarFavorito(<?= $procedimentoID ?>, <?= $isFavorito ? '\'remover\'' : '\'adicionar\'' ?>)">
                <i class="bi <?= $isFavorito ? 'bi-star-fill' : 'bi-star' ?>"></i>
                <?= $isFavorito ? 'Desfavoritar' : 'Favoritar' ?>
            </button>
            
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle btn-icon" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear"></i> Ações
                </button>
                <ul class="dropdown-menu shadow-sm" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="editar_procedimento.php?id=<?= $procedimentoID ?>">
                        <i class="bi bi-pencil-fill me-2"></i> Editar Procedimento
                    </a></li>
                    <li><a class="dropdown-item" href="anotacoes.php?id=<?= $procedimentoID ?>">
                        <i class="bi bi-journal-text me-2"></i> Ver Anotações
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="gerar_relatorio.php?id=<?= $procedimentoID ?>">
                        <i class="bi bi-file-pdf me-2"></i> Gerar Relatório
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Navegação por Abas -->
    <ul class="nav nav-tabs mb-4" id="procedimentoTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="resumo-tab" data-bs-toggle="tab" data-bs-target="#resumo" type="button" role="tab" aria-controls="resumo" aria-selected="true">
                <i class="bi bi-info-circle me-1"></i> Resumo
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="movimentacoes-tab" data-bs-toggle="tab" data-bs-target="#movimentacoes" type="button" role="tab" aria-controls="movimentacoes" aria-selected="false">
                <i class="bi bi-list-check me-1"></i> Movimentações
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cautelares-tab" data-bs-toggle="tab" data-bs-target="#cautelares" type="button" role="tab" aria-controls="cautelares" aria-selected="false">
                <i class="bi bi-lock me-1"></i> Cautelares
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="objetos-tab" data-bs-toggle="tab" data-bs-target="#objetos" type="button" role="tab" aria-controls="objetos" aria-selected="false">
                <i class="bi bi-box me-1"></i> Objetos
            </button>
        </li>
    </ul>

    <!-- Conteúdo das Abas -->
    <div class="tab-content" id="procedimentoTabsContent">
        <!-- Aba de Resumo -->
        <div class="tab-pane fade show active" id="resumo" role="tabpanel" aria-labelledby="resumo-tab">
            <div class="row g-4">
                <!-- Painel de Informações Gerais -->
                <div class="col-lg-6">
                    <div class="card procedimento-card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-info-circle me-2"></i> Informações Gerais
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                            <i class="bi bi-calendar2-event fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Data da Instauração</small>
                                            <strong><?= htmlspecialchars(date('d/m/Y', strtotime($procedimento['DataInstauracao'] ?? 'now'))) ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                            <i class="bi bi-tag fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Situação</small>
                                            <span class="badge <?= htmlspecialchars($procedimento['Cor'] ?? 'bg-secondary') ?>">
                                                <?= htmlspecialchars($procedimento['Situacao'] ?? 'Não informada') ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                            <i class="bi bi-building fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Delegacia</small>
                                            <strong><?= htmlspecialchars($procedimento['Delegacia'] ?? 'Não informada') ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                            <i class="bi bi-person fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Delegado</small>
                                            <strong><?= htmlspecialchars($procedimento['Delegado'] ?? 'Não informado') ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                            <i class="bi bi-person-vcard fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Escrivão</small>
                                            <strong><?= htmlspecialchars($procedimento['Escrivao'] ?? 'Não informado') ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                            <i class="bi bi-tools fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Meios Empregados</small>
                                            <strong><?= htmlspecialchars($procedimento['MeiosEmpregados'] ?? 'Nenhum') ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Painel de Envolvidos -->
                <div class="col-lg-6">
                    <div class="card procedimento-card mb-4">
                        <div class="card-header bg-info text-white">
                            <i class="bi bi-people me-2"></i> Envolvidos
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted fw-bold mb-2"><i class="bi bi-person-x me-1"></i> Investigados:</label>
                                <div class="p-2 border-start border-3 border-info rounded bg-light">
                                    <?= htmlspecialchars($procedimento['Investigados'] ?? 'Ignorado(a)') ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted fw-bold mb-2"><i class="bi bi-person-arms-up me-1"></i> Vítimas:</label>
                                <div class="p-2 border-start border-3 border-warning rounded bg-light">
                                    <?= htmlspecialchars($procedimento['Vitimas'] ?? 'Nenhuma') ?>
                                </div>
                            </div>
                            <div>
                                <label class="text-muted fw-bold mb-2"><i class="bi bi-question-circle me-1"></i> Motivo Aparente:</label>
                                <div class="p-2 border-start border-3 border-secondary rounded bg-light">
                                    <?= htmlspecialchars($procedimento['MotivoAparente'] ?? 'Não informado') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Painel de Processos e RAIs -->
                <div class="col-12">
                    <div class="card procedimento-card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <i class="bi bi-file-earmark-text me-2"></i> Processos Judiciais e RAIs
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Coluna: Processos Judiciais -->
                                <div class="col-md-6">
                                    <h5 class="mb-3 section-title">Processos Judiciais</h5>
                                    <?php if (empty($processosJudiciais)): ?>
                                        <div class="alert alert-light text-muted">
                                            <i class="bi bi-info-circle me-2"></i> Nenhum Processo Judicial registrado.
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group">
                                            <?php foreach ($processosJudiciais as $processo): ?>
                                                <div class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1"><?= htmlspecialchars($processo['Numero'] ?? 'Não informado') ?></h6>
                                                    </div>
                                                    <p class="mb-1 small text-muted"><?= htmlspecialchars($processo['Descricao'] ?? 'Sem descrição') ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Coluna: RAIs -->
                                <div class="col-md-6">
                                    <h5 class="mb-3 section-title">RAIs</h5>
                                    <?php if (empty($rais)): ?>
                                        <div class="alert alert-light text-muted">
                                            <i class="bi bi-info-circle me-2"></i> Nenhum RAI registrado.
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group">
                                            <?php foreach ($rais as $rai): ?>
                                                <div class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1">
                                                            <a href="https://atendimento.ssp.go.gov.br/#/atendimento/<?= urlencode($rai['Numero']) ?>" 
                                                               target="_blank" 
                                                               class="text-decoration-none">
                                                               <i class="bi bi-link-45deg me-1"></i>
                                                               <?= htmlspecialchars($rai['Numero']) ?>
                                                            </a>
                                                        </h6>
                                                    </div>
                                                    <p class="mb-1 small text-muted"><?= htmlspecialchars($rai['Descricao'] ?? 'Sem descrição') ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Resumo das movimentações recentes -->
                <div class="col-12">
                    <div class="card procedimento-card mb-4">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-clock-history me-2"></i> Movimentações Recentes
                            </div>
                            <button class="btn btn-sm btn-dark" id="verTodasMovimentacoes">
                                Ver Todas <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($movimentacoes)): ?>
                                <div class="alert alert-light m-3 text-muted">
                                    <i class="bi bi-info-circle me-2"></i> Nenhuma movimentação registrada.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Assunto</th>
                                                <th>Prazo</th>
                                                <th>Situação</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            // Limita a 5 movimentações recentes
                                            $movimentacoesRecentes = array_slice($movimentacoes, 0, 5);
                                            foreach ($movimentacoesRecentes as $mov): 
                                                // Define classe para o prazo
                                                $classPrazo = 'bg-success';
                                                if ($mov['dias_restantes'] <= PRAZO_LARANJA) {
                                                    $classPrazo = 'bg-danger';
                                                } elseif ($mov['dias_restantes'] <= PRAZO_AMARELO) {
                                                    $classPrazo = 'bg-warning text-dark';
                                                }
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                        $tipoCores = [
                                                            'Prisões' => 'bg-danger',
                                                            'Remessa de IP' => 'bg-danger',
                                                            'Requisição MP' => 'bg-info'
                                                        ];
                                                        $corTipo = $tipoCores[$mov['Tipo']] ?? 'bg-warning text-dark';
                                                        ?>
                                                        <span class="badge <?= $corTipo ?>">
                                                            <?= htmlspecialchars($mov['Tipo'] ?? 'Não informado') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($mov['Assunto'] ?? 'Não informado') ?></td>
                                                    <td>
                                                        <?php if ($mov['Situacao'] === 'Finalizado'): ?>
                                                            <span class="badge bg-success">Finalizado</span>
                                                        <?php else: ?>
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge <?= $classPrazo ?> me-2">
                                                                    <?= htmlspecialchars($mov['dias_restantes']) ?>
                                                                </span>
                                                                <small class="text-muted">
                                                                    <?= htmlspecialchars(date('d/m/Y', strtotime($mov['DataVencimento']))) ?>
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($mov['Situacao'] === 'Finalizado'): ?>
                                                            <i class="bi bi-check-circle-fill text-success"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-hourglass-split text-warning"></i>
                                                        <?php endif; ?>
                                                        <?= htmlspecialchars($mov['Situacao']) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aba de Movimentações -->
        <div class="tab-pane fade" id="movimentacoes" role="tabpanel" aria-labelledby="movimentacoes-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Movimentações</h4>
                <a href="mov.php?procedimento_id=<?= $procedimentoID ?>" class="btn btn-success">
                    <i class="bi bi-plus-lg me-1"></i> Nova Movimentação
                </a>
            </div>
            
            <?php if (empty($movimentacoes)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill me-2"></i> Nenhuma movimentação registrada.
                </div>
            <?php else: ?>
                <div class="card procedimento-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Assunto</th>
                                        <th>Situação</th>
                                        <th>Data Vencimento</th>
                                        <th>Prazo (dias)</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($movimentacoes as $movimentacao): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $tipoCoresEspecificos = [
                                                    'Prisões' => 'bg-danger',
                                                    'Remessa de IP' => 'bg-danger',
                                                    'Requisição MP' => 'bg-info'
                                                ];
                                                $corTipo = $tipoCoresEspecificos[$movimentacao['Tipo']] ?? 'bg-warning text-dark';
                                                ?>
                                                <span class="badge <?= $corTipo ?>">
                                                    <?= htmlspecialchars($movimentacao['Tipo'] ?? 'Não informado') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($movimentacao['Assunto'] ?? 'Não informado') ?></td>
                                            <td>
                                                <span class="badge <?= $movimentacao['Situacao'] === 'Finalizado' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                    <?= htmlspecialchars($movimentacao['Situacao'] ?? 'Não informada') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($movimentacao['DataVencimento'] ?? ''))) ?></td>
                                            <td>
                                                <?php if ($movimentacao['Situacao'] === 'Finalizado'): ?>
                                                    <span class="text-success">
                                                        <i class="bi bi-check-circle-fill"></i> Finalizado
                                                    </span>
                                                <?php else: ?>
                                                    <?php 
                                                    $diasRestantes = $movimentacao['dias_restantes'];
                                                    $corBadge = 'bg-success';
                                                    
                                                    if ($diasRestantes <= PRAZO_LARANJA) {
                                                        $corBadge = 'bg-danger';
                                                    } elseif ($diasRestantes <= PRAZO_AMARELO) {
                                                        $corBadge = 'bg-warning text-dark';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $corBadge ?> fs-6">
                                                        <?= htmlspecialchars($diasRestantes) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="mov.php?id=<?= $movimentacao['ID'] ?>&procedimento_id=<?= $procedimentoID ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="ver_movimentacao.php?id=<?= $movimentacao['ID'] ?>" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Ver Detalhes">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button"
                                                           class="btn btn-sm btn-outline-danger excluir-movimentacao" 
                                                           data-id="<?= $movimentacao['ID'] ?>"
                                                           data-procedimento-id="<?= $procedimentoID ?>"
                                                           data-bs-toggle="tooltip" 
                                                           title="Excluir">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

<!-- Seção de Cautelares -->
<section id="secao-cautelares" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title mb-0">Cautelares</h3>
        <a href="adicionar_cautelar.php?procedimento_id=<?= $procedimentoID ?>" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> Nova Cautelar
        </a>
    </div>
    
    <?php if (empty($cautelares)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill me-2"></i> Nenhuma cautelar registrada para este procedimento.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($cautelares as $cautelar): ?>
                <div class="col-md-4">
                    <div class="card cautelar-card h-100">
                        <div class="card-header bg-dark text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-file-text me-2"></i> Processo</span>
                                <span class="badge bg-light text-dark">
                                    <?php 
                                    $cumprimento = $cautelar['QuantidadeSolicitada'] > 0 
                                        ? round(($cautelar['QuantidadeCumprida'] / $cautelar['QuantidadeSolicitada']) * 100) 
                                        : 0;
                                    echo $cumprimento . '%';
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="fw-bold mb-3"><?= htmlspecialchars($cautelar['ProcessoJudicial'] ?? 'Não informado') ?></p>
                            
                            <p class="mb-2"><strong>Tipos de Cautelares:</strong></p>
                            <div class="mb-3 p-2 border-start border-3 border-dark bg-light rounded">
                                <?= $cautelar['TiposCautelares'] ?>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Progresso de Cumprimento:</span>
                                    <span class="fw-bold">
                                        <?= htmlspecialchars($cautelar['QuantidadeCumprida'] ?? '0') ?> 
                                        de 
                                        <?= htmlspecialchars($cautelar['QuantidadeSolicitada'] ?? '0') ?>
                                    </span>
                                </div>
                                <div class="progress">
                                    <?php 
                                    $progresso = $cautelar['QuantidadeSolicitada'] > 0 
                                        ? ($cautelar['QuantidadeCumprida'] / $cautelar['QuantidadeSolicitada']) * 100 
                                        : 0;
                                    
                                    $progressoClass = 'bg-success';
                                    if ($progresso < 50) {
                                        $progressoClass = 'bg-danger';
                                    } elseif ($progresso < 100) {
                                        $progressoClass = 'bg-warning';
                                    }
                                    ?>
                                    <div class="progress-bar <?= $progressoClass ?>" 
                                         role="progressbar" 
                                         style="width: <?= $progresso ?>%" 
                                         aria-valuenow="<?= $progresso ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="editar_cautelar.php?solicitacao_id=<?= $cautelar['SolicitacaoID'] ?>&procedimento_id=<?= $procedimentoID ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </a>
                                <div>
                                    <a href="adicionar_cumprimento.php?solicitacao_id=<?= $cautelar['SolicitacaoID'] ?>&procedimento_id=<?= $procedimentoID ?>&origem=procedimentos" 
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle me-1"></i> Cumprir
                                    </a>
                                    <button type="button"
                                           class="btn btn-sm btn-outline-danger excluir-cautelar"
                                           data-id="<?= $cautelar['SolicitacaoID'] ?>"
                                           data-procedimento-id="<?= $procedimentoID ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Seção de Objetos -->
<section id="secao-objetos" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title mb-0">Objetos Apreendidos</h3>
        <a href="adicionar_objeto.php?procedimento_id=<?= $procedimentoID ?>" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> Novo Objeto
        </a>
    </div>
    
    <?php if (empty($objetos)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill me-2"></i> Nenhum objeto registrado para este procedimento.
        </div>
    <?php else: ?>
        <form id="form-editar-situacao-local" method="POST" action="editar_situacao_local_em_massa.php">
            <input type="hidden" name="procedimento_id" value="<?= htmlspecialchars($procedimentoID) ?>">
            
            <div class="card procedimento-card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-pencil-square me-2"></i> Edição em Massa
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="nova_situacao" class="form-label">Nova Situação</label>
                            <select name="nova_situacao" id="nova_situacao" class="form-select" required>
                                <option value="">Selecione a nova situação</option>
                                <?php foreach ($situacoesDisponiveis as $situacao): ?>
                                    <option value="<?= htmlspecialchars($situacao['ID']) ?>">
                                        <?= htmlspecialchars($situacao['Nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="novo_local" class="form-label">Novo Local de Armazenagem</label>
                            <select name="novo_local" id="novo_local" class="form-select" required>
                                <option value="">Selecione o novo local</option>
                                <?php foreach ($locaisDisponiveis as $local): ?>
                                    <option value="<?= htmlspecialchars($local['ID']) ?>">
                                        <?= htmlspecialchars($local['Nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" id="btn-confirm-update-local" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-repeat me-2"></i> Atualizar Selecionados
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card procedimento-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" style="min-width: 800px;">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">
                                        <div class="form-check">
                                            <input type="checkbox" id="check-all" class="form-check-input">
                                        </div>
                                    </th>
                                    <th style="width: 15%;">Tipo de Objeto</th>
                                    <th style="width: 30%;">Descrição</th>
                                    <th style="width: 10%;">Quantidade</th>
                                    <th style="width: 15%;">Situação</th>
                                    <th style="width: 15%;">Local de Armazenagem</th>
                                    <th style="width: 10%;" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($objetos as $objeto): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input objeto-checkbox" name="objetos[]" value="<?= htmlspecialchars($objeto['ID']) ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?= htmlspecialchars($objeto['TipoObjeto'] ?? 'Não informado') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($objeto['Descricao'] ?? 'Sem descrição') ?></td>
                                        <td class="text-center fw-bold"><?= htmlspecialchars($objeto['Quantidade'] ?? '0') ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($objeto['Situacao'] ?? 'Não informada') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($objeto['LocalArmazenagem'] ?? 'Não informado') ?></td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="adicionar_objeto.php?objeto_id=<?= htmlspecialchars($objeto['ID']) ?>&procedimento_id=<?= htmlspecialchars($procedimentoID) ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                       class="btn btn-sm btn-outline-danger excluir-objeto" 
                                                       data-id="<?= htmlspecialchars($objeto['ID']) ?>" 
                                                       data-procedimento-id="<?= htmlspecialchars($procedimentoID) ?>"
                                                       data-bs-toggle="tooltip" 
                                                       title="Excluir">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</section><?php
include '../includes/header.php'; // Inclui a navbar e configurações globais

// Verificar e definir constantes para gestão de prazos se não existirem
if (!defined('PRAZO_VERDE')) define('PRAZO_VERDE', 15);    
if (!defined('PRAZO_AMARELO')) define('PRAZO_AMARELO', 10);
if (!defined('PRAZO_LARANJA')) define('PRAZO_LARANJA', 5);   

// Obter o ID do procedimento a partir da URL
$procedimentoID = $_GET['id'] ?? null;
if (!$procedimentoID) {
    echo "<div class='alert alert-danger shadow-sm' role='alert'>
            <i class='bi bi-exclamation-triangle-fill me-2'></i> Procedimento não encontrado.
          </div>";
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
        sp.Cor AS Cor, 
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
    LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID 
    LEFT JOIN Usuarios uDelegado ON uDelegado.ID = p.DelegadoID
    LEFT JOIN Usuarios uEscrivao ON uEscrivao.ID = p.EscrivaoID
    LEFT JOIN Delegacias d ON d.ID = p.DelegaciaID
    WHERE p.ID = :id
";
$usuarioLogadoID = $_SESSION['usuario_id'] ?? null;

$stmt = $pdo->prepare($queryProcedimento);
$stmt->execute([
    'id' => $procedimentoID,
    'usuarioLogadoID' => $usuarioLogadoID
]);

$procedimento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$procedimento) {
    echo "<div class='alert alert-danger shadow-sm' role='alert'>
            <i class='bi bi-exclamation-triangle-fill me-2'></i> Procedimento não encontrado.
          </div>";
    include '../includes/footer.php';
    exit;
}

// Demais consultas para buscar movimentações, RAIs, processos judiciais, etc...
// [Mantenha o restante das consultas SQL conforme o código original]

// Consulta para movimentações
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

// Buscar RAIs e processos judiciais
$queryRAIs = "SELECT Numero, Descricao FROM RAIs WHERE ProcedimentoID = :id";
$stmtRAIs = $pdo->prepare($queryRAIs);
$stmtRAIs->execute(['id' => $procedimentoID]);
$rais = $stmtRAIs->fetchAll(PDO::FETCH_ASSOC);

$queryProcessosJudiciais = "SELECT Numero, Descricao FROM ProcessosJudiciais WHERE ProcedimentoID = :id";
$stmtProcessosJudiciais = $pdo->prepare($queryProcessosJudiciais);
$stmtProcessosJudiciais->execute(['id' => $procedimentoID]);
$processosJudiciais = $stmtProcessosJudiciais->fetchAll(PDO::FETCH_ASSOC);

// Buscar cautelares
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

// Buscar objetos
$queryObjetos = "
    SELECT 
        o.ID,
        t.Nome AS TipoObjeto,
        o.Descricao,
        o.Quantidade,
        s.Nome AS Situacao,
        l.Nome AS LocalArmazenagem
    FROM Objetos o
    LEFT JOIN TiposObjeto t ON o.TipoObjetoID = t.ID
    LEFT JOIN SituacoesObjeto s ON o.SituacaoID = s.ID
    LEFT JOIN LocaisArmazenagem l ON o.LocalArmazenagemID = l.ID
    WHERE o.ProcedimentoID = :procedimentoID
    ORDER BY o.ID DESC
";

$stmtObjetos = $pdo->prepare($queryObjetos);
$stmtObjetos->execute(['procedimentoID' => $procedimentoID]);
$objetos = $stmtObjetos->fetchAll(PDO::FETCH_ASSOC);

// Consulta para listar situações disponíveis e locais de armazenagem
$querySituacoes = "SELECT ID, Nome FROM SituacoesObjeto ORDER BY Nome";
$stmtSituacoes = $pdo->query($querySituacoes);
$situacoesDisponiveis = $stmtSituacoes->fetchAll(PDO::FETCH_ASSOC);

$queryLocais = "SELECT ID, Nome FROM LocaisArmazenagem ORDER BY Nome";
$stmtLocais = $pdo->query($queryLocais);
$locaisDisponiveis = $stmtLocais->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Adicione CSS personalizado no cabeçalho -->
<style>
    :root {
        --primary-color: #0d6efd;
        --secondary-color: #6c757d;
        --success-color: #198754;
        --info-color: #0dcaf0;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-color: #f8f9fa;
        --dark-color: #212529;
    }
    
    .procedimento-card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }
    
    .procedimento-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        border-top-left-radius: 8px !important;
        border-top-right-radius: 8px !important;
        font-weight: 500;
    }
    
    .list-group-item-action:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    .btn {
        border-radius: 4px;
        font-weight: 500;
    }
    
    .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-icon i {
        margin-right: 6px;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: none;
        font-weight: 500;
    }
    
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .cautelar-card {
        height: 100%;
        border: none;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }
    
    .cautelar-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    
    .btn-outline-primary, .btn-outline-danger, .btn-outline-warning, .btn-outline-info {
        border-width: 1.5px;
    }
    
    .floating-action-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1030;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
    }
    
    .custom-tab {
        padding: 0.75rem 1.25rem;
        border-radius: 0.375rem;
        margin-right: 0.5rem;
        cursor: pointer;
        font-weight: 500;
    }
    
    .custom-tab.active {
        background-color: #fff;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .section-title {
        font-weight: 500;
        position: relative;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background-color: var(--primary-color);
        border-radius: 3px;
    }
    
    .info-list {
        list-style: none;
        padding-left: 0;
    }
    
    .info-list li {
        margin-bottom: 0.5rem;
        padding: 0.5rem;
        border-radius: 4px;
    }
    
    .info-list li:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .info-list-label {
        font-weight: 500;
        color: #6c757d;
    }
    
    /* Cores para badges de situação */
    .badge-finalizado {
        background-color: #198754 !important;
        color: white !important;
    }
    
    .badge-pendente {
        background-color: #0d6efd !important;
        color: white !important;
    }
    
    .badge-atrasado {
        background-color: #dc3545 !important;
        color: white !important;
    }
    
    /* Animações para elementos */
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Progress bar estilizado */
    .progress {
        height: 8px;
        border-radius: 4px;
    }
    
    /* Container principal com espaçamento melhorado */
    .main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1.5rem;
    }
</style>

<div class="main-container fade-in">
    <!-- Alertas de Sucesso/Erro -->
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Cabeçalho de Procedimento -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?= htmlspecialchars($procedimento['TipoID'] == 1 ? 'Inquérito Policial' : 'VPI') ?> - Detalhes</h1>
            <p class="text-muted mb-0">
                Número: <a href="https://spp.ssp.go.gov.br/documentos?procedimentoId=<?= htmlspecialchars($procedimento['NumeroProcedimento']) ?>" 
                          target="_blank" class="fw-bold text-decoration-none">
                    <?= htmlspecialchars($procedimento['NumeroProcedimento']) ?>
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </p>
        </div>
        
        <div class="d-flex gap-2">
            <?php $isFavorito = !empty($procedimento['Favorito']); ?>
            <button class="btn <?= $isFavorito ? 'btn-danger' : 'btn-outline-secondary' ?> btn-icon" 
                   onclick="gerenciarFavorito(<?= $procedimentoID ?>, <?= $isFavorito ? '\'remover\'' : '\'adicionar\'' ?>)">
                <i class="bi <?= $isFavorito ? 'bi-star-fill' : 'bi-star' ?>"></i>
                <?= $isFavorito ? 'Desfavoritar' : 'Favoritar' ?>
            </button>
            
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle btn-icon" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear"></i> Ações
                </button>
                <ul class="dropdown-menu shadow-sm" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="editar_procedimento.php?id=<?= $procedimentoID ?>">
                        <i class="bi bi-pencil-fill me-2"></i> Editar Procedimento
                    </a></li>
                    <li><a class="dropdown-item" href="anotacoes.php?id=<?= $procedimentoID ?>">
                        <i class="bi bi-journal-text me-2"></i> Ver Anotações
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="gerar_relatorio.php?id=<?= $procedimentoID ?>">
                        <i class="bi bi-file-pdf me-2"></i> Gerar Relatório
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Navegação por Seções em vez de Abas -->
<div class="mb-4">
    <div class="card">
        <div class="card-header bg-dark py-3">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <a class="nav-link active text-white" href="#secao-resumo">
                        <i class="bi bi-info-circle me-1"></i> Resumo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#secao-movimentacoes">
                        <i class="bi bi-list-check me-1"></i> Movimentações
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#secao-cautelares">
                        <i class="bi bi-lock me-1"></i> Cautelares
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#secao-objetos">
                        <i class="bi bi-box me-1"></i> Objetos
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Seções de Conteúdo -->
<!-- Cada seção é uma div com ID que corresponde aos links acima -->

<!-- Seção de Resumo -->
<section id="secao-resumo" class="mb-5">
    <h3 class="mb-4 section-title">Resumo do Procedimento</h3>
    
    <div class="row g-4">
        <!-- Painel de Informações Gerais -->
        <div class="col-lg-6">
            <div class="card procedimento-card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle me-2"></i> Informações Gerais
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                    <i class="bi bi-calendar2-event fs-4"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Data da Instauração</small>
                                    <strong><?= htmlspecialchars(date('d/m/Y', strtotime($procedimento['DataInstauracao'] ?? 'now'))) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                    <i class="bi bi-tag fs-4"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Situação</small>
                                    <span class="badge <?= htmlspecialchars($procedimento['Cor'] ?? 'bg-secondary') ?>">
                                        <?= htmlspecialchars($procedimento['Situacao'] ?? 'Não informada') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                    <i class="bi bi-building fs-4"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Delegacia</small>
                                    <strong><?= htmlspecialchars($procedimento['Delegacia'] ?? 'Não informada') ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                    <i class="bi bi-person fs-4"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Delegado</small>
                                    <strong><?= htmlspecialchars($procedimento['Delegado'] ?? 'Não informado') ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                    <i class="bi bi-person-vcard fs-4"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Escrivão</small>
                                    <strong><?= htmlspecialchars($procedimento['Escrivao'] ?? 'Não informado') ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-muted me-3" style="width: 40px; text-align: center;">
                                    <i class="bi bi-tools fs-4"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Meios Empregados</small>
                                    <strong><?= htmlspecialchars($procedimento['MeiosEmpregados'] ?? 'Nenhum') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel de Envolvidos -->
        <div class="col-lg-6">
            <div class="card procedimento-card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-people me-2"></i> Envolvidos
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted fw-bold mb-2"><i class="bi bi-person-x me-1"></i> Investigados:</label>
                        <div class="p-2 border-start border-3 border-info rounded bg-light">
                            <?= htmlspecialchars($procedimento['Investigados'] ?? 'Ignorado(a)') ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted fw-bold mb-2"><i class="bi bi-person-arms-up me-1"></i> Vítimas:</label>
                        <div class="p-2 border-start border-3 border-warning rounded bg-light">
                            <?= htmlspecialchars($procedimento['Vitimas'] ?? 'Nenhuma') ?>
                        </div>
                    </div>
                    <div>
                        <label class="text-muted fw-bold mb-2"><i class="bi bi-question-circle me-1"></i> Motivo Aparente:</label>
                        <div class="p-2 border-start border-3 border-secondary rounded bg-light">
                            <?= htmlspecialchars($procedimento['MotivoAparente'] ?? 'Não informado') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Painel de Processos e RAIs -->
        <div class="col-12">
            <div class="card procedimento-card mb-4">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-file-earmark-text me-2"></i> Processos Judiciais e RAIs
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Coluna: Processos Judiciais -->
                        <div class="col-md-6">
                            <h5 class="mb-3 section-title">Processos Judiciais</h5>
                            <?php if (empty($processosJudiciais)): ?>
                                <div class="alert alert-light text-muted">
                                    <i class="bi bi-info-circle me-2"></i> Nenhum Processo Judicial registrado.
                                </div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($processosJudiciais as $processo): ?>
                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?= htmlspecialchars($processo['Numero'] ?? 'Não informado') ?></h6>
                                            </div>
                                            <p class="mb-1 small text-muted"><?= htmlspecialchars($processo['Descricao'] ?? 'Sem descrição') ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Coluna: RAIs -->
                        <div class="col-md-6">
                            <h5 class="mb-3 section-title">RAIs</h5>
                            <?php if (empty($rais)): ?>
                                <div class="alert alert-light text-muted">
                                    <i class="bi bi-info-circle me-2"></i> Nenhum RAI registrado.
                                </div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($rais as $rai): ?>
                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">
                                                    <a href="https://atendimento.ssp.go.gov.br/#/atendimento/<?= urlencode($rai['Numero']) ?>" 
                                                       target="_blank" 
                                                       class="text-decoration-none">
                                                       <i class="bi bi-link-45deg me-1"></i>
                                                       <?= htmlspecialchars($rai['Numero']) ?>
                                                    </a>
                                                </h6>
                                            </div>
                                            <p class="mb-1 small text-muted"><?= htmlspecialchars($rai['Descricao'] ?? 'Sem descrição') ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumo das movimentações recentes -->
        <div class="col-12">
            <div class="card procedimento-card mb-4">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-clock-history me-2"></i> Movimentações Recentes
                    </div>
                    <a href="#secao-movimentacoes" class="btn btn-sm btn-dark">
                        Ver Todas <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($movimentacoes)): ?>
                        <div class="alert alert-light m-3 text-muted">
                            <i class="bi bi-info-circle me-2"></i> Nenhuma movimentação registrada.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Assunto</th>
                                        <th>Prazo</th>
                                        <th>Situação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Limita a 5 movimentações recentes
                                    $movimentacoesRecentes = array_slice($movimentacoes, 0, 5);
                                    foreach ($movimentacoesRecentes as $mov): 
                                        // Define classe para o prazo
                                        $classPrazo = 'bg-success';
                                        if ($mov['dias_restantes'] <= PRAZO_LARANJA) {
                                            $classPrazo = 'bg-danger';
                                        } elseif ($mov['dias_restantes'] <= PRAZO_AMARELO) {
                                            $classPrazo = 'bg-warning text-dark';
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $tipoCores = [
                                                    'Prisões' => 'bg-danger',
                                                    'Remessa de IP' => 'bg-danger',
                                                    'Requisição MP' => 'bg-info'
                                                ];
                                                $corTipo = $tipoCores[$mov['Tipo']] ?? 'bg-warning text-dark';
                                                ?>
                                                <span class="badge <?= $corTipo ?>">
                                                    <?= htmlspecialchars($mov['Tipo'] ?? 'Não informado') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($mov['Assunto'] ?? 'Não informado') ?></td>
                                            <td>
                                                <?php if ($mov['Situacao'] === 'Finalizado'): ?>
                                                    <span class="badge bg-success">Finalizado</span>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge <?= $classPrazo ?> me-2">
                                                            <?= htmlspecialchars($mov['dias_restantes']) ?>
                                                        </span>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars(date('d/m/Y', strtotime($mov['DataVencimento']))) ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($mov['Situacao'] === 'Finalizado'): ?>
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-hourglass-split text-warning"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($mov['Situacao']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seção de Movimentações -->
<section id="secao-movimentacoes" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title mb-0">Movimentações</h3>
        <a href="mov.php?procedimento_id=<?= $procedimentoID ?>" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> Nova Movimentação
        </a>
    </div>
    
    <?php if (empty($movimentacoes)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill me-2"></i> Nenhuma movimentação registrada.
        </div>
    <?php else: ?>
        <div class="card procedimento-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="min-width: 800px;">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Tipo</th>
                                <th style="width: 30%;">Assunto</th>
                                <th style="width: 15%;">Situação</th>
                                <th style="width: 15%;">Data Vencimento</th>
                                <th style="width: 15%;">Prazo (dias)</th>
                                <th style="width: 10%;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimentacoes as $movimentacao): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $tipoCoresEspecificos = [
                                            'Prisões' => 'bg-danger',
                                            'Remessa de IP' => 'bg-danger',
                                            'Requisição MP' => 'bg-info'
                                        ];
                                        $corTipo = $tipoCoresEspecificos[$movimentacao['Tipo']] ?? 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?= $corTipo ?>">
                                            <?= htmlspecialchars($movimentacao['Tipo'] ?? 'Não informado') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($movimentacao['Assunto'] ?? 'Não informado') ?></td>
                                    <td>
                                        <span class="badge <?= $movimentacao['Situacao'] === 'Finalizado' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= htmlspecialchars($movimentacao['Situacao'] ?? 'Não informada') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($movimentacao['DataVencimento'] ?? ''))) ?></td>
                                    <td>
                                        <?php if ($movimentacao['Situacao'] === 'Finalizado'): ?>
                                            <span class="text-success">
                                                <i class="bi bi-check-circle-fill"></i> Finalizado
                                            </span>
                                        <?php else: ?>
                                            <?php 
                                            $diasRestantes = $movimentacao['dias_restantes'];
                                            $corBadge = 'bg-success';
                                            
                                            if ($diasRestantes <= PRAZO_LARANJA) {
                                                $corBadge = 'bg-danger';
                                            } elseif ($diasRestantes <= PRAZO_AMARELO) {
                                                $corBadge = 'bg-warning text-dark';
                                            }
                                            ?>
                                            <span class="badge <?= $corBadge ?> fs-6">
                                                <?= htmlspecialchars($diasRestantes) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="mov.php?id=<?= $movimentacao['ID'] ?>&procedimento_id=<?= $procedimentoID ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="ver_movimentacao.php?id=<?= $movimentacao['ID'] ?>" 
                                               class="btn btn-sm btn-outline-info" 
                                               data-bs-toggle="tooltip" 
                                               title="Ver Detalhes">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button"
                                                   class="btn btn-sm btn-outline-danger excluir-movimentacao" 
                                                   data-id="<?= $movimentacao['ID'] ?>"
                                                   data-procedimento-id="<?= $procedimentoID ?>"
                                                   data-bs-toggle="tooltip" 
                                                   title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

        <!-- Aba de Cautelares -->
        <div class="tab-pane fade" id="cautelares" role="tabpanel" aria-labelledby="cautelares-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Cautelares</h4>
                <a href="adicionar_cautelar.php?procedimento_id=<?= $procedimentoID ?>" class="btn btn-success">
                    <i class="bi bi-plus-lg me-1"></i> Nova Cautelar
                </a>
            </div>
            
            <?php if (empty($cautelares)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill me-2"></i> Nenhuma cautelar registrada para este procedimento.
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($cautelares as $cautelar): ?>
                        <div class="col-md-4">
                            <div class="card cautelar-card h-100">
                                <div class="card-header bg-dark text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-file-text me-2"></i> Processo</span>
                                        <span class="badge bg-light text-dark">
                                            <?php 
                                            $cumprimento = $cautelar['QuantidadeSolicitada'] > 0 
                                                ? round(($cautelar['QuantidadeCumprida'] / $cautelar['QuantidadeSolicitada']) * 100) 
                                                : 0;
                                            echo $cumprimento . '%';
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="fw-bold mb-3"><?= htmlspecialchars($cautelar['ProcessoJudicial'] ?? 'Não informado') ?></p>
                                    
                                    <p class="mb-2"><strong>Tipos de Cautelares:</strong></p>
                                    <div class="mb-3 p-2 border-start border-3 border-dark bg-light rounded">
                                        <?= $cautelar['TiposCautelares'] ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Progresso de Cumprimento:</span>
                                            <span class="fw-bold">
                                                <?= htmlspecialchars($cautelar['QuantidadeCumprida'] ?? '0') ?> 
                                                de 
                                                <?= htmlspecialchars($cautelar['QuantidadeSolicitada'] ?? '0') ?>
                                            </span>
                                        </div>
                                        <div class="progress">
                                            <?php 
                                            $progresso = $cautelar['QuantidadeSolicitada'] > 0 
                                                ? ($cautelar['QuantidadeCumprida'] / $cautelar['QuantidadeSolicitada']) * 100 
                                                : 0;
                                            
                                            $progressoClass = 'bg-success';
                                            if ($progresso < 50) {
                                                $progressoClass = 'bg-danger';
                                            } elseif ($progresso < 100) {
                                                $progressoClass = 'bg-warning';
                                            }
                                            ?>
                                            <div class="progress-bar <?= $progressoClass ?>" 
                                                 role="progressbar" 
                                                 style="width: <?= $progresso ?>%" 
                                                 aria-valuenow="<?= $progresso ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <div class="d-flex justify-content-between">
                                        <a href="editar_cautelar.php?solicitacao_id=<?= $cautelar['SolicitacaoID'] ?>&procedimento_id=<?= $procedimentoID ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i> Editar
                                        </a>
                                        <div>
                                            <a href="adicionar_cumprimento.php?solicitacao_id=<?= $cautelar['SolicitacaoID'] ?>&procedimento_id=<?= $procedimentoID ?>&origem=procedimentos" 
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle me-1"></i> Cumprir
                                            </a>
                                            <button type="button"
                                                   class="btn btn-sm btn-outline-danger excluir-cautelar"
                                                   data-id="<?= $cautelar['SolicitacaoID'] ?>"
                                                   data-procedimento-id="<?= $procedimentoID ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Aba de Objetos -->
        <div class="tab-pane fade" id="objetos" role="tabpanel" aria-labelledby="objetos-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Objetos Apreendidos</h4>
                <a href="adicionar_objeto.php?procedimento_id=<?= $procedimentoID ?>" class="btn btn-success">
                    <i class="bi bi-plus-lg me-1"></i> Novo Objeto
                </a>
            </div>
            
            <?php if (empty($objetos)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill me-2"></i> Nenhum objeto registrado para este procedimento.
                </div>
            <?php else: ?>
                <form id="form-editar-situacao-local" method="POST" action="editar_situacao_local_em_massa.php">
                    <input type="hidden" name="procedimento_id" value="<?= htmlspecialchars($procedimentoID) ?>">
                    
                    <div class="card procedimento-card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-pencil-square me-2"></i> Edição em Massa
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="nova_situacao" class="form-label">Nova Situação</label>
                                    <select name="nova_situacao" id="nova_situacao" class="form-select" required>
                                        <option value="">Selecione a nova situação</option>
                                        <?php foreach ($situacoesDisponiveis as $situacao): ?>
                                            <option value="<?= htmlspecialchars($situacao['ID']) ?>">
                                                <?= htmlspecialchars($situacao['Nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="novo_local" class="form-label">Novo Local de Armazenagem</label>
                                    <select name="novo_local" id="novo_local" class="form-select" required>
                                        <option value="">Selecione o novo local</option>
                                        <?php foreach ($locaisDisponiveis as $local): ?>
                                            <option value="<?= htmlspecialchars($local['ID']) ?>">
                                                <?= htmlspecialchars($local['Nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" id="btn-confirm-update-local" class="btn btn-primary w-100">
                                        <i class="bi bi-arrow-repeat me-2"></i> Atualizar Selecionados
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card procedimento-card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th width="40">
                                                <div class="form-check">
                                                    <input type="checkbox" id="check-all" class="form-check-input">
                                                </div>
                                            </th>
                                            <th>Tipo de Objeto</th>
                                            <th>Descrição</th>
                                            <th>Quantidade</th>
                                            <th>Situação</th>
                                            <th>Local de Armazenagem</th>
                                            <th class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($objetos as $objeto): ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input objeto-checkbox" name="objetos[]" value="<?= htmlspecialchars($objeto['ID']) ?>">
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info text-dark">
                                                        <?= htmlspecialchars($objeto['TipoObjeto'] ?? 'Não informado') ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($objeto['Descricao'] ?? 'Sem descrição') ?></td>
                                                <td class="text-center fw-bold"><?= htmlspecialchars($objeto['Quantidade'] ?? '0') ?></td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?= htmlspecialchars($objeto['Situacao'] ?? 'Não informada') ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($objeto['LocalArmazenagem'] ?? 'Não informado') ?></td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a href="adicionar_objeto.php?objeto_id=<?= htmlspecialchars($objeto['ID']) ?>&procedimento_id=<?= htmlspecialchars($procedimentoID) ?>" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           data-bs-toggle="tooltip" 
                                                           title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button" 
                                                               class="btn btn-sm btn-outline-danger excluir-objeto" 
                                                               data-id="<?= htmlspecialchars($objeto['ID']) ?>" 
                                                               data-procedimento-id="<?= htmlspecialchars($procedimentoID) ?>"
                                                               data-bs-toggle="tooltip" 
                                                               title="Excluir">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (tooltipTriggerList.length > 0) {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                boundary: document.body
            });
        });
    }
    
    // Ativa a navegação por links de âncora com rolagem suave
    const links = document.querySelectorAll('a[href^="#secao-"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Destaca o link ativo
                links.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // Rolagem suave
                window.scrollTo({
                    top: targetElement.offsetTop - 20,
                    behavior: 'smooth'
                });
                
                // Atualiza a URL com a âncora
                history.pushState(null, null, targetId);
            }
        });
    });
    
    // Verifica se há uma âncora na URL ao carregar
    if (window.location.hash && window.location.hash.startsWith('#secao-')) {
        const targetElement = document.querySelector(window.location.hash);
        if (targetElement) {
            setTimeout(() => {
                window.scrollTo({
                    top: targetElement.offsetTop - 20,
                    behavior: 'smooth'
                });
                
                // Destaca o link ativo
                const activeLink = document.querySelector(`a[href="${window.location.hash}"]`);
                if (activeLink) {
                    links.forEach(l => l.classList.remove('active'));
                    activeLink.classList.add('active');
                }
            }, 300);
        }
    }
    
    // Manipulação de navegação botão "Ver todas movimentações"
    document.querySelector('a[href="#secao-movimentacoes"]')?.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 20,
                behavior: 'smooth'
            });
            
            // Destaca o link da navegação
            links.forEach(l => l.classList.remove('active'));
            document.querySelector(`.nav-link[href="${targetId}"]`)?.classList.add('active');
        }
    });
    
    // Checkbox "Selecionar Todos" para objetos
    const checkAll = document.getElementById('check-all');
    if (checkAll) {
        const checkboxes = document.querySelectorAll('.objeto-checkbox');
        
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => checkbox.checked = checkAll.checked);
        });
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const todosMarcados = [...checkboxes].every(cb => cb.checked);
                const algunsMarcados = [...checkboxes].some(cb => cb.checked);
                
                checkAll.checked = todosMarcados;
                checkAll.indeterminate = algunsMarcados && !todosMarcados;
            });
        });
    }
    
    // Manipuladores de exclusão
    document.querySelectorAll('.excluir-objeto').forEach(button => {
        button.addEventListener('click', function() {
            const objetoId = this.getAttribute('data-id');
            const procedimentoId = this.getAttribute('data-procedimento-id');
            
            confirmarExclusao('objeto', objetoId, procedimentoId);
        });
    });
    
    document.querySelectorAll('.excluir-cautelar').forEach(button => {
        button.addEventListener('click', function() {
            const cautelarId = this.getAttribute('data-id');
            const procedimentoId = this.getAttribute('data-procedimento-id');
            
            confirmarExclusao('cautelar', cautelarId, procedimentoId, 'procedimentos');
        });
    });
    
    document.querySelectorAll('.excluir-movimentacao').forEach(button => {
        button.addEventListener('click', function() {
            const movId = this.getAttribute('data-id');
            const procedimentoId = this.getAttribute('data-procedimento-id');
            
            confirmarExclusao('movimentação', movId, procedimentoId);
        });
    });
    
    // Botão de atualização em massa de objetos
    document.getElementById('btn-confirm-update-local')?.addEventListener('click', function() {
        const form = document.getElementById('form-editar-situacao-local');
        const novaSituacao = document.getElementById('nova_situacao').value;
        const novoLocal = document.getElementById('novo_local').value;
        const objetosSelecionados = document.querySelectorAll('input[name="objetos[]"]:checked');
        
        if (!novaSituacao || !novoLocal) {
            alertaPersonalizado('Selecione tanto a situação quanto o local de armazenagem.', 'warning');
            return;
        }
        
        if (objetosSelecionados.length === 0) {
            alertaPersonalizado('Selecione pelo menos um objeto para atualizar.', 'warning');
            return;
        }
        
        confirmarAcao(
            'Atualizar Situação e Local',
            'Tem certeza que deseja atualizar a situação e o local de armazenagem dos objetos selecionados?',
            function() {
                form.submit();
            }
        );
    });
});

// Funções auxiliares
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
            alertaPersonalizado(
                acao === 'adicionar' ? 'Adicionado aos favoritos!' : 'Removido dos favoritos!', 
                'success',
                function() {
                    location.reload();
                }
            );
        } else {
            alertaPersonalizado('Erro: ' + (data.message || 'Não foi possível atualizar o favorito.'), 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alertaPersonalizado('Erro na comunicação com o servidor.', 'error');
    });
}

function confirmarExclusao(tipo, id, procedimentoId, origem = null) {
    let url, params;
    
    switch(tipo) {
        case 'objeto':
            url = 'excluir_objeto.php';
            params = {
                objeto_id: id,
                procedimento_id: procedimentoId
            };
            break;
        case 'cautelar':
            url = 'excluir_cautelar.php';
            params = {
                solicitacao_id: id,
                procedimento_id: procedimentoId
            };
            if (origem) params.origem = origem;
            break;
        case 'movimentação':
            url = 'excluir_movimentacao.php';
            params = {
                id: id,
                procedimento_id: procedimentoId
            };
            break;
    }
    
    confirmarAcao(
        `Excluir ${tipo}`,
        `Tem certeza que deseja excluir este(a) ${tipo}?<br>Esta ação não pode ser desfeita.`,
        function() {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(params)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertaPersonalizado(
                        `${tipo.charAt(0).toUpperCase() + tipo.slice(1)} excluído(a) com sucesso.`,
                        'success',
                        function() {
                            location.reload();
                        }
                    );
                } else {
                    alertaPersonalizado(`Erro ao excluir ${tipo}: ${data.message || 'Erro desconhecido.'}`, 'error');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                alertaPersonalizado(`Ocorreu um erro ao tentar excluir o(a) ${tipo}.`, 'error');
            });
        }
    );
}

function confirmarAcao(titulo, mensagem, callback) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: titulo,
            html: mensagem,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, confirmar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    } else {
        // Fallback para o confirm nativo se o SweetAlert não estiver disponível
        if (confirm(mensagem.replace(/<br>/g, '\n'))) {
            callback();
        }
    }
}

function alertaPersonalizado(mensagem, tipo, callback = null) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: tipo === 'error' ? 'Erro' : 
                  tipo === 'warning' ? 'Atenção' : 
                  tipo === 'success' ? 'Sucesso' : 'Informação',
            text: mensagem,
            icon: tipo,
            confirmButtonText: 'OK'
        }).then(() => {
            if (callback) callback();
        });
    } else {
        // Fallback para o alert nativo
        alert(mensagem);
        if (callback) callback();
    }
}

// Aplica margens de seção na rolagem
window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('section[id^="secao-"]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let currentSection = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 100;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');
        
        if (pageYOffset >= sectionTop && pageYOffset < sectionTop + sectionHeight) {
            currentSection = sectionId;
        }
    });
    
    if (currentSection !== '') {
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentSection}`) {
                link.classList.add('active');
            }
        });
    }
}); => {
            checkbox.addEventListener('change', function() {
                const todosMarcados = [...checkboxes].every(cb => cb.checked);
                const algunsMarcados = [...checkboxes].some(cb => cb.checked);
                
                checkAll.checked = todosMarcados;
                checkAll.indeterminate = algunsMarcados && !todosMarcados;
            });
        });
    }
    
    // Adiciona animações de entrada para elementos da página
    const animarElementos = () => {
        const elementos = document.querySelectorAll('.procedimento-card, .cautelar-card');
        elementos.forEach((elem, index) => {
            setTimeout(() => {
                elem.classList.add('fade-in');
            }, index * 100);
        });
    };
    
    animarElementos();
    
    // Manipulador de exclusão de objetos
    document.querySelectorAll('.excluir-objeto').forEach(button => {
        button.addEventListener('click', function() {
            const objetoId = this.getAttribute('data-id');
            const procedimentoId = this.getAttribute('data-procedimento-id');
            
            confirmarExclusao('objeto', objetoId, procedimentoId);
        });
    });
    
    // Manipulador de exclusão de cautelares
    document.querySelectorAll('.excluir-cautelar').forEach(button => {
        button.addEventListener('click', function() {
            const cautelarId = this.getAttribute('data-id');
            const procedimentoId = this.getAttribute('data-procedimento-id');
            
            confirmarExclusao('cautelar', cautelarId, procedimentoId, 'procedimentos');
        });
    });
    
    // Manipulador de exclusão de movimentações
    document.querySelectorAll('.excluir-movimentacao').forEach(button => {
        button.addEventListener('click', function() {
            const movId = this.getAttribute('data-id');
            const procedimentoId = this.getAttribute('data-procedimento-id');
            
            confirmarExclusao('movimentação', movId, procedimentoId);
        });
    });
    
    // Botão de atualização em massa de objetos
    document.getElementById('btn-confirm-update-local')?.addEventListener('click', function() {
        const form = document.getElementById('form-editar-situacao-local');
        const novaSituacao = document.getElementById('nova_situacao').value;
        const novoLocal = document.getElementById('novo_local').value;
        const objetosSelecionados = document.querySelectorAll('input[name="objetos[]"]:checked');
        
        if (!novaSituacao || !novoLocal) {
            alertaPersonalizado('Selecione tanto a situação quanto o local de armazenagem.', 'warning');
            return;
        }
        
        if (objetosSelecionados.length === 0) {
            alertaPersonalizado('Selecione pelo menos um objeto para atualizar.', 'warning');
            return;
        }
        
        confirmarAcao(
            'Atualizar Situação e Local',
            'Tem certeza que deseja atualizar a situação e o local de armazenagem dos objetos selecionados?',
            function() {
                form.submit();
            }
        );
    });
});

// Funções auxiliares
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
            alertaPersonalizado(
                acao === 'adicionar' ? 'Adicionado aos favoritos!' : 'Removido dos favoritos!', 
                'success',
                function() {
                    location.reload();
                }
            );
        } else {
            alertaPersonalizado('Erro: ' + (data.message || 'Não foi possível atualizar o favorito.'), 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alertaPersonalizado('Erro na comunicação com o servidor.', 'error');
    });
}

function confirmarExclusao(tipo, id, procedimentoId, origem = null) {
    let url, params;
    
    switch(tipo) {
        case 'objeto':
            url = 'excluir_objeto.php';
            params = {
                objeto_id: id,
                procedimento_id: procedimentoId
            };
            break;
        case 'cautelar':
            url = 'excluir_cautelar.php';
            params = {
                solicitacao_id: id,
                procedimento_id: procedimentoId
            };
            if (origem) params.origem = origem;
            break;
        case 'movimentação':
            url = 'excluir_movimentacao.php';
            params = {
                id: id,
                procedimento_id: procedimentoId
            };
            break;
    }
    
    confirmarAcao(
        `Excluir ${tipo}`,
        `Tem certeza que deseja excluir este(a) ${tipo}?<br>Esta ação não pode ser desfeita.`,
        function() {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(params)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertaPersonalizado(
                        `${tipo.charAt(0).toUpperCase() + tipo.slice(1)} excluído(a) com sucesso.`,
                        'success',
                        function() {
                            location.reload();
                        }
                    );
                } else {
                    alertaPersonalizado(`Erro ao excluir ${tipo}: ${data.message || 'Erro desconhecido.'}`, 'error');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                alertaPersonalizado(`Ocorreu um erro ao tentar excluir o(a) ${tipo}.`, 'error');
            });
        }
    );
}

function confirmarAcao(titulo, mensagem, callback) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: titulo,
            html: mensagem,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, confirmar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    } else {
        // Fallback para o confirm nativo se o SweetAlert não estiver disponível
        if (confirm(mensagem.replace(/<br>/g, '\n'))) {
            callback();
        }
    }
}

function alertaPersonalizado(mensagem, tipo, callback = null) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: tipo === 'error' ? 'Erro' : 
                  tipo === 'warning' ? 'Atenção' : 
                  tipo === 'success' ? 'Sucesso' : 'Informação',
            text: mensagem,
            icon: tipo,
            confirmButtonText: 'OK'
        }).then(() => {
            if (callback) callback();
        });
    } else {
        // Fallback para o alert nativo
        alert(mensagem);
        if (callback) callback();
    }
}

// Inicializa a navegação por hash na URL
function inicializarNavegacaoPorHash() {
    // Verifica se há um hash na URL ao carregar a página
    const hash = window.location.hash;
    if (hash) {
        const targetTab = document.querySelector(`[data-bs-target="${hash}"]`);
        if (targetTab) {
            const tab = new bootstrap.Tab(targetTab);
            tab.show();
        }
    }

    // Atualiza o hash na URL quando uma aba é clicada
    const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            const targetId = event.target.getAttribute('data-bs-target');
            window.location.hash = targetId;
        });
    });
}