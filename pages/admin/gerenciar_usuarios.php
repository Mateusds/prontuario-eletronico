<?php
session_start();
require '../../includes/config.php';

// Verificar se é administrador
if ($_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Buscar todos os usuários ativos (situacao = 1)
$usuarios = $pdo->query("SELECT id, nome, email, tipo FROM usuarios WHERE situacao = 1")->fetchAll();

// Processar exclusão de usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir_usuario'])) {
    $usuario_id = $_POST['usuario_id'];
    
    try {
        // Atualiza a situação do usuário para "inativo" (0)
        $stmt = $pdo->prepare("UPDATE usuarios SET situacao = 0 WHERE id = ?");
        $stmt->execute([$usuario_id]);
        
        $_SESSION['success'] = "Usuário excluido com sucesso!";
        header('Location: gerenciar_usuarios.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao desativar usuário: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Gerenciar Usuários</h1>
            <a href="configuracao_clinica.php" class="btn-voltar">Voltar</a>
            <a href="criar_perfil.php" class="btn-criar">
                <i class="fas fa-plus"></i> Criar Novo Perfil
            </a>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <table class="usuarios-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= $usuario['nome'] ?></td>
                            <td><?= $usuario['email'] ?></td>
                            <td><?= ucfirst($usuario['tipo']) ?></td>
                            <td class="acoes">
                                <div class="button-group">
                                    <a href="editar_perfil.php?id=<?= $usuario['id'] ?>" class="btn-editar">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <form method="post" style="display:inline;" id="formExcluir_<?= $usuario['id'] ?>">
                                        <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                        <input type="hidden" name="excluir_usuario" value="1">
                                        <button type="button" onclick="confirmarExclusao('<?= $usuario['nome'] ?>', 'formExcluir_<?= $usuario['id'] ?>')" class="btn-excluir">
                                            <i class="fas fa-trash-alt"></i> Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
    <script>
    // Função para exibir o modal de confirmação
    function confirmarExclusao(nome, formId) {
        const modal = document.getElementById('confirmModal');
        const modalMessage = document.getElementById('modalMessage');
        const confirmButton = document.getElementById('confirmButton');
        const cancelButton = document.getElementById('cancelButton');

        // Define a mensagem do modal
        modalMessage.textContent = `Tem certeza que deseja desativar o usuário "${nome}"?`;

        // Exibe o modal
        modal.style.display = 'flex';

        // Configura o botão de confirmação
        confirmButton.onclick = function() {
            document.getElementById(formId).submit(); // Envia o formulário
            modal.style.display = 'none'; // Fecha o modal
        };

        // Configura o botão de cancelar
        cancelButton.onclick = function() {
            modal.style.display = 'none'; // Fecha o modal
        };

        // Fecha o modal ao clicar fora dele
        modal.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    }

    // Função para remover a mensagem após 5 segundos
    setTimeout(function() {
        var successMessage = document.querySelector('.alert.success');
        if (successMessage) {
            successMessage.remove();
        }
    }, 5000); // 5000 milissegundos = 5 segundos
    </script>
    <!-- Modal de confirmação -->
    <div id="confirmModal" class="modal" style="display: none;">
        <div class="modal-content">
            <p id="modalMessage"></p>
            <div class="modal-buttons">
                <button id="confirmButton" class="btn-confirm">Confirmar</button>
                <button id="cancelButton" class="btn-cancel">Cancelar</button>
            </div>
        </div>
    </div>
</body>
</html> 