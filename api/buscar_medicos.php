<?php
// Define o caminho absoluto para o config.php
$configPath = '../includes/config.php';

// Verifica se o arquivo existe
if (!file_exists($configPath)) {
    http_response_code(500);
    die(json_encode(['error' => 'Arquivo de configuração não encontrado em: ' . $configPath]));
}

require $configPath;

header('Content-Type: application/json');

try {
    // Verifica se o parâmetro foi enviado
    if (!isset($_GET['especialidade_id']) || empty($_GET['especialidade_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Parâmetro especialidade_id é obrigatório']);
        exit();
    }

    $especialidade_id = intval($_GET['especialidade_id']);

    // Prepara e executa a consulta
    $stmt = $pdo->prepare("SELECT id, nome FROM medicos WHERE especialidade_id = ?");
    $stmt->execute([$especialidade_id]);
    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verifica se encontrou resultados
    if (empty($medicos)) {
        echo json_encode([]);
    } else {
        echo json_encode($medicos);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro inesperado: ' . $e->getMessage()]);
}
?> 