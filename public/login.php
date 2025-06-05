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
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Token CSRF inválido.");
    }

    // Sanitização e validação das entradas
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
    $senha = filter_input(INPUT_POST, 'senha', FILTER_UNSAFE_RAW);

    // Validações adicionais
    if (empty($usuario) || empty($senha)) {
        $error = "Por favor, preencha todos os campos.";
    } elseif (strlen($usuario) < 3 || strlen($usuario) > 50) {
        $error = "O usuário deve ter entre 3 e 50 caracteres.";
    } elseif (strlen($senha) < 6 || strlen($senha) > 72) {
        $error = "A senha deve ter entre 6 e 72 caracteres.";
    } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $usuario)) {
        $error = "O usuário contém caracteres inválidos.";
    } else {
        if (isset($pdo)) {
            try {
                // Realiza a autenticação
                $user = autenticarUsuario($pdo, $usuario, $senha);

                if ($user) {
                    // Limpa a sessão antes de definir novos dados
                    session_regenerate_id(true);
                    
                    // Salva os dados do usuário na sessão
                    $_SESSION['usuario'] = htmlspecialchars($user['Usuario'], ENT_QUOTES, 'UTF-8');
                    $_SESSION['usuario_id'] = (int)$user['ID'];
                    $_SESSION['funcao'] = htmlspecialchars($user['Funcao'], ENT_QUOTES, 'UTF-8');

                    // Verifica se é necessário trocar a senha
                    if ($user['TrocarSenha'] == 1) {
                        header('Location: trocar_senha.php?primeiro_acesso=1');
                        exit;
                    }

                    // Redireciona para o dashboard ou URL salva
                    $redirectUrl = isset($_SESSION['redirect_url']) ? 
                        filter_var($_SESSION['redirect_url'], FILTER_SANITIZE_URL) : 
                        'dashboard.php';
                    
                    unset($_SESSION['redirect_url']);
                    header("Location: " . $redirectUrl);
                    exit;
                } else {
                    registrarTentativaFalha();
                    $error = "Usuário ou senha inválidos.";
                }
            } catch (PDOException $e) {
                error_log("Erro de login: " . $e->getMessage());
                $error = "Erro ao tentar fazer login. Por favor, tente novamente.";
            }
        } else {
            $error = "Erro na configuração da conexão com o banco de dados.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISPOL - Sistema Integrado de Procedimentos Policiais</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/favicon.png">
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1a2942;
            --primary-dark: #141d2f;
            --primary-light: #243b5c;
            --secondary: #2c3e50;
            --accent: #3498db;
            --accent-hover: #2980b9;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --text-primary: #ffffff;
            --text-secondary: #b4bcc8;
            --text-muted: #7f8c97;
            --border-color: #344761;
            --input-bg: #1e2940;
            --card-bg: #212b3d;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Padrão de fundo com grid animado */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridAnimation 15s linear infinite;
            opacity: 0.5;
            z-index: 0;
        }

        @keyframes gridAnimation {
            0% { transform: translateY(0); }
            100% { transform: translateY(50px); }
        }

        /* Container Principal */
        .login-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            z-index: 1;
        }

        /* Card de Login */
        .login-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            display: flex;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Seção de Informações */
        .info-section {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            padding: 40px;
            width: 40%;
            color: var(--text-primary);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .info-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotateBackground 10s linear infinite;
        }

        @keyframes rotateBackground {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .info-content {
            position: relative;
            z-index: 1;
        }

        .shield-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .info-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .info-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Seção do Formulário */
        .form-section {
            width: 60%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            max-width: 120px;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        .form-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .form-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Campos do Formulário */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-control {
            width: 100%;
            height: 50px;
            background: var(--input-bg);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 0 16px; /* Removido o padding para ícone */
            font-size: 1rem;
            color: #ffffff; /* Texto sempre branco */
            position: relative;
            z-index: 1;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            background: var(--input-bg); /* Fundo continua escuro */
            color: #ffffff; /* Texto continua branco */
        }

        .form-control::placeholder {
            color: #cccccc; /* Placeholder cinza claro */
            opacity: 0.7;
        }

        /* Botão de Submit */
        .submit-btn {
            width: 100%;
            height: 50px;
            background: var(--accent);
            color: var(--text-primary);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
        }

        .submit-btn:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Mensagens de Erro */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.3s ease;
        }

        .alert-danger {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.2);
            color: var(--danger);
        }

        /* Checkbox personalizado */
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .form-check-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .footer-text {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .security-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 10px;
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        .security-info i {
            color: var(--success);
        }

        /* Animações */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
            }

            .info-section,
            .form-section {
                width: 100%;
            }

            .info-section {
                padding: 30px;
                text-align: center;
            }

            .shield-icon {
                font-size: 3rem;
            }

            .info-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 0;
            }

            .login-card {
                border-radius: 0;
                min-height: 100vh;
            }

            .form-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Seção de Informações -->
            <div class="info-section">
                <div class="info-content">
                    <i class="fas fa-shield-alt shield-icon"></i>
                    <h2 class="info-title">Bem-vindo ao SISPOL</h2>
                    <p class="info-subtitle">Sistema Integrado de Procedimentos Policiais. Garantindo segurança, eficiência e transparência em cada procedimento.</p>
                </div>
            </div>

            <!-- Seção do Formulário -->
            <div class="form-section">
                <div class="form-header">
                    <img src="<?= BASE_URL ?>/assets/logo-gih.png" alt="Logo SISPOL" class="logo">
                    <h1 class="form-title">Acesso Restrito</h1>
                    <p class="form-subtitle">Entre com suas credenciais para acessar o sistema</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger fade-in">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="loginForm" autocomplete="off">
                    <div class="form-group">
                        <label for="usuario" class="form-label">Usuário</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="mostrarSenha">
                        <label class="form-check-label" for="mostrarSenha">Mostrar senha</label>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Acessar o Sistema
                    </button>
                </form>

                <div class="login-footer">
                    <div class="footer-text">
                        &copy; <?= date('Y') ?> SISPOL - Polícia Civil
                    </div>
                    <div class="security-info">
                        <i class="fas fa-lock"></i>
                        Conexão segura | Sessão criptografada
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar/ocultar senha
            const senhaInput = document.getElementById('senha');
            const mostrarSenhaCheckbox = document.getElementById('mostrarSenha');
            
            mostrarSenhaCheckbox.addEventListener('change', function() {
                senhaInput.type = this.checked ? 'text' : 'password';
            });
            
            // Prevenir espaços iniciais
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.startsWith(' ')) {
                        this.value = this.value.trim();
                    }
                });
            });
            
            // Animação de entrada
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach((group, index) => {
                group.style.opacity = '0';
                group.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    group.style.transition = 'all 0.5s ease';
                    group.style.opacity = '1';
                    group.style.transform = 'translateY(0)';
                }, 100 * (index + 1));
            });
            
            // Foco automático no campo de usuário
            document.getElementById('usuario').focus();
        });
    </script>
</body>
</html>