<?php
require '../includes/config.php';
header('Content-Type: application/json');

try {
    // Verificar se o especialidade_id foi passado
    if (!isset($_GET['especialidade_id'])) {
        throw new Exception('Especialidade não informada');
    }

    $especialidade_id = intval($_GET['especialidade_id']);

    // Query para buscar médicos da especialidade
    $query = "SELECT 
                m.id,
                m.nome
              FROM medicos m
              INNER JOIN medico_especialidade me ON m.id = me.medico_id
              WHERE me.especialidade_id = :especialidade_id
              AND m.ativo = 1
              ORDER BY m.nome ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['especialidade_id' => $especialidade_id]);
    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($medicos)) {
        throw new Exception('Nenhum médico encontrado para esta especialidade');
    }

    echo json_encode([
        'success' => true,
        'data' => $medicos
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}