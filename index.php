<?php
session_start();

// Verifica se o usuário está logado
if (isset($_SESSION['usuario_id'])) {
    // Redireciona para o dashboard apropriado
    switch ($_SESSION['perfil']) {
        case 'medico':
            header('Location: pages/medico/agenda.php');
            break;
        case 'atendente':
            header('Location: pages/atendente/agenda.php');
            break;
        default:
            header('Location: pages/dashboard.php');
            break;
    }
    exit();
} else {
    // Se não estiver logado, redireciona para a tela de login
    header('Location: pages/login.php');
    exit();
}
?> 