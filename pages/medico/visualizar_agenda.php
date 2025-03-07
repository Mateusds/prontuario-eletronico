<?php
session_start();
require '../../includes/config.php';
require '../../includes/functions.php';

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

$medico_id = $_SESSION['user_id']; // Supondo que o ID do médico está na sessão

// Log para depuração
error_log("ID do Médico: " . $medico_id);

$agenda = buscarAgendaMedico($medico_id);

// Debug: Exibir os dados retornados pela função
echo "<pre>";
print_r($agenda);
echo "</pre>";
exit; // Interrompe a execução para verificar os dados

// Organizar a agenda por data
$agenda_organizada = [];
foreach ($agenda as $item) {
    $data = $item['data'];
    if (!isset($agenda_organizada[$data])) {
        $agenda_organizada[$data] = [];
    }
    $agenda_organizada[$data][] = $item;
}

// Log para depuração
error_log("Agenda Organizada: " . print_r($agenda_organizada, true));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Visualizar Agenda</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <main class="content">
            <h1>Minha Agenda</h1>
            
            <div class="agenda-container">
                <?php if (empty($agenda_organizada)): ?>
                    <p>Nenhum horário cadastrado.</p>
                <?php else: ?>
                    <?php foreach ($agenda_organizada as $data => $horarios): ?>
                        <div class="dia-agenda">
                            <h3><?= date('d/m/Y', strtotime($data)) ?></h3>
                            <div class="horarios-list">
                                <?php foreach ($horarios as $horario): ?>
                                    <div class="horario-item">
                                        <span><?= substr($horario['horario'], 0, 5) ?></span>
                                        <span class="status-badge <?= $horario['status'] ?>">
                                            <?= ucfirst($horario['status']) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 