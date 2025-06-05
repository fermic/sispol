<?php
session_start();
require_once '../config/db.php';
require_once '../includes/helpers.php';
require_once '../includes/functions.php';

// Obtém os IDs da URL
$objetoID = $_GET['objeto_id'] ?? null;
$procedimentoID = $_GET['procedimento_id'] ?? null;

if (!$objetoID || !$procedimentoID) {
    die("Parâmetros inválidos");
}

// Busca os dados do procedimento com envolvidos
$queryProcedimento = "
    SELECT 
        p.NumeroProcedimento,
        p.DataFato,
        p.DataInstauracao,
        p.TipoID,
        d.Nome AS Delegacia,
        (
            SELECT GROUP_CONCAT(
                DISTINCT CONCAT(
                    '<div class=\"envolvido-item\">',
                    '<strong>', v.Nome, '</strong>',
                    '<div class=\"ms-2\">',
                    (SELECT GROUP_CONCAT(
                        CONCAT('<span class=\"badge bg-danger me-1\">', c.Nome, ' (', vc.Modalidade, ')</span>')
                        SEPARATOR ' '
                    )
                    FROM Vitimas_Crimes vc
                    LEFT JOIN Crimes c ON vc.CrimeID = c.ID
                    WHERE vc.VitimaID = v.ID),
                    '</div>',
                    '</div>'
                ) SEPARATOR ''
            )
            FROM Vitimas v
            WHERE v.ProcedimentoID = p.ID
        ) AS Vitimas,
        (
            SELECT GROUP_CONCAT(
                DISTINCT CONCAT(
                    '<div class=\"envolvido-item\">',
                    '<strong>', i.Nome, '</strong>',
                    '</div>'
                ) SEPARATOR ''
            )
            FROM Investigados i
            WHERE i.ProcedimentoID = p.ID
        ) AS Investigados
    FROM Procedimentos p
    LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID
    LEFT JOIN Delegacias d ON d.ID = p.DelegaciaID
    WHERE p.ID = :id
";

$stmtProcedimento = $pdo->prepare($queryProcedimento);
$stmtProcedimento->execute(['id' => $procedimentoID]);
$procedimento = $stmtProcedimento->fetch(PDO::FETCH_ASSOC);

// Busca os dados do objeto
$queryObjeto = "
    SELECT 
        o.*,
        t.Nome AS TipoObjeto
    FROM Objetos o
    LEFT JOIN TiposObjeto t ON o.TipoObjetoID = t.ID
    WHERE o.ID = :id
";

$stmtObjeto = $pdo->prepare($queryObjeto);
$stmtObjeto->execute(['id' => $objetoID]);
$objeto = $stmtObjeto->fetch(PDO::FETCH_ASSOC);

// Busca o histórico de movimentações
$queryMovimentacoes = "
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
";

