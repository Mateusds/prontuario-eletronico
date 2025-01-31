<?php
session_start();
require '../../includes/config.php';

// Verificar se é administrador
if ($_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Processar atualização da configuração
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_clinica = $_POST['nome_clinica'] ?? '';
    $dias_selecionados = $_POST['dias'] ?? [];
    
    // Array para armazenar todos os dados
    $dados = [':nome' => $nome_clinica];
    
    // Construir a query dinamicamente
    $colunas = ['nome'];
    $valores = [':nome'];
    
    foreach (['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'] as $dia) {
        $abertura = $_POST["horario_abertura_$dia"] ?? '00:00:00';
        $fechamento = $_POST["horario_fechamento_$dia"] ?? '00:00:00';
        
        // Se o dia não foi selecionado, define como fechado
        if (!in_array(ucfirst($dia), $dias_selecionados)) {
            $abertura = '00:00:00';
            $fechamento = '00:00:00';
        }
        
        $colunas[] = "horario_abertura_$dia";
        $colunas[] = "horario_fechamento_$dia";
        
        $valores[] = ":horario_abertura_$dia";
        $valores[] = ":horario_fechamento_$dia";
        
        $dados[":horario_abertura_$dia"] = $abertura;
        $dados[":horario_fechamento_$dia"] = $fechamento;
    }
    
    try {
        $sql = "INSERT INTO clinicas (" . implode(', ', $colunas) . ") 
                VALUES (" . implode(', ', $valores) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($dados);
        
        $_SESSION['success'] = "Clínica cadastrada com sucesso!";
        header('Location: configuracao_clinica.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao cadastrar clínica: " . $e->getMessage();
    }
}

// Recuperar clínicas cadastradas
try {
    // Recuperar apenas clínicas ativas
    $sql = "SELECT * FROM clinicas WHERE situacao = 1";
    $stmt = $pdo->query($sql);
    $clinicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($clinicas)) {
        // Comente ou remova a linha abaixo para não exibir a mensagem
        // echo "Nenhuma clínica ativa cadastrada.";
    } else {
        foreach ($clinicas as $clinica) {
            // Comente ou remova a linha abaixo
            // echo "ID: " . $clinica['id'] . " - Nome: " . $clinica['nome'] . "<br>";
        }
    }
} catch (PDOException $e) {
    echo "Erro ao consultar clínicas: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configuração da Clínica</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dias-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .dia-item {
            flex: 1 1 120px;
            max-width: 150px;
            position: relative;
            overflow: visible;
        }
        
        .dia-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            background-color: #f5f5f5;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        
        .dia-header label {
            cursor: pointer;
            margin: 0;
        }
        
        .horario-content {
            display: none;
            margin-top: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 10px;
        }
        
        .horario-inputs {
            display: flex;
            flex-direction: column;
            gap: 5px;
            width: 100%;
        }
        
        .horario-inputs .modern-input {
            width: 100%;
        }
        
        .horario-inputs .modern-input.compact {
            width: 100%;
            margin: 0;
        }
        
        .horario-inputs .modern-input.compact input {
            width: 100%;
            box-sizing: border-box;
        }
        
        .toggle-switch input:checked ~ .horario-content {
            display: block;
        }
        
        .horario-lista {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .horario-lista li {
            margin-bottom: 5px;
        }
        
        .horario-lista li:last-child {
            margin-bottom: 0;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 24px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-label {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .toggle-label:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-label {
            background-color: #4CAF50;
        }
        
        input:checked + .toggle-label:before {
            transform: translateX(16px);
        }
        
        .clinicas-list {
            text-align: center; /* Centraliza o texto */
            margin: 20px auto; /* Adiciona margem e centraliza o container */
            max-width: 800px; /* Define uma largura máxima para o container */
            padding: 20px;
            background-color: #f9f9f9; /* Fundo claro */
            border-radius: 8px; /* Bordas arredondadas */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombra suave */
        }
        
        .clinicas-list h2 {
            font-size: 24px; /* Tamanho da fonte */
            color: #333; /* Cor do texto */
            margin-bottom: 20px; /* Espaçamento abaixo do título */
        }
        
        .clinica-card {
            background-color: #fff; /* Fundo branco */
            border: 1px solid #ddd; /* Borda suave */
            border-radius: 8px; /* Bordas arredondadas */
            padding: 15px;
            margin-bottom: 15px; /* Espaçamento entre os cards */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombra suave */
            text-align: left; /* Alinha o conteúdo do card à esquerda */
        }
        
        .clinica-card h3 {
            font-size: 20px; /* Tamanho da fonte */
            color: #444; /* Cor do texto */
            margin-bottom: 10px; /* Espaçamento abaixo do título */
        }
        
        .clinica-card p {
            font-size: 16px; /* Tamanho da fonte */
            color: #666; /* Cor do texto */
            margin-bottom: 8px; /* Espaçamento abaixo dos parágrafos */
        }
        
        .clinica-card .actions {
            display: flex;
            gap: 10px; /* Espaçamento entre os botões */
            justify-content: flex-end; /* Alinha os botões à direita */
        }
        
        .btn-edit, .btn-delete {
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        
        .btn-edit {
            background-color: #4CAF50; /* Verde */
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #45a049; /* Verde mais escuro */
        }
        
        .btn-delete {
            background-color: #f44336; /* Vermelho */
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #e53935; /* Vermelho mais escuro */
        }
        
        .btn-primary {
            padding: 6px 12px; /* Reduz o padding para um tamanho menor */
            font-size: 13px; /* Reduz o tamanho da fonte */
            border-radius: 4px; /* Bordas arredondadas */
            cursor: pointer; /* Muda o cursor para pointer */
            width: auto; /* Define a largura como automática */
            max-width: 180px; /* Define uma largura máxima para o botão */
            display: block; /* Transforma o botão em um bloco */
            margin: 0 auto; /* Centraliza o botão horizontalmente */
        }
        
        .alert.success {
            position: fixed; /* Fixa a mensagem na tela */
            top: 20px; /* Distância do topo */
            left: 50%; /* Centraliza horizontalmente */
            transform: translateX(-50%); /* Ajusta a posição */
            padding: 10px 20px;
            background-color: #4CAF50; /* Cor de fundo verde */
            color: white; /* Cor do texto */
            border-radius: 4px; /* Bordas arredondadas */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombra suave */
            z-index: 1000; /* Garante que a mensagem fique acima de outros elementos */
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php include '../../includes/menu_lateral.php'; ?>
        <main class="content">
            <h1>Configuração da Clínica</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="form-container card animate__animated animate__fadeInUp">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nome_clinica">Nome da Clínica:</label>
                        <div class="modern-input">
                            <i class="fas fa-hospital input-icon"></i>
                            <input type="text" name="nome_clinica" id="nome_clinica" required placeholder="Digite o nome da clínica">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Horário de Funcionamento:</label>
                        <div class="dias-container">
                            <?php
                            $dias_semana = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
                            
                            foreach ($dias_semana as $dia): ?>
                                <div class="dia-item">
                                    <div class="dia-header">
                                        <div class="toggle-switch">
                                            <input type="checkbox" id="dia_<?= strtolower($dia) ?>" name="dias[]" value="<?= $dia ?>">
                                            <label for="dia_<?= strtolower($dia) ?>" class="toggle-label"></label>
                                        </div>
                                        <label for="dia_<?= strtolower($dia) ?>"><?= $dia ?></label>
                                    </div>
                                    <div class="horario-content">
                                        <div class="horario-inputs">
                                            <div class="modern-input compact">
                                                <i class="fas fa-clock input-icon"></i>
                                                <input type="time" name="horario_abertura_<?= strtolower($dia) ?>" class="horario-abertura" value="">
                                            </div>
                                            <span class="horario-separador">-</span>
                                            <div class="modern-input compact">
                                                <i class="fas fa-clock input-icon"></i>
                                                <input type="time" name="horario_fechamento_<?= strtolower($dia) ?>" class="horario-fechamento" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary btn-animate">
                        <span>Salvar Configurações</span>
                        <i class="fas fa-save"></i>
                    </button>
                </form>
            </div>

            <div class="clinicas-list">
                <h2>Clínicas Cadastradas</h2>
                <?php if (empty($clinicas)): ?>
                    <div class="alert info">Nenhuma clínica cadastrada.</div>
                <?php else: ?>
                    <?php foreach ($clinicas as $clinica): ?>
                        <div class="clinica-card">
                            <h3><?= htmlspecialchars($clinica['nome']) ?></h3>
                            <?php
                            $dias_semana = ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'];
                            foreach ($dias_semana as $dia): 
                                $horario_abertura = $clinica["horario_abertura_$dia"] ?? null;
                                $horario_fechamento = $clinica["horario_fechamento_$dia"] ?? null;
                                if ($horario_abertura && $horario_fechamento && $horario_abertura != '00:00:00' && $horario_fechamento != '00:00:00'): ?>
                                    <p><strong><?= ucfirst($dia) ?>:</strong> <?= htmlspecialchars($horario_abertura) ?> às <?= htmlspecialchars($horario_fechamento) ?></p>
                                <?php endif;
                            endforeach; ?>
                            <div class="actions">
                                <a href="editar_clinica.php?id=<?= $clinica['id'] ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="excluir_clinica.php?id=<?= $clinica['id'] ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta clínica?')">
                                    <i class="fas fa-trash"></i> Excluir
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        // Atualizar a página após cadastro bem-sucedido
        <?php if (isset($_SESSION['success'])): ?>
            setTimeout(function() {
                window.location.reload();
            }, 2000);
        <?php endif; ?>
        
        // Função para mostrar/esconder horários ao alternar o toggle
        function toggleHorario(checkbox) {
            const horarioContent = checkbox.closest('.dia-item').querySelector('.horario-content');
            if (checkbox.checked) {
                horarioContent.style.display = 'block';
            } else {
                horarioContent.style.display = 'none';
            }
        }
        
        // Adiciona o evento de change a todos os toggles
        document.querySelectorAll('.toggle-switch input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleHorario(this);
            });
            
            // Verifica o estado inicial
            if (checkbox.checked) {
                checkbox.closest('.dia-item').querySelector('.horario-content').style.display = 'block';
            }
        });

        // Função para remover a mensagem após 5 segundos
        setTimeout(function() {
            var successMessage = document.querySelector('.alert.success');
            if (successMessage) {
                successMessage.remove();
            }
        }, 5000); // 5000 milissegundos = 5 segundos

        // Função para abrir o modal de edição
        function editarClinica(id, nome, horarioAbertura, horarioFechamento) {
            document.getElementById('editar_id').value = id;
            document.getElementById('editar_nome_clinica').value = nome;
            document.getElementById('editar_horario_abertura').value = horarioAbertura;
            document.getElementById('editar_horario_fechamento').value = horarioFechamento;
            document.getElementById('editarClinicaModal').style.display = 'block';
        }

        // Função para fechar o modal
        function fecharModal() {
            document.getElementById('editarClinicaModal').style.display = 'none';
        }

        // Fechar o modal ao clicar fora dele
        window.onclick = function(event) {
            var modal = document.getElementById('editarClinicaModal');
            if (event.target == modal) {
                fecharModal();
            }
        }
    </script>
</body>
</html>