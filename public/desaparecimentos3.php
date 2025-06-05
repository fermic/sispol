<?php
include '../includes/header.php';
require_once '../config/db.php';

// Obter filtros
$dataInicio = $_GET['data_inicio'] ?? null;
$dataFim = $_GET['data_fim'] ?? null;
$dataLocInicio = $_GET['data_loc_inicio'] ?? null;
$dataLocFim = $_GET['data_loc_fim'] ?? null;
$nome = $_GET['nome'] ?? null;
$idadeMin = $_GET['idade_min'] ?? null;
$idadeMax = $_GET['idade_max'] ?? null;
$situacao = $_GET['situacao'] ?? 'Desaparecido'; // Define "Desaparecido" como valor padrão
$rai = $_GET['rai'] ?? null;

// Definir o número de resultados por página
$resultsPerPage = isset($resultsPerPage) ? $resultsPerPage : 20; // Valor padrão de 20
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Calcular o offset
$offset = ($page - 1) * $resultsPerPage;

// Consulta principal
$query = "
    SELECT 
        d.*,
        (SELECT COUNT(*) FROM ComentariosDesaparecimentos c WHERE c.DesaparecidoID = d.ID) AS ComentariosCount
    FROM Desaparecidos d 
    WHERE 1=1";
$params = [];

// Adicionar filtros dinamicamente
if (!empty($dataInicio) && !empty($dataFim)) {
    $query .= " AND DataDesaparecimento BETWEEN :dataInicio AND :dataFim";
    $params[':dataInicio'] = $dataInicio;
    $params[':dataFim'] = $dataFim;
}

if (!empty($dataLocInicio) && !empty($dataLocFim)) {
    $query .= " AND DataLocalizacao BETWEEN :dataLocInicio AND :dataLocFim";
    $params[':dataLocInicio'] = $dataLocInicio;
    $params[':dataLocFim'] = $dataLocFim;
}

if (!empty($nome)) {
    $query .= " AND Vitima LIKE :nome";
    $params[':nome'] = "%$nome%";
}

if (!empty($situacao)) {
    $query .= " AND Situacao = :situacao";
    $params[':situacao'] = $situacao;
}

if (!empty($rai)) {
    $query .= " AND RAI LIKE :rai";
    $params[':rai'] = "%$rai%";
}

// Adicionar ordenação
$query .= " ORDER BY 
    CASE WHEN Situacao = 'Desaparecido' THEN 1 ELSE 2 END, 
    DataDesaparecimento DESC";

// Adicionar limites para paginação
$query .= " LIMIT :limit OFFSET :offset";

// Preparar e executar consulta
$stmt = $pdo->prepare($query);

// Vincular parâmetros
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $resultsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

// Executar consulta
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter contagem total de registros com filtros aplicados
$countQuery = "
    SELECT COUNT(*) AS total 
    FROM Desaparecidos d 
    WHERE 1=1";

// Adicionar filtros à consulta de contagem
$countParams = [];
if (!empty($dataInicio) && !empty($dataFim)) {
    $countQuery .= " AND DataDesaparecimento BETWEEN :dataInicio AND :dataFim";
    $countParams[':dataInicio'] = $dataInicio;
    $countParams[':dataFim'] = $dataFim;
}

if (!empty($dataLocInicio) && !empty($dataLocFim)) {
    $countQuery .= " AND DataLocalizacao BETWEEN :dataLocInicio AND :dataLocFim";
    $countParams[':dataLocInicio'] = $dataLocInicio;
    $countParams[':dataLocFim'] = $dataLocFim;
}

if (!empty($nome)) {
    $countQuery .= " AND Vitima LIKE :nome";
    $countParams[':nome'] = "%$nome%";
}

if (!empty($situacao)) {
    $countQuery .= " AND Situacao = :situacao";
    $countParams[':situacao'] = $situacao;
}

if (!empty($rai)) {
    $countQuery .= " AND RAI LIKE :rai";
    $countParams[':rai'] = "%$rai%";
}

