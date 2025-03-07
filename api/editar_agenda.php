<?php
header('Content-Type: application/json');

// Caminho absoluto para o arquivo de configuração
$configPath = '../includes/config.php';

if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Arquivo de configuração não encontrado em: ' . $configPath]);
    exit;
}

require $configPath;

// Verifique se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receba os dados do formulário
    $id = $_POST['id'];
    $data_inicio = $_POST['data_inicio'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];

    // Atualize os dados no banco de dados
    try {
        $stmt = $pdo->prepare("UPDATE agendas SET 
            data_agenda = :data_inicio, 
            horario_inicio = :horario_inicio, 
            horario_fim = :horario_fim 
            WHERE id = :id");
        $stmt->execute([
            ':data_inicio' => $data_inicio,
            ':horario_inicio' => $horario_inicio,
            ':horario_fim' => $horario_fim,
            ':id' => $id
        ]);

        // Verifique se a atualização foi bem-sucedida
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Agenda atualizada com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nenhuma agenda foi atualizada']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar agenda: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido']);
}
?> 