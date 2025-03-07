<?php
require '../../includes/config.php';
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Verificar se o ID do médico foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: cadastro_medico.php');
    exit();
}

$medico_id = $_GET['id'];

// Buscar dados do médico
try {
    $stmt = $pdo->prepare("
        SELECT m.id, m.nome, m.crm, m.especialidade_id, m.unidade_id, m.situacao 
        FROM medicos m 
        WHERE m.id = ?
    ");
    $stmt->execute([$medico_id]);
    $medico = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$medico) {
        $_SESSION['error'] = "Médico não encontrado.";
        header('Location: cadastro_medico.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erro ao buscar dados do médico: " . $e->getMessage();
    header('Location: cadastro_medico.php');
    exit();
}

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validação dos dados
    $nome = trim($_POST['nome']);
    $crm = trim($_POST['crm']);
    $especialidade_id = $_POST['especialidade'];
    $unidade_id = $_POST['unidade'];
    $situacao = isset($_POST['situacao']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("
            UPDATE medicos 
            SET nome = ?, crm = ?, especialidade_id = ?, unidade_id = ?, situacao = ?
            WHERE id = ?
        ");
        $stmt->execute([$nome, $crm, $especialidade_id, $unidade_id, $situacao, $medico_id]);
        
        $_SESSION['success'] = "Médico atualizado com sucesso!";
        header('Location: cadastro_medico.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao atualizar médico: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Médico</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/cadastro_medico.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        
        <main class="content">
            <h1>Editar Médico</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="cadastro-medico-container">
                <form method="post" class="cadastro-medico-form">
                    <div class="form-columns">
                        <div class="form-group">
                            <label for="nome">Nome Completo:</label>
                            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($medico['nome']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="crm">CRM:</label>
                            <input type="text" id="crm" name="crm" value="<?= htmlspecialchars($medico['crm']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="especialidade">Especialidade:</label>
                            <select id="especialidade" name="especialidade" required>
                                <option value="">Selecione a especialidade</option>
                                <?php
                                $stmt = $pdo->query("SELECT id, nome FROM especialidades");
                                while ($especialidade = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($especialidade['id'] == $medico['especialidade_id']) ? 'selected' : '';
                                    echo "<option value='{$especialidade['id']}' $selected>{$especialidade['nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="unidade">Unidade:</label>
                            <select id="unidade" name="unidade" required>
                                <option value="">Selecione a unidade</option>
                                <?php
                                $stmt = $pdo->query("SELECT id, nome FROM clinicas");
                                while ($unidade = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($unidade['id'] == $medico['unidade_id']) ? 'selected' : '';
                                    echo "<option value='{$unidade['id']}' $selected>{$unidade['nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="situacao">Situação:</label>
                            <label class="switch">
                                <input type="checkbox" id="situacao" name="situacao" <?= $medico['situacao'] == 1 ? 'checked' : '' ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn-cadastrar">Salvar Alterações</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 