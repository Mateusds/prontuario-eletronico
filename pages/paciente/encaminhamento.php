<?php
session_start();
require '../../includes/config.php';

// Verificar se é paciente ou admin
if (!isset($_SESSION['paciente_id']) && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

$paciente_id = $_SESSION['paciente_id'] ?? null;

// Buscar encaminhamentos
$stmt = $pdo->prepare("SELECT p.*, u.nome as medico_nome 
                      FROM prontuarios p
                      JOIN usuarios u ON p.medico_id = u.id
                      WHERE p.paciente_id = ? AND p.encaminhamento IS NOT NULL
                      ORDER BY p.data_atendimento DESC");
$stmt->execute([$paciente_id]);
$encaminhamentos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Encaminhamentos</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Encaminhamentos</h1>
            <a href="../paciente/paciente.php" class="btn-voltar">Voltar</a>
            
            <div class="encaminhamentos-list">
                <?php if (count($encaminhamentos) > 0): ?>
                    <?php foreach ($encaminhamentos as $encaminhamento): ?>
                        <div class="encaminhamento-card">
                            <h3>Encaminhamento de Dr. <?= $encaminhamento['medico_nome'] ?></h3>
                            <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($encaminhamento['data_atendimento'])) ?></p>
                            <div class="encaminhamento-content">
                                <h4>Encaminhamento:</h4>
                                <p><?= nl2br($encaminhamento['encaminhamento']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert info">Nenhum encaminhamento encontrado.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 