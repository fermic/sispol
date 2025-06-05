<?php
include '../config/db.php'; // Inclui a configuração do banco de dados

header('Content-Type: application/json');

try {
    // Caso o método seja POST, adicionar um novo crime
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['termo'])) {
        $termo = trim($_POST['termo']);

        // Verifica se o crime já existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Crimes WHERE Nome = :nome");
        $stmt->execute(['nome' => $termo]);

        if ($stmt->fetchColumn() == 0) {
            // Insere novo crime se não existir
            $stmt = $pdo->prepare("INSERT INTO Crimes (Nome) VALUES (:nome)");
            $stmt->execute(['nome' => $termo]);
        }

        // Retorna o crime recém-adicionado
        echo json_encode(['id' => $pdo->lastInsertId(), 'nome' => $termo]);
        exit;
    }

    // Busca crimes existentes com base no termo de pesquisa
    $termo = $_GET['q'] ?? '';
    $stmt = $pdo->prepare("SELECT ID, Nome FROM Crimes WHERE Nome LIKE :termo ORDER BY Nome ASC");
    $stmt->execute(['termo' => "%$termo%"]);
    $crimes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retorna os resultados da busca
    echo json_encode($crimes);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
