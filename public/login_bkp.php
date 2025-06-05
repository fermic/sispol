<?php
ob_start(); // Inicia o buffer de saída

// Configuração de tempo de sessão em 10 horas
$tempoSessao = 36000; // 10 horas em segundos
ini_set('session.gc_maxlifetime', $tempoSessao);
ini_set('session.cookie_lifetime', $tempoSessao);

session_start(); // Garante que a sessão esteja iniciada

// Inclui os arquivos necessários
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../config/db.php';

// Gera o token CSRF se não estiver definido
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Gera um token seguro
}

// Verifica se a função isUserLoggedIn está disponível
if (!function_exists('isUserLoggedIn')) {
    die('Erro: A função isUserLoggedIn não está disponível.');
}

// Verifica se o usuário já está logado
if (isUserLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica o token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Token CSRF inválido.");
    }

    // Validação e sanitização das entradas
    $usuario = sanitizeInput(INPUT_POST, 'usuario');
    $senha = sanitizeInput(INPUT_POST, 'senha');

    if (!empty($usuario) && !empty($senha)) {
        if (isset($pdo)) {
            // Realiza a autenticação
            $user = autenticarUsuario($pdo, $usuario, $senha);

            if ($user) {
                // Salva os dados do usuário na sessão
                $_SESSION['usuario'] = $user['Usuario'];
                $_SESSION['usuario_id'] = $user['ID'];
                $_SESSION['funcao'] = $user['Funcao'];

                // Verifica se é necessário trocar a senha
                if ($user['TrocarSenha'] == 1) {
                    header('Location: trocar_senha.php?primeiro_acesso=1');
                    exit;
                }

                // Redireciona para o dashboard ou URL salva
                $redirectUrl = $_SESSION['redirect_url'] ?? 'dashboard.php';
                unset($_SESSION['redirect_url']);
                header("Location: $redirectUrl");
                exit;
            } else {
                registrarTentativaFalha();
                $error = "Usuário ou senha inválidos.";
            }
        } else {
            $error = "Erro na configuração da conexão com o banco de dados.";
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
    <title>Login - Gestão de Procedimentos Policiais</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
    <style>
body {
    background: linear-gradient(to right, #232526, #414345);
    color: #fff;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

        .login-container {
            background: #fff;
            color: #333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.25);
        }
        .login-container .form-control {
            border-radius: 50px;
        }
        .login-container button {
            border-radius: 50px;
        }
        .logo img {
            max-width: 180px;
        }
        .footer-text {
            font-size: 0.85rem;
            text-align: center;
            margin-top: 20px;
            color: #a9a9a9;
        }
        .footer-text a {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-container">
                    <div class="text-center logo mb-4">
                        <img src="<?= BASE_URL ?>/assets/logo-gih.png" alt="Logo">
                    </div>
                    <h2 class="text-center mb-4">Bem-vindo</h2>
                    <form method="POST">
                        <!-- Exibe erros -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger text-center">
                                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuário</label>
                            <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Digite seu usuário" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" name="senha" id="senha" class="form-control" placeholder="Digite sua senha" required>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
                    <div class="footer-text mt-4">
                        <p>&copy; <?= date('Y') ?> Gestão de Procedimentos Policiais.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
