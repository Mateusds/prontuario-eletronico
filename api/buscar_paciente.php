<?php
require '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_GET['cpf'])) {
    echo json_encode(['error' => 'CPF não fornecido']);
    exit;
}

$cpf = $_GET['cpf'];

$stmt = $pdo->prepare("SELECT * FROM pacientes WHERE cpf = ?");
$stmt->execute([$cpf]);
$paciente = $stmt->fetch();

if (!$paciente) {
    echo json_encode(['error' => 'Paciente não encontrado']);
    exit;
}

echo json_encode([
    'id' => $paciente['id'],
    'nome_completo' => $paciente['nome_completo'],
    'telefone' => $paciente['telefone'],
    'email' => $paciente['email'],
    'layout' => [
        'modal_style' => 'max-width: 600px; margin: 1.75rem auto; border-radius: 0.5rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);',
        'button_style' => 'width: 120px; padding: 0.5rem 1rem; border-radius: 0.25rem; font-size: 0.9rem; transition: all 0.3s ease;',
        'button_hover' => 'transform: translateY(-1px); box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);'
    ]
]); 