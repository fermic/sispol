<?php
require_once 'funcoes.php';

// Obter a data atual
$dataAtual = date('Y-m-d');
$escalas = obterEscalasComSubstituicoes(); // Função que retorna escalas com substituições detalhadas
usort($escalas, function($a, $b) {
    return strtotime($a['data_inicio']) - strtotime($b['data_inicio']);
});

$plantaoHoje = obterPoliciaisDePlantao($dataAtual); // Função para obter os policiais de plantão hoje

// Filtrar a escala da semana atual
$escalaAtual = null;
foreach ($escalas as $escala) {
    if ($dataAtual >= $escala['data_inicio'] && $dataAtual <= $escala['data_fim']) {
        $escalaAtual = $escala;
        break;
    }
}

$policialFiltro = isset($_GET['policial']) ? trim($_GET['policial']) : null;

// Obter lista de todos os policiais
$policiais = obterTodosPoliciais(); // Função deve retornar um array com todos os policiais

// Função para destacar nome
function destacarNome($nome, $filtro) {
    if ($filtro && stripos($nome, $filtro) !== false) {
        return "<span class='text-warning fw-bold'>$nome</span>";
    }
    return $nome;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Plantão da Morte</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .semana-atual {
            background-color: #f0f8ff; /* Destaque para a semana atual */
        }
        .table td, .table th {
            vertical-align: middle; /* Centraliza verticalmente */
        }
        .policia-hoje {
            color: #000000; /* Preto para os policiais de plantão hoje */
            font-weight: bold;
        }
        .table-warning {
            background-color: #fff3cd !important; /* Fundo destacado para filtro */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center text-danger">Plantão da Morte</h1>

        <!-- Botão Adicionar Escala -->
        <div class="d-flex justify-content-end mb-3">
            <a href="cadastrar_plantao.php" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Adicionar Escala
            </a>
        </div>

        <!-- Tabela da Semana Atual -->
        <?php if ($escalaAtual): ?>
            <div class="mt-5">
                <h3 class="text-primary">Semana Atual</h3>
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Semana</th>
                            <th>Policial 1</th>
                            <th>Policial 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="semana-atual">
                            <td>
                                <?= date('d/m/Y', strtotime($escalaAtual['data_inicio'])) ?> à <?= date('d/m/Y', strtotime($escalaAtual['data_fim'])) ?>
                            </td>
                            <td>
<?php if ($escalaAtual['policial1_substituido_todos']): ?>
    <span style="text-decoration: line-through;">
        <b><?= $escalaAtual['policial1_original'] ?></b>
    </span>
    ➡ <b><?= $escalaAtual['policial1_substituto'] ?></b>
<?php elseif (!empty($escalaAtual['policial1_substituicoes'])): ?>
    <b><?= $escalaAtual['policial1_original'] ?></b>
    <?php
    // Remove duplicatas e ordena as substituições
    $policial1SubstituicoesOrdenadas = array_unique($escalaAtual['policial1_substituicoes'], SORT_REGULAR);
    usort($policial1SubstituicoesOrdenadas, function($a, $b) {
        return strtotime($a['dia']) - strtotime($b['dia']);
    });
    foreach ($policial1SubstituicoesOrdenadas as $substituicao): ?>
        <br>
        <small>
            <?= $substituicao['substituto'] ?> - (<?= date('d/m', strtotime($substituicao['dia'])) ?>)
        </small>
    <?php endforeach; ?>
<?php else: ?>
    <b><?= $escalaAtual['policial1_original'] ?></b>
<?php endif; ?>

                            </td>
                            <td>
<?php if ($escalaAtual['policial2_substituido_todos']): ?>
    <span style="text-decoration: line-through;">
        <b><?= $escalaAtual['policial2_original'] ?></b>
    </span>
    ➡ <b><?= $escalaAtual['policial2_substituto'] ?></b>
<?php elseif (!empty($escalaAtual['policial2_substituicoes'])): ?>
    <b><?= $escalaAtual['policial2_original'] ?></b>
    <?php
    // Remove duplicatas e ordena as substituições
    $policial2SubstituicoesOrdenadas = array_unique($escalaAtual['policial2_substituicoes'], SORT_REGULAR);
    usort($policial2SubstituicoesOrdenadas, function($a, $b) {
        return strtotime($a['dia']) - strtotime($b['dia']);
    });
    foreach ($policial2SubstituicoesOrdenadas as $substituicao): ?>
        <br>
        <small>
            <?= $substituicao['substituto'] ?> - (<?= date('d/m', strtotime($substituicao['dia'])) ?>)
        </small>
    <?php endforeach; ?>
<?php else: ?>
    <b><?= $escalaAtual['policial2_original'] ?></b>
<?php endif; ?>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Campo de Busca -->
        <div class="mb-4">
            <form method="GET" class="d-flex">
                <select name="policial" class="form-select me-2">
                    <option value="">Selecione um policial</option>
                    <?php foreach ($policiais as $policial): ?>
                        <option value="<?= htmlspecialchars($policial['nome']) ?>" <?= $policialFiltro === $policial['nome'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($policial['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </form>
        </div>

        <!-- Tabela: Todas as Escalas -->
        <div class="mt-5">
            <h3 class="text-primary">Escalas</h3>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Semana</th>
                        <th>Policial 1</th>
                        <th>Policial 2</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($escalas)): ?>
                        <?php foreach ($escalas as $escala): ?>
                            <?php
                            $destacar = $policialFiltro &&
                                (stripos($escala['policial1_original'], $policialFiltro) !== false ||
                                stripos($escala['policial2_original'], $policialFiltro) !== false);
                            ?>
                            <tr class="<?= ($dataAtual >= $escala['data_inicio'] && $dataAtual <= $escala['data_fim']) ? 'semana-atual' : '' ?> <?= $destacar ? 'table-warning' : '' ?>">
                                <td>
                                    <?= date('d/m/Y', strtotime($escala['data_inicio'])) ?> à <?= date('d/m/Y', strtotime($escala['data_fim'])) ?>
                                </td>
<td>
<?php if (!empty($escala['policial1_substituido_todos'])): ?>
    <span style="text-decoration: line-through;">
        <b><?= $escala['policial1_original'] ?></b>
    </span>
    ➡ <b><?= $escala['policial1_substituto'] ?></b>
<?php elseif (!empty($escala['policial1_substituicoes'])): ?>
    <?php
    // Ordena os dias em ordem crescente e remove duplicatas
    $policial1SubstituicoesOrdenadas = array_unique($escala['policial1_substituicoes'], SORT_REGULAR);
    usort($policial1SubstituicoesOrdenadas, function($a, $b) {
        return strtotime($a['dia']) - strtotime($b['dia']);
    });
    ?>
    <b><?= $escala['policial1_original'] ?></b>
    <?php foreach ($policial1SubstituicoesOrdenadas as $substituicao): ?>
        <br>
        <small>
            <?= $substituicao['substituto'] ?> - (<?= date('d/m', strtotime($substituicao['dia'])) ?>)
        </small>
    <?php endforeach; ?>
<?php else: ?>
    <b><?= $escala['policial1_original'] ?></b>
<?php endif; ?>
</td>

<td>
<?php if (!empty($escala['policial2_substituido_todos'])): ?>
    <span style="text-decoration: line-through;">
        <b><?= $escala['policial2_original'] ?></b>
    </span>
    ➡ <b><?= $escala['policial2_substituto'] ?></b>
<?php elseif (!empty($escala['policial2_substituicoes'])): ?>
    <?php
    // Ordena os dias em ordem crescente e remove duplicatas
    $policial2SubstituicoesOrdenadas = array_unique($escala['policial2_substituicoes'], SORT_REGULAR);
    usort($policial2SubstituicoesOrdenadas, function($a, $b) {
        return strtotime($a['dia']) - strtotime($b['dia']);
    });
    ?>
    <b><?= $escala['policial2_original'] ?></b>
    <?php foreach ($policial2SubstituicoesOrdenadas as $substituicao): ?>
        <br>
        <small>
            <?= $substituicao['substituto'] ?> - (<?= date('d/m', strtotime($substituicao['dia'])) ?>)
        </small>
    <?php endforeach; ?>
<?php else: ?>
    <b><?= $escala['policial2_original'] ?></b>
<?php endif; ?>
</td>

                                <td>
                                    <a href="gerenciar_plantao.php?escala_id=<?= $escala['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Realizar Troca
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhuma escala registrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
