<?php
session_start();
require '../../includes/config.php';

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Buscar consultas agendadas
$medico_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT c.*, p.nome_completo as paciente_nome 
                      FROM consultas c
                      JOIN pacientes p ON c.paciente_id = p.id
                      WHERE c.medico_id = ? AND c.data_consulta >= NOW()
                      ORDER BY c.data_consulta ASC");
$stmt->execute([$medico_id]);
$consultas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agenda Médica</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Agenda Médica</h1>
            <a href="../admin/configuracao_clinica.php" class="btn-voltar">Voltar</a>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="consultas-list">
                <?php foreach ($consultas as $consulta): ?>
                    <div class="consulta-card">
                        <h3><?= $consulta['paciente_nome'] ?></h3>
                        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($consulta['data_consulta'])) ?></p>
                        <p><strong>Status:</strong> <?= ucfirst($consulta['status']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>