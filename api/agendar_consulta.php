<?php
session_start();
require '../includes/config.php';

// Verificar se a requisição é POST ou se está em modo de teste (GET)
$isTestMode = ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['test']));

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$isTestMode) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se os parâmetros foram passados
if (!isset($_GET['agenda_id']) || !isset($_GET['paciente_id']) || !isset($_GET['medico_id']) || !isset($_GET['data_consulta'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$agenda_id = intval($_GET['agenda_id']);
$paciente_id = intval($_GET['paciente_id']);
$medico_id = intval($_GET['medico_id']);
$data_consulta = urldecode($_GET['data_consulta']); // Decodifica o valor da data e hora

try {
    // Verificar se a agenda existe
    $stmt = $pdo->prepare("SELECT vagas_disponiveis FROM agendas WHERE id = ?");
    $stmt->execute([$agenda_id]);
    $agenda = $stmt->fetch();

    if (!$agenda) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Agenda não encontrada. ID: ' . $agenda_id]);
        exit;
    }

    // Verificar se há vagas disponíveis
    if ($agenda['vagas_disponiveis'] <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Não há vagas disponíveis']);
        exit;
    }

    // Inserir a consulta na tabela de consultas
    $stmt = $pdo->prepare("INSERT INTO consultas (agenda_id, paciente_id, medico_id, data_consulta, status) VALUES (?, ?, ?, ?, 'agendada')");
    $stmt->execute([$agenda_id, $paciente_id, $medico_id, $data_consulta]);

    // Atualizar o número de vagas disponíveis
    $stmt = $pdo->prepare("UPDATE agendas SET vagas_disponiveis = vagas_disponiveis - 1 WHERE id = ?");
    $stmt->execute([$agenda_id]);

    // Atualizar o status da agenda para "esgotada" se as vagas chegarem a zero
    if ($agenda['vagas_disponiveis'] == 1) {
        $stmt = $pdo->prepare("UPDATE agendas SET status = 'esgotada' WHERE id = ?");
        $stmt->execute([$agenda_id]);
    }

    echo json_encode(['success' => true, 'message' => 'Consulta agendada com sucesso']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao agendar consulta: ' . $e->getMessage()]);
}