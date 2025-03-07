<?php
session_start();
require '../../includes/config.php';

// Verifica se o usuário está logado e é médico ou admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin')) {
    header('Location: ../login.php');
    exit;
}

// Verifica se o ID da guia foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID da guia inválido.');
}

$guia_id = $_GET['id'];

// Buscar os detalhes da guia
try {
    $stmt = $pdo->prepare("
        SELECT g.*, p.nome_completo AS paciente
        FROM guias g
        JOIN pacientes p ON g.paciente_id = p.id
        WHERE g.id = ?
    ");
    $stmt->execute([$guia_id]);
    $guia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$guia) {
        die('Guia não encontrada.');
    }
} catch (PDOException $e) {
    die('Erro ao buscar guia: ' . $e->getMessage());
}

include '../../includes/header.php';
include '../../includes/menu_lateral.php';
?>

<main class="content">
    <h1>Visualizar Guia</h1>
    
    <div class="card">
        <div class="card-header">
            <h3>Detalhes da Guia</h3>
        </div>
        <div class="card-body">
            <div class="guia-detalhes">
                <div class="detalhe-item">
                    <span class="detalhe-label">Paciente:</span>
                    <span class="detalhe-valor"><?= htmlspecialchars($guia['paciente']) ?></span>
                </div>
                <div class="detalhe-item">
                    <span class="detalhe-label">Tipo de Atendimento:</span>
                    <span class="detalhe-valor"><?= htmlspecialchars($guia['tipo_atendimento']) ?></span>
                </div>
                <div class="detalhe-item">
                    <span class="detalhe-label">Data de Emissão:</span>
                    <span class="detalhe-valor"><?= date('d/m/Y H:i', strtotime($guia['data_emissao'])) ?></span>
                </div>
                <div class="detalhe-item">
                    <span class="detalhe-label">Descrição:</span>
                    <span class="detalhe-valor"><?= htmlspecialchars($guia['descricao']) ?></span>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?> 