<?php
session_start();
require '../../includes/config.php';

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

$medico_id = $_SESSION['user_id'];

// Buscar pacientes na fila
$stmt = $pdo->prepare("SELECT c.*, p.nome_completo as paciente_nome 
                      FROM consultas c
                      JOIN pacientes p ON c.paciente_id = p.id
                      WHERE c.medico_id = ? AND c.status = 'agendada'
                      ORDER BY c.data_consulta ASC");
$stmt->execute([$medico_id]);
$pacientes = $stmt->fetchAll();

// Processar chamada de paciente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['chamar_paciente'])) {
    $consulta_id = $_POST['consulta_id'];
    
    // Atualizar status da consulta
    $stmt = $pdo->prepare("UPDATE consultas SET status = 'em_andamento' WHERE id = ?");
    $stmt->execute([$consulta_id]);
    
    // Atualizar painel
    header('Location: atendimento.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Atendimento Médico</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Atendimento Médico</h1>
            <a href="../medico/medico.php" class="btn-voltar">Voltar</a>
            
            <div class="fila-atendimento">
                <?php foreach ($pacientes as $paciente): ?>
                    <div class="paciente-card">
                        <h3><?= $paciente['paciente_nome'] ?></h3>
                        <p>Horário: <?= date('H:i', strtotime($paciente['data_consulta'])) ?></p>
                        <form method="post">
                            <input type="hidden" name="consulta_id" value="<?= $paciente['id'] ?>">
                            <button type="submit" name="chamar_paciente">Chamar Paciente</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
