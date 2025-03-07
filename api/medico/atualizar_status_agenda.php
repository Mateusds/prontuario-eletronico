<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';

// Verifica se o ID foi enviado
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da agenda não informado']);
    exit;
}

// Verifica se o status foi enviado
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status não informado']);
    exit;
}

$id = intval($_GET['id']);
$novoStatus = $data['status'] === 'Ativa' ? 1 : 0;

try {
    // Atualiza o status no banco de dados
    $stmt = $pdo->prepare("UPDATE agendas SET situacao = :status WHERE id = :id");
    $stmt->execute([':status' => $novoStatus, ':id' => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Status atualizado com sucesso'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Agenda não encontrada'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar status: ' . $e->getMessage()
    ]);
} 