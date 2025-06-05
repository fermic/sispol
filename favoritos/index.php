<?php
require_once 'db.php';
require_once 'functions.php';

$search = $_GET['search'] ?? '';
$links = $search ? searchLinks($pdo, $search) : getAllLinks($pdo);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Rápido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <style>
        
.card {
    transition: transform 0.2s ease-in-out; /* Transição suave */
}

.card:hover {
    transform: scale(1.05); /* Aumenta ligeiramente o card */
}

    </style>
</head>
<body class="bg-light text-dark">
    <div class="container my-4">
        <!-- Título -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="fw-bold text-uppercase text-dark mb-3">Links de Acesso Rápido</h1>
            <a href="adicionar.php" target="_blank" class="btn btn-success">
                <i class="fas fa-plus"></i> Adicionar Link
            </a>
        </div>

        <!-- Campo de busca -->
        <div class="mb-4">
            <input type="text" class="form-control bg-light text-dark border-dark" id="search" placeholder="Digite para buscar..." autocomplete="off">
        </div>

        <!-- Resultados da busca -->
<div id="resultados" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php foreach ($links as $link): ?>
    <div class="col">
        <div class="card bg-white text-dark shadow-sm h-100">
            <div class="card-header bg-light text-dark text-center">
                <h5 class="card-title mb-0"><?= htmlspecialchars($link['titulo']) ?></h5>
            </div>
            <div class="card-body">
                <a href="redirect.php?id=<?= $link['id'] ?>" target="_blank" class="btn btn-primary btn-lg w-100 mb-2">
                    <i class="fas fa-external-link-alt"></i> Acessar
                </a>
                <?php if (!empty($link['usuario']) || !empty($link['senha']) || !empty($link['observacoes']) || !empty($link['url'])): ?>
                    <button class="btn btn-secondary btn-lg w-100 mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?= $link['id'] ?>" aria-expanded="false" aria-controls="details-<?= $link['id'] ?>">
                        <i class="fas fa-info-circle"></i> Detalhes
                    </button>
                    <div class="collapse" id="details-<?= $link['id'] ?>">
                        <?php if (!empty($link['usuario'])): ?>
                            <p><strong>Usuário:</strong> <span class="text-dark"><?= htmlspecialchars($link['usuario']) ?></span></p>
                        <?php endif; ?>
                        <?php if (!empty($link['senha'])): ?>
                            <p>
                                <strong>Senha:</strong> 
                                <span class="text-dark" id="senha-mascarada-<?= $link['id'] ?>">***</span>
                                <button class="btn btn-warning btn-sm ms-2" onclick="copiarSenha('<?= $link['senha'] ?>', 'senha-mascarada-<?= $link['id'] ?>')">
                                    <i class="fas fa-copy"></i> Copiar
                                </button>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($link['url'])): ?>
                            <p>
                                <strong>URL:</strong> 
                                <button class="btn btn-warning btn-sm" onclick="copiarURL('<?= htmlspecialchars($link['url']) ?>')">
                                    <i class="fas fa-copy"></i> Copiar URL
                                </button>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($link['observacoes'])): ?>
                            <div class="alert alert-secondary"><?= htmlspecialchars($link['observacoes']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-secondary text-center small">
                <i class="fas fa-eye"></i> Acessos: <?= $link['acessos'] ?? 0 ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

    </div>

    <script>
        document.getElementById('search').addEventListener('input', function () {
            const searchQuery = this.value.trim();
            const resultadosDiv = document.getElementById('resultados');

            fetch(`search.php?search=${encodeURIComponent(searchQuery)}`)
                .then(response => response.json())
                .then(data => {
                    resultadosDiv.innerHTML = '';

                    if (data.length === 0) {
                        resultadosDiv.innerHTML = '<p class="text-center text-muted">Nenhum link encontrado.</p>';
                        return;
                    }

                    data.forEach(link => {
                        const card = gerarCard(link);
                        resultadosDiv.innerHTML += card;
                    });
                })
                .catch(error => console.error('Erro ao buscar links:', error));
        });

function gerarCard(link) {
    return `
        <div class="col">
            <div class="card bg-white text-dark shadow-sm h-100">
                <div class="card-header bg-light text-dark text-center">
                    <h5 class="card-title mb-0">${link.titulo}</h5>
                </div>
                <div class="card-body">
                    <a href="redirect.php?id=${link.id}" target="_blank" class="btn btn-primary btn-lg w-100 mb-2">
                        <i class="fas fa-external-link-alt"></i> Acessar
                    </a>
                    ${(link.usuario || link.senha || link.observacoes || link.url) ? `
                        <button class="btn btn-secondary btn-lg w-100 mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#details-${link.id}" aria-expanded="false" aria-controls="details-${link.id}">
                            <i class="fas fa-info-circle"></i> Detalhes
                        </button>
                        <div class="collapse" id="details-${link.id}">
                            ${link.usuario ? `<p><strong>Usuário:</strong> <span class="text-dark">${link.usuario}</span></p>` : ''}
                            ${link.senha ? `<p><strong>Senha:</strong> <span class="text-dark">***</span>
                                <button class="btn btn-warning btn-sm ms-2" onclick="copiarSenha('${link.senha}')">
                                    <i class="fas fa-copy"></i> Copiar
                                </button>
                            </p>` : ''}
                            ${link.url ? `<p><strong>URL:</strong>
                                <button class="btn btn-warning btn-sm" onclick="copiarURL('${link.url}')">
                                    <i class="fas fa-copy"></i> Copiar URL
                                </button>
                            </p>` : ''}
                            ${link.observacoes ? `<div class="alert alert-secondary">${link.observacoes}</div>` : ''}
                        </div>
                    ` : ''}
                </div>
                <div class="card-footer bg-light text-dark text-center small">
                    <i class="fas fa-eye"></i> Acessos: ${link.acessos ?? 0}
                </div>
            </div>
        </div>
    `;
}





function copiarSenha(senha) {
    // Copiar a senha diretamente para o clipboard
    navigator.clipboard.writeText(senha).then(() => {
        alert('Senha copiada!');
    }).catch(() => {
        alert('Erro ao copiar a senha. Tente novamente.');
    });
}

function copiarURL(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('URL copiada!');
    }).catch(() => {
        alert('Erro ao copiar a URL. Tente novamente.');
    });
}



    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
