<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'sistemasdelegacia';
$username = 'sistemasdelegacia';
$password = '89@Freitas#fmf';

try {
    // Criar uma nova conexão PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Configurações de erro do PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Exibe erro caso a conexão falhe
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
