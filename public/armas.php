<?php
include '../includes/header.php';
require_once '../config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para acessar esta página.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter os valores para os filtros
$especies = $pdo->query("SELECT ID, Nome FROM ArmaEspecie ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
$calibres = $pdo->query("SELECT ID, Nome FROM ArmaCalibre ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
$marcas = $pdo->query("SELECT ID, Nome FROM ArmaMarca ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
$situacoes = $pdo->query("SELECT ID, Nome FROM SituacoesObjeto ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);
$locais = $pdo->query("SELECT ID, Nome FROM LocaisArmazenagem ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);

// Campos de busca
$filtros = [
    'especie' => $_GET['especie'] ?? '',
    'calibre' => $_GET['calibre'] ?? '',
    'marca' => $_GET['marca'] ?? '',
    'local' => $_GET['local'] ?? '',
    'numero_serie' => $_GET['numero_serie'] ?? '',
    'lacre' => $_GET['lacre'] ?? '',
    'possui_processo' => $_GET['possui_processo'] ?? '',
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_fim' => $_GET['data_fim'] ?? '',
];

// Contar o total de registros para paginação
$query_count = "
    SELECT COUNT(*) AS total
    FROM Objetos o
    INNER JOIN ArmasFogo a ON o.ID = a.ObjetoID
    INNER JOIN ArmaEspecie ae ON a.EspecieID = ae.ID
    INNER JOIN ArmaCalibre ac ON a.CalibreID = ac.ID
    INNER JOIN ArmaMarca am ON a.MarcaID = am.ID
    INNER JOIN ArmaModelo amo ON a.ModeloID = amo.ID
    INNER JOIN LocaisArmazenagem la ON o.LocalArmazenagemID = la.ID
    INNER JOIN Procedimentos p ON o.ProcedimentoID = p.ID
    LEFT JOIN ProcessosJudiciais pj ON a.ProcessoJudicialID = pj.ID
    WHERE 1=1
";

// Adiciona os filtros na contagem
if (!empty($filtros['especie'])) $query_count .= " AND ae.ID = :especie";
if (!empty($filtros['calibre'])) $query_count .= " AND ac.ID = :calibre";
if (!empty($filtros['marca'])) $query_count .= " AND am.ID = :marca";
if (!empty($filtros['local'])) $query_count .= " AND la.ID = :local";
if (!empty($filtros['numero_serie'])) $query_count .= " AND a.NumeroSerie LIKE :numero_serie";
if (!empty($filtros['lacre'])) $query_count .= " AND o.LacreAtual LIKE :lacre";
if ($filtros['possui_processo'] === 'sim') $query_count .= " AND a.ProcessoJudicialID IS NOT NULL";
if ($filtros['possui_processo'] === 'nao') $query_count .= " AND a.ProcessoJudicialID IS NULL";
// Adiciona filtros de período na contagem
if (!empty($filtros['data_inicio'])) $query_count .= " AND o.DataApreensao >= :data_inicio";
if (!empty($filtros['data_fim'])) $query_count .= " AND o.DataApreensao <= :data_fim";

// Prepara e executa a contagem
$stmt_count = $pdo->prepare($query_count);
foreach ($filtros as $key => $value) {
    if (!empty($value) && $key !== 'possui_processo') {
        if ($key === 'numero_serie' || $key === 'lacre') {
            $stmt_count->bindValue(":$key", "%$value%", PDO::PARAM_STR);
        } else if ($key === 'data_inicio' || $key === 'data_fim') {
            $stmt_count->bindValue(":$key", $value, PDO::PARAM_STR);
        } else {
            $stmt_count->bindValue(":$key", $value, PDO::PARAM_STR);
        }
    }
}
$stmt_count->execute();
$total_records = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

// Configuração de paginação
$records_per_page = 20;
$total_pages = ceil($total_records / $records_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Construção da query para listar armas
$query = "
    SELECT 
        o.ID AS ObjetoID,
        o.ProcedimentoID as ProcedimentoID,
        (
            SELECT tmo.Nome 
            FROM MovimentacoesObjeto mo
            INNER JOIN TiposMovimentacaoObjeto tmo ON mo.TipoMovimentacaoID = tmo.ID
            WHERE mo.ObjetoID = o.ID
            ORDER BY mo.DataMovimentacao DESC, mo.ID DESC
            LIMIT 1
        ) AS UltimaMovimentacao,
        ae.Nome AS Especie,
        ac.Nome AS Calibre,
        am.Nome AS Marca,
        amo.Nome AS Modelo,
        a.NumeroSerie,
        o.LacreAtual,
        o.DataApreensao,
        la.Nome AS LocalArmazenagem,
        p.NumeroProcedimento,
        pj.Numero AS Processo
    FROM Objetos o
    INNER JOIN ArmasFogo a ON o.ID = a.ObjetoID
    INNER JOIN ArmaEspecie ae ON a.EspecieID = ae.ID
    INNER JOIN ArmaCalibre ac ON a.CalibreID = ac.ID
    INNER JOIN ArmaMarca am ON a.MarcaID = am.ID
    INNER JOIN ArmaModelo amo ON a.ModeloID = amo.ID
    INNER JOIN LocaisArmazenagem la ON o.LocalArmazenagemID = la.ID
    INNER JOIN Procedimentos p ON o.ProcedimentoID = p.ID
    LEFT JOIN ProcessosJudiciais pj ON a.ProcessoJudicialID = pj.ID
    WHERE 1=1
";

// Adiciona os filtros selecionados
if (!empty($filtros['especie'])) $query .= " AND ae.ID = :especie";
if (!empty($filtros['calibre'])) $query .= " AND ac.ID = :calibre";
if (!empty($filtros['marca'])) $query .= " AND am.ID = :marca";
if (!empty($filtros['local'])) $query .= " AND la.ID = :local";
if (!empty($filtros['numero_serie'])) $query .= " AND a.NumeroSerie LIKE :numero_serie";
if (!empty($filtros['lacre'])) $query .= " AND o.LacreAtual LIKE :lacre";
if ($filtros['possui_processo'] === 'sim') $query .= " AND a.ProcessoJudicialID IS NOT NULL";
if ($filtros['possui_processo'] === 'nao') $query .= " AND a.ProcessoJudicialID IS NULL";
// Adiciona filtros de período
if (!empty($filtros['data_inicio'])) $query .= " AND o.DataApreensao >= :data_inicio";
if (!empty($filtros['data_fim'])) $query .= " AND o.DataApreensao <= :data_fim";

// Ordenação e paginação
$query .= " ORDER BY o.DataApreensao DESC LIMIT :offset, :limit";

$stmt = $pdo->prepare($query);
foreach ($filtros as $key => $value) {
    if (!empty($value) && $key !== 'possui_processo') {
        if ($key === 'numero_serie' || $key === 'lacre') {
            $stmt->bindValue(":$key", "%$value%", PDO::PARAM_STR);
        } else if ($key === 'data_inicio' || $key === 'data_fim') {
            $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
        }
    }
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$armas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar os tipos de movimentação para o modal
$tiposMovimentacao = $pdo->query("SELECT ID, Nome FROM TiposMovimentacaoObjeto ORDER BY Nome")->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-5" style="max-width: 95%; margin: 0 auto;">
    <h1 class="text-center">Armas de Fogo</h1>

    <!-- Botão de Movimentação em Lote -->
    <div class="mb-3">
        <button type="button" id="btnMovimentarLote" class="btn btn-primary" disabled>
            <i class="fas fa-exchange-alt"></i> Movimentar em Lote
        </button>
    </div>

    <!-- Formulário de filtros -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="especie" class="form-label">Espécie</label>
                <select name="especie" id="especie" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($especies as $especie): ?>
                        <option value="<?= $especie['ID'] ?>" <?= $filtros['especie'] == $especie['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($especie['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="calibre" class="form-label">Calibre</label>
                <select name="calibre" id="calibre" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($calibres as $calibre): ?>
                        <option value="<?= $calibre['ID'] ?>" <?= $filtros['calibre'] == $calibre['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($calibre['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="marca" class="form-label">Marca</label>
                <select name="marca" id="marca" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($marcas as $marca): ?>
                        <option value="<?= $marca['ID'] ?>" <?= $filtros['marca'] == $marca['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($marca['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="local" class="form-label">Local de Armazenagem</label>
                <select name="local" id="local" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($locais as $local): ?>
                        <option value="<?= $local['ID'] ?>" <?= $filtros['local'] == $local['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($local['Nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="numero_serie" class="form-label">Número de Série</label>
                <input type="text" name="numero_serie" id="numero_serie" class="form-control" value="<?= htmlspecialchars($filtros['numero_serie']) ?>" placeholder="Número de Série">
            </div>
            <div class="col-md-3 mb-3">
                <label for="lacre" class="form-label">Lacre Atual</label>
                <input type="text" name="lacre" id="lacre" class="form-control" value="<?= htmlspecialchars($filtros['lacre']) ?>" placeholder="Lacre Atual">
            </div>
            <div class="col-md-3 mb-3">
                <label for="possui_processo" class="form-label">Possui Processo Vinculado?</label>
                <select name="possui_processo" id="possui_processo" class="form-select">
                    <option value="">Todos</option>
                    <option value="sim" <?= $filtros['possui_processo'] == 'sim' ? 'selected' : '' ?>>Sim</option>
                    <option value="nao" <?= $filtros['possui_processo'] == 'nao' ? 'selected' : '' ?>>Não</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="data_inicio" class="form-label">Data Inicial</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="data_fim" class="form-label">Data Final</label>
                <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= htmlspecialchars($filtros['data_fim']) ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="armas.php" class="btn btn-secondary">Limpar Filtros</a>
    </form>

    <!-- Formulário para movimentação em lote -->
    <form id="formMovimentacaoLote" method="POST" action="movimentar_objetos_lote.php">
        <input type="hidden" name="procedimento_id" value="0">
        
        <!-- Tabela de armas -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selecionarTodos">
                        </div>
                    </th>
                    <th>Última Movimentação</th>
                    <th>Espécie</th>
                    <th>Calibre</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Número de Série</th>
                    <th>Lacre Atual</th>
                    <th>Data da Apreensão</th>
                    <th>Local de Armazenagem</th>
                    <th>Procedimento Relacionado</th>
                    <th>Processo</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($armas): ?>
                    <?php foreach ($armas as $arma): ?>
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input objeto-checkbox" type="checkbox" 
                                           name="objetos[]" value="<?= $arma['ObjetoID'] ?>"
                                           data-procedimento="<?= $arma['ProcedimentoID'] ?>">
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($arma['UltimaMovimentacao'])): ?>
                                    <span class="badge bg-primary"><?= htmlspecialchars($arma['UltimaMovimentacao']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sem movimentação</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($arma['Especie']) ?></td>
                            <td><?= htmlspecialchars($arma['Calibre']) ?></td>
                            <td><?= htmlspecialchars($arma['Marca']) ?></td>
                            <td><?= htmlspecialchars($arma['Modelo']) ?></td>
                            <td><?= htmlspecialchars($arma['NumeroSerie']) ?></td>
                            <td><?= htmlspecialchars($arma['LacreAtual']) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($arma['DataApreensao']))) ?></td>
                            <td><?= htmlspecialchars($arma['LocalArmazenagem']) ?></td>
                            <td>
                                <?php if (!empty($arma['NumeroProcedimento'])): ?>
                                    <a href="ver_procedimento.php?id=<?= htmlspecialchars($arma['ProcedimentoID']) ?>">
                                        <?= htmlspecialchars($arma['NumeroProcedimento']) ?>
                                    </a>
                                <?php else: ?>
                                    Não informado
                                <?php endif; ?>
                            </td>
                            <td><?= !empty($arma['Processo']) ? htmlspecialchars($arma['Processo']) : 'Sem processo vinculado' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" class="text-center">Nenhuma arma encontrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>

    <!-- Modal de Movimentação em Lote -->
    <div class="modal fade" id="modalMovimentacaoLote" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Movimentar Armas em Lote</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formMovimentacao">
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
                                <option value="">Selecione o Tipo</option>
                                <?php foreach ($tiposMovimentacao as $tipo): ?>
                                    <option value="<?= $tipo['ID'] ?>" data-nome="<?= htmlspecialchars($tipo['Nome']) ?>">
                                        <?= htmlspecialchars($tipo['Nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="observacao" class="form-label">Observação</label>
                            <textarea name="observacao" id="observacao" class="form-control" rows="3" placeholder="Digite uma observação sobre a movimentação (opcional)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarMovimentacao">Confirmar Movimentação</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Paginação -->
    <nav aria-label="Pagination">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" tabindex="-1">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">Próxima</a>
            </li>
        </ul>
    </nav>
</div>

<script>
$(document).ready(function() {
    // Função para normalizar texto (remover acentos e converter para maiúsculas)
    function normalizarTexto(texto) {
        return texto.normalize('NFD')
                   .replace(/[\u0300-\u036f]/g, '')
                   .toUpperCase();
    }

    // Função para analisar os procedimentos selecionados
    function analisarProcedimentos() {
        const objetosSelecionados = $('.objeto-checkbox:checked');
        
        if (objetosSelecionados.length === 0) {
            return {
                total: 0,
                procedimentos: new Set(),
                podeMovimentar: false,
                detalhes: []
            };
        }

        const procedimentos = new Set();
        const detalhes = [];
        
        objetosSelecionados.each(function() {
            const procedimentoID = $(this).data('procedimento');
            const row = $(this).closest('tr');
            
            if (procedimentoID) {
                procedimentos.add(procedimentoID.toString());
                
                // Capturar detalhes da arma
                detalhes.push({
                    objetoID: $(this).val(),
                    procedimentoID: procedimentoID,
                    procedimentoNumero: row.find('td:nth-child(11) a').text().trim() || 'N/A',
                    especie: row.find('td:nth-child(3)').text().trim(),
                    marca: row.find('td:nth-child(5)').text().trim(),
                    modelo: row.find('td:nth-child(6)').text().trim(),
                    numeroSerie: row.find('td:nth-child(7)').text().trim()
                });
            }
        });
        
        return {
            total: objetosSelecionados.length,
            procedimentos: procedimentos,
            procedimentosCount: procedimentos.size,
            podeMovimentar: objetosSelecionados.length > 0,
            detalhes: detalhes
        };
    }

    // Selecionar/Deselecionar todos
    $('#selecionarTodos').change(function() {
        const isChecked = $(this).prop('checked');
        $('.objeto-checkbox').prop('checked', isChecked);
        atualizarBotaoMovimentacao();
    });

    // Atualizar checkbox "selecionar todos" e estado do botão
    $('.objeto-checkbox').change(function() {
        const totalCheckboxes = $('.objeto-checkbox').length;
        const checkboxesMarcados = $('.objeto-checkbox:checked').length;
        
        // Atualizar estado do checkbox "selecionar todos"
        if (checkboxesMarcados === 0) {
            $('#selecionarTodos').prop('indeterminate', false);
            $('#selecionarTodos').prop('checked', false);
        } else if (checkboxesMarcados === totalCheckboxes) {
            $('#selecionarTodos').prop('indeterminate', false);
            $('#selecionarTodos').prop('checked', true);
        } else {
            $('#selecionarTodos').prop('indeterminate', true);
            $('#selecionarTodos').prop('checked', false);
        }
        
        atualizarBotaoMovimentacao();
    });

    // Atualizar estado do botão de movimentação
    function atualizarBotaoMovimentacao() {
        const analise = analisarProcedimentos();
        
        console.log('Análise dos procedimentos:', analise);
        
        const btnMovimentar = $('#btnMovimentarLote');
        
        // Habilita o botão se houver objetos selecionados
        btnMovimentar.prop('disabled', analise.total === 0);
        
        // Atualizar texto do botão com informações úteis
        if (analise.total === 0) {
            btnMovimentar.html('<i class="fas fa-exchange-alt"></i> Movimentar em Lote');
        } else if (analise.procedimentosCount === 1) {
            btnMovimentar.html(`<i class="fas fa-exchange-alt"></i> Movimentar ${analise.total} arma(s) - 1 procedimento`);
        } else {
            btnMovimentar.html(`<i class="fas fa-exchange-alt"></i> Movimentar ${analise.total} arma(s) - ${analise.procedimentosCount} procedimentos`);
        }
        
        // Armazenar dados globalmente para uso no modal
        window.dadosSelecao = analise;
        
        // Atualizar campo hidden
        if (analise.procedimentosCount === 1) {
            $('input[name="procedimento_id"]').val(Array.from(analise.procedimentos)[0]);
        } else {
            $('input[name="procedimento_id"]').val('MULTIPLOS');
        }
    }

    // Função para gerar resumo detalhado
    function gerarResumoSelecao(analise) {
        let html = `
            <div class="alert alert-info mb-3">
                <h6><i class="fas fa-info-circle"></i> Resumo da Seleção</h6>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Total de armas:</strong> ${analise.total}
                    </div>
                    <div class="col-md-4">
                        <strong>Procedimentos:</strong> ${analise.procedimentosCount}
                    </div>
                    <div class="col-md-4">
                        <strong>Status:</strong> 
                        ${analise.procedimentosCount === 1 
                            ? '<span class="badge bg-success">Mesmo procedimento</span>' 
                            : '<span class="badge bg-warning">Múltiplos procedimentos</span>'}
                    </div>
                </div>
        `;

        if (analise.procedimentosCount > 1) {
            html += `
                <div class="alert alert-warning alert-sm mb-2">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Atenção:</strong> Você está movimentando armas de diferentes procedimentos. 
                    Verifique se a movimentação é apropriada para todos os casos.
                </div>
            `;
        }

        // Agrupar por procedimento
        const porProcedimento = {};
        analise.detalhes.forEach(item => {
            if (!porProcedimento[item.procedimentoID]) {
                porProcedimento[item.procedimentoID] = {
                    numero: item.procedimentoNumero,
                    armas: []
                };
            }
            porProcedimento[item.procedimentoID].armas.push(item);
        });

        html += '<div class="accordion accordion-flush" id="accordionProcedimentos">';
        
        Object.keys(porProcedimento).forEach((procId, index) => {
            const proc = porProcedimento[procId];
            html += `
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading${index}">
                        <button class="accordion-button ${index === 0 ? '' : 'collapsed'}" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#collapse${index}">
                            <strong>Procedimento:</strong> ${proc.numero} 
                            <span class="badge bg-primary ms-2">${proc.armas.length} arma(s)</span>
                        </button>
                    </h2>
                    <div id="collapse${index}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" 
                         data-bs-parent="#accordionProcedimentos">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Espécie</th>
                                            <th>Marca/Modelo</th>
                                            <th>Nº Série</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;
            
            proc.armas.forEach(arma => {
                html += `
                    <tr>
                        <td>${arma.especie}</td>
                        <td>${arma.marca} ${arma.modelo}</td>
                        <td><code>${arma.numeroSerie}</code></td>
                    </tr>
                `;
            });
            
            html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div></div>';
        
        return html;
    }

    // Abrir modal de movimentação
    $('#btnMovimentarLote').click(function() {
        const analise = window.dadosSelecao || analisarProcedimentos();
        
        if (analise.total === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Selecione pelo menos uma arma para movimentar.'
            });
            return;
        }

        // Confirmação especial para múltiplos procedimentos
        if (analise.procedimentosCount > 1) {
            Swal.fire({
                title: 'Múltiplos Procedimentos Detectados',
                html: `
                    <div class="text-start">
                        <p>Você selecionou <strong>${analise.total} armas</strong> de <strong>${analise.procedimentosCount} procedimentos diferentes</strong>.</p>
                        <p class="text-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Certifique-se de que a movimentação em lote é apropriada para todos os procedimentos envolvidos.
                        </p>
                        <p>Deseja continuar?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, continuar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ffc107'
            }).then((result) => {
                if (result.isConfirmed) {
                    abrirModal(analise);
                }
            });
        } else {
            abrirModal(analise);
        }
    });

    function abrirModal(analise) {
        // Adicionar resumo ao modal
        const resumoHtml = gerarResumoSelecao(analise);
        
        // Remover resumo anterior se existir
        $('#resumo-selecao').remove();
        
        // Adicionar novo resumo
        $('.modal-body form').prepend('<div id="resumo-selecao">' + resumoHtml + '</div>');
        
        $('#modalMovimentacaoLote').modal('show');
    }

    // Atualizar a função de confirmação
    $('#btnConfirmarMovimentacao').click(function() {
        const form = $('#formMovimentacao');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        const analise = window.dadosSelecao || analisarProcedimentos();
        const tipoMovimentacao = $('#tipo_movimentacao option:selected').text();
        const dataMovimentacao = $('#data_movimentacao').val();

        // Formata a data para exibição
        const dataFormatada = new Date(dataMovimentacao).toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        let mensagemConfirmacao = `
            <div class="text-start">
                <p><strong>Quantidade de armas:</strong> ${analise.total}</p>
                <p><strong>Data da movimentação:</strong> ${dataFormatada}</p>
                <p><strong>Procedimentos envolvidos:</strong> ${analise.procedimentosCount}</p>
                <p><strong>Tipo de movimentação:</strong> ${tipoMovimentacao}</p>
        `;

        if (analise.procedimentosCount > 1) {
            mensagemConfirmacao += `
                <div class="alert alert-warning mt-2">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Esta movimentação afetará ${analise.procedimentosCount} procedimentos diferentes.
                </div>
            `;
        }

        mensagemConfirmacao += '</div>';

        Swal.fire({
            title: 'Confirmar Movimentação',
            html: mensagemConfirmacao,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd'
        }).then((result) => {
            if (result.isConfirmed) {
                realizarMovimentacao();
            }
        });
    });

    // Atualizar a função de realizar movimentação
    function realizarMovimentacao() {
        const formData = new FormData($('#formMovimentacaoLote')[0]);
        formData.append('tipo_movimentacao', $('#tipo_movimentacao').val());
        formData.append('observacao', $('#observacao').val());
        formData.append('data_movimentacao', $('#data_movimentacao').val());

        // Adicionar informação sobre múltiplos procedimentos
        const analise = window.dadosSelecao || analisarProcedimentos();
        formData.append('multiplos_procedimentos', analise.procedimentosCount > 1 ? '1' : '0');
        formData.append('total_procedimentos', analise.procedimentosCount);

        // Mostrar loading
        Swal.fire({
            title: 'Processando...',
            text: 'Movimentando armas, aguarde...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: 'movimentar_objetos_lote.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#modalMovimentacaoLote').modal('hide');
                
                let mensagemSucesso = 'Armas movimentadas com sucesso!';
                if (analise.procedimentosCount > 1) {
                    mensagemSucesso += ` A movimentação foi registrada em ${analise.procedimentosCount} procedimentos.`;
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: mensagemSucesso
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                console.error('Erro na requisição:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao movimentar as armas: ' + (xhr.responseText || 'Erro desconhecido')
                });
            }
        });
    }

    // Atualizar o evento de fechar o modal
    $('#modalMovimentacaoLote').on('hidden.bs.modal', function () {
        $('#formMovimentacao')[0].reset();
        // Restaura a data atual ao fechar o modal
        $('#data_movimentacao').val(new Date().toISOString().slice(0, 16));
        $('#resumo-selecao').remove();
        window.dadosSelecao = null;
    });

    // Inicializar interface
    atualizarBotaoMovimentacao();

    // Debug: Mostrar dados dos checkboxes ao carregar a página
    console.log('=== DEBUG: Dados dos checkboxes ===');
    $('.objeto-checkbox').each(function() {
        console.log('Checkbox ID:', $(this).val(), 'Procedimento:', $(this).data('procedimento'));
    });
});
</script>

<?php include '../includes/footer.php'; ?>