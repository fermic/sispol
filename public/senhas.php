<?php
include '../config/db.php'; // Inclua a conexão com o banco de dados
include '../includes/header.php';
session_start();

// Inicializar variáveis do formulário
$id = null;
$sistema = '';
$url = '';
$login = '';
$senha = '';
$observacoes = '';

// Verificar se é uma edição (verificar 'id' na URL)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM Senhas WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $senhaData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($senhaData) {
        $sistema = $senhaData['sistema'];
        $url = $senhaData['url'];
        $login = $senhaData['login'];
        $senha = decrypt_password($senhaData['senha']); // Descriptografa a senha para exibir no formulário
        $observacoes = $senhaData['observacoes'];
    } else {
        $_SESSION['error'] = "Registro não encontrado.";
        header('Location: senhas.php');
        exit;
    }
}


if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Chama a função de exclusão
    if (delete_senha($pdo, $delete_id)) {
        $_SESSION['success'] = "Registro excluído com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao excluir o registro.";
    }

    // Redireciona para evitar reenvio da solicitação de exclusão
    header('Location: senhas.php');
    exit;
}



// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se o ID foi enviado e não está vazio
    $id = isset($_POST['id']) && !empty(trim($_POST['id'])) ? $_POST['id'] : null;

    $sistema = $_POST['sistema'];
    $url = $_POST['url'];
    $login = $_POST['login'];
    $senha = encrypt_password($_POST['senha']); // Criptografa a senha antes de salvar
    $observacoes = $_POST['observacoes'];

    if ($id !== null) {
        // Atualizar registro existente
        $stmt = $pdo->prepare("
            UPDATE Senhas 
            SET sistema = :sistema, url = :url, login = :login, senha = :senha, observacoes = :observacoes 
            WHERE id = :id
        ");
        $stmt->execute([
            'sistema' => $sistema,
            'url' => $url,
            'login' => $login,
            'senha' => $senha,
            'observacoes' => $observacoes,
            'id' => $id
        ]);
        $_SESSION['success'] = "Registro atualizado com sucesso!";
    } else {
        // Criar novo registro
        $stmt = $pdo->prepare("
            INSERT INTO Senhas (sistema, url, login, senha, observacoes) 
            VALUES (:sistema, :url, :login, :senha, :observacoes)
        ");
        $stmt->execute([
            'sistema' => $sistema,
            'url' => $url,
            'login' => $login,
            'senha' => $senha,
            'observacoes' => $observacoes
        ]);
        $_SESSION['success'] = "Novo registro criado com sucesso!";
    }

    header('Location: senhas.php'); // Redireciona após salvar
    exit;
}



// Buscar sistemas cadastrados
$stmt = $pdo->query("SELECT * FROM Senhas ORDER BY sistema ASC");
$sistemas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Senhas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 95%; margin: 0 auto;">
    <h1 class="text-center">Gerenciador de Senhas</h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <!-- Botão para exibir o formulário -->
    <button id="btn-novo-registro" class="btn btn-primary mb-4">
        <i class="bi bi-plus-circle"></i> Cadastrar Novo Registro
    </button>

    <!-- Formulário de cadastro (inicialmente oculto) -->
    <form id="form-adicionar-senha" method="POST" class="mb-5" style="display: <?= $id ? 'block' : 'none'; ?>">
        <input type="hidden" name="id" value="<?= isset($id) ? htmlspecialchars($id) : '' ?>">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="sistema" class="form-label">Sistema</label>
                <input type="text" id="sistema" name="sistema" class="form-control" value="<?= htmlspecialchars($sistema) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="url" class="form-label">URL</label>
                <input type="url" id="url" name="url" class="form-control" value="<?= htmlspecialchars($url) ?>">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="login" class="form-label">Login</label>
                <input type="text" id="login" name="login" class="form-control" value="<?= htmlspecialchars($login) ?>" required>
            </div>
<div class="col-md-6">
    <label for="senha" class="form-label">Senha</label>
    <input type="text" id="senha" name="senha" class="form-control" value="<?= htmlspecialchars($senha) ?>" required>
</div>

        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea id="observacoes" name="observacoes" class="form-control"><?= htmlspecialchars($observacoes) ?></textarea>
        </div>
       <div class="d-flex gap-2">
    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="senhas.php" class="btn btn-secondary">Cancelar</a>
</div>

        
    </form>

    <!-- Lista de sistemas -->
    <h2>Senhas Cadastradas</h2>
    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
            <th>Sistema</th>
            <th>URL</th>
            <th>Login</th>
            <th>Senha</th>
            <th>Observações</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($sistemas as $sistema): ?>
            <tr>
                <td><?= htmlspecialchars($sistema['sistema']) ?></td>
<td>
    <a href="<?= htmlspecialchars($sistema['url']) ?>" target="_blank" class="btn btn-primary btn-sm">
        <i class="bi bi-box-arrow-up-right"></i> Acessar
    </a>
</td>


                <td><?= htmlspecialchars($sistema['login']) ?></td>
<td>
    <span class="senha-mascarada badge bg-secondary text-wrap" id="senha-<?= $sistema['id'] ?>">********</span>
    <button class="btn btn-sm btn-secondary btn-copiar-senha" 
        data-senha="<?= htmlspecialchars(decrypt_password($sistema['senha'])) ?>">Copiar</button>
    <button class="btn btn-sm btn-info btn-exibir-senha" 
        data-id="<?= $sistema['id'] ?>" 
        data-senha="<?= htmlspecialchars(decrypt_password($sistema['senha'])) ?>">Exibir Senha</button>
</td>


                <td><?= htmlspecialchars($sistema['observacoes']) ?></td>
                <td>
                    <button onclick="editarRegistro(<?= $sistema['id'] ?>)" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square"></i> Editar
                    </button>
<a href="senhas.php?delete_id=<?= $sistema['id'] ?>" 
   class="btn btn-danger btn-sm"
   onclick="return confirm('Tem certeza de que deseja excluir este registro? Esta ação não pode ser desfeita.')">
    <i class="bi bi-trash"></i> Excluir
</a>


                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    // Exibir formulário para novo registro
// Alternar exibição do formulário ao clicar no botão "Cadastrar Novo Registro"
document.getElementById('btn-novo-registro').addEventListener('click', function () {
    const form = document.getElementById('form-adicionar-senha');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block'; // Exibe o formulário
        this.innerHTML = '<i class="bi bi-dash-circle"></i> Ocultar Formulário'; // Atualiza o texto do botão
    } else {
        form.style.display = 'none'; // Oculta o formulário
        this.innerHTML = '<i class="bi bi-plus-circle"></i> Cadastrar Novo Registro'; // Atualiza o texto do botão
    }
});


    // Função para editar registro
    function editarRegistro(id) {
        window.location.href = `senhas.php?id=${id}`;
    }
    
