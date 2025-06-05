<?php
require_once 'db.php';
require_once 'functions.php';

// Verificar se o parâmetro 'search' foi enviado e não está vazio
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search === '') {
    // Retornar todos os links se o campo de busca estiver vazio
    $links = getAllLinks($pdo);
} else {
    // Buscar links com base no termo de pesquisa
    $links = searchLinks($pdo, $search);
}

// Retornar os resultados como JSON
header('Content-Type: application/json');
echo json_encode($links);
exit;
?>
