<?php
session_start();
require '../includes/config.php';

// Definir o cabeçalho para JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // Verificar se é médico ou admin
    if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
        throw new Exception('Acesso não autorizado', 403);
    }

    $sql = "SELECT 
        a.id, a.medico_id, a.especialidade_id, a.unidade_id, a.vigencia_inicio, a.vigencia_fim, 
        a.periodo_inicio, a.periodo_fim, a.duracao, a.procedimento, a.situacao, 
        a.seg, a.ter, a.qua, a.qui, a.sex, a.sab, a.vagas_disponiveis,
        e.nome AS especialidade_nome,
        CASE 
            WHEN a.periodo_inicio IS NULL OR a.periodo_fim IS NULL THEN 'Período não definido'
            ELSE CONCAT(a.periodo_inicio, ' - ', a.periodo_fim)
        END AS periodo_formatado
    FROM agendas a
    LEFT JOIN especialidades e ON a.especialidade_id = e.id";

    $stmt = $pdo->query($sql);
    $agendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Processar as agendas
    $agendasProcessadas = [];
    foreach ($agendas as $agenda) {
        // Mapeia os dias da semana
        $diasSemana = [];
        if ($agenda['seg'] == 1) $diasSemana[] = 'Seg';
        if ($agenda['ter'] == 1) $diasSemana[] = 'Ter';
        if ($agenda['qua'] == 1) $diasSemana[] = 'Qua';
        if ($agenda['qui'] == 1) $diasSemana[] = 'Qui';
        if ($agenda['sex'] == 1) $diasSemana[] = 'Sex';
        if ($agenda['sab'] == 1) $diasSemana[] = 'Sáb';

        $agenda['dias_semana'] = implode(', ', $diasSemana) ?: 'N/A';
        $agenda['periodo'] = $agenda['periodo_formatado'];
        $agendasProcessadas[] = $agenda;
    }

    echo json_encode(['success' => true, 'data' => $agendasProcessadas]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}