<?php
// Inicia o buffer de saída para capturar possíveis erros
ob_start();

header('Content-Type: application/json');
require_once '../includes/config.php';

// Log para depuração
error_log("Recebida requisição para atualizar status da agenda");

// Verifica se o ID foi enviado
if (!isset($_GET['id'])) {
    error_log("ID da agenda não informado");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da agenda não informado']);
    ob_end_flush();
    exit;
}

$id = intval($_GET['id']);

// Verifica se o status foi enviado corretamente
$rawData = file_get_contents('php://input');
if (empty($rawData)) {
    error_log("Payload vazio recebido");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payload vazio']);
    ob_end_flush();
    exit;
}

$data = json_decode($rawData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Erro ao decodificar JSON: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato JSON inválido']);
    ob_end_flush();
    exit;
}

if (!isset($data['status']) || !in_array($data['status'], ['Ativa', 'Inativa'])) {
    error_log("Status inválido ou não informado. Recebido: " . ($data['status'] ?? 'null'));
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status inválido. Deve ser "Ativa" ou "Inativa"']);
    ob_end_flush();
    exit;
}

$novoStatus = $data['status'] === 'Ativa' ? 1 : 0; // 1 para Ativa, 0 para Inativa

try {
    // Verifica se a agenda existe
    $stmt = $pdo->prepare("SELECT id, status FROM agendas WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $agenda = $stmt->fetch();

    if (!$agenda) {
        error_log("Agenda não encontrada: ID $id");
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Agenda não encontrada'
        ]);
        exit;
    }

    // Verifica se o status atual já é o mesmo que o novo status
    if ($agenda['status'] == $novoStatus) {
        error_log("Status já está atualizado para agenda ID: $id");
        echo json_encode([
            'success' => true,
            'message' => 'Status já está atualizado'
        ]);
        exit;
    }

    // Atualiza o status no banco de dados
    $stmt = $pdo->prepare("UPDATE agendas SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $novoStatus, ':id' => $id]);

    if ($stmt->rowCount() > 0) {
        error_log("Status atualizado com sucesso para agenda ID: $id");
        error_log("Status atual: " . $agenda['status']);
        error_log("Novo status: " . $novoStatus);
        echo json_encode([
            'success' => true,
            'message' => 'Status atualizado com sucesso'
        ]);
    } else {
        error_log("Nenhuma linha afetada ao atualizar agenda ID: $id");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao atualizar status no banco de dados'
        ]);
    }
} catch (PDOException $e) {
    error_log("Erro ao atualizar status: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar status: ' . $e->getMessage()
    ]);
}

// Limpa o buffer de saída
ob_end_flush(); 