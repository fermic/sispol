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

<!-- Container Principal -->
<div class="container-fluid mt-4" style="max-width: 95%; margin: 0 auto;">
    <?php
    // Exibir mensagens de sucesso ou erro
    if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'encontrado'): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <strong>Sucesso!</strong> A pessoa foi marcada como encontrada.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Erro!</strong> 
            <?= isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Ocorreu um erro ao processar sua solicitação.' ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <!-- Cabeçalho da Página -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0 fs-4">Gerenciamento de Desaparecimentos</h2>
                <span class="badge bg-light text-dark fs-6">
                    Total: <strong><?= $totalResults ?></strong> registros
                </span>
                <a href="adicionar_desaparecimento.php" class="btn btn-light">
                    <i class="bi bi-plus-circle"></i> Adicionar Novo
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Formulário de Pesquisa -->
            <form method="get" class="mb-4">
                <div class="row g-3">
                    <!-- Período de Desaparecimento -->
                    <div class="col-md-6 mb-2">
                        <div class="card h-100 border-secondary">
                            <div class="card-header bg-light">
                                <label for="data_inicio" class="form-label mb-0">
                                    <i class="bi bi-calendar-event"></i> Período de Desaparecimento
                                </label>
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <input type="date" name="data_inicio" id="data_inicio" class="form-control" 
                                           value="<?= htmlspecialchars($dataInicio) ?>">
                                    <span class="input-group-text"><i class="bi bi-arrow-right"></i></span>
                                    <input type="date" name="data_fim" id="data_fim" class="form-control" 
                                           value="<?= htmlspecialchars($dataFim) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Período de Localização -->
                    <div class="col-md-6 mb-2">
                        <div class="card h-100 border-secondary">
                            <div class="card-header bg-light">
                                <label for="data_loc_inicio" class="form-label mb-0">
                                    <i class="bi bi-geo-alt"></i> Período de Localização
                                </label>
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <input type="date" name="data_loc_inicio" id="data_loc_inicio" class="form-control" 
                                           value="<?= htmlspecialchars($dataLocInicio) ?>">
                                    <span class="input-group-text"><i class="bi bi-arrow-right"></i></span>
                                    <input type="date" name="data_loc_fim" id="data_loc_fim" class="form-control" 
                                           value="<?= htmlspecialchars($dataLocFim) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <!-- Situação -->
                    <div class="col-md-3 mb-2">
                        <div class="form-floating">
                            <select name="situacao" id="situacao" class="form-select">
                                <option value="">Todos</option>
                                <option value="Desaparecido" <?= $situacao === 'Desaparecido' ? 'selected' : '' ?>>Desaparecido</option>
                                <option value="Encontrado" <?= $situacao === 'Encontrado' ? 'selected' : '' ?>>Encontrado</option>
                            </select>
                            <label for="situacao">Situação</label>
                        </div>
                    </div>

                    <!-- Nome -->
                    <div class="col-md-4 mb-2">
                        <div class="form-floating">
                            <input type="text" name="nome" id="nome" class="form-control" 
                                   value="<?= isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : '' ?>">
                            <label for="nome">Nome</label>
                        </div>
                    </div>

                    <!-- RAI -->
                    <div class="col-md-3 mb-2">
                        <div class="form-floating">
                            <input type="text" name="rai" id="rai" class="form-control" 
                                   value="<?= isset($_GET['rai']) ? htmlspecialchars($_GET['rai']) : '' ?>">
                            <label for="rai">RAI</label>
                        </div>
                    </div>

                    <!-- Botão Pesquisar -->
                    <div class="col-md-2 mb-2 d-flex align-items-stretch">
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-search me-2"></i> Pesquisar
                        </button>
                    </div>
                </div>
            </form>

            <!-- Tabela de Resultados -->
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" width="10%">Situação</th>
                            <th scope="col" width="15%">RAI</th>
                            <th scope="col" width="25%">Nome</th>
                            <th scope="col" width="10%">Idade</th>
                            <th scope="col" width="15%">Desaparecido em</th>
                            <th scope="col" width="25%">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultados)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i> Nenhum registro encontrado.
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resultados as $registro): ?>
                                <tr>
                                    <td>
                                        <?php if ($registro['Situacao'] === 'Encontrado'): ?>
                                            <span class="badge bg-success p-2 w-100 d-block">
                                                <i class="bi bi-check-circle me-1"></i> 
                                                <?= htmlspecialchars($registro['Situacao']) ?>
                                            </span>
                                        <?php elseif ($registro['Situacao'] === 'Desaparecido'): ?>
                                            <span class="badge bg-danger p-2 w-100 d-block">
                                                <i class="bi bi-exclamation-circle me-1"></i> 
                                                <?= htmlspecialchars($registro['Situacao']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary p-2 w-100 d-block">
                                                <i class="bi bi-question-circle me-1"></i> N/A
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($registro['RAI'])): ?>
                                            <a href="https://atendimento.ssp.go.gov.br/#/atendimento/<?= urlencode($registro['RAI']) ?>" 
                                               target="_blank" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="bi bi-file-earmark-text me-1"></i>
                                                <?= htmlspecialchars($registro['RAI']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="fw-bold">
                                            <?= htmlspecialchars($registro['Vitima'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php if (isset($registro['Idade']) && $registro['Idade'] !== null): ?>
                                            <span class="badge bg-light text-dark p-2">
                                                <i class="bi bi-person me-1"></i>
                                                <?= htmlspecialchars($registro['Idade']) ?> anos
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($registro['DataDesaparecimento']): ?>
                                            <span class="badge bg-warning text-dark p-2">
                                                <i class="bi bi-calendar me-1"></i>
                                                <?= htmlspecialchars(date('d/m/Y', strtotime($registro['DataDesaparecimento']))) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="btn-group w-100" role="group">
                                            <!-- Botão para adicionar comentário -->
                                            <a href="comentarios.php?id=<?= $registro['ID'] ?>" 
                                               class="btn btn-info btn-sm" title="Comentários">
                                                <i class="bi bi-chat-left-dots"></i>
                                                <span class="badge bg-white text-info ms-1">
                                                    <?= $registro['ComentariosCount'] ?>
                                                </span>
                                            </a>
                                            
                                            <!-- Botão para editar o desaparecimento -->
                                            <a href="adicionar_desaparecimento.php?id=<?= $registro['ID'] ?>" 
                                               class="btn btn-warning btn-sm" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            <?php if ($registro['Situacao'] === 'Desaparecido'): ?>
                                                <!-- Botão para marcar como encontrado -->
                                                <button class="btn btn-success btn-sm" 
                                                        onclick="abrirModalEncontrado(<?= $registro['ID'] ?>, '<?= htmlspecialchars($registro['Vitima']) ?>')"
                                                        title="Marcar como encontrado">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            <?php endif; ?>

                                            <!-- Botão para excluir o desaparecimento -->
                                            <button class="btn btn-danger btn-sm" 
                                                    onclick="confirmarExclusao(<?= $registro['ID'] ?>)"
                                                    title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                    <i class="bi bi-chevron-left"></i> Anterior
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="bi bi-chevron-left"></i> Anterior</span>
                            </li>
                        <?php endif; ?>

                        <?php 
                        // Cálculo para exibir apenas algumas páginas
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                    Próxima <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Próxima <i class="bi bi-chevron-right"></i></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <!-- Modal para marcar como encontrado -->
            <div class="modal fade" id="modalEncontrado" tabindex="-1" aria-labelledby="modalEncontradoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="modalEncontradoLabel">
                                <i class="bi bi-check-circle-fill me-2"></i> Marcar como Encontrado
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <form id="formEncontrado" action="marcar_encontrado.php" method="GET">
                            <div class="modal-body">
                                <input type="hidden" id="desaparecidoId" name="id" value="">
                                
                                <div class="alert alert-success mb-4">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    Você está marcando <strong id="nomeVitima"></strong> como encontrado(a).
                                </div>
                                
                                <div class="mb-3">
                                    <label for="dataLocalizacao" class="form-label">Data da Localização</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                        <input type="date" class="form-control" id="dataLocalizacao" name="data_localizacao" 
                                               value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="form-text">Informe a data em que a pessoa foi localizada.</div>
                                </div>
                                
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-lg me-2"></i> Confirmar Localização
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Elemento do troféu (inicialmente oculto) -->
            <div id="trofeu-localizado" style="display: none;">
                <div class="trofeu-conteudo">
                    <i class="bi bi-trophy-fill"></i>
                    <h3>PARABÉNS!</h3>
                    <p>Você ajudou a localizar:</p>
                    <div class="nome-localizado" id="nome-pessoa-trofeu"></div>
                    <p>Mais uma família recebendo boas notícias!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adicionar biblioteca confetti.js antes do script -->
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
        animation: pulse 2s infinite, glow 3s infinite;
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
    
    /* Nome da pessoa localizada com destaque */
    .nome-localizado {
        font-weight: bold;
        color: #dc3545;
        font-size: 22px;
        margin: 10px 0;
        text-transform: uppercase;
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
</style>

<script>
    // Função para confirmar exclusão
    function confirmarExclusao(id) {
        Swal.fire({
            title: 'Tem certeza?',
            text: "Esta ação não poderá ser revertida!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'excluir_desaparecimento.php?id=' + id;
            }
        });
    }

    // Função para abrir modal de marcar como encontrado
    function abrirModalEncontrado(id, nome) {
        // Preenche os dados na modal
        document.getElementById('desaparecidoId').value = id;
        document.getElementById('nomeVitima').textContent = nome;
        
        // Abre a modal
        var myModal = new bootstrap.Modal(document.getElementById('modalEncontrado'));
        myModal.show();
    }
    
    // Função para criar a celebração com troféu e confetes
    function celebrarLocalizacao(nomePessoa) {
        // Atualiza o nome no troféu
        document.getElementById('nome-pessoa-trofeu').textContent = nomePessoa;
        
        // Exibe o troféu
        const trofeu = document.getElementById('trofeu-localizado');
        trofeu.style.display = 'flex';
        
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
        
        return duracaoConfetti;
    }

    // Configurar o formulário de encontrado
    document.addEventListener('DOMContentLoaded', function() {
        const formEncontrado = document.getElementById('formEncontrado');
        
        if (formEncontrado) {
            formEncontrado.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Oculta a modal para mostrar a celebração
                var myModal = bootstrap.Modal.getInstance(document.getElementById('modalEncontrado'));
                myModal.hide();
                
                // Obtém o nome da pessoa do formulário
                const nomePessoa = document.getElementById('nomeVitima').textContent;
                
                // Inicia a celebração
                const duracaoComemoracaoDados = celebrarLocalizacao(nomePessoa);
                
                // Pequeno atraso para ver a celebração antes de enviar o formulário
                setTimeout(() => {
                    this.submit();
                }, duracaoComemoracaoDados);
            });
        }
        
        // Inicializar tooltips do Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<?php include '../includes/footer.php'; ?>