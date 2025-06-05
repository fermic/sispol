<?php
session_start();
include '../includes/header.php'; // Inclui a navbar e configurações globais
include_once '../config/db.php'; // Conexão com o banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}

function obterMesEmPortugues($mesIngles) {
    $meses = [
        'January' => 'Janeiro',
        'February' => 'Fevereiro',
        'March' => 'Março',
        'April' => 'Abril',
        'May' => 'Maio',
        'June' => 'Junho',
        'July' => 'Julho',
        'August' => 'Agosto',
        'September' => 'Setembro',
        'October' => 'Outubro',
        'November' => 'Novembro',
        'December' => 'Dezembro'
    ];
    return $meses[$mesIngles] ?? $mesIngles; // Retorna o mês traduzido ou o original caso não mapeado
}

// Filtros (Ano e Tipo de Procedimento)
$anoSelecionado = $_GET['ano'] ?? date('Y');
$tipoSelecionado = $_GET['tipo'] ?? '';
if (empty($tipoSelecionado)) {
    $tipoSelecionado = 1; // ID do tipo "IP"
}



// Busca de Procedimentos
$query = "
    SELECT 
    p.ID,
    DATE_FORMAT(p.DataInstauracao, '%M') AS Mes, -- Nome do mês
    p.DataInstauracao,
    p.MotivoAparente,
    sp.Nome AS Situacao, -- Situação do procedimento
    p.NumeroProcedimento,
    p.Dependente,
    op.Nome AS Origem,
    (
        SELECT GROUP_CONCAT(DISTINCT v.Nome SEPARATOR ', ') 
        FROM Vitimas v 
        WHERE v.ProcedimentoID = p.ID
    ) AS Vitimas,
    (
        SELECT GROUP_CONCAT(DISTINCT i.Nome SEPARATOR ', ') 
        FROM Investigados i 
        WHERE i.ProcedimentoID = p.ID
    ) AS Investigados,
    (
        SELECT GROUP_CONCAT(CONCAT(c.Nome, ' - ', vc.Modalidade) SEPARATOR '\n') 
        FROM Vitimas_Crimes vc 
        LEFT JOIN Crimes c ON vc.CrimeID = c.ID 
        WHERE vc.VitimaID IN (SELECT v.ID FROM Vitimas v WHERE v.ProcedimentoID = p.ID)
    ) AS Crimes,
    (
        SELECT GROUP_CONCAT(DISTINCT me.Nome SEPARATOR ', ') 
        FROM ProcedimentosMeiosEmpregados pme 
        LEFT JOIN MeiosEmpregados me ON pme.MeioEmpregadoID = me.ID 
        WHERE pme.ProcedimentoID = p.ID
    ) AS MeiosEmpregados,
    (
        SELECT GROUP_CONCAT(DISTINCT r.Numero SEPARATOR '\n')
        FROM RAIs r
        WHERE r.ProcedimentoID = p.ID
    ) AS RAIs,
(
    SELECT 
        CASE 
            WHEN COUNT(*) = 0 THEN NULL
            ELSE GROUP_CONCAT(CONCAT(pj.Numero, ' (', COALESCE(pj.Descricao, 'Sem descrição'), ')') SEPARATOR '\n')
        END
    FROM ProcessosJudiciais pj
    WHERE pj.ProcedimentoID = p.ID
) AS ProcessosJudiciais,

    uEscrivao.Nome AS Escrivao,
    (
        SELECT COUNT(*)
        FROM Movimentacoes m
        WHERE m.ProcedimentoID = p.ID 
          AND m.TipoID = (SELECT ID FROM TiposMovimentacao WHERE Nome = 'Requisição MP')
          AND m.Situacao = 'Em andamento'
    ) AS RequisicaoMP
