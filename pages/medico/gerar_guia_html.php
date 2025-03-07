<?php
require '../../includes/config.php';

// Verifica se o número da guia foi passado
if (!isset($_GET['numero_guia'])) {
    die('Número da guia não fornecido.');
}

// Ajusta o número da guia para 18 caracteres
$numeroGuia = substr($_GET['numero_guia'], 0, 18);

try {
    // Adicionando depuração
    error_log("Tentando buscar guia: " . $numeroGuia);
    
    // Busca os dados da guia no banco de dados
    $stmt = $pdo->prepare("
        SELECT g.*, p.nome_completo AS paciente_nome, p.cpf, p.data_nascimento, p.telefone, p.email, 
               e.nome AS especialidade_nome, m.nome AS medico_nome
        FROM guias g
        JOIN pacientes p ON g.paciente_id = p.id
        JOIN especialidades e ON g.especialidade = e.id
        JOIN medicos m ON g.medico_responsavel = m.id
        WHERE g.numero_guia = :numeroGuia
    ");
    $stmt->execute([':numeroGuia' => $numeroGuia]);
    $guia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$guia) {
        // Adicionando mais informações de depuração
        error_log("Guia não encontrada: " . $numeroGuia);
        die('Guia não encontrada. Número: ' . $numeroGuia);
    }
} catch (PDOException $e) {
    die('Erro ao buscar guia: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Guia de Autorização</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .guia { width: 100%; max-width: 800px; margin: 0 auto; border: 1px solid #000; padding: 20px; }
        .guia h1 { text-align: center; }
        .guia .info { margin-bottom: 20px; }
        .guia .info p { margin: 5px 0; }
        .guia .assinaturas { margin-top: 20px; display: flex; justify-content: space-between; }
        
        /* Estilos para impressão */
        @media print {
            body { margin: 0; padding: 0; }
            .guia { border: none; padding: 0; max-width: 100%; }
            button { display: none; }
            .guia h1 { margin-top: 0; }
            .guia .info { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="guia">
        <h1>LOGO DA CLÍNICA</h1>
        <div class="info">
            <p>Nome da Clínica e Endereço</p>
            <p>Endereço: Rua da Saúde, 123, Centro, Cidade - Estado - CEP</p>
            <p>Telefone: (XX) XXXXX-XXXX | E-mail: contato@clinica.com</p>
            <p>CNPJ: xx.xxx.xxx/xxxx | Inscrição Estadual: xxxxxxx</p>
            <p>Registro CRM: 12345</p>
        </div>
        <hr>
        <div class="info">
            <p>Número da Guia: <?= substr($guia['numero_guia'], 4) ?> | Data de Emissão: <?= date('d/m/Y', strtotime($guia['data_emissao'])) ?></p>
        </div>
        <hr>
        <div class="info">
            <p>Paciente: <?= $guia['paciente_nome'] ?></p>
            <p>CPF: <?= $guia['cpf'] ?></p>
            <p>Data de Nascimento: <?= date('d/m/Y', strtotime($guia['data_nascimento'])) ?></p>
            <p>Telefone: <?= $guia['telefone'] ?></p>
            <p>E-mail: <?= $guia['email'] ?></p>
        </div>
        <hr>
        <div class="info">
            <p>Tipo de Atendimento: <?= $guia['tipo_atendimento'] ?></p>
            <p>Especialidade: <?= $guia['especialidade_nome'] ?></p>
            <p>Médico Responsável: <?= $guia['medico_nome'] ?></p>
            <p>CRM do Médico: <?= $guia['crm_medico'] ?></p>
            <p>Data da Consulta: <?= date('d/m/Y', strtotime($guia['data_consulta'])) ?></p>
            <p>Hora da Consulta: <?= $guia['hora_consulta'] ?></p>
            <p>Procedimento Autorizado: <?= $guia['procedimento_autorizado'] ?></p>
            <p>Código do Procedimento: <?= $guia['codigo_procedimento'] ?></p>
            <p>Motivo da Consulta: <?= $guia['motivo_consulta'] ?></p>
        </div>
        <hr>
        <div class="info">
            <p>Nome do Plano de Saúde: <?= isset($guia['plano_saude']) ? $guia['plano_saude'] : 'Não informado' ?></p>
            <p>Número da Carteirinha: <?= isset($guia['numero_carteirinha']) ? $guia['numero_carteirinha'] : 'Não informado' ?></p>
            <p>Validade da Carteirinha: 01/12/2025</p>
            <p>Autorização do Plano: Confirmada</p>
        </div>
        <hr>
        <div class="info">
            <p>Termos e Condições: A autorização não garante a cobertura integral do procedimento, dependendo da análise do plano.</p>
            <p>Observações: Caso haja alteração na data da consulta, informe à clínica com antecedência.</p>
        </div>
        <hr>
        <div class="assinaturas">
            <p>Assinatura do Médico: ___________________________</p>
            <p>Assinatura do Paciente: ___________________________</p>
        </div>
    </div>

    <!-- Botões de impressão e fechar -->
    <div style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-times"></i> Fechar
        </button>
    </div>
</body>
</html> 