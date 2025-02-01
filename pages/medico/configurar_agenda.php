<?php
session_start();
require '../../includes/config.php';

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configurar Agenda Médica</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Configurar Agenda Médica</h1>
            
            <div class="configuracao-form">
                <form id="form-configuracao">
                    <div class="form-group">
                        <label for="unidade">Unidade:</label>
                        <select id="unidade" name="unidade" required>
                            <option value="">Selecione a unidade</option>
                            <?php
                            // Buscar clínicas ativas do banco de dados
                            $stmt = $pdo->query("SELECT * FROM clinicas WHERE situacao = 1");
                            while ($clinica = $stmt->fetch()) {
                                echo "<option value='{$clinica['id']}'>{$clinica['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="especialidade">Especialidade:</label>
                        <select id="especialidade" name="especialidade" required>
                            <option value="">Selecione a especialidade</option>
                            <?php
                            // Buscar especialidades do banco de dados
                            $stmt = $pdo->query("SELECT * FROM especialidades");
                            while ($especialidade = $stmt->fetch()) {
                                echo "<option value='{$especialidade['id']}'>{$especialidade['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="medico">Médico:</label>
                        <select id="medico" name="medico" required>
                            <option value="">Selecione o médico</option>
                            <?php
                            // Buscar médicos ativos do banco de dados
                            $stmt = $pdo->query("SELECT * FROM medicos WHERE situacao = 1");
                            if ($stmt->rowCount() > 0) {
                                while ($medico = $stmt->fetch()) {
                                    echo "<option value='{$medico['id']}'>{$medico['nome']}</option>";
                                }
                            } else {
                                echo "<option value=''>Nenhum médico disponível</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-primary" onclick="configurarAgenda()">
                            <i class="fas fa-cog"></i> Configurar Agenda
                        </button>
                        <button type="button" class="btn-secondary" onclick="limparFormulario()">
                            <i class="fas fa-eraser"></i> Limpar
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function configurarAgenda() {
            const unidade = document.getElementById('unidade').value;
            const especialidade = document.getElementById('especialidade').value;
            const medico = document.getElementById('medico').value;

            if (!unidade || !especialidade || !medico) {
                Swal.fire('Erro', 'Por favor, preencha todos os campos.', 'error');
                return;
            }

            // Implementar lógica de configuração da agenda
            Swal.fire('Sucesso', 'Agenda configurada com sucesso!', 'success');
        }

        function limparFormulario() {
            document.getElementById('form-configuracao').reset();
            Swal.fire('Limpo', 'Formulário limpo com sucesso!', 'info');
        }
    </script>
</body>
</html> 