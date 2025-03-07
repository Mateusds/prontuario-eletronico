<?php
session_start();
require_once '../../includes/db.php'; // Conexão com o banco de dados

$response = ['success' => false, 'message' => 'Erro ao processar a requisição.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medico = $_POST['medico'];
    $vigenciaDe = $_POST['vigencia'];
    $vigenciaAte = $_POST['vigenciaate'];
    $horarioInicio = $_POST['horainicio'];
    $horarioFim = $_POST['horafinal'];
    $dias = implode(',', $_POST['dias'] ?? []);
    $tipo = $_POST['tipo'];
    $idadeInicial = $_POST['idade_inicial'];
    $idadeFinal = $_POST['idade_final'];
    $quantidade = $_POST['quantatendimento'];
    $quantidadeExtra = $_POST['quantatendimentoextra'];
    $especialidade = $_POST['espcialidade'];
    $procedimento = $_POST['procedimento'];

    // Insere os dados no banco de dados
    $sql = "INSERT INTO agendas (medico, vigencia_de, vigencia_ate, horario_inicio, horario_fim, dias, tipo, idade_inicial, idade_final, quantidade, quantidade_extra, especialidade, procedimento)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$medico, $vigenciaDe, $vigenciaAte, $horarioInicio, $horarioFim, $dias, $tipo, $idadeInicial, $idadeFinal, $quantidade, $quantidadeExtra, $especialidade, $procedimento])) {
        $response['success'] = true;
        $response['message'] = 'Agenda criada com sucesso.';
    } else {
        $response['message'] = 'Erro ao inserir no banco de dados.';
    }
}

echo json_encode($response); 