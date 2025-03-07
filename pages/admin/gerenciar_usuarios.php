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
        $_SESSION['error'] = "Erro ao excluir usuário: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>Gerenciar Usuários</h1>
                <form id="logoutForm" action="../../pages/logout.php" method="post" style="margin-left: 20px;">
                    <button type="submit" class="btn-sair">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
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
                                        <button type="button" onclick="confirmarExclusao('<?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>', 'formExcluir_<?= $usuario['id'] ?>')" class="btn-excluir">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmarExclusao(nome, formId) {
        Swal.fire({
            title: 'Confirmação',
            text: `Tem certeza que deseja excluir o usuário "${nome}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    // Função para remover a mensagem após 5 segundos
    setTimeout(function() {
        var successMessage = document.querySelector('.alert.success');
        if (successMessage) {
            successMessage.remove();
        }
    }, 5000); // 5000 milissegundos = 5 segundos
    </script>
</body>
</html> 