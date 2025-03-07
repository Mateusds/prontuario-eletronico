<?php
session_start();
require '../../includes/config.php';

// Buscar paciente em atendimento
$medico_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT c.*, p.nome_completo as paciente_nome 
                      FROM consultas c
                      JOIN pacientes p ON c.paciente_id = p.id
                      WHERE c.medico_id = ? AND c.status = 'em_andamento'
                      ORDER BY c.data_consulta ASC LIMIT 1");
$stmt->execute([$medico_id]);
$paciente = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel de Chamadas</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .btn-sair {
            position: fixed;
            right: 20px;
            top: 20px;
            background-color: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            width: auto;
            z-index: 1000;
        }

        .btn-sair:hover {
            background-color: #c82333;
        }

        .painel-container {
            text-align: center;
            padding: 50px;
            background: #2c3e50;
            color: white;
            min-height: 100vh;
        }
        
        .paciente-info {
            background: white;
            color: #2c3e50;
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>Painel de Chamadas</h1>
                <form id="logoutForm" action="../../pages/logout.php" method="post">
                    <button type="submit" class="btn-sair">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
            <a href="../medico/medico.php" class="btn-voltar">Voltar</a>
            
            <?php if ($paciente): ?>
                <div class="paciente-info">
                    <h2>Paciente: <?= $paciente['paciente_nome'] ?></h2>
                    <p>Por favor, dirija-se ao consultório</p>
                </div>
            <?php else: ?>
                <div class="paciente-info">
                    <h2>Aguardando próximo paciente</h2>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
