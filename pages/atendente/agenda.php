<?php
session_start();
require '../../includes/config.php';

// Verificar se é atendente ou admin
if ($_SESSION['user_type'] != 'atendente' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

$atendente_id = $_SESSION['user_id'];

// Buscar consultas agendadas
$stmt = $pdo->prepare("SELECT c.*, p.nome_completo as paciente_nome 
                      FROM consultas c
                      JOIN pacientes p ON c.paciente_id = p.id
                      WHERE c.medico_id = ? AND c.data_consulta >= NOW()
                      ORDER BY c.data_consulta ASC");
$stmt->execute([$atendente_id]);
$consultas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agenda do Atendente</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Agenda do Atendente</h1>
            <a href="../atendente/atendente.php" class="btn-voltar">Voltar</a>
            
            <div class="consultas-list">
                <?php foreach ($consultas as $consulta): ?>
                    <div class="consulta-card">
                        <h3><?= $consulta['paciente_nome'] ?></h3>
                        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($consulta['data_consulta'])) ?></p>
                        <p><strong>Status:</strong> <?= ucfirst($consulta['status']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <script>
        function sair() {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Você será desconectado do sistema!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, sair!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../../pages/logout.php';
                }
            });
        }
    </script>
</body>
</html>