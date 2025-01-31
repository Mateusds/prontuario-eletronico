<?php
session_start();
require '../../includes/config.php';

// Verificar se é paciente ou admin
if (!isset($_SESSION['paciente_id']) && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

$paciente_id = $_SESSION['paciente_id'] ?? null;

// Buscar atestados
$stmt = $pdo->prepare("SELECT p.*, u.nome as medico_nome 
                      FROM prontuarios p
                      JOIN usuarios u ON p.medico_id = u.id
                      WHERE p.paciente_id = ? AND p.atestado IS NOT NULL
                      ORDER BY p.data_atendimento DESC");
$stmt->execute([$paciente_id]);
$atestados = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Atestados</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Atestados</h1>
            <a href="../paciente/paciente.php" class="btn-voltar">Voltar</a>
            
            <div class="atestados-list">
                <?php if (count($atestados) > 0): ?>
                    <?php foreach ($atestados as $atestado): ?>
                        <div class="atestado-card">
                            <h3>Atestado de Dr. <?= $atestado['medico_nome'] ?></h3>
                            <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($atestado['data_atendimento'])) ?></p>
                            <div class="atestado-content">
                                <h4>Atestado:</h4>
                                <p><?= nl2br($atestado['atestado']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert info">Nenhum atestado encontrado.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 