<?php
header('Content-Type: application/json');
require '../includes/config.php';

$especialidade_id = $_GET['especialidade_id'] ?? null;

if (!$especialidade_id) {
    echo json_encode(['success' => false, 'message' => 'ID da especialidade não fornecido']);
    exit();
}

try {
    // Consulta para verificar vagas disponíveis
    $query = "SELECT COUNT(*) as vagasDisponiveis 
              FROM agendas 
              WHERE especialidade_id = :especialidade_id 
              AND vagas_disponiveis > 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['especialidade_id' => $especialidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'vagasDisponiveis' => $result['vagasDisponiveis'] ?? 0
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao verificar vagas',
        'details' => $e->getMessage()
    ]);
}