$stmtMovimentacoes = $pdo->prepare($queryMovimentacoes);
$stmtMovimentacoes->execute([':objetoID' => $objetoID]);
$movimentacoes = $stmtMovimentacoes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadeia de Custódia - Impressão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            body {
                font-size: 12pt;
            }
            .container {
                width: 100%;
                max-width: none;
                padding: 0;
                margin: 0;
            }
        }
        .header {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 20px;
        }
        .header-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .header-subtitle {
            text-align: center;
            font-size: 14pt;
            margin-bottom: 30px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h6 {
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
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
            background: #000;
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
            background: #000;
            border: 2px solid #fff;
        }
        .timeline-content {
            border: 1px solid #000;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .timeline-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .timeline-observation {
            border-left: 3px solid #000;
            padding: 10px;
            margin-top: 10px;
        }
        .timeline-footer {
            font-size: 0.9em;
            border-top: 1px solid #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10pt;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .envolvidos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .envolvidos-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
        }
        .envolvidos-card h6 {
            color: #1a237e;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 14pt;
        }
        .envolvido-item {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 10px;
            border-left: 4px solid #1a237e;
        }
        .envolvido-item:last-child {
            margin-bottom: 0;
        }
        .envolvido-item strong {
            color: #1a237e;
            display: block;
            margin-bottom: 3px;
        }
        .envolvido-item small {
            color: #666;
            font-size: 0.9em;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
        }
        .info-card h6 {
            color: #1a237e;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 14pt;
        }
        .info-item {
            margin-bottom: 12px;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }
        .info-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 1.1em;
            color: #333;
        }
        @media print {
            .envolvidos-grid,
            .info-grid {
                display: block;
            }
            .envolvidos-card,
            .info-card {
                break-inside: avoid;
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
        }
        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .timeline-date {
            color: #666;
            font-size: 0.9em;
        }
        .timeline-content {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .timeline-observation {
            background: #f8f9fa;
            border-left: 4px solid #1a237e;
            padding: 15px;
            margin-top: 15px;
            font-size: 1em;
            color: #333;
        }
        .timeline-footer {
            font-size: 0.9em;
            color: #666;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">
        <!-- Botões de controle (não aparecem na impressão) -->
        <div class="no-print mb-4">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            <a href="ver_procedimento.php?id=<?= $procedimentoID ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Cabeçalho -->
        <div class="header">
            <div class="header-title">
                CADEIA DE CUSTÓDIA
            </div>
            <div class="header-subtitle">
                Polícia Civil do Estado de Goiás
            </div>

            <!-- Dados do Procedimento e Envolvidos -->
            <div class="info-grid">
                <!-- Dados do Procedimento -->
                <div class="info-card">
                    <h6><i class="bi bi-file-text me-2"></i>Dados do Procedimento</h6>
                    <div class="info-item">
                        <div class="info-label">Número</div>
                        <div class="info-value">
                            <span class="badge bg-primary"><?= htmlspecialchars($procedimento['NumeroProcedimento']) ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">
                            <span class="badge bg-info"><?= $procedimento['TipoID'] == 1 ? 'Inquérito Policial' : 'VPI' ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Data da Instauração</div>
                        <div class="info-value"><?= formatarDataBrasileira($procedimento['DataInstauracao']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Data do Fato</div>
                        <div class="info-value"><?= formatarDataBrasileira($procedimento['DataFato']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Delegacia</div>
                        <div class="info-value"><?= htmlspecialchars($procedimento['Delegacia']) ?></div>
                    </div>
                </div>

                <!-- Envolvidos -->
                <div class="envolvidos-card">
                    <h6><i class="bi bi-people me-2"></i>Envolvidos</h6>
                    <?php if (!empty($procedimento['Vitimas'])): ?>
                        <div class="mb-4">
                            <h6 class="text-danger mb-3">Vítimas</h6>
                            <?= $procedimento['Vitimas'] ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($procedimento['Investigados'])): ?>
                        <div>
                            <h6 class="text-primary mb-3">Autores</h6>
                            <?= $procedimento['Investigados'] ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dados do Objeto -->
            <div class="info-card">
                <h6><i class="bi bi-box-seam me-2"></i>Dados do Objeto</h6>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">
                            <span class="badge bg-dark"><?= htmlspecialchars($objeto['TipoObjeto']) ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Quantidade</div>
                        <div class="info-value"><?= htmlspecialchars($objeto['Quantidade']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Lacre Atual</div>
                        <div class="info-value">
                            <?php if ($objeto['LacreAtual']): ?>
                                <span class="badge bg-dark"><?= htmlspecialchars($objeto['LacreAtual']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Não informado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="info-item mt-3">
                    <div class="info-label">Descrição</div>
                    <div class="info-value"><?= htmlspecialchars($objeto['Descricao']) ?></div>
                </div>
            </div>
        </div>

        <!-- Histórico de Movimentações -->
        <div class="info-section">
            <h6>Histórico de Movimentações</h6>
            <?php if (empty($movimentacoes)): ?>
                <p class="text-muted">Nenhuma movimentação registrada.</p>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($movimentacoes as $mov): ?>
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="badge <?= getBadgeClassMovimentacao($mov['TipoMovimentacao'], $mov['CorMovimentacao']) ?>">
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

        <!-- Rodapé -->
        <div class="footer">
            <p>
                Documento gerado em <?= date('d/m/Y H:i:s') ?> - 
                Sistema de Gestão de Procedimentos - SSP/GO
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 