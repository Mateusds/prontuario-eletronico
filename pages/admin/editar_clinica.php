<?php
session_start();
require '../../includes/config.php';

// Verificar se é administrador
if ($_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Verificar se o ID da clínica foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID da clínica inválido.";
    header('Location: configuracao_clinica.php');
    exit();
}

$id = $_GET['id'];

// Recuperar os dados da clínica
try {
    $sql = "SELECT * FROM clinicas WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $clinica = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$clinica) {
        $_SESSION['error'] = "Clínica não encontrada.";
        header('Location: configuracao_clinica.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erro ao recuperar dados da clínica: " . $e->getMessage();
    header('Location: configuracao_clinica.php');
    exit();
}

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_clinica = $_POST['nome_clinica'] ?? '';
    $horario_abertura = $_POST['horario_abertura'] ?? '';
    $horario_fechamento = $_POST['horario_fechamento'] ?? '';

    // Processar horários por dia
    $dias_semana = ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'];
    
    try {
        // Iniciar transação
        $pdo->beginTransaction();

        // Atualizar horários
        $sql = "UPDATE clinicas SET 
                nome = :nome,
                horario_abertura = :horario_abertura,
                horario_fechamento = :horario_fechamento,
                horario_abertura_seg = :horario_abertura_seg,
                horario_fechamento_seg = :horario_fechamento_seg,
                horario_abertura_ter = :horario_abertura_ter,
                horario_fechamento_ter = :horario_fechamento_ter,
                horario_abertura_qua = :horario_abertura_qua,
                horario_fechamento_qua = :horario_fechamento_qua,
                horario_abertura_qui = :horario_abertura_qui,
                horario_fechamento_qui = :horario_fechamento_qui,
                horario_abertura_sex = :horario_abertura_sex,
                horario_fechamento_sex = :horario_fechamento_sex,
                horario_abertura_sab = :horario_abertura_sab,
                horario_fechamento_sab = :horario_fechamento_sab,
                horario_abertura_dom = :horario_abertura_dom,
                horario_fechamento_dom = :horario_fechamento_dom
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $params = [
            ':nome' => $nome_clinica,
            ':horario_abertura' => $horario_abertura,
            ':horario_fechamento' => $horario_fechamento,
            ':id' => $id
        ];

        foreach ($dias_semana as $dia) {
            $params[':horario_abertura_'.$dia] = $_POST[$dia.'_abertura'] ?? null;
            $params[':horario_fechamento_'.$dia] = $_POST[$dia.'_fechamento'] ?? null;
        }

        $stmt->execute($params);

        $pdo->commit();
        $_SESSION['success'] = "Clínica e horários atualizados com sucesso!";
        header('Location: configuracao_clinica.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erro ao atualizar clínica: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Clínica</title>
    <link rel="stylesheet" href="../../assets/css/editar_clinica.css">
</head>
<body>
    <div class="editar-clinica-container">
        <h1>Editar Clínica</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <form class="editar-clinica-form" method="POST" action="">
            <div class="form-group">
                <label for="nome_clinica">Nome da Clínica:</label>
                <input type="text" name="nome_clinica" id="nome_clinica" value="<?= htmlspecialchars($clinica['nome']) ?>" required>
            </div>

            <div class="horario-container">
                <?php
                $dias_semana = [
                    'seg' => 'Segunda-feira',
                    'ter' => 'Terça-feira',
                    'qua' => 'Quarta-feira',
                    'qui' => 'Quinta-feira',
                    'sex' => 'Sexta-feira',
                    'sab' => 'Sábado',
                    'dom' => 'Domingo'
                ];
                
                foreach ($dias_semana as $dia => $label): 
                    $abertura = $clinica['horario_abertura_'.$dia] ?? null;
                    $fechamento = $clinica['horario_fechamento_'.$dia] ?? null;
                ?>
                    <div class="dia-horario">
                        <div class="dia-header" onclick="toggleHorario('<?= $dia ?>')">
                            <span><?= $label ?></span>
                            <span class="seta" id="seta-<?= $dia ?>">▼</span>
                        </div>
                        <div class="horario-content" id="horario-<?= $dia ?>" style="display: none;">
                            <div class="horario-inputs">
                                <div class="modern-input">
                                    <span class="input-icon">⏰</span>
                                    <input type="time" 
                                           id="<?= $dia ?>_abertura" 
                                           name="<?= $dia ?>_abertura" 
                                           value="<?= $abertura ? htmlspecialchars($abertura) : '' ?>"
                                           placeholder="Abertura">
                                </div>
                                <span class="horario-separador">às</span>
                                <div class="modern-input">
                                    <span class="input-icon">⏰</span>
                                    <input type="time" 
                                           id="<?= $dia ?>_fechamento" 
                                           name="<?= $dia ?>_fechamento" 
                                           value="<?= $fechamento ? htmlspecialchars($fechamento) : '' ?>"
                                           placeholder="Fechamento">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-primary">Salvar Alterações</button>
        </form>
    </div>

    <script>
    function toggleHorario(dia) {
        const content = document.getElementById('horario-' + dia);
        const seta = document.getElementById('seta-' + dia);
        
        // Fechar todos os outros horários
        document.querySelectorAll('.horario-content').forEach(otherContent => {
            if (otherContent.id !== 'horario-' + dia) {
                otherContent.style.display = 'none';
                const otherSeta = document.getElementById('seta-' + otherContent.id.split('-')[1]);
                if (otherSeta) otherSeta.textContent = '▼';
            }
        });
        
        // Alternar apenas o horário clicado
        if (content.style.display === 'none' || content.style.display === '') {
            content.style.display = 'block';
            seta.textContent = '▲';
        } else {
            content.style.display = 'none';
            seta.textContent = '▼';
        }
    }

    // Esconder todos os horários ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.horario-content').forEach(content => {
            content.style.display = 'none';
        });
    });
    </script>
</body>
</html> 