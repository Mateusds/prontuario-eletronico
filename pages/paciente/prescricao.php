<?php
session_start();
require '../../includes/config.php';

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
$consulta_id = $_GET['consulta_id'] ?? null;

// Buscar prescrições da consulta
$prescricao = [];
if ($consulta_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM prescricoes WHERE consulta_id = ?");
        $stmt->execute([$consulta_id]);
        $prescricao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao buscar prescrições: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Prescrições</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Prescrições</h1>
            <a href="../paciente/paciente.php" class="btn-voltar">Voltar</a>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (!empty($prescricao)): ?>
                <table class="prescricao-table">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Dosagem</th>
                            <th>Instruções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescricao as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['medicamento']) ?></td>
                                <td><?= htmlspecialchars($item['dosagem']) ?></td>
                                <td><?= htmlspecialchars($item['instrucoes']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert info">Nenhuma prescrição encontrada para esta consulta.</div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>