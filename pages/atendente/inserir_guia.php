<?php

$data_emissao = date('Y-m-d H:i:s'); // Captura a data e horário atuais
$stmt = $pdo->prepare("INSERT INTO guias (numero_guia, paciente_id, data_emissao, tipo_atendimento) VALUES (?, ?, ?, ?)");
$stmt->execute([$numero_guia, $paciente_id, $data_emissao, $tipo_atendimento]); 