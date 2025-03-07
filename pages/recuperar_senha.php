<?php
session_start();
require '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Verificar se o email existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Gerar token de recuperação
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Salvar token no banco
        $stmt = $pdo->prepare("UPDATE usuarios SET token_recuperacao = ?, token_expira = ? WHERE id = ?");
        $stmt->execute([$token, $expira, $usuario['id']]);

        // Enviar email com o link de recuperação (simulado aqui)
        $link = "http://seusite.com/pages/redefinir_senha.php?token=$token";
        $_SESSION['success'] = "Um link de recuperação foi enviado para o seu email.";
    } else {
        $_SESSION['error'] = "Email não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - Prontuário Eletrônico</title>
    <link rel="stylesheet" href="../assets/css/recuperar_senha.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="recover-container">
        <h2>Recuperar Senha</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="../includes/recuperar_senha.php" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Enviar</button>
        </form>

        <div class="forgot-password">
            <a href="login.php">
                <i class="fas fa-arrow-left"></i>
                Voltar para o Login
            </a>
        </div>
    </div>
</body>
</html> 