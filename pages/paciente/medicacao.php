<?php
session_start();
require '../../includes/config.php';

// Verificar se é paciente ou admin
if (!isset($_SESSION['paciente_id']) && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

$paciente_id = $_SESSION['paciente_id'] ?? null;

// Buscar medicações
$stmt = $pdo->prepare("SELECT p.*, u.nome as medico_nome 
                      FROM prontuarios p
                      JOIN usuarios u ON p.medico_id = u.id
                      WHERE p.paciente_id = ? AND p.prescricao IS NOT NULL
                      ORDER BY p.data_atendimento DESC");
$stmt->execute([$paciente_id]);
$medicacoes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Medicações</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Medicações</h1>
            <a href="index.php" class="btn-voltar">Voltar</a>
            
            <div class="medicacoes-list">
                <?php if (count($medicacoes) > 0): ?>
                    <?php foreach ($medicacoes as $medicacao): ?>
                        <div class="medicacao-card">
                            <h3>Medicação prescrita por Dr. <?= $medicacao['medico_nome'] ?></h3>
                            <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($medicacao['data_atendimento'])) ?></p>
                            <div class="medicacao-content">
                                <h4>Medicação:</h4>
                                <p><?= nl2br($medicacao['prescricao']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert info">Nenhuma medicação encontrada.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 