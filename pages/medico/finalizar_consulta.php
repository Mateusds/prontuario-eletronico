<?php
session_start();
require '../../includes/config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../pages/login.php');
    exit();
}

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

$medico_id = $_SESSION['user_id'];
$consulta_id = $_GET['consulta_id'] ?? null;

// Buscar detalhes da consulta
$consulta = [];
if ($consulta_id) {
    try {
        if ($_SESSION['user_type'] == 'admin') {
            // Admin pode ver qualquer consulta
            $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id = ?");
            $stmt->execute([$consulta_id]);
        } else {
            // Médico só pode ver suas próprias consultas
            $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id = ? AND medico_id = ?");
            $stmt->execute([$consulta_id, $medico_id]);
        }
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao buscar consulta: " . $e->getMessage();
    }
}

// Processar finalização da consulta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finalizar_consulta'])) {
    $diagnostico = $_POST['diagnostico'];
    $observacoes = $_POST['observacoes'];

    try {
        $stmt = $pdo->prepare("UPDATE consultas SET status = 'finalizada', diagnostico = ?, observacoes = ? WHERE id = ?");
        $stmt->execute([$diagnostico, $observacoes, $consulta_id]);
        $_SESSION['success'] = "Consulta finalizada com sucesso!";
        header('Location: finalizar_consulta.php?consulta_id=' . $consulta_id);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao finalizar consulta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Consulta</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Finalizar Consulta</h1>
            <a href="../medico/medico.php" class="btn-voltar">Voltar</a>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (!empty($consulta)): ?>
                <div class="consulta-info">
                    <p><strong>Paciente:</strong> <?= htmlspecialchars($consulta['paciente_nome']) ?></p>
                    <p><strong>Data:</strong> <?= htmlspecialchars($consulta['data_consulta']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($consulta['status']) ?></p>
                </div>

                <form method="post">
                    <div class="form-group">
                        <label for="diagnostico">Diagnóstico:</label>
                        <textarea id="diagnostico" name="diagnostico" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="observacoes">Observações:</label>
                        <textarea id="observacoes" name="observacoes"></textarea>
                    </div>
                    <button type="submit" name="finalizar_consulta">Finalizar Consulta</button>
                </form>
            <?php else: ?>
                <div class="alert info">Consulta não encontrada.</div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html> 