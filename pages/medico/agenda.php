<?php
session_start();
require '../../includes/config.php';

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Buscar consultas agendadas
$medico_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT c.*, p.nome_completo as paciente_nome, p.telefone, p.email 
                      FROM consultas c
                      JOIN pacientes p ON c.paciente_id = p.id
                      WHERE c.medico_id = ? AND c.data_consulta >= NOW()
                      ORDER BY c.data_consulta ASC");
$stmt->execute([$medico_id]);
$consultas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agenda Médica</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/medico-agenda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <div class="header-container">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1>Agenda Médica</h1>
                    <a href="../../pages/logout.php" class="btn-sair">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </div>
                <div class="header-actions">
                    <button class="btn-primary" onclick="abrirModalAgendamento()" style="width: 150px; padding: 0.5rem; font-size: 14px;">
                        <i class="fas fa-plus"></i> Nova Consulta
                    </button>
                    <a href="../medico/configurar_agenda.php" class="btn-primary" style="width: 180px; padding: 0.5rem; margin-left: 0.5rem; text-decoration: none;">
                        <i class="fas fa-cog"></i> Configurar Agenda
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="filtros">
                <input type="text" id="search" placeholder="Pesquisar paciente...">
                <input type="text" id="date-filter" placeholder="Filtrar por data...">
                <select id="status-filter">
                    <option value="">Todos os status</option>
                    <option value="agendado">Agendado</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="cancelado">Cancelado</option>
                    <option value="realizado">Realizado</option>
                </select>
            </div>

            <div class="consultas-list">
                <?php foreach ($consultas as $consulta): ?>
                    <div class="consulta-card status-<?= $consulta['status'] ?>">
                        <div class="consulta-header">
                            <h3><?= $consulta['paciente_nome'] ?></h3>
                            <div class="consulta-actions">
                                <button class="btn-icon" onclick="editarConsulta(<?= $consulta['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" onclick="confirmarCancelamento(<?= $consulta['id'] ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($consulta['data_consulta'])) ?></p>
                        <p><strong>Status:</strong> <span class="status-badge"><?= ucfirst($consulta['status']) ?></span></p>
                        <p><strong>Contato:</strong> <?= $consulta['telefone'] ?> | <?= $consulta['email'] ?></p>
                        <button class="btn-link" onclick="mostrarDetalhes(<?= $consulta['id'] ?>)">
                            Ver mais detalhes
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Modal de Agendamento -->
    <div id="modal-agendamento" class="modal">
        <div class="modal-content" style="max-width: 500px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <span class="close-modal" onclick="fecharModalAgendamento()" style="font-size: 24px; color: #666; cursor: pointer; position: absolute; right: 20px; top: 15px;">&times;</span>
            <h2 style="font-size: 1.5rem; color: #333; margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Agendar Nova Consulta</h2>
            <form id="form-agendamento" style="padding: 0 1rem;">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="cpf" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: #555; text-align: center;">CPF do Paciente:</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: center;">
                        <input type="text" id="cpf" name="cpf" placeholder="Digite o CPF" required 
                               style="width: 200px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 6px; transition: all 0.3s ease;"
                               onfocus="this.style.borderColor='#007bff'; this.style.boxShadow='0 0 0 2px rgba(0,123,255,0.25)';"
                               onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
                        <button type="button" class="btn-secondary" onclick="buscarPacientePorCPF()" 
                                style="padding: 0.5rem; width: 40px; background: #0056b3; border: none; border-radius: 6px; cursor: pointer; transition: all 0.3s ease;"
                                onmouseover="this.style.background='#e0e0e0'"
                                onmouseout="this.style.background='#f0f0f0'">
                            <i class="fas fa-search" style="font-size: 14px;"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group" id="paciente-info" style="display: none; background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; position: relative;">
                    <div class="check-circle" onclick="toggleCheck()" style="position: absolute; right: 10px; top: 10px; width: 20px; height: 20px; border-radius: 50%; background: #e0e0e0; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.3s ease;">
                        <i class="fas fa-check" style="color: white; font-size: 12px; display: none;"></i>
                    </div>
                    <h3 style="font-size: 1.1rem; color: #333; margin-bottom: 0.75rem;">Dados do Paciente</h3>
                    <p style="margin: 0.25rem 0;"><strong>Nome:</strong> <span id="paciente-nome" style="color: #666;"></span></p>
                    <p style="margin: 0.25rem 0;"><strong>Telefone:</strong> <span id="paciente-telefone" style="color: #666;"></span></p>
                    <p style="margin: 0.25rem 0;"><strong>Email:</strong> <span id="paciente-email" style="color: #666;"></span></p>
                    <input type="hidden" id="paciente_id" name="paciente_id">
                </div>
                
                <button type="submit" class="btn-primary" 
                        style="width: 200px; padding: 0.75rem; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; margin: 0 auto; display: block;">
                    Agendar Consulta
                </button>
            </form>
        </div>
    </div>

    <script>
        // Inicialização do Flatpickr para filtro de data
        flatpickr("#date-filter", {
            dateFormat: "d/m/Y",
            locale: "pt"
        });

        // Funções JavaScript
        function abrirModalAgendamento() {
            document.getElementById('modal-agendamento').style.display = 'block';
            carregarPacientes();
        }

        function fecharModalAgendamento() {
            document.getElementById('modal-agendamento').style.display = 'none';
        }

        function carregarPacientes() {
            // Implementar AJAX para carregar pacientes
        }

        function editarConsulta(id) {
            // Implementar lógica de edição
        }

        function confirmarCancelamento(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Você deseja cancelar esta consulta?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, cancelar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implementar lógica de cancelamento
                }
            });
        }

        function mostrarDetalhes(id) {
            // Implementar exibição de detalhes completos
        }

        // Filtros e pesquisa
        document.getElementById('search').addEventListener('input', function() {
            // Implementar filtro de pesquisa
        });

        document.getElementById('date-filter').addEventListener('change', function() {
            // Implementar filtro por data
        });

        document.getElementById('status-filter').addEventListener('change', function() {
            // Implementar filtro por status
        });

        function toggleCheck() {
            const checkCircle = document.querySelector('.check-circle');
            const checkIcon = document.querySelector('.check-circle .fa-check');
            const btnAgendar = document.querySelector('#form-agendamento button[type="submit"]');
            
            if (checkCircle.style.backgroundColor === 'rgb(40, 167, 69)') { // Verifica se está verde
                checkCircle.style.backgroundColor = '#e0e0e0'; // Volta para cinza
                checkIcon.style.display = 'none';
                btnAgendar.disabled = true;
                btnAgendar.style.opacity = '0.6';
                btnAgendar.style.cursor = 'not-allowed';
                btnAgendar.title = 'Por favor, selecione o paciente clicando no check verde para habilitar o agendamento';
                
                // Reativar a animação pulsante
                checkCircle.classList.add('pulsing');
                checkCircle.style.animationPlayState = 'running';
            } else {
                checkCircle.style.backgroundColor = '#28a745'; // Muda para verde
                checkCircle.classList.remove('pulsing');
                checkIcon.style.display = 'block';
                btnAgendar.disabled = false;
                btnAgendar.style.opacity = '1';
                btnAgendar.style.cursor = 'pointer';
                btnAgendar.title = ''; // Remove o tooltip quando habilitado
            }
        }

        function buscarPacientePorCPF() {
            const cpfComMascara = document.getElementById('cpf').value;
            const cpf = removerMascaraCPF(cpfComMascara);
            
            // Resetar o check
            const checkCircle = document.querySelector('.check-circle');
            const checkIcon = document.querySelector('.check-circle .fa-check');
            checkCircle.style.backgroundColor = '#e0e0e0';
            checkIcon.style.display = 'none';

            if (!cpf || cpf.length !== 11) {  // Verifica se o CPF tem 11 dígitos
                Swal.fire('Erro', 'Por favor, insira um CPF válido.', 'error');
                return;
            }

            fetch('../../api/buscar_paciente.php?cpf=' + cpf)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na requisição: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Dados recebidos:', data); // Adicionado para depuração
                    
                    if (!data.success) {
                        Swal.fire('Erro', data.message, 'error');
                        document.getElementById('paciente-info').style.display = 'none';
                    } else {
                        const paciente = data.paciente;
                        document.getElementById('paciente-nome').textContent = paciente.nome_completo || 'Não informado';
                        
                        // Verifica se os campos existem antes de exibi-los
                        document.getElementById('paciente-telefone').textContent = 
                            paciente.hasOwnProperty('telefone') && paciente.telefone ? paciente.telefone : 'Não informado';
                        
                        document.getElementById('paciente-email').textContent = 
                            paciente.hasOwnProperty('email') && paciente.email ? paciente.email : 'Não informado';
                        
                        document.getElementById('paciente_id').value = paciente.id;
                        document.getElementById('paciente-info').style.display = 'block';
                        
                        // Adicionar animação pulsante
                        checkCircle.classList.add('pulsing');
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    Swal.fire('Erro', 'Ocorreu um erro ao buscar o paciente. Verifique o console para mais detalhes.', 'error');
                    document.getElementById('paciente-info').style.display = 'none';
                });
        }

        function removerMascaraCPF(cpfComMascara) {
            return cpfComMascara.replace(/\D/g, '');
        }

        // Adicionar evento de input para o campo CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            // Aplicar máscara
            this.value = aplicarMascaraCPF(this.value);
            
            // Se o CPF tiver 14 caracteres (com máscara), buscar automaticamente
            if (this.value.length === 14) {
                buscarPacientePorCPF();
            }
        });

        // Função para aplicar máscara de CPF
        function aplicarMascaraCPF(cpf) {
            // Remove tudo que não for dígito
            cpf = cpf.replace(/\D/g, '');
            
            // Aplica a máscara enquanto o usuário digita
            if (cpf.length > 3) {
                cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            }
            if (cpf.length > 6) {
                cpf = cpf.replace(/(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
            }
            if (cpf.length > 9) {
                cpf = cpf.replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
            }
            
            // Limita o tamanho máximo do CPF com máscara
            if (cpf.length > 14) {
                cpf = cpf.substring(0, 14);
            }
            
            return cpf;
        }

        // Modificar o envio do formulário para abrir nova janela
        document.getElementById('form-agendamento').addEventListener('submit', function(e) {
            e.preventDefault(); // Impede o envio padrão do formulário
            
            const checkIcon = document.querySelector('.check-circle .fa-check');
            const pacienteId = document.getElementById('paciente_id').value;
            
            if (checkIcon.style.display !== 'block') {
                Swal.fire('Atenção', 'Por favor, confirme a seleção do paciente clicando no check.', 'warning');
                return;
            }

            // Abre nova janela com a agenda de consultas
            const width = 1000;
            const height = 600;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            
            window.open(
                `agendar_consulta.php?paciente_id=${pacienteId}`,
                'AgendarConsulta',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
            );
        });

        // Inicializar o botão como desativado
        document.addEventListener('DOMContentLoaded', function() {
            const btnAgendar = document.querySelector('#form-agendamento button[type="submit"]');
            btnAgendar.disabled = true;
            btnAgendar.style.opacity = '0.6';
            btnAgendar.style.cursor = 'not-allowed';
            
            // Adicionar tooltip
            btnAgendar.title = 'Por favor, selecione o paciente clicando no check verde para habilitar o agendamento';
        });
    </script>
</body>
</html>