// Preparar e executar consulta de contagem
$countStmt = $pdo->prepare($countQuery);
foreach ($countParams as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalResults = (int)$countStmt->fetchColumn(); // Número total de registros filtrados

// Recalcular o total de páginas com base no número de registros filtrados
$totalPages = $totalResults > 0 ? ceil($totalResults / $resultsPerPage) : 1;

?>


<div class="container mt-5" style="max-width: 95%; margin: 0 auto;">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gerenciamento de Desaparecimentos</h2>
    <p class="text-muted mb-0">Total de registros encontrados: <strong><?= $totalResults ?></strong></p>
    <a href="adicionar_desaparecimento.php" class="btn btn-primary">Adicionar Desaparecimento</a>
</div>



<!-- Formulário de Pesquisa -->
<form method="get" class="mb-4">
    <!-- Linha 1: Períodos -->
    <div class="row">
        <!-- Período de Desaparecimento -->
        <div class="col-md-6 mb-3">
            <label for="data_inicio" class="form-label">Período de Desaparecimento</label>
            <div class="input-group">
                <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= htmlspecialchars($dataInicio) ?>">
                <span class="input-group-text">a</span>
                <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= htmlspecialchars($dataFim) ?>">
            </div>
        </div>

        <!-- Período de Localização -->
        <div class="col-md-6 mb-3">
            <label for="data_loc_inicio" class="form-label">Período de Localização</label>
            <div class="input-group">
                <input type="date" name="data_loc_inicio" id="data_loc_inicio" class="form-control" value="<?= htmlspecialchars($dataLocInicio) ?>">
                <span class="input-group-text">a</span>
                <input type="date" name="data_loc_fim" id="data_loc_fim" class="form-control" value="<?= htmlspecialchars($dataLocFim) ?>">
            </div>
        </div>
    </div>

    <!-- Linha 2: Situação, Nome, RAI e Botão -->
    <div class="row">
        <!-- Situação -->
        <div class="col-md-3 mb-3">
            <label for="situacao" class="form-label">Situação</label>
            <select name="situacao" id="situacao" class="form-select">
                <option value="">Todos</option>
                <option value="Desaparecido" <?= $situacao === 'Desaparecido' ? 'selected' : '' ?>>Desaparecido</option>
                <option value="Encontrado" <?= $situacao === 'Encontrado' ? 'selected' : '' ?>>Encontrado</option>
            </select>
        </div>

        <!-- Nome -->
        <div class="col-md-4 mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" name="nome" id="nome" class="form-control" value="<?= isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : '' ?>">
        </div>

        <!-- RAI -->
        <div class="col-md-3 mb-3">
            <label for="rai" class="form-label">RAI</label>
            <input type="text" name="rai" id="rai" class="form-control" value="<?= isset($_GET['rai']) ? htmlspecialchars($_GET['rai']) : '' ?>">
        </div>

        <!-- Botão Pesquisar -->
        <div class="col-md-2 mb-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Pesquisar</button>
        </div>
    </div>
</form>


    <!-- Resultados -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Situação</th>
                <th>RAI</th>
                <th>Nome</th>
                <th>Idade</th>
                <th>Desaparecido em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($resultados)): ?>
                <tr>
                    <td colspan="7" class="text-center">Nenhum registro encontrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($resultados as $registro): ?>
                    <tr>
                        <td>
    <?php if ($registro['Situacao'] === 'Encontrado'): ?>
        <span class="badge bg-success"><?= htmlspecialchars($registro['Situacao']) ?></span>
    <?php elseif ($registro['Situacao'] === 'Desaparecido'): ?>
        <span class="badge bg-danger"><?= htmlspecialchars($registro['Situacao']) ?></span>
    <?php else: ?>
        <span class="badge bg-secondary">N/A</span>
    <?php endif; ?>
</td>

                        <td>
    <?php if (!empty($registro['RAI'])): ?>
        <a href="https://atendimento.ssp.go.gov.br/#/atendimento/<?= urlencode($registro['RAI']) ?>" target="_blank">
            <?= htmlspecialchars($registro['RAI']) ?>
        </a>
    <?php else: ?>
        N/A
    <?php endif; ?>
</td>

                        <td><?= htmlspecialchars($registro['Vitima'] ?? 'N/A') ?></td>
                        <td><?= isset($registro['Idade']) && $registro['Idade'] !== null ? htmlspecialchars($registro['Idade']) . ' anos' : '-' ?></td>

                        <td><?= $registro['DataDesaparecimento'] ? htmlspecialchars(date('d/m/Y', strtotime($registro['DataDesaparecimento']))) : 'N/A' ?></td>

<td>
    <!-- Botão para adicionar comentário -->
    <a href="comentarios.php?id=<?= $registro['ID'] ?>" class="btn btn-info btn-sm">
        <i class="bi bi-chat-left-dots"></i> Comentários (<?= $registro['ComentariosCount'] ?>)
    </a>
    
    <!-- Botão para editar o desaparecimento -->
    <a href="adicionar_desaparecimento.php?id=<?= $registro['ID'] ?>" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil-square"></i> Editar
    </a>
    
    <?php if ($registro['Situacao'] === 'Desaparecido'): ?>
        <!-- Botão para marcar como encontrado -->
        <button 
            class="btn btn-success btn-sm" 
            onclick="marcarComoEncontrado(<?= $registro['ID'] ?>)">
            <i class="bi bi-check-circle"></i> Encontrado
        </button>
    <?php endif; ?>

    <!-- Botão para excluir o desaparecimento -->
    <button 
        class="btn btn-danger btn-sm" 
        onclick="confirmarExclusao(<?= $registro['ID'] ?>)">
        <i class="bi bi-x-circle"></i>
    </button>
</td>




                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<nav>
    <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Anterior</a>
            </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Próxima</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Adicione a biblioteca confetti.js -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

