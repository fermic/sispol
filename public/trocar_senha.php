<?php
session_start();
require_once '../config/config.php'; // Configurações globais
require_once '../includes/functions.php'; // Funções reutilizáveis
require_once '../config/db.php'; // Conexão com o banco de dados

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p class='text-center text-danger'>Você precisa estar logado para trocar sua senha.</p>";
    include '../includes/footer.php';
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_atual = trim(filter_input(INPUT_POST, 'senha_atual', FILTER_DEFAULT));
    $nova_senha = trim(filter_input(INPUT_POST, 'nova_senha', FILTER_DEFAULT));
    $confirmar_senha = trim(filter_input(INPUT_POST, 'confirmar_senha', FILTER_DEFAULT));

    if (!empty($senha_atual) && !empty($nova_senha) && !empty($confirmar_senha)) {
        if ($nova_senha === $confirmar_senha) {
            try {
                // Busca a senha atual do usuário no banco de dados
                $stmt = $pdo->prepare("SELECT Senha FROM Usuarios WHERE ID = :id");
                $stmt->execute(['id' => $_SESSION['usuario_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($senha_atual, $user['Senha'])) {
                    // Atualiza a senha
                    $nova_senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE Usuarios SET Senha = :senha, TrocarSenha = 0 WHERE ID = :id");
                    $stmt->execute([
                        'senha' => $nova_senha_hash,
                        'id' => $_SESSION['usuario_id'],
                    ]);

                    // Define a mensagem de sucesso
                    $success = "Sua senha foi alterada com sucesso.";

                    // Redireciona para procedimentos.php
                    header('Location: procedimentos.php');
                    exit;
                } else {
                    $error = "A senha atual está incorreta.";
                }
            } catch (Exception $e) {
                $error = "Ocorreu um erro ao trocar a senha. Por favor, tente novamente.";
            }
        } else {
            $error = "A nova senha e a confirmação de senha não correspondem.";
        }
    } else {
        $error = "Por favor, preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trocar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Trocar Senha</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="mx-auto" style="max-width: 400px;">
        <div class="mb-3">
            <label for="senha_atual" class="form-label">Senha Atual</label>
            <input type="password" name="senha_atual" id="senha_atual" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="nova_senha" class="form-label">Nova Senha</label>
            <input type="password" name="nova_senha" id="nova_senha" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
            <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Trocar Senha</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
