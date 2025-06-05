<?php
// Define o novo endereço para onde o usuário será redirecionado
$newLocation = '/sistema/public/login.php'; // Substitua pelo novo caminho ou URL completo

// Redireciona o usuário para o novo local
header("Location: $newLocation");
exit;
?>
