<?php
session_start();
require '../../includes/config.php';

// Verificar se é administrador
if ($_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Processar o formulário de criação de perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
    $tipo = $_POST['tipo'];

    try {
        // Inserir o novo usuário no banco de dados
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $senha, $tipo]);

        $_SESSION['success'] = "Perfil criado com sucesso!";
        header('Location: gerenciar_usuarios.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao criar perfil: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Novo Perfil</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <h2>Menu Admin</h2>
            <ul>
                <li><a href="configuracao_clinica.php">Configuração</a></li>
                <li><a href="gerenciar_usuarios.php">Gerenciar Usuários</a></li>
            </ul>
        </aside>

        <main class="content">
            <h1>Criar Novo Perfil</h1>
            <a href="gerenciar_usuarios.php" class="btn-voltar">Voltar</a>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="post" class="configuracao-form">
                <div class="form-group">
                    <label for="nome">Nome Completo:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                <div class="form-group">
                    <label for="tipo">Tipo de Usuário:</label>
                    <select id="tipo" name="tipo" required>
                        <option value="admin">Administrador</option>
                        <option value="medico">Médico</option>
                        <option value="atendente">Atendente</option>
                    </select>
                </div>
                <button type="submit" class="btn-criar">
                    <i class="fas fa-save"></i> Salvar Perfil
                </button>
            </form>
        </main>
    </div>
</body>
</html> 