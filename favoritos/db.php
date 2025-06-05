<?php
$host = 'localhost';
$dbname = 'sistemasdelegacia';
$user = 'sistemasdelegacia';
$password = '89@Freitas#fmf';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}
