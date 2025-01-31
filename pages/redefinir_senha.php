<?php
session_start();
require '../includes/config.php';

$token = $_GET['token'] ?? null;

// Verificar token válido
if ($token) {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token_recuperacao = ? AND token_expira > NOW()");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();
}

// Processar redefinição de senha
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $usuario) {
    $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_BCRYPT);

    // Atualizar senha e limpar token
    $stmt = $pdo->prepare("UPDATE usuarios SET senha = ?, token_recuperacao = NULL, token_expira = NULL WHERE id = ?");
    $stmt->execute([$nova_senha, $usuario['id']]);

    $_SESSION['success'] = "Senha redefinida com sucesso!";
    header('Location: ../pages/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Redefinir Senha</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($usuario): ?>
            <form method="post">
                <div class="form-group">
                    <label for="nova_senha">Nova Senha:</label>
                    <input type="password" id="nova_senha" name="nova_senha" required>
                </div>
                <button type="submit">Redefinir Senha</button>
            </form>
        <?php else: ?>
            <div class="alert error">Link inválido ou expirado.</div>
        <?php endif; ?>
    </div>
</body>
</html> 