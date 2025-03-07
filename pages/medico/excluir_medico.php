<?php
require '../../includes/config.php';
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Verificar se o ID do médico foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID do médico não informado.";
    header('Location: cadastro_medico.php');
    exit();
}

$medico_id = $_GET['id'];

try {
    // Verificar se o médico existe
    $stmt = $pdo->prepare("SELECT id FROM medicos WHERE id = ?");
    $stmt->execute([$medico_id]);
    $medico = $stmt->fetch();

    if (!$medico) {
        $_SESSION['error'] = "Médico não encontrado.";
        header('Location: cadastro_medico.php');
        exit();
    }

    // Excluir o médico
    $stmt = $pdo->prepare("DELETE FROM medicos WHERE id = ?");
    $stmt->execute([$medico_id]);

    $_SESSION['success'] = "Médico excluído com sucesso!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Erro ao excluir médico: " . $e->getMessage();
}

header('Location: cadastro_medico.php');
exit();
?> 