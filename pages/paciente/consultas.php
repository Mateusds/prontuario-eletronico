<?php
session_start();
require '../../includes/config.php';

// Verificar se é paciente ou admin
if (!isset($_SESSION['paciente_id']) && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

$paciente_id = $_SESSION['paciente_id'] ?? null;

// Buscar histórico de consultas
$stmt = $pdo->prepare("SELECT c.*, u.nome as medico_nome 
                      FROM consultas c
                      JOIN usuarios u ON c.medico_id = u.id
                      WHERE c.paciente_id = ?
                      ORDER BY c.data_consulta DESC");
$stmt->execute([$paciente_id]);
$consultas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consultas de Pacientes</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Consultas de Pacientes</h1>
            <a href="../paciente/paciente.php" class="btn-voltar">Voltar</a>
            
            <div class="consultas-list">
                <?php foreach ($consultas as $consulta): ?>
                    <div class="consulta-card">
                        <h3>Consulta com Dr. <?= $consulta['medico_nome'] ?></h3>
                        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($consulta['data_consulta'])) ?></p>
                        <p><strong>Status:</strong> <?= ucfirst($consulta['status']) ?></p>
                        <?php if ($consulta['status'] == 'concluida'): ?>
                            <a href="prescricao.php?consulta_id=<?= $consulta['id'] ?>">Ver Prescrição</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>