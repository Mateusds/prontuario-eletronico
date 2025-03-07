<?php
session_start();
require '../includes/config.php';

// Verifica se o usuário está logado e tem permissão
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin')) {
    die('Acesso negado');
}

if (isset($_POST['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE guias SET situacao = 0 WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        echo 'success';
    } catch (PDOException $e) {
        echo 'Erro ao inativar guia: ' . $e->getMessage();
    }
} else {
    echo 'ID da guia não fornecido';
}