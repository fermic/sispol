<?php
// Configurações do Banco de Dados
$host = 'localhost';
$dbname = 'sistemasdelegacia';
$user = 'sistemasdelegacia';
$password = '89@Freitas#fmf';

try {
    // Criando uma conexão com PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
