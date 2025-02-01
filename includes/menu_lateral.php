<?php
// Obtém o caminho completo da página atual
$current_path = $_SERVER['PHP_SELF'];
?>

<aside class="sidebar">
    <h2>PRONTUÁRIO ELETRÔNICO</h2>
    <ul>
        <?php if ($_SESSION['user_type'] == 'admin'): ?>
            <li>
                <a href="../admin/configuracao_clinica.php" class="<?= (strpos($current_path, 'configuracao_clinica.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Configuração da Clínica
                </a>
            </li>
            <li>
                <a href="gerenciar_usuarios.php" class="<?= (strpos($current_path, 'gerenciar_usuarios.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Gerenciar Usuários
                </a>
            </li>
            <li>
                <a href="../medico/cadastro_medico.php" class="<?= (strpos($current_path, 'medico/cadastro_medico.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-user-plus"></i> Cadastrar Médico
                </a>
            </li>
            <li>
                <a href="../admin/relatorios.php" class="<?= (strpos($current_path, 'admin/relatorios.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i> Relatórios
                </a>
            </li>
        <?php endif; ?>

        <?php if ($_SESSION['user_type'] == 'medico' || $_SESSION['user_type'] == 'admin'): ?>
            <li>
                <a href="../medico/agenda.php" class="<?= (strpos($current_path, 'medico/agenda.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i> Agenda Médica
                </a>
            </li>
            <li>
                <a href="../medico/painel.php" class="<?= (strpos($current_path, 'medico/painel.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-bell"></i> Painel de Chamadas
                </a>
            </li>
            <li>
                <a href="../medico/atendimento.php" class="<?= (strpos($current_path, 'medico/atendimento.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-user-md"></i> Atendimento Médico
                </a>
            </li>
            <li>
                <a href="../medico/finalizar_consulta.php" class="<?= (strpos($current_path, 'medico/finalizar_consulta.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-check-circle"></i> Finalizar Consulta
                </a>
            </li>
        <?php endif; ?>

        <?php if ($_SESSION['user_type'] == 'atendente' || $_SESSION['user_type'] == 'admin'): ?>
            <li>
                <a href="../atendente/agenda.php" class="<?= (strpos($current_path, 'atendente/agenda.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-calendar-check"></i> Agenda do Atendente
                </a>
            </li>
        <?php endif; ?>

        <?php if ($_SESSION['user_type'] == 'paciente' || $_SESSION['user_type'] == 'admin'): ?>
            <li>
                <a href="../paciente/cadastro.php" class="<?= (strpos($current_path, 'paciente/cadastro.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-user-plus"></i> Cadastro de Paciente
                </a>
            </li>
            <li>
                <a href="../paciente/consultas.php" class="<?= (strpos($current_path, 'paciente/consultas.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-stethoscope"></i> Consultas de Pacientes
                </a>
            </li>
            <li>
                <a href="../paciente/prescricao.php" class="<?= (strpos($current_path, 'paciente/prescricao.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-prescription-bottle"></i> Prescrições
                </a>
            </li>
            <li>
                <a href="../paciente/atestado.php" class="<?= (strpos($current_path, 'paciente/atestado.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-file-medical"></i> Atestados
                </a>
            </li>
            <li>
                <a href="../paciente/encaminhamento.php" class="<?= (strpos($current_path, 'paciente/encaminhamento.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-share-square"></i> Encaminhamentos
                </a>
            </li>
            <li>
                <a href="../paciente/medicacao.php" class="<?= (strpos($current_path, 'paciente/medicacao.php') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-pills"></i> Medicações
                </a>
            </li>
        <?php endif; ?>
    </ul>
</aside> 