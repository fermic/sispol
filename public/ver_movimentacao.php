<?php
include '../includes/header.php';
require_once '../config/db.php';

// Verificar se o ID da movimentação foi passado na URL
$movimentacaoID = $_GET['id'] ?? null;
if (!$movimentacaoID) {
    echo "<p class='text-center text-danger'>Movimentação não encontrada.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter os detalhes da movimentação
$queryMovimentacao = "
    SELECT m.*, t.Nome AS TipoNome, u.Nome AS Responsavel
    FROM Movimentacoes m
    LEFT JOIN TiposMovimentacao t ON m.TipoID = t.ID
    LEFT JOIN Usuarios u ON m.ResponsavelID = u.ID
    WHERE m.ID = :movimentacao_id
";
$stmtMovimentacao = $pdo->prepare($queryMovimentacao);
$stmtMovimentacao->execute(['movimentacao_id' => $movimentacaoID]);
$movimentacao = $stmtMovimentacao->fetch(PDO::FETCH_ASSOC);

if (!$movimentacao) {
    echo "<p class='text-center text-danger'>Movimentação não encontrada.</p>";
    include '../includes/footer.php';
    exit;
}

// Obter os documentos anexados à movimentação
$queryDocumentos = "
    SELECT ID, NomeArquivo, Caminho 
    FROM DocumentosMovimentacao 
    WHERE MovimentacaoID = :movimentacao_id
";
$stmtDocumentos = $pdo->prepare($queryDocumentos);
$stmtDocumentos->execute(['movimentacao_id' => $movimentacaoID]);
$documentos = $stmtDocumentos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">

<?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>
    
    <h1 class="text-center">Detalhes da Movimentação</h1>
    
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title"><?= htmlspecialchars($movimentacao['Assunto']) ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Tipo:</strong> <?= htmlspecialchars($movimentacao['TipoNome']) ?></p>
            <p><strong>Detalhes:</strong> <?= nl2br(htmlspecialchars($movimentacao['Detalhes'])) ?></p>
            <p><strong>Situação:</strong> <?= htmlspecialchars($movimentacao['Situacao']) ?></p>
            <p><strong>Data de Vencimento:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($movimentacao['DataVencimento']))) ?></p>
            <p><strong>Data de Criação:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($movimentacao['DataCriacao']))) ?></p>
            <?php if ($movimentacao['DataConclusao']): ?>
                <p><strong>Data de Conclusão:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($movimentacao['DataConclusao']))) ?></p>
            <?php endif; ?>
            <p><strong>Responsável:</strong> <?= htmlspecialchars($movimentacao['Responsavel']) ?></p>
        </div>
    </div>

<div class="card mt-4">
    <div class="card-header">
        <h4 class="card-title">Documentos Anexados</h4>
    </div>
    <div class="card-body">
        <?php if (empty($documentos)): ?>
            <p class="text-muted">Nenhum documento anexado.</p>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($documentos as $documento): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <!-- Nome do Arquivo com link para visualização -->
                        <a href="<?= htmlspecialchars($documento['Caminho']) ?>" 
                           target="_blank" 
                           class="text-decoration-none" 
                           title="Visualizar Documento">
                           <?= htmlspecialchars($documento['NomeArquivo']) ?>
                        </a>
                        <div>
                            <!-- Botão de Download -->
                            <a href="<?= htmlspecialchars($documento['Caminho']) ?>" 
                               class="btn btn-sm btn-primary me-2" 
                               download 
                               title="Baixar Documento">
                                <i class="fas fa-download"></i>
                            </a>

                            <!-- Botão de Exclusão -->
                            <a href="excluir_documento.php?id=<?= htmlspecialchars($documento['ID'] ?? '') ?>&movimentacao_id=<?= htmlspecialchars($movimentacaoID ?? '') ?>&procedimento_id=<?= htmlspecialchars($movimentacao['ProcedimentoID'] ?? '') ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Tem certeza que deseja excluir este documento?');" 
                               title="Excluir Documento">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>



<div class="mt-4 mb-4">
    <a href="ver_procedimento.php?id=<?= htmlspecialchars($movimentacao['ProcedimentoID']) ?>" class="btn btn-secondary">Voltar para o procedimento</a>
<a href="mov.php?id=<?= htmlspecialchars($movimentacao['ID']) ?>&procedimento_id=<?= htmlspecialchars($movimentacao['ProcedimentoID']) ?>" 
   class="btn btn-warning">
   Editar Movimentação
</a>

</div>

</div>

<?php include '../includes/footer.php'; ?>
