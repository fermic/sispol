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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Bebas+Neue&display=swap" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
    <style>
        :root {
            --primary-color: #1a1e21;
            --secondary-color: #343a40;
            --accent-color: #0d6efd;
            --dark-accent: #0a58ca;
            --text-color: #f8f9fa;
            --muted-text: #adb5bd;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: url('<?= BASE_URL ?>/assets/images/police-background.jpg') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
            margin: 0;
            width: 100%;
            height: 100%;
        }
        
        body::before {
            content: '';
            position: fixed; /* Alterado de absolute para fixed */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 0;
        }
        
        .login-container {
            position: relative;
            z-index: 1;
            max-width: 450px;
            width: 100%;
            background: rgba(33, 37, 41, 0.9);
            color: var(--text-color);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.6);
            border-top: 4px solid var(--accent-color);
            overflow: hidden; /* Impede que o conteúdo estoure */
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            margin-bottom: 15px;
            position: relative;
        }
        
        .logo img {
            max-width: 180px; /* Aumentado de 120px para 180px */
        }
        
        .logo::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: var(--accent-color);
            margin: 15px auto;
        }
        
        .title-section h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.5rem;
            letter-spacing: 1px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .subtitle {
            font-size: 0.9rem;
            color: var(--muted-text);
            margin-bottom: 30px;
        }
        
        .form-section label {
            font-size: 0.85rem;
            color: var(--muted-text);
            margin-bottom: 5px;
        }
        
        .input-group {
            margin-bottom: 20px;
            border-radius: 5px;
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .input-group-text {
            background-color: rgba(13, 110, 253, 0.2);
            border: none;
            color: var(--text-color);
        }
        
        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--text-color);
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            color: var(--text-color);
            box-shadow: none;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .btn-login {
            background: linear-gradient(to right, var(--accent-color), var(--dark-accent));
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 15px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: linear-gradient(to right, var(--dark-accent), var(--accent-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            font-size: 0.8rem;
            color: var(--muted-text);
        }
        
        .footer-text span {
            display: inline-block;
            position: relative;
        }
        
        .footer-text span::before,
        .footer-text span::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 30px;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .footer-text span::before {
            right: 100%;
            margin-right: 10px;
        }
        
        .footer-text span::after {
            left: 100%;
            margin-left: 10px;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #ff7f8f;
            border: 1px solid rgba(220, 53, 69, 0.2);
            border-radius: 5px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            padding: 10px;
            max-width: 100%;
            word-wrap: break-word;
        }
        
        .badge-section {
            position: absolute;
            top: -25px;
            right: 20px;
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 3px;
            letter-spacing: 1px;
            box-shadow: 0 3px 8px rgba(220, 53, 69, 0.3);
        }
        
        /* Animações */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-container {
            animation: fadeIn 0.6s ease-out;
        }
        
        /* Media queries */
        @media (max-width: 576px) {
            .login-container {
                padding: 25px;
                margin: 0 15px;
            }
            
            .title-section h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 mx-auto">
                <div class="login-container">
                    <div class="badge-section">
                        <i class="fas fa-shield-alt me-1"></i> ACESSO RESTRITO
                    </div>
                    
                    <div class="header-section">
                        <div class="logo">
                            <img src="<?= BASE_URL ?>/assets/logo-gih.png" alt="Logo GIH">
                        </div>
                        <div class="title-section">
                            <h2>Grupo de Investigação de Homicídios</h2>
                            <p class="subtitle">Sistema de Gestão de Procedimentos Policiais</p>
                        </div>
                    </div>
                    
                    <!-- Exibe erros -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="form-section">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Identificação do Usuário</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" name="usuario" id="usuario" class="form-control" 
                                       placeholder="Digite seu usuário" required autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="senha" class="form-label">Senha de Acesso</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="senha" id="senha" class="form-control" 
                                       placeholder="Digite sua senha" required>
                            </div>
                        </div>
                        
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        
                        <button type="submit" class="btn btn-login w-100">
                            <i class="fas fa-sign-in-alt me-2"></i> Acessar Sistema
                        </button>
                    </form>
                    
                    <div class="footer-text">
                        <span>&copy; <?= date('Y') ?> PCGO</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>