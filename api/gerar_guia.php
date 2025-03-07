<?php
require '../includes/config.php';

header('Content-Type: application/json');

// Debug temporário
error_log('Dados recebidos: ' . print_r($_POST, true));
error_log('Dados brutos: ' . file_get_contents('php://input'));

// Define o fuso horário correto (exemplo para Brasília)
date_default_timezone_set('America/Sao_Paulo');

// Verifica se todos os campos necessários foram enviados
$requiredFields = [
    'paciente_id', 'tipo_atendimento', 'especialidade', 
    'medico_responsavel', 'crm_medico', 'data_consulta', 
    'hora_consulta', 'codigo_procedimento', 'data_emissao'
];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Campo obrigatório faltando: $field"]);
        exit;
    }
}

// Função para gerar o número da guia
function gerarNumeroGuia() {
    $prefixo = 'GUIA';
    $dataHora = date('YmdHis'); // Agora usará o fuso horário correto
    return substr($prefixo . $dataHora, 0, 18); // Garante 18 caracteres
}

try {
    // Gera o número da guia
    $numeroGuia = gerarNumeroGuia();

    // Verifica se a guia está sendo gerada manualmente ou automaticamente
    $status = isset($_POST['geracao_manual']) && $_POST['geracao_manual'] === 'true' ? 2 : 1; // 2 = Liberada, 1 = Aguardando liberação

    // Logs para depuração
    error_log("Geracao manual: " . ($_POST['geracao_manual'] ?? 'false'));
    error_log("Status definido: " . $status);

    // No momento de inserir a guia no banco de dados
    $dataEmissao = date('Y-m-d H:i:s'); // Usa o fuso horário correto

    // Prepara a query para inserir a guia
    $stmt = $pdo->prepare("
        INSERT INTO guias (
            paciente_id, numero_guia, data_emissao, tipo_atendimento, 
            especialidade, medico_responsavel, crm_medico, data_consulta, 
            hora_consulta, codigo_procedimento, motivo_consulta, status, situacao
        ) VALUES (
            :paciente_id, :numero_guia, :data_emissao, :tipo_atendimento, 
            :especialidade, :medico_responsavel, :crm_medico, :data_consulta, 
            :hora_consulta, :codigo_procedimento, :motivo_consulta, :status, 1
        )
    ");

    // Executa a query com os dados do formulário
    $stmt->execute([
        ':paciente_id' => $_POST['paciente_id'],
        ':numero_guia' => $numeroGuia,
        ':data_emissao' => $dataEmissao,
        ':tipo_atendimento' => $_POST['tipo_atendimento'],
        ':especialidade' => $_POST['especialidade'],
        ':medico_responsavel' => $_POST['medico_responsavel'],
        ':crm_medico' => $_POST['crm_medico'],
        ':data_consulta' => $_POST['data_consulta'],
        ':hora_consulta' => $_POST['hora_consulta'],
        ':codigo_procedimento' => $_POST['codigo_procedimento'],
        ':motivo_consulta' => $_POST['motivo_consulta'] ?? null,
        ':status' => $status // Usa o ID correto do status
    ]);

    // Retorna sucesso
    echo json_encode([
        'success' => true,
        'numero_guia' => $numeroGuia,
        'message' => 'Guia gerada com sucesso!'
    ]);
} catch (PDOException $e) {
    // Retorna erro caso ocorra uma exceção
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao gerar guia: ' . $e->getMessage()
    ]);
}
?> 