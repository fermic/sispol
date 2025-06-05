<?php
require_once 'funcoes.php';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $escalaId = $_POST['escala_id'];
    $policialSubstituido = $_POST['policial_substituido'];
    $policialSubstituto = $_POST['policial_substituto'];
    $diasSubstituicao = $_POST['dias_substituicao']; // Array com os dias selecionados

    try {
        global $conn;
        $conn->beginTransaction();

        // Insere substituições no banco
        $query = "
            INSERT INTO substituicoes (escala_id, dia_substituicao, policial_substituido_id, policial_substituto_id)
            VALUES (:escala_id, :dia_substituicao, :policial_substituido, :policial_substituto)
        ";
        $stmt = $conn->prepare($query);
        foreach ($diasSubstituicao as $dia) {
            $stmt->execute([
                'escala_id' => $escalaId,
                'dia_substituicao' => $dia,
                'policial_substituido' => $policialSubstituido,
                'policial_substituto' => $policialSubstituto,
            ]);
        }

        $conn->commit();
        header('Location: index.php'); // Redireciona para a página principal
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $mensagem = "Erro ao registrar substituições: " . $e->getMessage();
    }
}

// Obtém todas as escalas
try {
    global $conn;
    $escalas = $conn->query("SELECT id, data_inicio, data_fim FROM escala ORDER BY data_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
    $todosPoliciais = $conn->query("SELECT id, nome FROM policiais ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// Verificar se foi recebido o escala_id na URL
$escalaIdSelecionada = isset($_GET['escala_id']) ? intval($_GET['escala_id']) : null;

// Filtrar os policiais da escala selecionada
$policiaisEscalados = [];
if ($escalaIdSelecionada) {
    $query = "
        SELECT p1.id AS policial1_id, p1.nome AS policial1_nome, 
               p2.id AS policial2_id, p2.nome AS policial2_nome
        FROM escala e
        JOIN policiais p1 ON e.policial1_id = p1.id
        JOIN policiais p2 ON e.policial2_id = p2.id
        WHERE e.id = :escala_id
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute(['escala_id' => $escalaIdSelecionada]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $policiaisEscalados[] = ['id' => $resultado['policial1_id'], 'nome' => $resultado['policial1_nome']];
        $policiaisEscalados[] = ['id' => $resultado['policial2_id'], 'nome' => $resultado['policial2_nome']];
    }
}

// Filtrar os policiais não escalados para substitutos
$policiaisNaoEscalados = array_filter($todosPoliciais, function ($policial) use ($policiaisEscalados) {
    return !in_array($policial['id'], array_column($policiaisEscalados, 'id'));
});

// Buscar a escala selecionada para carregar os dias
$datasEscalaSelecionada = [];
if ($escalaIdSelecionada) {
    $query = "SELECT data_inicio, data_fim FROM escala WHERE id = :escala_id";
    $stmt = $conn->prepare($query);
    $stmt->execute(['escala_id' => $escalaIdSelecionada]);
    $escalaSelecionada = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($escalaSelecionada) {
        $dataInicio = new DateTime($escalaSelecionada['data_inicio']);
        $dataFim = new DateTime($escalaSelecionada['data_fim']);

        while ($dataInicio <= $dataFim) {
            $datasEscalaSelecionada[] = $dataInicio->format('Y-m-d');
            $dataInicio->modify('+1 day');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Gerenciar Plantão</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function selecionarTodosDias(checkbox) {
            const checkboxes = document.querySelectorAll('input[name="dias_substituicao[]"]');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Gerenciar Plantão</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-info"><?= $mensagem ?></div>
        <?php endif; ?>

        <!-- Botão Voltar -->
        <div class="d-flex justify-content-start mb-4">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar para Escalas
            </a>
        </div>

        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label for="escala_id" class="form-label">Semana de Plantão</label>
                <select id="escala_id" name="escala_id" class="form-select" required>
                    <option value="">Selecione uma semana</option>
                    <?php foreach ($escalas as $escala): ?>
                        <option value="<?= $escala['id'] ?>" <?= $escalaIdSelecionada === $escala['id'] ? 'selected' : '' ?>>
                            <?= date('d/m/Y', strtotime($escala['data_inicio'])) ?> à <?= date('d/m/Y', strtotime($escala['data_fim'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="policial_substituido" class="form-label">Policial Substituído</label>
                <select id="policial_substituido" name="policial_substituido" class="form-select" required>
                    <option value="">Selecione o policial</option>
                    <?php foreach ($policiaisEscalados as $policial): ?>
                        <option value="<?= $policial['id'] ?>"><?= $policial['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="policial_substituto" class="form-label">Policial Substituto</label>
                <select id="policial_substituto" name="policial_substituto" class="form-select" required>
                    <option value="">Selecione o policial</option>
                    <?php foreach ($policiaisNaoEscalados as $policial): ?>
                        <option value="<?= $policial['id'] ?>"><?= $policial['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Dias da Substituição</label>
                <div class="form-check mb-2">
                    <input type="checkbox" id="todos_dias" class="form-check-input" onclick="selecionarTodosDias(this)">
                    <label for="todos_dias" class="form-check-label">Selecionar Todos os Dias</label>
                </div>
                <div class="d-flex flex-wrap">
                    <?php if (!empty($datasEscalaSelecionada)): ?>
                        <?php foreach ($datasEscalaSelecionada as $dia): ?>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="checkbox" id="dia_<?= $dia ?>" name="dias_substituicao[]" value="<?= $dia ?>">
                                <label class="form-check-label" for="dia_<?= $dia ?>">
                                    <?= date('d/m/Y', strtotime($dia)) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-danger">Selecione uma escala para carregar os dias.</p>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Substituição</button>
        </form>
    </div>
</body>
</html>
