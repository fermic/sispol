<?php
include '../includes/header.php';
require '../config/db.php'; // Conexão com o banco de dados

// Obter os filtros da URL ou definir valores padrão
$dataInicio = $_GET['data_inicio'] ?? null;
$dataFim = $_GET['data_fim'] ?? null;
$numeroProcedimento = $_GET['numero_procedimento'] ?? null;
$rai = $_GET['rai'] ?? null;
$envolvido = $_GET['envolvido'] ?? null;

// Construir a consulta com filtros
$query = "
    SELECT 
        cc.ID AS CumprimentoID,
        cc.DataCumprimento,
        p.NumeroProcedimento,
        p.ID AS ProcedimentoID,
        cc.RAI,
        tc.Nome AS TipoCautelar,
        GROUP_CONCAT(ec.Nome SEPARATOR ', ') AS Envolvidos
    FROM CumprimentosCautelares cc
    LEFT JOIN SolicitacoesCautelares sc ON cc.SolicitacaoCautelarID = sc.ID
    LEFT JOIN Procedimentos p ON sc.ProcedimentoID = p.ID
    LEFT JOIN TiposCautelar tc ON cc.TipoCautelarID = tc.ID
    LEFT JOIN EnvolvidosCumprimentoCautelar ec ON cc.ID = ec.CumprimentoCautelarID
    WHERE 1=1
";

// Adicionar filtros à consulta
$params = [];
if ($dataInicio && $dataFim) {
    $query .= " AND cc.DataCumprimento BETWEEN :dataInicio AND :dataFim";
    $params[':dataInicio'] = $dataInicio;
    $params[':dataFim'] = $dataFim;
}
if ($numeroProcedimento) {
    $query .= " AND p.NumeroProcedimento LIKE :numeroProcedimento";
    $params[':numeroProcedimento'] = "%{$numeroProcedimento}%";
}
if ($rai) {
    $query .= " AND cc.RAI LIKE :rai";
    $params[':rai'] = "%{$rai}%";
}
if ($envolvido) {
    $query .= " AND ec.Nome LIKE :envolvido";
    $params[':envolvido'] = "%{$envolvido}%";
}

$query .= "
    GROUP BY cc.ID, cc.DataCumprimento, p.NumeroProcedimento, cc.RAI, tc.Nome
    ORDER BY cc.DataCumprimento DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cumprimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2 class="mb-4">Lista de Cumprimentos de Medidas Cautelares Realizados</h2>

    <!-- Formulário de Filtros -->
<form method="get" class="mb-4">
    <div class="row g-2 align-items-end">
        <!-- Filtro por Data de Início -->
        <div class="col-auto">
            <label for="data_inicio" class="form-label">Data Início</label>
            <input type="date" name="data_inicio" id="data_inicio" 
                   class="form-control form-control-sm" 
                   value="<?= htmlspecialchars($_GET['data_inicio'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <!-- Filtro por Data de Fim -->
        <div class="col-auto">
            <label for="data_fim" class="form-label">Data Fim</label>
            <input type="date" name="data_fim" id="data_fim" 
                   class="form-control form-control-sm" 
                   value="<?= htmlspecialchars($_GET['data_fim'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <!-- Filtro por Número do Procedimento -->
        <div class="col-auto">
            <label for="numero_procedimento" class="form-label">Procedimento</label>
            <input type="text" name="numero_procedimento" id="numero_procedimento" 
                   class="form-control form-control-sm" 
                   value="<?= htmlspecialchars($_GET['numero_procedimento'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                   placeholder="Número">
        </div>

        <!-- Filtro por RAI -->
        <div class="col-auto">
            <label for="rai" class="form-label">RAI</label>
            <input type="text" name="rai" id="rai" 
                   class="form-control form-control-sm" 
                   value="<?= htmlspecialchars($_GET['rai'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                   placeholder="RAI">
        </div>

        <!-- Filtro por Envolvido -->
        <div class="col-auto">
            <label for="envolvido" class="form-label">Envolvido</label>
            <input type="text" name="envolvido" id="envolvido" 
                   class="form-control form-control-sm" 
                   value="<?= htmlspecialchars($_GET['envolvido'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                   placeholder="Nome do Envolvido">
        </div>

        <!-- Botões -->
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        </div>
        <div class="col-auto">
            <a href="listar_cumprimentos.php" class="btn btn-secondary btn-sm">Limpar</a>
        </div>
        <div class="col-auto">
            <a href="adicionar_cumprimento.php?origem=listagem_cumprimentos" class="btn btn-success btn-sm">Adicionar Cumprimento Avulso</a>
        </div>
    </div>
</form>


    <!-- Tabela de cumprimentos -->
<table class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Data do Cumprimento</th>
            <th>Procedimento</th>
            <th>RAI</th>
            <th>Tipo de Cautelar</th>
            <th>Envolvidos</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($cumprimentos) > 0): ?>
            <?php foreach ($cumprimentos as $cumprimento): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($cumprimento['DataCumprimento'] ?? '1970-01-01')) ?></td>
                    <td>
                        <?php if (!empty($cumprimento['NumeroProcedimento'])): ?>
                            <a href="ver_procedimento.php?id=<?= htmlspecialchars($cumprimento['ProcedimentoID'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($cumprimento['NumeroProcedimento'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        <?php else: ?>
                            Avulso
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($cumprimento['RAI'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($cumprimento['TipoCautelar'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($cumprimento['Envolvidos'] ?? 'Nenhum envolvido', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-center">
                        <a href="excluir_cumprimento.php?id=<?= htmlspecialchars($cumprimento['CumprimentoID'], ENT_QUOTES, 'UTF-8') ?>"
                           title="Excluir"
                           onclick="return confirm('Tem certeza que deseja excluir este cumprimento?');">
                            <i class="fas fa-trash-alt text-danger"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">Nenhum cumprimento registrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</div>

<?php include '../includes/footer.php'; ?>
