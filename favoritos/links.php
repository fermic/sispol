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
    <title>Links Rápidos - Tabela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light text-dark">
    <div class="container my-4">
        <!-- Título -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold text-uppercase display-5 text-dark mb-3">Tabela de Links</h1>
            <a href="adicionar.php" target="_blank" class="btn btn-success">
                <i class="fas fa-plus"></i> Adicionar Link
            </a>
        </div>

        <!-- Campo de busca -->
        <div class="mb-4">
            <input type="text" class="form-control bg-light text-dark border-dark" id="search" placeholder="Digite para buscar..." autocomplete="off">
        </div>

        <!-- Tabela Responsiva -->
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Título e Detalhes</th>
                        <th scope="col" class="text-center">Acessos</th>
                        <th scope="col" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="resultados">
                    <?php foreach ($links as $link): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($link['titulo']) ?></strong>
                            <?php if (!empty($link['usuario']) || !empty($link['senha']) || !empty($link['observacoes'])): ?>
                                <div class="mt-2">
                                    <?php if (!empty($link['usuario'])): ?>
                                        <p class="mb-1"><strong>Usuário:</strong> <?= htmlspecialchars($link['usuario']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($link['senha'])): ?>
                                        <p class="mb-1">
                                            <strong>Senha:</strong> <?= htmlspecialchars($link['senha']) ?>
                                            <button class="btn btn-warning btn-sm ms-2" onclick="copiarSenha('<?= $link['senha'] ?>')">
                                                <i class="fas fa-copy"></i> Copiar
                                            </button>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($link['observacoes'])): ?>
                                        <p class="text-muted small mb-0"><?= htmlspecialchars($link['observacoes']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">
                                <i class="fas fa-eye"></i> <?= $link['acessos'] ?? 0 ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="redirect.php?id=<?= $link['id'] ?>" target="_blank" class="btn btn-primary btn-sm">
                                <i class="fas fa-external-link-alt"></i> Acessar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Função para copiar senha
        function copiarSenha(senha) {
            navigator.clipboard.writeText(senha).then(() => alert('Senha copiada!'));
        }

        // Busca dinâmica em tempo real
        document.getElementById('search').addEventListener('input', function () {
            const searchQuery = this.value.trim();
            const resultadosTbody = document.getElementById('resultados');

            fetch(`search.php?search=${encodeURIComponent(searchQuery)}`)
                .then(response => response.json())
                .then(data => {
                    resultadosTbody.innerHTML = '';

                    if (data.length === 0) {
                        resultadosTbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Nenhum link encontrado.</td></tr>';
                        return;
                    }

                    data.forEach(link => {
                        const row = gerarLinha(link);
                        resultadosTbody.innerHTML += row;
                    });
                })
                .catch(error => console.error('Erro ao buscar links:', error));
        });

        // Função para gerar uma linha da tabela
        function gerarLinha(link) {
            return `
                <tr>
                    <td>
                        <strong>${link.titulo}</strong>
                        ${(link.usuario || link.senha || link.observacoes) ? `
                            <div class="mt-2">
                                ${link.usuario ? `<p class="mb-1"><strong>Usuário:</strong> ${link.usuario}</p>` : ''}
                                ${link.senha ? `
                                    <p class="mb-1">
                                        <strong>Senha:</strong> ${link.senha}
                                        <button class="btn btn-warning btn-sm ms-2" onclick="copiarSenha('${link.senha}')">
                                            <i class="fas fa-copy"></i> Copiar
                                        </button>
                                    </p>` : ''}
                                ${link.observacoes ? `<p class="text-muted small mb-0">${link.observacoes}</p>` : ''}
                            </div>
                        ` : ''}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">
                            <i class="fas fa-eye"></i> ${link.acessos ?? 0}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="redirect.php?id=${link.id}" target="_blank" class="btn btn-primary btn-sm">
                            <i class="fas fa-external-link-alt"></i> Acessar
                        </a>
                    </td>
                </tr>
            `;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
