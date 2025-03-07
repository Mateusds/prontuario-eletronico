<?php
session_start();
require '../../includes/config.php';

// Verificar se o ID da agenda foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: configurar_agenda.php');
    exit();
}

$agenda_id = $_GET['id'];

// Buscar os dados da agenda no banco de dados
$stmt = $pdo->prepare("SELECT id, data_agenda AS data_inicio, data_agenda AS data_fim, horario_inicio, horario_fim FROM agendas WHERE id = ?");
$stmt->execute([$agenda_id]);
$agenda = $stmt->fetch();

// Verificar se a agenda existe
if (!$agenda) {
    header('Location: configurar_agenda.php');
    exit();
}

// Comente ou remova a linha abaixo para não exibir o array na tela
// var_dump($agenda);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Agenda</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <main class="content">
            <button class="btn-sair" onclick="sair()">
                <i class="fas fa-sign-out-alt"></i> Sair
            </button>
            <h1>Editar Agenda</h1>
            
            <div class="configuracao-form">
                <form id="form-editar-agenda">
                    <input type="hidden" name="id" value="<?php echo $agenda['id']; ?>">
                    
                    <div class="form-group">
                        <label for="data_inicio">Data:</label>
                        <input type="date" id="data_inicio" name="data_inicio" value="<?php echo $agenda['data_inicio']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="horario_inicio">Horário Início:</label>
                        <input type="time" id="horario_inicio" name="horario_inicio" value="<?php echo substr($agenda['horario_inicio'], 0, 5); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="horario_fim">Horário Fim:</label>
                        <input type="time" id="horario_fim" name="horario_fim" value="<?php echo substr($agenda['horario_fim'], 0, 5); ?>" required>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-primary" onclick="salvarEdicao()">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <button type="button" class="btn-secondary" onclick="fecharJanela()">
                            <i class="fas fa-times"></i> Fechar
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function salvarEdicao() {
            const formData = new FormData(document.getElementById('form-editar-agenda'));

            fetch('../../api/editar_agenda.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log(response); // Verifique a resposta completa
                return response.text(); // Use .text() para ver o conteúdo bruto
            })
            .then(text => {
                console.log(text); // Exibe o conteúdo bruto da resposta
                try {
                    return JSON.parse(text); // Tenta converter para JSON
                } catch (error) {
                    throw new Error('Resposta inválida da API: ' + text);
                }
            })
            .then(data => {
                if (data.success) {
                    Swal.fire('Sucesso', 'Agenda atualizada com sucesso!', 'success').then(() => {
                        window.opener.carregarAgendas(); // Atualiza a lista na janela principal
                        window.close(); // Fecha a janela de edição
                    });
                } else {
                    throw new Error(data.message || 'Erro ao atualizar agenda');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro', error.message, 'error');
            });
        }

        function fecharJanela() {
            window.close();
        }

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
                    window.location.href = '../../includes/logout.php';
                }
            });
        }
    </script>
</body>
</html> 