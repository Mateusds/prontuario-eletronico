<?php
session_start();
require '../../includes/config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../pages/login.php');
    exit();
}

// Verificar se é paciente ou admin
if (!isset($_SESSION['paciente_id']) && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Página Inicial do Paciente</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="consultas.php">Consultas</a></li>
                <li><a href="prescricao.php">Prescrições</a></li>
                <li><a href="atestado.php">Atestados</a></li>
                <li><a href="encaminhamento.php">Encaminhamentos</a></li>
                <li><a href="medicacao.php">Medicações</a></li>
            </ul>
        </aside>

        <main class="content">
            <h1>Bem-vindo, Paciente!</h1>
            <p>Selecione uma opção no menu ao lado para acessar suas informações.</p>
        </main>
    </div>
</body>
</html> 