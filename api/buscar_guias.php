<?php
require '../includes/config.php';

try {
    $stmt = $pdo->query("
        SELECT g.id, g.numero_guia, p.nome_completo AS paciente, g.data_emissao, g.tipo_atendimento 
        FROM guias g
        JOIN pacientes p ON g.paciente_id = p.id
        WHERE g.situacao = 1
        ORDER BY g.data_emissao DESC
    ");
    $guias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erro ao buscar guias: ' . $e->getMessage());
}

if (count($guias) > 0) {
    foreach ($guias as $guia) {
        echo '<tr>
            <td>'.htmlspecialchars(substr($guia['numero_guia'], 4)).'</td>
            <td>'.htmlspecialchars($guia['paciente']).'</td>
            <td>'.date('d/m/Y H:i:s', strtotime($guia['data_emissao'])).'</td>
            <td>'.htmlspecialchars($guia['tipo_atendimento']).'</td>
            <td>
                <div class="btn-group">
                    <button onclick="abrirGuia(\''.$guia['numero_guia'].'\')" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Visualizar
                    </button>
                    <button onclick="excluirGuia('.$guia['id'].')" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i> Excluir
                    </button>
                </div>
            </td>
        </tr>';
    }
    echo '<tr>
        <td colspan="5" class="text-center fw-bold">
            Total de guias: <span class="badge bg-primary">'.count($guias).'</span>
        </td>
    </tr>';
} else {
    echo '<tr>
        <td colspan="5" class="text-center">Nenhuma guia encontrada</td>
    </tr>';
} 