FROM Procedimentos p
LEFT JOIN TiposProcedimento tp ON p.TipoID = tp.ID
LEFT JOIN OrigensProcedimentos op ON p.OrigemID = op.ID
LEFT JOIN Usuarios uEscrivao ON p.EscrivaoID = uEscrivao.ID
LEFT JOIN SituacoesProcedimento sp ON p.SituacaoID = sp.ID -- Junção com a tabela de Situações
WHERE YEAR(p.DataInstauracao) = :ano
  AND (:tipo = '' OR p.TipoID = :tipo)
ORDER BY p.DataInstauracao ASC;

";



$stmt = $pdo->prepare($query);
$stmt->execute(['ano' => $anoSelecionado, 'tipo' => $tipoSelecionado]);
$procedimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$procedimentosPorMes = [];
foreach ($procedimentos as $proc) {
    // Converte o nome do mês retornado do banco para português
    $mesEmPortugues = obterMesEmPortugues($proc['Mes']);
    $procedimentosPorMes[$mesEmPortugues][] = $proc;
}

?>

<div class="container mt-5" style="max-width: 95%; margin: 0 auto;">

    <h3 class="text-center mb-4">Procedimentos por Ordem de Instauração</h3>

    <!-- Filtros -->
    <form method="GET" class="mb-2">
        <div class="row justify-content-center">
            <div class="col-md-3">
    <label for="ano" class="form-label">Ano</label>
    <select id="ano" name="ano" class="form-select">
        <option value="todos" <?= ($anoSelecionado === 'todos') ? 'selected' : '' ?>>Todos</option>
        <?php for ($i = date('Y'); $i >= 2000; $i--): ?>
            <option value="<?= $i ?>" <?= ($i == $anoSelecionado) ? 'selected' : '' ?>><?= $i ?></option>
        <?php endfor; ?>
    </select>
