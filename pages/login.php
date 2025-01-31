<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Prontuário Eletrônico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="../includes/auth.php" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit">Entrar</button>
        </form>

        <div class="forgot-password">
            <a href="recuperar_senha.php">Esqueceu sua senha?</a>
        </div>
    </div>

    <?php
    // Verificar se o login foi bem-sucedido
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] == 'admin') {
            header('Location: admin/configuracao_clinica.php');  // Caminho corrigido
        } else {
            header('Location: relatorios.php');
        }
        exit();
    }
    ?>
</body>
</html>