<!-- Estilos para o troféu de comemoração -->
<style>
    #trofeu-localizado {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.5s ease;
    }
    
    #trofeu-localizado.ativo {
        opacity: 1;
    }
    
    .trofeu-conteudo {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 30px 50px;
        text-align: center;
        box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
        animation: pulse 2s infinite;
        max-width: 90%;
    }
    
    .trofeu-conteudo i.bi-trophy-fill {
        font-size: 80px;
        color: gold;
        margin-bottom: 15px;
        display: block;
        text-shadow: 0 0 10px rgba(255, 215, 0, 0.7);
    }
    
    .trofeu-conteudo h3 {
        font-size: 28px;
        color: #28a745;
        margin-bottom: 10px;
        font-weight: bold;
    }
    
    .trofeu-conteudo p {
        font-size: 18px;
        color: #212529;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }
    
    /* Efeito de brilho ao redor do troféu */
    @keyframes glow {
        0% {
            box-shadow: 0 0 10px 3px rgba(255, 215, 0, 0.5);
        }
        50% {
            box-shadow: 0 0 20px 5px rgba(255, 215, 0, 0.8);
        }
        100% {
            box-shadow: 0 0 10px 3px rgba(255, 215, 0, 0.5);
        }
    }
    
    .trofeu-conteudo {
        animation: pulse 2s infinite, glow 3s infinite;
    }
    
    /* Nome da pessoa localizada com destaque */
    .nome-localizado {
        font-weight: bold;
        color: #dc3545;
        font-size: 22px;
        margin: 10px 0;
        text-transform: uppercase;
    }
</style>

<script>
    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja excluir este registro? Esta ação não poderá ser desfeita.')) {
            window.location.href = 'excluir_desaparecimento.php?id=' + id;
        }
    }

    function marcarComoEncontrado(id) {
        if (confirm('Tem certeza que deseja marcar este desaparecimento como "Encontrado"?')) {
            // Obter nome da pessoa a partir da linha da tabela
            const linha = event.target.closest('tr');
            const nomePessoa = linha ? linha.querySelector('td:nth-child(3)').textContent : 'esta pessoa';
            
            // Cria elemento para o troféu
            const trofeu = document.createElement('div');
            trofeu.id = 'trofeu-localizado';
            trofeu.innerHTML = `
                <div class="trofeu-conteudo">
                    <i class="bi bi-trophy-fill"></i>
                    <h3>PARABÉNS!</h3>
                    <p>Você ajudou a localizar:</p>
                    <div class="nome-localizado">${nomePessoa}</div>
                    <p>Mais uma família recebendo boas notícias!</p>
                </div>
            `;
            document.body.appendChild(trofeu);
            
            // Aplica animação ao troféu
            setTimeout(() => {
                trofeu.classList.add('ativo');
            }, 100);
            
            // Sons de comemoração (opcional - usuário pode ter áudio desativado)
            try {
                const audio = new Audio('https://soundbible.com/mp3/Ta Da-SoundBible.com-1884170640.mp3');
                audio.play();
            } catch (e) {
                console.log('Áudio não suportado ou desativado');
            }
            
            // Duração da comemoração
            const duracaoConfetti = 6000; // 6 segundos de confetti
            
            // Confetti inicial - explosão principal
            confetti({
                particleCount: 500,
                spread: 150,
                startVelocity: 45,
                gravity: 1.2,
                origin: { y: 0.6 },
                colors: ['#ffd700', '#28a745', '#17a2b8', '#dc3545', '#ffffff']
            });
            
            // Canhões laterais de confetti
            setTimeout(() => {
                confetti({
                    particleCount: 200,
                    angle: 60,
                    spread: 80,
                    origin: { x: 0 },
                    colors: ['#ffd700', '#28a745', '#ffffff']
                });
                
                confetti({
                    particleCount: 200,
                    angle: 120,
                    spread: 80,
                    origin: { x: 1 },
                    colors: ['#17a2b8', '#dc3545', '#ffffff']
                });
            }, 750);
            
            // Segunda onda de confetti
            setTimeout(() => {
                confetti({
                    particleCount: 300,
                    spread: 120,
                    startVelocity: 35,
                    gravity: 0.9,
                    origin: { y: 0.7 },
                    colors: ['#ffd700', '#28a745', '#17a2b8', '#dc3545', '#ffffff']
                });
            }, 1500);
            
            // Terceira onda - chuva de confetti do topo
            setTimeout(() => {
                confetti({
                    particleCount: 200,
                    spread: 180,
                    startVelocity: 25,
                    gravity: 0.65,
                    origin: { y: 0 },
                    scalar: 1.2
                });
            }, 3000);

            // Redireciona para a página de marcação como encontrado
            setTimeout(() => {
                window.location.href = 'marcar_encontrado.php?id=' + id;
            }, duracaoConfetti);
        }
    }
</script>

<?php include '../includes/footer.php'; ?>