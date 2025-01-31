<?php
session_start();
require '../../includes/config.php';

// Verificar se é administrador
if ($_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Buscar dados para relatórios
$consultas_por_mes = $pdo->query("SELECT DATE_FORMAT(data_consulta, '%Y-%m') as mes, COUNT(*) as total 
                                 FROM consultas 
                                 GROUP BY mes 
                                 ORDER BY mes DESC")->fetchAll();

$usuarios_ativos = $pdo->query("SELECT tipo, COUNT(*) as total 
                               FROM usuarios 
                               GROUP BY tipo")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatórios</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Relatórios</h1>
            <a href="configuracao_clinica.php" class="btn-voltar">Voltar</a>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="relatorio-section">
                <h2>Consultas por Mês</h2>
                <table class="relatorio-table">
                    <thead>
                        <tr>
                            <th>Mês</th>
                            <th>Total de Consultas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultas_por_mes as $consulta): ?>
                            <tr>
                                <td><?= date('m/Y', strtotime($consulta['mes'])) ?></td>
                                <td><?= $consulta['total'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="relatorio-section">
                <h2>Usuários Ativos</h2>
                <table class="relatorio-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios_ativos as $usuario): ?>
                            <tr>
                                <td><?= ucfirst($usuario['tipo']) ?></td>
                                <td><?= $usuario['total'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html> 