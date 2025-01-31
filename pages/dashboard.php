<?php
session_start();
require '../includes/config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Dados para médicos
if ($user_type == 'medico') {
    // Consultas agendadas
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM consultas WHERE medico_id = ? AND status = 'agendada'");
    $stmt->execute([$user_id]);
    $consultas_agendadas = $stmt->fetch()['total'];

    // Consultas concluídas
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM consultas WHERE medico_id = ? AND status = 'concluida'");
    $stmt->execute([$user_id]);
    $consultas_concluidas = $stmt->fetch()['total'];

    // Próxima consulta
    $stmt = $pdo->prepare("SELECT c.*, p.nome_completo as paciente_nome 
                          FROM consultas c
                          JOIN pacientes p ON c.paciente_id = p.id
                          WHERE c.medico_id = ? AND c.status = 'agendada'
                          ORDER BY c.data_consulta ASC LIMIT 1");
    $stmt->execute([$user_id]);
    $proxima_consulta = $stmt->fetch();
}

// Dados para atendentes
if ($user_type == 'atendente') {
    // Total de consultas agendadas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM consultas WHERE status = 'agendada'");
    $consultas_agendadas = $stmt->fetch()['total'];

    // Total de consultas canceladas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM consultas WHERE status = 'cancelada'");
    $consultas_canceladas = $stmt->fetch()['total'];

    // Próxima consulta
    $stmt = $pdo->query("SELECT c.*, p.nome_completo as paciente_nome, u.nome as medico_nome 
                        FROM consultas c
                        JOIN pacientes p ON c.paciente_id = p.id
                        JOIN usuarios u ON c.medico_id = u.id
                        WHERE c.status = 'agendada'
                        ORDER BY c.data_consulta ASC LIMIT 1");
    $proxima_consulta = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php if ($user_type == 'medico'): ?>
                    <li><a href="agenda.php">Agenda</a></li>
                    <li><a href="atendimento.php">Atendimento</a></li>
                <?php else: ?>
                    <li><a href="consultas.php">Agendar Consulta</a></li>
                    <li><a href="gerenciar_consultas.php">Gerenciar Consultas</a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <main class="content">
            <h1>Dashboard</h1>
            
            <div class="dashboard-stats">
                <?php if ($user_type == 'medico'): ?>
                    <div class="stat-card">
                        <h3>Consultas Agendadas</h3>
                        <p><?= $consultas_agendadas ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Consultas Concluídas</h3>
                        <p><?= $consultas_concluidas ?></p>
                    </div>
                <?php else: ?>
                    <div class="stat-card">
                        <h3>Consultas Agendadas</h3>
                        <p><?= $consultas_agendadas ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Consultas Canceladas</h3>
                        <p><?= $consultas_canceladas ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="proxima-consulta">
                <h2>Próxima Consulta</h2>
                <?php if ($proxima_consulta): ?>
                    <div class="consulta-card">
                        <h3>Paciente: <?= $proxima_consulta['paciente_nome'] ?></h3>
                        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($proxima_consulta['data_consulta'])) ?></p>
                        <?php if ($user_type == 'atendente'): ?>
                            <p><strong>Médico:</strong> Dr. <?= $proxima_consulta['medico_nome'] ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert info">Nenhuma consulta agendada.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
