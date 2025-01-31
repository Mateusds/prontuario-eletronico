<?php
// Caminho correto para o arquivo config.php
require '../../includes/config.php';

session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../pages/login.php');
    exit();
}

// Verificar se é paciente ou admin
if (!isset($_SESSION['paciente_id']) && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

$paciente_id = $_SESSION['paciente_id'] ?? null;

// Buscar informações do paciente
$paciente = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
    $stmt->execute([$paciente_id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erro ao buscar informações do paciente: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Paciente</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Perfil do Paciente</h1>
            <a href="index.php" class="btn-voltar">Voltar</a>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (!empty($paciente)): ?>
                <div class="perfil-info">
                    <p><strong>Nome:</strong> <?= htmlspecialchars($paciente['nome']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($paciente['email']) ?></p>
                    <p><strong>Telefone:</strong> <?= htmlspecialchars($paciente['telefone']) ?></p>
                    <p><strong>Endereço:</strong> <?= htmlspecialchars($paciente['endereco']) ?></p>
                </div>
            <?php else: ?>
                <div class="alert info">Nenhuma informação encontrada.</div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
