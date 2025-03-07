<?php
require '../includes/config.php';

header('Content-Type: application/json');

try {
    // Verifica se o parâmetro foi enviado
    if (!isset($_GET['medico_id']) || empty($_GET['medico_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parâmetro medico_id é obrigatório']);
        exit();
    }

    $medico_id = intval($_GET['medico_id']);

    // Prepara e executa a consulta para buscar o CRM
    $stmt = $pdo->prepare("SELECT crm FROM medicos WHERE id = ?");
    $stmt->execute([$medico_id]);
    $medico = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($medico) {
        echo json_encode(['success' => true, 'crm' => $medico['crm']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Médico não encontrado']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro inesperado: ' . $e->getMessage()]);
}
?> 