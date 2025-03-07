<?php
require '../includes/config.php';
header('Content-Type: application/json');

try {
    // Verificar se a conexão com o banco de dados está funcionando
    if (!$pdo) {
        throw new Exception("Erro na conexão com o banco de dados.");
    }

    // Consulta para listar especialidades que possuem agendas disponíveis
    $query = "SELECT DISTINCT e.id, e.nome 
              FROM especialidades e
              INNER JOIN agendas a ON e.id = a.especialidade_id
              WHERE a.vigencia_inicio <= NOW() AND a.vigencia_fim >= NOW()";
    $stmt = $pdo->prepare($query);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar a consulta no banco de dados.");
    }

    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($especialidades)) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => 'Nenhuma especialidade com agendas disponíveis encontrada.'
        ]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'data' => $especialidades
    ]);
} catch (Exception $e) {
    // Log do erro no servidor
    error_log("Erro em listar_especialidades.php: " . $e->getMessage());
    
    // Retornar mensagem de erro detalhada
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao listar especialidades',
        'details' => $e->getMessage()
    ]);
} 