<?php
session_start();
require '../../includes/config.php';

// Verificar se é atendente
if ($_SESSION['user_type'] != 'atendente') {
    header('Location: ../../pages/login.php');
    exit();
}

// Buscar consultas agendadas
$consultas = $pdo->query("SELECT c.*, p.nome_completo as paciente_nome, u.nome as medico_nome 
                        FROM consultas c
                        JOIN pacientes p ON c.paciente_id = p.id
                        JOIN usuarios u ON c.medico_id = u.id
                        WHERE c.status = 'agendada'
                        ORDER BY c.data_consulta ASC")->fetchAll();

// Processar cancelamento de consulta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancelar_consulta'])) {
    $consulta_id = $_POST['consulta_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE consultas SET status = 'cancelada' WHERE id = ?");
        $stmt->execute([$consulta_id]);
        $_SESSION['success'] = "Consulta cancelada com sucesso!";
        header('Location: gerenciar_consultas.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao cancelar consulta: " . $e->getMessage();
    }
}

// Processar reagendamento de consulta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reagendar_consulta'])) {
    $consulta_id = $_POST['consulta_id'];
    $nova_data = $_POST['nova_data'];
    
    try {
        $stmt = $pdo->prepare("UPDATE consultas SET data_consulta = ? WHERE id = ?");
        $stmt->execute([$nova_data, $consulta_id]);
        $_SESSION['success'] = "Consulta reagendada com sucesso!";
        header('Location: gerenciar_consultas.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao reagendar consulta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Consultas</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <h2>Menu Atendente</h2>
            <ul>
                <li><a href="agenda.php">Agenda</a></li>
                <li><a href="consultas.php">Agendar Consulta</a></li>
                <li><a href="gerenciar_consultas.php">Gerenciar Consultas</a></li>
            </ul>
        </aside>

        <main class="content">
            <h1>Gerenciar Consultas</h1>
            
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
                        <h3>Consulta de <?= $consulta['paciente_nome'] ?></h3>
                        <p><strong>Médico:</strong> Dr. <?= $consulta['medico_nome'] ?></p>
                        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($consulta['data_consulta'])) ?></p>
                        
                        <div class="actions">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                                <button type="submit" name="cancelar_consulta" class="btn-cancelar">Cancelar</button>
                            </form>
                            
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                                <input type="datetime-local" name="nova_data" required>
                                <button type="submit" name="reagendar_consulta" class="btn-reagendar">Reagendar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html> 