</div>

            <div class="col-md-3">
                <label for="tipo" class="form-label">Tipo de Procedimento</label>
                <select id="tipo" name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <?php
                    $tipos = $pdo->query("SELECT ID, Nome FROM TiposProcedimento ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($tipos as $tipo):
                        $selecionado = ($tipo['Nome'] === 'IP' && empty($tipoSelecionado)) || ($tipo['ID'] == $tipoSelecionado) ? 'selected' : '';
                    ?>
                        <option value="<?= $tipo['ID'] ?>" <?= $selecionado ?>>
                            <?= htmlspecialchars($tipo['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>

            <div class="col-md-2 align-self-end">
                <!-- Botão Exportar CSV -->
                <a 
                    href="exportar_csv.php?ano=<?= htmlspecialchars($anoSelecionado) ?>&tipo=<?= htmlspecialchars($tipoSelecionado) ?>" 
                    class="btn btn-success w-100">
                    Exportar CSV
                </a>
            </div>
        </div>
    </form>


    <!-- Resultados -->
    <?php if (empty($procedimentosPorMes)): ?>
        <p class="text-center text-muted">Nenhum procedimento encontrado para o filtro aplicado.</p>
    <?php else: ?>
        <?php foreach ($procedimentosPorMes as $mes => $procs): ?>
            <h3 class="mt-5"><?= htmlspecialchars($anoSelecionado) ?> - <?= htmlspecialchars($mes) ?></h3>
<style>
    /* Ajusta o tamanho do texto da tabela */
    .tabela-compacta {
        font-size: 0.75rem; /* Tamanho reduzido */
        line-height: 1.2; /* Reduz o espaçamento entre linhas */
    }

    /* Impede quebra de linha */
    .no-wrap {
        white-space: nowrap;
    }
</style>


<div class="table-responsive" style="overflow-x: auto;">
    <table class="table table-striped table-bordered tabela-compacta">
<thead class="table-dark">
    <tr>
        <th class="no-wrap">Instauração</th>
        <th class="no-wrap">Situação</th>
        <th class="no-wrap">Número</th>
        <th class="no-wrap">RAI</th>
        <th class="no-wrap">Origem</th>
        <th class="no-wrap">Vítimas</th>
        <th class="no-wrap">Investigados</th>
        <th class="no-wrap">Crimes</th>
        <th class="no-wrap">Meio Empregado</th>
        <th class="no-wrap">Processo Judicial</th>
        <th class="no-wrap">Requisição MP</th>
        <th class="no-wrap">Escrivão</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($procs as $proc): ?>
        <tr>
            <td class="no-wrap"><?= htmlspecialchars(date('d/m/Y', strtotime($proc['DataInstauracao']))) ?></td>
            <td class="no-wrap"><?= htmlspecialchars($proc['Situacao'] ?? 'Não informado') ?></td>
            <td class="no-wrap">
                <a href="ver_procedimento.php?id=<?= $proc['ID'] ?>">
                    <?= htmlspecialchars($proc['NumeroProcedimento']) ?>
                </a> - <i class="bi bi-info-circle text-dark"
           data-bs-toggle="tooltip"
           data-bs-placement="top"
           title="<?= htmlspecialchars($proc['MotivoAparente']) ?>"></i>
            </td>
<td class="no-wrap">
    <?php if (!empty($proc['RAIs'])): ?>
        <!-- Converte '\n' em <br> para quebra de linha -->
        <?= nl2br(htmlspecialchars($proc['RAIs'])) ?>
    <?php else: ?>
        <span class="text-muted">Nenhum RAI</span>
    <?php endif; ?>
</td>
            <td class="no-wrap"><?= htmlspecialchars($proc['Origem'] ?? 'Não informado') ?></td>
            <td>
                <?php if (!empty($proc['Vitimas'])): ?>
                    <?php foreach (explode(',', $proc['Vitimas']) as $vitima): ?>
                        <div class="no-wrap"><?= htmlspecialchars($vitima) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    Nenhuma
                <?php endif; ?>
            </td>
            <td>
                <?php if (!empty($proc['Investigados'])): ?>
                    <?php foreach (explode(',', $proc['Investigados']) as $investigado): ?>
                        <div class="no-wrap"><?= htmlspecialchars($investigado) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    Nenhum
                <?php endif; ?>
            </td>
            <td class="no-wrap">
                <?php if (!empty($proc['Crimes'])): ?>
                    <?php 
                    $crimesArray = array_unique(explode("\n", $proc['Crimes'])); 
                    foreach ($crimesArray as $crime): ?>
                        <div><?= htmlspecialchars(trim($crime)) ?></div>
                    <?php endforeach; ?>
                    <?php if (!empty($proc['Dependente']) && $proc['Dependente'] == 1): ?>
                        <small class="text-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> Fora da estatística
                        </small>
                    <?php endif; ?>
                <?php else: ?>
                    Nenhum
                <?php endif; ?>
            </td>
            <td class="no-wrap">
                <?php if (!empty($proc['MeiosEmpregados'])): ?>
                    <?php foreach (explode(',', $proc['MeiosEmpregados']) as $meio): ?>
                        <div><?= htmlspecialchars(trim($meio)) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    Nenhum
                <?php endif; ?>
            </td>
<td class="no-wrap">
    <?php if (!empty($proc['ProcessosJudiciais']) && trim($proc['ProcessosJudiciais']) !== "()"): ?>
        <?= nl2br(htmlspecialchars($proc['ProcessosJudiciais'])) ?>
    <?php else: ?>
        <span class="text-muted">Nenhum Processo Judicial</span>
    <?php endif; ?>
</td>


<td class="no-wrap">
    <?php if ($proc['RequisicaoMP'] > 0): ?>
        <span class="badge badge-purple">Sim</span>
    <?php else: ?>
        Não
    <?php endif; ?>
</td>

            <td class="no-wrap"><?= htmlspecialchars($proc['Escrivao'] ?? 'Não informado') ?></td>


        </tr>
    <?php endforeach; ?>
</tbody>


    </table>
</div>

        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl, {
                html: true, // Permite HTML na tooltip
                container: 'body' // Garante que a tooltip não quebre o layout
            });
        });
    });
</script>


<?php include '../includes/footer.php'; ?>
