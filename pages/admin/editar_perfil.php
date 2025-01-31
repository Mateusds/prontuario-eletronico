<?php
session_start();
require '../../includes/config.php';

// Verificar se é administrador
if ($_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Buscar informações do usuário
$usuario_id = $_GET['id'];
$usuario = $pdo->query("SELECT * FROM usuarios WHERE id = $usuario_id")->fetch();

// Lista de telas disponíveis
$telas = [
    'configuracao_clinica.php' => 'Configuração da Clínica',
    'gerenciar_usuarios.php' => 'Gerenciar Usuários',
    'relatorios.php' => 'Relatórios',
    '../medico/agenda.php' => 'Agenda Médica',
    '../atendente/agenda.php' => 'Agenda do Atendente',
    '../paciente/consultas.php' => 'Consultas de Pacientes',
    '../medico/painel.php' => 'Painel de Chamadas',
    '../medico/atendimento.php' => 'Atendimento Médico',
    '../medico/finalizar_consulta.php' => 'Finalizar Consulta',
    '../paciente/prescricao.php' => 'Prescrições',
    '../paciente/atestado.php' => 'Atestados',
    '../paciente/encaminhamento.php' => 'Encaminhamentos',
    '../paciente/medicacao.php' => 'Medicações'
];

// Processar atualização do perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_usuario = $_POST['tipo_usuario'];
    
    try {
        // Atualizar o tipo de usuário
        $stmt = $pdo->prepare("UPDATE usuarios SET tipo = ? WHERE id = ?");
        $stmt->execute([$tipo_usuario, $usuario_id]);
        
        // Atualizar permissões de telas
        if (isset($_POST['telas'])) {
            // Remover permissões antigas
            $pdo->prepare("DELETE FROM permissoes_telas WHERE usuario_id = ?")->execute([$usuario_id]);
            
            // Inserir novas permissões
            $stmt = $pdo->prepare("INSERT INTO permissoes_telas (usuario_id, tela) VALUES (?, ?)");
            foreach ($_POST['telas'] as $tela) {
                $stmt->execute([$usuario_id, $tela]);
            }
        }
        
        $_SESSION['success'] = "Perfil atualizado com sucesso!";
        header('Location: gerenciar_usuarios.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao atualizar perfil: " . $e->getMessage();
    }
}

// Buscar telas permitidas para o usuário
$telas_permitidas = $pdo->query("SELECT tela FROM permissoes_telas WHERE usuario_id = $usuario_id")
    ->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <h2>Menu Admin</h2>
            <ul>
                <li><a href="configuracao_clinica.php">Configuração</a></li>
                <li><a href="gerenciar_usuarios.php">Gerenciar Usuários</a></li>
            </ul>
        </aside>

        <main class="content">
            <h1>Editar Perfil: <?= $usuario['nome'] ?></h1>
            <a href="gerenciar_usuarios.php" class="btn-voltar">Voltar</a>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>Tipo de Usuário:</label>
                    <select name="tipo_usuario" required>
                        <option value="admin" <?= ($usuario['tipo'] ?? '') == 'admin' ? 'selected' : '' ?>>Administrador</option>
                        <option value="medico" <?= ($usuario['tipo'] ?? '') == 'medico' ? 'selected' : '' ?>>Médico</option>
                        <option value="atendente" <?= ($usuario['tipo'] ?? '') == 'atendente' ? 'selected' : '' ?>>Atendente</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Telas Permitidas:</label>
                    <?php foreach ($telas as $arquivo => $nome): ?>
                        <label>
                            <input type="checkbox" name="telas[]" value="<?= $arquivo ?>"
                                <?= in_array($arquivo, $telas_permitidas) ? 'checked' : '' ?>>
                            <?= $nome ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
                
                <div class="button-group" style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn-salvar">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>
</html> 