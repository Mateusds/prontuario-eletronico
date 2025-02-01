<?php
require '../../includes/config.php';
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica se o campo nome está preenchido
    if (!isset($_POST['nome']) || empty(trim($_POST['nome']))) {
        $_SESSION['error'] = "O campo Nome deve ser preenchido.";
        header('Location: cadastro_medico.php');
        exit();
    }

    // Coleta os dados do formulário
    $nome = trim($_POST['nome']);
    $crm = $_POST['crm'];
    $especialidade_id = $_POST['especialidade'];
    $unidade_id = $_POST['unidade'];
    $situacao = 1; // Novo médico sempre cadastrado como ativo

    try {
        $stmt = $pdo->prepare("INSERT INTO medicos (nome, crm, especialidade_id, unidade_id, situacao) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $crm, $especialidade_id, $unidade_id, $situacao]);
        $_SESSION['success'] = "Médico cadastrado com sucesso!";
        header('Location: cadastro_medico.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao cadastrar médico: " . $e->getMessage();
        header('Location: cadastro_medico.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Médico</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/cadastro_medico.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#nome').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            $('.toggle-status').change(function() {
                var toggle = $(this); // Armazena a referência do botão
                var medicoId = toggle.data('id');
                var situacao = toggle.is(':checked') ? 1 : 0;

                $.ajax({
                    url: 'atualizar_status.php',
                    method: 'POST',
                    data: { id: medicoId, situacao: situacao },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            alert(response.message || 'Status atualizado com sucesso!');
                            // Não recarrega a página, apenas atualiza o estado do botão
                        } else {
                            alert(response.message || 'Erro ao atualizar status');
                            toggle.prop('checked', !toggle.prop('checked')); // Reverte o estado do botão em caso de erro
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro na requisição: ' + error);
                        toggle.prop('checked', !toggle.prop('checked')); // Reverte o estado do botão em caso de erro
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        
        <main class="content">
            <h1>Cadastro de Médico</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success" style="text-align: center; margin: 20px auto;"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="cadastro-medico-container">
                <form method="post" class="cadastro-medico-form">
                    <div class="form-columns">
                        <div class="form-group">
                            <label for="nome">Nome Completo:</label>
                            <input type="text" id="nome" name="nome" required style="width: 300px; height: 10px;">
                        </div>
                        
                        <div class="form-group" style="display: flex; gap: 10px; align-items: flex-end;">
                            <div>
                                <label for="crm">CRM:</label>
                                <input type="text" id="crm" name="crm" required style="width: 100px; height: 10px;">
                            </div>
                            <div>
                                <label for="uf" style="display: block; margin-bottom: 5px;">UF:</label>
                                <select id="uf" name="uf" required style="width: 120px;">
                                    <option value="">Selecione</option>
                                    <option value="AC">AC</option>
                                    <option value="AL">AL</option>
                                    <option value="AP">AP</option>
                                    <option value="AM">AM</option>
                                    <option value="BA">BA</option>
                                    <option value="CE">CE</option>
                                    <option value="DF">DF</option>
                                    <option value="ES">ES</option>
                                    <option value="GO">GO</option>
                                    <option value="MA">MA</option>
                                    <option value="MT">MT</option>
                                    <option value="MS">MS</option>
                                    <option value="MG">MG</option>
                                    <option value="PA">PA</option>
                                    <option value="PB">PB</option>
                                    <option value="PR">PR</option>
                                    <option value="PE">PE</option>
                                    <option value="PI">PI</option>
                                    <option value="RJ">RJ</option>
                                    <option value="RN">RN</option>
                                    <option value="RS">RS</option>
                                    <option value="RO">RO</option>
                                    <option value="RR">RR</option>
                                    <option value="SC">SC</option>
                                    <option value="SP">SP</option>
                                    <option value="SE">SE</option>
                                    <option value="TO">TO</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="especialidade">Especialidade:</label>
                            <select id="especialidade" name="especialidade" required style="width: 300px; margin-top: 5px;">
                                <option value="">Selecione a especialidade</option>
                                <?php
                                try {
                                    // Busca as especialidades diretamente do banco
                                    $stmt = $pdo->query("SELECT id, nome FROM especialidades");
                                    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (count($especialidades) > 0) {
                                        foreach ($especialidades as $especialidade) {
                                            echo "<option value='{$especialidade['id']}'>{$especialidade['nome']}</option>";
                                        }
                                    } else {
                                        echo "<option value=''>Nenhuma especialidade cadastrada</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Erro ao carregar especialidades</option>";
                                    error_log("Erro ao buscar especialidades: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="unidade">Unidade:</label>
                            <select id="unidade" name="unidade" required style="width: 300px; margin-top: 5px;">
                                <option value="">Selecione a unidade</option>
                                <?php
                                try {
                                    // Busca as unidades diretamente do banco
                                    $stmt = $pdo->query("SELECT id, nome FROM clinicas");
                                    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (count($unidades) > 0) {
                                        foreach ($unidades as $unidade) {
                                            echo "<option value='{$unidade['id']}'>{$unidade['nome']}</option>";
                                        }
                                    } else {
                                        echo "<option value=''>Nenhuma unidade cadastrada</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option value=''>Erro ao carregar unidades</option>";
                                    error_log("Erro ao buscar unidades: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-cadastrar">Cadastrar</button>
                </form>
            </div>

            <!-- Lista de Médicos Cadastrados -->
            <div class="lista-medicos">
                <h2 style="text-align: center;">Médicos Cadastrados</h2>
                <?php
                try {
                    $stmt = $pdo->query("
                        SELECT m.id, m.nome, m.crm, e.nome as especialidade, c.nome as unidade, 
                               CASE WHEN m.situacao = 1 THEN 'Ativo' ELSE 'Inativo' END as situacao
                        FROM medicos m
                        JOIN especialidades e ON m.especialidade_id = e.id
                        JOIN clinicas c ON m.unidade_id = c.id
                        ORDER BY m.nome ASC
                    ");
                    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($medicos) > 0) {
                        echo '<table class="tabela-medicos">';
                        echo '<thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>CRM</th>
                                    <th>Especialidade</th>
                                    <th>Unidade</th>
                                    <th>Situação</th>
                                </tr>
                              </thead>
                              <tbody>';
                        
                        foreach ($medicos as $medico) {
                            echo '<tr>
                                    <td>'.htmlspecialchars($medico['nome']).'</td>
                                    <td>'.$medico['crm'].'</td>
                                    <td>'.htmlspecialchars($medico['especialidade']).'</td>
                                    <td>'.htmlspecialchars($medico['unidade']).'</td>
                                    <td>
                                        <label class="switch">
                                            <input type="checkbox" class="toggle-status" data-id="'.$medico['id'].'" '.($medico['situacao'] == 1 ? 'checked' : '').'>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                  </tr>';
                        }
                        
                        echo '</tbody></table>';
                    } else {
                        echo '<p style="text-align: center;">Nenhum médico cadastrado ainda.</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p class="error" style="text-align: center;">Erro ao carregar lista de médicos: '.$e->getMessage().'</p>';
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html> 