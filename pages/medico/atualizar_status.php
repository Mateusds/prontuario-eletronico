<?php
header('Content-Type: application/json');
require '../../includes/config.php';
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medicoId = $_POST['id'];
    $situacao = $_POST['situacao'];

    try {
        $stmt = $pdo->prepare("UPDATE medicos SET situacao = ? WHERE id = ?");
        $stmt->execute([$situacao, $medicoId]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida: dados incompletos']);
}
?> 