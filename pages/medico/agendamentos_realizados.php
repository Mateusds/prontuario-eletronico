<?php
session_start();
require '../../includes/config.php';

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

try {
    // Buscar os agendamentos realizados
    $query = "SELECT c.id, p.nome_completo AS paciente, m.nome AS medico, 
                     DATE(c.data_consulta) AS data_consulta, 
                     TIME(c.data_consulta) AS horario, 
                     c.status 
              FROM consultas c
              JOIN pacientes p ON c.paciente_id = p.id
              JOIN medicos m ON c.medico_id = m.id
              ORDER BY c.data_consulta DESC";
    $stmt = $pdo->query($query);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar agendamentos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agendamentos Realizados</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .tabela-agendamentos {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 20px;
        }
        
        .tabela-agendamentos th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .tabela-agendamentos td {
            padding: 12px 15px;
            background-color: white;
            border-bottom: 1px solid #e9ecef;
        }
        
        .tabela-agendamentos tr {
            transition: all 0.2s ease;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .tabela-agendamentos tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-badge.agendada {
            background-color: #d1fae5; /* Verde claro */
            color: #065f46; /* Verde escuro */
        }
        
        .status-badge.cancelado {
            background-color: #fee2e2; /* Vermelho claro */
            color: #991b1b; /* Vermelho escuro */
        }
        
        .status-badge.finalizado {
            background-color: #dbeafe; /* Azul claro */
            color: #1e40af; /* Azul escuro */
        }

        .btn-sair {
            position: fixed;
            right: 20px;
            top: 20px;
            background-color: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            width: auto;
            z-index: 1000;
        }

        .btn-sair:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <button class="btn-sair" onclick="sair()">
            <i class="fas fa-sign-out-alt"></i> Sair
        </button>
        <main class="content">
            <h1>Agendamentos Realizados</h1>
            
            <div class="lista-agendamentos">
                <table class="tabela-agendamentos">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($agendamentos) > 0): ?>
                            <?php foreach ($agendamentos as $agendamento): ?>
                                <tr>
                                    <td><?= htmlspecialchars($agendamento['paciente']) ?></td>
                                    <td><?= htmlspecialchars($agendamento['medico']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($agendamento['data_consulta'])) ?></td>
                                    <td><?= substr($agendamento['horario'], 0, 5) ?></td>
                                    <td>
                                        <?php
                                        // Normaliza o status para minúsculas e substitui "concluida" por "finalizado"
                                        $status = strtolower($agendamento['status']);
                                        $status = str_replace('concluida', 'finalizado', $status);
                                        ?>
                                        <span class="status-badge <?= $status ?>">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Nenhum agendamento realizado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function sair() {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Você será desconectado do sistema!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, sair!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../../pages/logout.php';
                }
            });
        }
    </script>
</body>
</html> 