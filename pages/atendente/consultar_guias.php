<?php
date_default_timezone_set('America/Sao_Paulo'); // Ajuste para o fuso horário correto
session_start();
require '../../includes/config.php';

// Verifica se o usuário está logado e é médico ou admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin')) {
    header('Location: ../login.php');
    exit;
}

// Buscar guias do banco de dados
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

include '../../includes/header.php';
include '../../includes/menu_lateral.php';
?>

<link rel="stylesheet" href="../../assets/css/consultar_guias.css">
<link rel="stylesheet" href="../../assets/css/global.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<head>
    <meta charset="UTF-8">
    <title>Consultar Guias Emitidas</title>
    <style>
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

<main class="content">
    <!-- Botão de Sair -->
    <div class="logout-button">
        <form id="logoutForm" action="../../pages/logout.php" method="post">
            <button type="submit" class="btn-sair">
                <i class="fas fa-sign-out-alt"></i> Sair
            </button>
        </form>
    </div>
    
    <h1>Consultar Guias Emitidas</h1>
    <div class="card">
        <div class="card-header">
            <h3>Lista de Guias</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Número da Guia</th>
                        <th>Paciente</th>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($guias) > 0): ?>
                        <?php foreach ($guias as $guia): ?>
                            <tr>
                                <td><?= htmlspecialchars(substr($guia['numero_guia'], 4)) ?></td>
                                <td><?= htmlspecialchars($guia['paciente']) ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($guia['data_emissao'])) ?></td>
                                <td><?= htmlspecialchars($guia['tipo_atendimento']) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button onclick="abrirGuia('<?= $guia['numero_guia'] ?>')" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Visualizar
                                        </button>
                                        <button onclick="excluirGuia(<?= $guia['id'] ?>)" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="5" class="text-center fw-bold">
                                Total de guias: <span class="badge bg-primary"><?= count($guias) ?></span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhuma guia encontrada</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Função para carregar as guias
function carregarGuias() {
    $.ajax({
        url: '../../api/buscar_guias.php',
        method: 'GET',
        success: function(response) {
            // Atualiza a tabela com as novas guias
            $('table tbody').html(response);
        },
        error: function(xhr) {
            console.error('Erro ao carregar guias:', xhr);
        }
    });
}

// Atualiza as guias a cada 3 segundos
setInterval(carregarGuias, 3000);

// Carrega as guias imediatamente ao carregar a página
$(document).ready(function() {
    carregarGuias();
});

function abrirGuia(numeroGuia) {
    const url = `../../pages/medico/gerar_guia_html.php?numero_guia=${numeroGuia}`;
    const width = 800;
    const height = 600;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
    
    console.log('URL da guia:', url);
    
    const novaJanela = window.open(url, 'Guia', `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`);
    
    if (!novaJanela || novaJanela.closed || typeof novaJanela.closed === 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Não foi possível abrir a guia. Verifique se o bloqueador de pop-ups está desativado.'
        });
    } else {
        // Verifica se a guia foi carregada corretamente
        novaJanela.onload = function() {
            if (novaJanela.document.body.innerText.includes("Guia não encontrada")) {
                novaJanela.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Guia não encontrada. Verifique o número da guia.'
                });
            }
        };
    }
}

function excluirGuia(guiaId) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Você não poderá reverter isso!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../../actions/inativar_guia.php',
                method: 'POST',
                data: { id: guiaId },
                success: function(response) {
                    console.log('Resposta do servidor:', response);
                    if (response === 'success') {
                        // Remove a linha da tabela
                        $(`tr:has(button[onclick*="excluirGuia(${guiaId})"])`).fadeOut(300, function() {
                            $(this).remove();
                        });
                        Swal.fire(
                            'Excluído!',
                            'A guia foi excluída com sucesso.',
                            'success'
                        );
                    } else {
                        Swal.fire(
                            'Erro!',
                            response,
                            'error'
                        );
                    }
                },
                error: function(xhr) {
                    console.error('Erro na requisição:', xhr);
                    Swal.fire(
                        'Erro!',
                        'Erro na requisição: ' + xhr.statusText,
                        'error'
                    );
                }
            });
        }
    });
}
</script>

<?php include '../../includes/footer.php'; ?> 