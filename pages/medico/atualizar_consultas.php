<?php
session_start();
require '../../includes/config.php';

try {
    $query = "SELECT c.id, 
                     DATE_FORMAT(c.data_consulta, '%d/%m/%Y %H:%i') AS data_consulta_formatada,
                     p.nome_completo AS paciente_nome, 
                     m.nome AS medico_nome, 
                     c.status,
                     e.nome AS especialidade_nome
              FROM consultas c
              JOIN pacientes p ON c.paciente_id = p.id
              LEFT JOIN medicos m ON c.medico_id = m.id
              LEFT JOIN agendas a ON c.agenda_id = a.id
              LEFT JOIN especialidades e ON a.especialidade_id = e.id
              ORDER BY c.data_consulta DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #666;">Erro ao carregar consultas</td></tr>';
    exit();
}

if (empty($consultas)) {
    echo '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #666;">Nenhuma consulta marcada</td></tr>';
} else {
    foreach ($consultas as $consulta) {
        echo '<tr>
                <td>'.$consulta['data_consulta_formatada'].'</td>
                <td>'.htmlspecialchars($consulta['paciente_nome']).'</td>
                <td>'.htmlspecialchars($consulta['medico_nome'] ?? 'Médico não informado').'</td>
                <td>'.htmlspecialchars($consulta['especialidade_nome'] ?? 'Não informada').'</td>
                <td>
                    <span class="status-badge '.$consulta['status'].'">
                        '.ucfirst($consulta['status']).'
                    </span>
                </td>
              </tr>';
    }
}
?> 