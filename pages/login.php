<?php
session_start();
session_destroy();  // Remova esta linha após os testes
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Prontuário Eletrônico</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<div class="login-container">
    <div class="logo-circle">
        <img src="../assets/images/logo.png" alt="Logo da Clínica">
    </div>
    <form action="../includes/auth.php" method="post">
        <div class="form-group email-field">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group password-field">
            <label for="senha">Senha:</label>
            <div style="position: relative; width: 100%;">
                <input type="password" id="senha" name="senha" required>
                <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
            </div>
        </div>
        <button type="submit">Entrar</button>
        <div class="forgot-password">
            <a href="recuperar_senha.php">
                <i class="fas fa-question-circle"></i>
                Esqueceu sua senha?
            </a>
        </div>
    </form>
</div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="logout-button">
                <form action="../includes/logout.php" method="post">
                    <button type="submit" class="btn-logout">Sair</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php
    // Verificar se o login foi bem-sucedido
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] == 'admin') {
            header('Location: admin/configuracao_clinica.php');
        } else {
            header('Location: relatorios.php');
        }
        exit();
    }
    ?>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('senha');

    togglePassword.addEventListener('click', function () {
        // Alterna o tipo de input entre 'password' e 'text'
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        // Alterna o ícone entre olho aberto e fechado
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>