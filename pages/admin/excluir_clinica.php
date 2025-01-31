<?php
session_start();
require '../../includes/config.php';

// Verificar se é administrador
if ($_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Verificar se o ID da clínica foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID da clínica inválido.";
    header('Location: configuracao_clinica.php');
    exit();
}

$id = $_GET['id'];

try {
    // Atualizar a situação da clínica para inativo (0)
    $sql = "UPDATE clinicas SET situacao = 0 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    $_SESSION['success'] = "Clínica excluida com sucesso!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Erro ao atualizar a situação da clínica: " . $e->getMessage();
}

// Redirecionar de volta para a página de configuração
header('Location: configuracao_clinica.php');
exit();
?> 