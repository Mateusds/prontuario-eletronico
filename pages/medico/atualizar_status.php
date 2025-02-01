<?php
header('Content-Type: application/json');
require '../../includes/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['situacao'])) {
    $medicoId = $_POST['id'];
    $situacao = $_POST['situacao'];

    try {
        $stmt = $pdo->prepare("UPDATE medicos SET situacao = ? WHERE id = ?");
        $stmt->execute([$situacao, $medicoId]);
        echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida: dados incompletos']);
}
?> 