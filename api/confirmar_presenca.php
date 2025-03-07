<?php
require '../includes/config.php';

header('Content-Type: application/json');

$dados = json_decode(file_get_contents('php://input'), true);

if (empty($dados['numero_guia'])) {
    echo json_encode(['success' => false, 'message' => 'Número da guia não fornecido']);
    exit;
}

try {
    // Atualiza o status da guia para 'confirmada'
    $stmt = $pdo->prepare("UPDATE guias SET status = 'confirmada' WHERE numero_guia = :numero_guia");
    $stmt->execute([':numero_guia' => $dados['numero_guia']]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao confirmar presença: ' . $e->getMessage()]);
}
?> 