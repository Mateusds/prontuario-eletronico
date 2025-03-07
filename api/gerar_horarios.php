<?php
session_start();
require '../includes/config.php';

// Limpar o buffer de saída
ob_clean();

// Definir o cabeçalho como JSON
header('Content-Type: application/json');

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Receber dados do POST
$data = json_decode(file_get_contents('php://input'), true);

// Verificar se os dados foram recebidos corretamente
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Campos obrigatórios
$requiredFields = [
    'agenda_id', 'especialidade_id', 'vigencia_inicio', 'vigencia_fim',
    'dias_semana', 'periodo_inicio', 'periodo_fim', 'duracao'
];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Campo obrigatório faltando: $field"]);
        exit;
    }
}

try {
    // Converter datas para DateTime
    $vigenciaInicio = new DateTime($data['vigencia_inicio']);
    $vigenciaFim = new DateTime($data['vigencia_fim']);
    $diasSemana = $data['dias_semana'];
    $periodoInicio = $data['periodo_inicio'];
    $periodoFim = $data['periodo_fim'];
    $duracaoConsulta = $data['duracao'];
    $totalVagas = $data['quantidade_beneficiario'];

    // Gerar horários
    $agendas = distribuirVagas($diasSemana, $data['vigencia_inicio'], $data['vigencia_fim'], $periodoInicio, $periodoFim, $duracaoConsulta, $totalVagas);

    // Iniciar transação
    $pdo->beginTransaction();
    
    // Preparar statement para inserção dos horários
    $sqlHorarios = "INSERT INTO horarios_agenda 
        (agenda_id, especialidade_id, data, horario, status) 
        VALUES (?, ?, ?, ?, 'disponivel')";
    
    $stmtHorarios = $pdo->prepare($sqlHorarios);
    
    // Inserir cada horário no banco
    foreach ($agendas as $agenda) {
        $result = $stmtHorarios->execute([
            $data['agenda_id'],
            $data['especialidade_id'],
            $agenda['data'],
            $agenda['horario_inicio']
        ]);
        
        if (!$result) {
            throw new Exception('Erro ao inserir horário: ' . implode(' ', $stmtHorarios->errorInfo()));
        }
    }
    
    // Commit da transação
    $pdo->commit();
    
    // Limpar o buffer de saída e enviar a resposta JSON
    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Horários gerados com sucesso']);
} catch (Exception $e) {
    // Rollback em caso de erro
    $pdo->rollBack();
    error_log('Erro ao gerar horários: ' . $e->getMessage());
    
    // Limpar o buffer de saída e enviar a resposta JSON de erro
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Função para gerar horários com base no período e duração da consulta.
 */
function gerarHorarios($inicio, $fim, $duracao) {
    $horarios = [];
    $current = strtotime($inicio);
    $end = strtotime($fim);
    
    while ($current < $end) {
        $horarios[] = date('H:i', $current);
        $current = strtotime("+$duracao minutes", $current);
    }
    
    return $horarios;
}

$horarios = gerarHorarios('08:00', '18:00', 10);
print_r($horarios); 