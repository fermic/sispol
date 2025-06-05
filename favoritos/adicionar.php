<?php
require_once 'db.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os dados do formulário
    $titulo = $_POST['titulo'] ?? '';
    $url = $_POST['url'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    // Validação básica
    if (empty($titulo) || empty($url)) {
        $erro = "Título e URL são obrigatórios!";
    } else {
        try {
            // Criptografa a senha, se fornecida
            $senha_criptografada = !empty($senha) ? encryptPassword($senha) : null;

            // Insere o link no banco de dados
            $stmt = $pdo->prepare("INSERT INTO links (titulo, url, usuario, senha, observacoes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$titulo, $url, $usuario, $senha_criptografada, $observacoes]);
            $sucesso = "Link cadastrado com sucesso!";
        } catch (Exception $e) {
            $erro = "Erro ao cadastrar o link: " . $e->getMessage();
        }
    }
}

// Função para criptografar a senha
function encryptPassword($password) {
    return openssl_encrypt($password, 'AES-256-CBC', SECRET_KEY, 0, substr(SECRET_KEY, 0, 16));
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Link</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Cadastrar Novo Link</h1>

        <!-- Mensagens de sucesso ou erro -->
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php elseif (!empty($sucesso)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Digite o título" required>
            </div>

            <div class="mb-3">
                <label for="url" class="form-label">URL</label>
                <input type="url" class="form-control" id="url" name="url" placeholder="https://exemplo.com" required>
            </div>

            <div class="mb-3">
                <label for="usuario" class="form-label">Usuário (opcional)</label>
                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Digite o usuário">
            </div>

            <div class="mb-3">
                <label for="senha" class="form-label">Senha (opcional)</label>
                <input type="text" class="form-control" id="senha" name="senha" placeholder="Digite a senha">
            </div>

            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3" placeholder="Informações adicionais"></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">Voltar</a>
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
