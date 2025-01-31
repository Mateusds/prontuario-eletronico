<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Verificar se o email existe
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Verificar a senha
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['tipo'];
            
            // Redirecionar conforme o tipo de usuário
            if ($user['tipo'] == 'medico') {
                header('Location: ../pages/medico/agenda.php');
            } elseif ($user['tipo'] == 'atendente') {
                header('Location: ../pages/atendente/agenda.php');
            } elseif ($user['tipo'] == 'admin') {
                header('Location: ../pages/admin/relatorios.php');
            } else {
                // Tipo de usuário desconhecido
                $_SESSION['error'] = "Tipo de usuário inválido";
                header('Location: ../pages/login.php');
            }
            exit();
        } else {
            // Senha incorreta
            $_SESSION['error'] = "Senha incorreta";
            header('Location: ../pages/login.php');
            exit();
        }
    } else {
        // Usuário não encontrado
        $_SESSION['error'] = "Email não cadastrado";
        header('Location: ../pages/login.php');
        exit();
    }
} else {
    // Método de requisição inválido
    $_SESSION['error'] = "Método de requisição inválido";
    header('Location: ../pages/login.php');
    exit();
}
?>