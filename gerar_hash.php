<?php
$senha = 'admin12345'; // Senha que você deseja hashear
$hash = password_hash($senha, PASSWORD_BCRYPT); // Gera o hash
echo $hash; // Exibe o hash gerado
?>

