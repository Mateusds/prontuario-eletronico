<?php
require '../../includes/config.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../pages/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica se o campo nome_completo está preenchido
    if (!isset($_POST['nome_completo']) || empty(trim($_POST['nome_completo']))) {
        $_SESSION['error'] = "O campo Nome Completo deve ser preenchido.";
        header('Location: cadastro.php');
        exit();
    }

    // Coleta os dados do formulário
    $nome_completo = trim($_POST['nome_completo']);
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];

    try {
        $stmt = $pdo->prepare("INSERT INTO pacientes (nome_completo, data_nascimento, telefone, email, cpf) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome_completo, $data_nascimento, $telefone, $email, $cpf]);
        $_SESSION['success'] = "Paciente cadastrado com sucesso!";
        header('Location: cadastro.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao cadastrar paciente: " . $e->getMessage();
        header('Location: cadastro.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Paciente</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/cadastro_paciente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>Cadastro de Paciente</h1>
                <form id="logoutForm" action="../../pages/logout.php" method="post">
                    <button type="submit" class="btn-sair">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </button>
                </form>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="cadastro-container">
                <form method="post">
                    <div class="form-columns">
                        <div class="form-group">
                            <label for="nome_completo">Nome Completo:</label>
                            <input type="text" id="nome_completo" name="nome_completo" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento:</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cpf">CPF:</label>
                            <input type="text" id="cpf" name="cpf" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefone">Telefone:</label>
                            <input type="tel" id="telefone" name="telefone" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cep">CEP:</label>
                            <input type="text" id="cep" name="cep" required>
                            <button type="button" id="btn-buscar-cep" class="btn-buscar-cep">Buscar CEP</button>
                        </div>
                        
                        <div class="form-group">
                            <label for="endereco">Endereço:</label>
                            <input type="text" id="endereco" name="endereco" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="numero">Número:</label>
                            <input type="text" id="numero" name="numero" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="complemento">Complemento:</label>
                            <input type="text" id="complemento" name="complemento">
                        </div>
                        
                        <div class="form-group">
                            <label for="bairro">Bairro:</label>
                            <input type="text" id="bairro" name="bairro" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cidade">Cidade:</label>
                            <input type="text" id="cidade" name="cidade" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <input type="text" id="estado" name="estado" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-cadastrar">Cadastrar</button>
                </form>
            </div>
        </main>
    </div>

    <script src="../../assets/js/cadastro_paciente.js"></script>
</body>
</html>
