<?php
session_start();
require '../../includes/config.php';

// Verificar se o paciente está logado
if (!isset($_SESSION['paciente_id'])) {
    header('Location: ../../pages/login.php');
    exit();
}

// Buscar médicos disponíveis
$medicos = $pdo->query("SELECT id, nome FROM usuarios WHERE tipo = 'medico'")->fetchAll();

// Processar solicitação de consulta
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paciente_id = $_SESSION['paciente_id'];
    $medico_id = $_POST['medico_id'];
    $data_consulta = $_POST['data_consulta'];
    $observacoes = $_POST['observacoes'];

    try {
        $stmt = $pdo->prepare("INSERT INTO consultas (paciente_id, medico_id, data_consulta, observacoes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$paciente_id, $medico_id, $data_consulta, $observacoes]);
        $_SESSION['success'] = "Consulta solicitada com sucesso!";
        header('Location: nova_consulta.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao solicitar consulta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Consulta</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="consultas.php">Histórico de Consultas</a></li>
                <li><a href="nova_consulta.php">Nova Consulta</a></li>
                <li><a href="prescricao.php">Prescrições</a></li>
                <li><a href="atestado.php">Atestados</a></li>
                <li><a href="medicacao.php">Medicações</a></li>
                <li><a href="encaminhamento.php">Encaminhamentos</a></li>
            </ul>
        </aside>

        <main class="content">
            <h1>Solicitar Nova Consulta</h1>
            
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
                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes" rows="4"></textarea>
                </div>
                <button type="submit">Solicitar Consulta</button>
            </form>
        </main>
    </div>
</body>
</html> 