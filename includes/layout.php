<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Prontuário Eletrônico</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <!-- Botão de Sair no canto superior direito -->
        <div class="logout-container">
            <a href="../../pages/logout.php" class="btn-sair"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>

        <aside class="sidebar">
            <h2>Menu</h2>
            <ul>
                <?php if ($_SESSION['user_type'] == 'admin'): ?>
                    <li><a href="configuracao_clinica.php"><i class="fas fa-cog"></i> Configuração da Clínica</a></li>
                    <li><a href="gerenciar_usuarios.php"><i class="fas fa-users"></i> Gerenciar Usuários</a></li>
                    <li><a href="relatorios.php"><i class="fas fa-chart-line"></i> Relatórios</a></li>
                <?php endif; ?>

                <?php if ($_SESSION['user_type'] == 'medico' || $_SESSION['user_type'] == 'admin'): ?>
                    <li><a href="../medico/agenda.php"><i class="fas fa-calendar-alt"></i> Agenda Médica</a></li>
                    <li><a href="../medico/painel.php"><i class="fas fa-bell"></i> Painel de Chamadas</a></li>
                    <li><a href="../medico/atendimento.php"><i class="fas fa-user-md"></i> Atendimento Médico</a></li>
                    <li><a href="../medico/finalizar_consulta.php"><i class="fas fa-check-circle"></i> Finalizar Consulta</a></li>
                <?php endif; ?>

                <?php if ($_SESSION['user_type'] == 'atendente' || $_SESSION['user_type'] == 'admin'): ?>
                    <li><a href="../atendente/agenda.php"><i class="fas fa-calendar-check"></i> Agenda do Atendente</a></li>
                <?php endif; ?>

                <?php if ($_SESSION['user_type'] == 'paciente' || $_SESSION['user_type'] == 'admin'): ?>
                    <li><a href="../paciente/consultas.php"><i class="fas fa-stethoscope"></i> Consultas de Pacientes</a></li>
                    <li><a href="../paciente/prescricao.php"><i class="fas fa-prescription-bottle"></i> Prescrições</a></li>
                    <li><a href="../paciente/atestado.php"><i class="fas fa-file-medical"></i> Atestados</a></li>
                    <li><a href="../paciente/encaminhamento.php"><i class="fas fa-share-square"></i> Encaminhamentos</a></li>
                    <li><a href="../paciente/medicacao.php"><i class="fas fa-pills"></i> Medicações</a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <main class="content">
            <?php include $content; ?> <!-- Conteúdo dinâmico da página -->
        </main>
    </div>
</body>
</html> 