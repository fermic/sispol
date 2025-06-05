<?php
include '../includes/header.php';
require_once '../config/db.php';

$desaparecidoID = $_GET['id'] ?? null;

if (!$desaparecidoID) {
    echo '<div class="alert alert-danger">ID do desaparecido não fornecido.</div>';
    include '../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioCriadorID = $_SESSION['usuario_id']; // Substitua pelo sistema de autenticação
    $comentario = $_POST['comentario'];
    $arquivos = [];

    // Caminho para upload
    $uploadDir = '../uploads/desaparecimentos/';

    // Verificar se a pasta existe; caso contrário, criar
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Criar a pasta com permissões adequadas
    }

    // Upload de arquivos
    if (!empty($_FILES['arquivos']['name'][0])) {
        foreach ($_FILES['arquivos']['tmp_name'] as $index => $tmpName) {
            $fileName = basename($_FILES['arquivos']['name'][$index]);
            $targetFile = $uploadDir . time() . '-' . $fileName;

            if (move_uploaded_file($tmpName, $targetFile)) {
                $arquivos[] = $targetFile;
            } else {
                echo '<div class="alert alert-danger">Falha ao fazer upload do arquivo: ' . htmlspecialchars($fileName) . '</div>';
            }
        }
    }

    $arquivosJson = json_encode($arquivos);

    // Inserir no banco
    $stmt = $pdo->prepare("INSERT INTO ComentariosDesaparecimentos (DesaparecidoID, UsuarioCriadorID, Comentario, Arquivos) VALUES (?, ?, ?, ?)");
    $stmt->execute([$desaparecidoID, $usuarioCriadorID, $comentario, $arquivosJson]);

    echo '<div class="alert alert-success">Comentário adicionado com sucesso!</div>';
}


// Buscar comentários
$stmt = $pdo->prepare("SELECT c.*, u.nome AS UsuarioCriador FROM ComentariosDesaparecimentos c JOIN Usuarios u ON c.UsuarioCriadorID = u.ID WHERE c.DesaparecidoID = ? ORDER BY c.CreatedAt DESC");
$stmt->execute([$desaparecidoID]);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar nome do desaparecido
$stmt = $pdo->prepare("SELECT Vitima FROM Desaparecidos WHERE ID = ?");
$stmt->execute([$desaparecidoID]);
$desaparecido = $stmt->fetch(PDO::FETCH_ASSOC);

$nomeDesaparecido = $desaparecido['Vitima'] ?? 'Não encontrado';
?>

<div class="container mt-5">
    <h2>Comentários para: <?= htmlspecialchars($nomeDesaparecido) ?></h2>

    <!-- Formulário de Comentário -->
    <form method="post" enctype="multipart/form-data" class="mb-4">
        <div class="mb-3">
            <label for="comentario" class="form-label">Comentário</label>
            <textarea name="comentario" id="comentario" class="form-control" rows="5"></textarea>
        </div>
        <div class="mb-3">
            <label for="arquivos" class="form-label">Upload de Arquivos</label>
            <input type="file" name="arquivos[]" id="arquivos" class="form-control" multiple>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar Comentário</button>
    </form>

    <!-- Lista de Comentários -->
    <h3>Comentários Existentes</h3>
    <?php if (empty($comentarios)): ?>
        <div class="alert alert-warning">Nenhum comentário encontrado.</div>
    <?php else: ?>
        <div class="row gy-4">
            <?php foreach ($comentarios as $comentario): ?>
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Informações do usuário e data -->
                                <div>
                                    <i class="bi bi-person-fill me-2"></i><strong><?= htmlspecialchars($comentario['UsuarioCriador']) ?></strong>
                                    <br>
                                    <small>
                                        <i class="bi bi-clock-fill me-2"></i>
                                        <?= date('d/m/Y H:i', strtotime($comentario['CreatedAt'])) ?>
                                    </small>
                                </div>
                                <!-- Botão de Excluir -->
                                <button 
                                    class="btn btn-danger btn-sm" 
                                    onclick="excluirComentario(<?= $comentario['ID'] ?>, <?= $desaparecidoID ?>)">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Conteúdo do comentário -->
                            <p class="mb-3"><?= nl2br(htmlspecialchars($comentario['Comentario'])) ?></p>
                            <!-- Arquivos -->
                            <?php if (!empty($comentario['Arquivos']) && is_array(json_decode($comentario['Arquivos'], true))): ?>
                                <?php $arquivos = json_decode($comentario['Arquivos'], true); ?>
                                <?php if (!empty($arquivos)): ?>
                                    <hr>
                                    <ul class="list-unstyled">
                                        <?php foreach ($arquivos as $arquivo): ?>
                                            <li>
                                                <a href="<?= htmlspecialchars($arquivo) ?>" target="_blank" class="btn btn-link text-decoration-none">
                                                    <i class="bi bi-download me-2"></i><?= htmlspecialchars(basename($arquivo)) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function excluirComentario(id, desaparecidoID) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Esta ação não poderá ser desfeita!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `excluir_comentario.php?id=${id}&desaparecido_id=${desaparecidoID}`;
        }
    });
}
</script>


<?php include '../includes/footer.php'; ?>