// Função para alternar a exibição da senha
document.querySelectorAll('.btn-exibir-senha').forEach(button => {
    button.addEventListener('click', function () {
        const senhaId = this.getAttribute('data-id'); // ID do registro
        const senhaDescriptografada = this.getAttribute('data-senha'); // Senha descriptografada
        const senhaSpan = document.getElementById(`senha-${senhaId}`); // Span da senha

        // Alternar entre exibir e ocultar senha
        if (senhaSpan.textContent === '********') {
            senhaSpan.textContent = senhaDescriptografada; // Mostra a senha descriptografada
            senhaSpan.classList.remove('bg-secondary'); // Remove cor cinza
            senhaSpan.classList.add('bg-success'); // Adiciona cor verde
            this.textContent = 'Ocultar Senha'; // Altera o texto do botão
        } else {
            senhaSpan.textContent = '********'; // Oculta a senha
            senhaSpan.classList.remove('bg-success'); // Remove cor verde
            senhaSpan.classList.add('bg-secondary'); // Adiciona cor cinza
            this.textContent = 'Exibir Senha'; // Altera o texto do botão
        }
    });
});

// Função para copiar a senha para o clipboard
document.querySelectorAll('.btn-copiar-senha').forEach(button => {
    button.addEventListener('click', function () {
        const senha = this.getAttribute('data-senha'); // Obtém a senha descriptografada
        navigator.clipboard.writeText(senha).then(() => {
            alert('Senha copiada para o clipboard!');
        });
    });
});


</script>
<!-- Ícones do Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</body>
</html>
