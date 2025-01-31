<?php
session_start();
require '../../includes/config.php';

// Verificar se é atendente
if ($_SESSION['user_type'] != 'atendente') {
    header('Location: ../../pages/login.php');
    exit();
}

// Buscar médicos e pacientes
$medicos = $pdo->query("SELECT id, nome FROM usuarios WHERE tipo = 'medico'")->fetchAll();
$pacientes = $pdo->query("SELECT id, nome_completo FROM pacientes")->fetchAll();

// Processar cadastro de consulta
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paciente_id = $_POST['paciente_id'];
    $medico_id = $_POST['medico_id'];
    $data_consulta = $_POST['data_consulta'];

    try {
        $stmt = $pdo->prepare("INSERT INTO consultas (paciente_id, medico_id, data_consulta) VALUES (?, ?, ?)");
        $stmt->execute([$paciente_id, $medico_id, $data_consulta]);
        $_SESSION['success'] = "Consulta agendada com sucesso!";
        header('Location: consultas.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao agendar consulta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agendar Consulta</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <h2>Menu Atendente</h2>
            <ul>
                <li><a href="agenda.php">Agenda</a></li>
                <li><a href="consultas.php">Agendar Consulta</a></li>
            </ul>
        </aside>

        <main class="content">
            <h1>Agendar Nova Consulta</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="paciente_id">Paciente:</label>
                    <select id="paciente_id" name="paciente_id" required>
                        <?php foreach ($pacientes as $paciente): ?>
                            <option value="<?= $paciente['id'] ?>"><?= $paciente['nome_completo'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="medico_id">Médico:</label>
                    <select id="medico_id" name="medico_id" required>
                        <?php foreach ($medicos as $medico): ?>
                            <option value="<?= $medico['id'] ?>">Dr. <?= $medico['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="data_consulta">Data e Hora:</label>
                    <input type="datetime-local" id="data_consulta" name="data_consulta" required>
                </div>
                <button type="submit">Agendar Consulta</button>
            </form>
        </main>
    </div>
</body>
</html>
