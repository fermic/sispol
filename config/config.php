<?php
include_once 'db.php'; // Inclui o arquivo que define $pdo
define('BASE_URL', '/sistema');

// Configurações para exibição de prazos
define('PRAZO_VERDE', 20); // Prazo em dias para exibir como verde
define('PRAZO_AMARELO', 15); // Prazo em dias para exibir como amarelo
define('PRAZO_LARANJA', 7); // Prazo em dias para exibir como laranja

?>