<?php
session_start();
require '../includes/config.php';

// Definir o cabeçalho para JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // Verificar se a requisição é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido', 405);
    }

    // Verificar se é médico ou admin
    if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
        throw new Exception('Acesso não autorizado', 403);
    }

    // Receber dados do POST
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Dados inválidos', 400);
    }

    // Validar os dados recebidos
    $requiredFields = [
        'medico_id', 'especialidade_id', 'unidade_id', 'vigencia_inicio', 'vigencia_fim',
        'dias_semana', 'periodo_inicio', 'periodo_fim', 'duracao', 'procedimento',
        'especialidade_nome', 'tipo', 'faixa_etaria_inicio', 'faixa_etaria_fim',
        'quantidade_beneficiario', 'total_vagas', 'vagas_disponiveis'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            throw new Exception("Campo obrigatório faltando: $field", 400);
        }
    }

    // Processar os dias da semana
    $diasSelecionados = isset($data['dias_semana']) ? explode(',', $data['dias_semana']) : [];
    $diasSemana = [
        'seg' => in_array('seg', $diasSelecionados) ? 1 : 0,
        'ter' => in_array('ter', $diasSelecionados) ? 1 : 0,
        'qua' => in_array('qua', $diasSelecionados) ? 1 : 0,
        'qui' => in_array('qui', $diasSelecionados) ? 1 : 0,
        'sex' => in_array('sex', $diasSelecionados) ? 1 : 0,
        'sab' => in_array('sab', $diasSelecionados) ? 1 : 0
    ];

    $sql = "INSERT INTO agendas (
        medico_id, especialidade_id, unidade_id, data_agenda, horario_inicio, horario_fim, 
        duracao, procedimento, criado_por, criado_em, status, visivel, vigencia_inicio, 
        vigencia_fim, situacao, especialidade_nome, periodo_inicio, periodo_fim, 
        tipo, faixa_etaria_inicio, faixa_etaria_fim, quantidade_beneficiario, total_vagas, 
        vagas_disponiveis, seg, ter, qua, qui, sex, sab
    ) VALUES (
        :medico_id, :especialidade_id, :unidade_id, :data_agenda, :horario_inicio, :horario_fim, 
        :duracao, :procedimento, :criado_por, NOW(), 1, 1, :vigencia_inicio, :vigencia_fim, 
        1, :especialidade_nome, :periodo_inicio, :periodo_fim, :tipo, 
        :faixa_etaria_inicio, :faixa_etaria_fim, :quantidade_beneficiario, :total_vagas, 
        :vagas_disponiveis, :seg, :ter, :qua, :qui, :sex, :sab
    )";

    // Parâmetros principais
    $params = [
        ':medico_id' => $data['medico_id'],
        ':especialidade_id' => $data['especialidade_id'],
        ':unidade_id' => $data['unidade_id'],
        ':data_agenda' => $data['vigencia_inicio'],
        ':horario_inicio' => $data['periodo_inicio'],
        ':horario_fim' => $data['periodo_fim'],
        ':duracao' => $data['duracao'],
        ':procedimento' => $data['procedimento'],
        ':criado_por' => $_SESSION['user_id'],
        ':vigencia_inicio' => $data['vigencia_inicio'],
        ':vigencia_fim' => $data['vigencia_fim'],
        ':especialidade_nome' => $data['especialidade_nome'],
        ':periodo_inicio' => $data['periodo_inicio'],
        ':periodo_fim' => $data['periodo_fim'],
        ':tipo' => $data['tipo'],
        ':faixa_etaria_inicio' => $data['faixa_etaria_inicio'],
        ':faixa_etaria_fim' => $data['faixa_etaria_fim'],
        ':quantidade_beneficiario' => $data['quantidade_beneficiario'],
        ':total_vagas' => $data['total_vagas'],
        ':vagas_disponiveis' => $data['vagas_disponiveis']
    ];

    // Adiciona os dias da semana aos parâmetros
    $params = array_merge($params, [
        ':seg' => $diasSemana['seg'],
        ':ter' => $diasSemana['ter'],
        ':qua' => $diasSemana['qua'],
        ':qui' => $diasSemana['qui'],
        ':sex' => $diasSemana['sex'],
        ':sab' => $diasSemana['sab']
    ]);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Agenda salva com sucesso.']);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}