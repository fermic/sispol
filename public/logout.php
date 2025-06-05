<?php
session_start();
session_destroy();

// Inclui o arquivo de configuração
require_once '../config/config.php';

// Redireciona para a página de login usando BASE_URL
header('Location: ' . BASE_URL . '/public/login.php');
exit;
?>
