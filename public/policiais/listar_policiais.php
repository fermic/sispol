<?php
include_once '../../includes/header.php';

// Verifica se o usuário está logado (assumindo que há uma verificação de sessão no sistema)
if (!isset($_SESSION['usuario_id'])) {
    echo "<div class='alert alert-danger'>Acesso não autorizado!</div>";
    include '../../includes/footer.php';
    exit;
}

// Busca dos policiais com paginação
$registrosPorPagina = 15;
$paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $registrosPorPagina;

// Contagem total para paginação
$stmtCount = $pdo->query("SELECT COUNT(*) FROM Policiais");
$totalRegistros = $stmtCount->fetchColumn();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Busca com paginação e ordenação
$stmt = $pdo->prepare("
    SELECT p.*, c.nome AS cargo_nome 
    FROM Policiais p
    LEFT JOIN Cargos c ON p.cargo = c.id
    ORDER BY p.nome ASC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$policiais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Funções auxiliares de formatação
function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) !== 11) {
        return $cpf;
    }
    return substr($cpf, 0, 3) . '.' . 
           substr($cpf, 3, 3) . '.' . 
           substr($cpf, 6, 3) . '-' . 
           substr($cpf, 9, 2);
}

function formatarTelefone($telefone) {
    // Remove qualquer caractere que não seja número
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Verifica o comprimento para determinar se é fixo ou celular
    if (strlen($telefone) === 11) {
        // Celular: (XX) XXXXX-XXXX
        return '(' . substr($telefone, 0, 2) . ') ' .
               substr($telefone, 2, 5) . '-' .
               substr($telefone, 7, 4);
    } elseif (strlen($telefone) === 10) {
        // Fixo: (XX) XXXX-XXXX
        return '(' . substr($telefone, 0, 2) . ') ' .
               substr($telefone, 2, 4) . '-' .
               substr($telefone, 6, 4);
    }
    
    // Retorna o valor original (não formatado) se o comprimento for inválido
    return $telefone;
}

// Implementação da funcionalidade de busca
$termoBusca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
if (!empty($termoBusca)) {
    $stmtBusca = $pdo->prepare("
        SELECT p.*, c.nome AS cargo_nome 
        FROM Policiais p
        LEFT JOIN Cargos c ON p.cargo = c.id
        WHERE 
            p.nome LIKE :termo OR 
            p.cpf LIKE :termo OR 
            p.funcional LIKE :termo OR 
            p.telefone LIKE :termo OR
            c.nome LIKE :termo
        ORDER BY p.nome ASC
        LIMIT :limit OFFSET :offset
    ");
    $stmtBusca->bindValue(':termo', "%$termoBusca%", PDO::PARAM_STR);
    $stmtBusca->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
    $stmtBusca->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmtBusca->execute();
    $policiais = $stmtBusca->fetchAll(PDO::FETCH_ASSOC);
    
    // Recalcula o total para a paginação na busca
    $stmtCountBusca = $pdo->prepare("
        SELECT COUNT(*) FROM Policiais p
        LEFT JOIN Cargos c ON p.cargo = c.id
        WHERE 
            p.nome LIKE :termo OR 
            p.cpf LIKE :termo OR 
            p.funcional LIKE :termo OR 
            p.telefone LIKE :termo OR
            c.nome LIKE :termo
    ");
    $stmtCountBusca->bindValue(':termo', "%$termoBusca%", PDO::PARAM_STR);
    $stmtCountBusca->execute();
    $totalRegistros = $stmtCountBusca->fetchColumn();
    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
}
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Listagem de Policiais</h4>
            <a href="cadastrar_policiais.php" class="btn btn-light">
                <i class="fas fa-user-plus"></i> Cadastrar Novo Policial
            </a>
        </div>
        
        <div class="card-body">
            <!-- Área de pesquisa -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" class="d-flex">
                        <input type="text" name="busca" class="form-control" placeholder="Buscar por nome, CPF, funcional..." value="<?= htmlspecialchars($termoBusca) ?>">
                        <button type="submit" class="btn btn-primary ml-2">Buscar</button>
                        <?php if (!empty($termoBusca)): ?>
                            <a href="listar_policiais.php" class="btn btn-secondary ml-2">Limpar</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="col-md-6 text-right">
                    <span class="text-muted">Total: <?= $totalRegistros ?> registros</span>
                </div>
            </div>
            
            <!-- Tabela de policiais -->
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Nome</th>
                            <th>Cargo</th>
                            <th>CPF</th>
                            <th>Funcional</th>
                            <th>Telefone</th>
                            <th>Anexo</th>
                            <th width="15%">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($policiais) > 0): ?>
                            <?php foreach ($policiais as $policial): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($policial['nome']) ?></strong></td>
                                    <td><?= htmlspecialchars($policial['cargo_nome'] ?? $policial['cargo']) ?></td>
                                    <td><?= htmlspecialchars(formatarCPF($policial['cpf'])) ?></td>
                                    <td><?= htmlspecialchars($policial['funcional']) ?></td>
                                    <td><?= htmlspecialchars(formatarTelefone($policial['telefone'])) ?></td>
                                    <td>
                                        <?php if (!empty($policial['anexo'])): ?>
                                            <a href="../../uploads/policiais/<?= htmlspecialchars($policial['anexo']) ?>" 
                                               target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-file-download"></i> Ver Anexo
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Sem anexo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="cadastrar_policiais.php?id=<?= $policial['id'] ?>" 
                                               class="btn btn-warning btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="excluir_policiais.php?id=<?= $policial['id'] ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Deseja realmente excluir o policial <?= htmlspecialchars($policial['nome']) ?>?');"
                                               title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhum policial encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($totalPaginas > 1): ?>
                <nav aria-label="Navegação de páginas">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $paginaAtual == 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=1<?= !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : '' ?>">Primeira</a>
                        </li>
                        <li class="page-item <?= $paginaAtual == 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $paginaAtual - 1 ?><?= !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : '' ?>">Anterior</a>
                        </li>
                        
                        <?php
                        $inicio = max(1, $paginaAtual - 2);
                        $fim = min($totalPaginas, $paginaAtual + 2);
                        
                        if ($inicio > 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        
                        for ($i = $inicio; $i <= $fim; $i++): 
                        ?>
                            <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?><?= !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; 
                        
                        if ($fim < $totalPaginas) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        ?>
                        
                        <li class="page-item <?= $paginaAtual == $totalPaginas ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $paginaAtual + 1 ?><?= !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : '' ?>">Próxima</a>
                        </li>
                        <li class="page-item <?= $paginaAtual == $totalPaginas ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $totalPaginas ?><?= !empty($termoBusca) ? '&busca=' . urlencode($termoBusca) : '' ?>">Última</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>