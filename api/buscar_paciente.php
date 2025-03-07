<?php
require '../includes/config.php';

header('Content-Type: application/json');

if (empty($_GET['cpf'])) {
    echo json_encode(['success' => false, 'message' => 'CPF não fornecido.']);
    exit;
}

$cpf = $_GET['cpf'];

try {
    // Remove caracteres não numéricos do CPF
    $cpf = preg_replace('/\D/', '', $cpf);

    // Busca o paciente pelo CPF (sem pontuação) incluindo telefone e email
    $stmt = $pdo->prepare("SELECT id, nome_completo, telefone, email FROM pacientes WHERE REPLACE(REPLACE(cpf, '.', ''), '-', '') = :cpf");
    $stmt->execute([':cpf' => $cpf]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($paciente) {
        echo json_encode(['success' => true, 'paciente' => $paciente]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Paciente não encontrado.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar paciente: ' . $e->getMessage()]);
}
?>