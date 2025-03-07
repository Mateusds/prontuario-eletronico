<?php
session_start();
require '../../includes/config.php';

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configurar Agenda Médica</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-badge.ativa {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-badge.inativa {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .status-badge.pendente {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-badge.não-informada {
            background-color: #e5e7eb;
            color: #374151;
        }

        .btn-status {
            padding: 6px 12px;
            border-radius: 20px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .btn-status-ativar {
            background-color: #28a745;
            color: white;
        }

        .btn-status-ativar:hover {
            background-color: #218838;
        }

        .btn-status-desativar {
            background-color: #dc3545;
            color: white;
        }

        .btn-status-desativar:hover {
            background-color: #c82333;
        }

        .btn-excluir {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-excluir:hover {
            background-color: #c82333;
        }

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
            <button class="btn-sair" onclick="sair()">
                <i class="fas fa-sign-out-alt"></i> Sair
            </button>
            <h1>Configurar Agenda Médica</h1>
            
            <div class="configuracao-form">
                <form id="form-configuracao">
                    <div class="form-group">
                        <label for="unidade">Unidade:</label>
                        <select id="unidade" name="unidade" required>
                            <option value="">Selecione a unidade</option>
                            <?php
                            // Buscar clínicas ativas do banco de dados
                            $stmt = $pdo->query("SELECT * FROM clinicas WHERE situacao = 1");
                            while ($clinica = $stmt->fetch()) {
                                echo "<option value='{$clinica['id']}'>{$clinica['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="especialidade">Especialidade:</label>
                        <select id="especialidade" name="especialidade" required onchange="carregarMedicos()">
                            <option value="">Selecione a especialidade</option>
                            <?php
                            // Buscar especialidades do banco de dados
                            $stmt = $pdo->query("SELECT * FROM especialidades");
                            while ($especialidade = $stmt->fetch()) {
                                echo "<option value='{$especialidade['id']}'>{$especialidade['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="medico">Médico:</label>
                        <select id="medico" name="medico" required>
                            <option value="">Selecione o médico</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-primary" onclick="configurarAgenda()">
                            <i class="fas fa-cog"></i> Configurar Agenda
                        </button>
                        <button type="button" class="btn-secondary" onclick="limparFormulario()">
                            <i class="fas fa-eraser"></i> Limpar
                        </button>
                    </div>
                </form>
            </div>

            <div style="margin-top: 30px;"></div>
            
            <h2 style="text-align: center;">Agendas Criadas</h2>
            <div class="lista-medicos" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-top: 20px;">
                <table class="tabela-medicos" id="lista-agendas" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Vigência</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Período</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Dia(s)</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Especialidade</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Procedimento</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Situação & Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Agendas serão carregadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function carregarMedicos() {
            const especialidadeId = document.getElementById('especialidade').value;
            const selectMedico = document.getElementById('medico');
            
            if (!especialidadeId) {
                selectMedico.innerHTML = '<option value="">Selecione o médico</option>';
                return;
            }

            fetch(`../../api/buscar_medicos.php?especialidade_id=${especialidadeId}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Resposta da API:', text);
                            throw new Error('Erro na resposta da API: ' + text);
                        });
                    }
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Erro ao parsear JSON:', text);
                            throw new Error('Resposta inválida da API: ' + text);
                        }
                    });
                })
                .then(data => {
                    selectMedico.innerHTML = '<option value="">Selecione o médico</option>';
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(medico => {
                            selectMedico.innerHTML += `<option value="${medico.id}">${medico.nome}</option>`;
                        });
                    } else {
                        selectMedico.innerHTML += '<option value="">Nenhum médico encontrado</option>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar médicos:', error);
                    Swal.fire('Erro', error.message || 'Não foi possível carregar os médicos', 'error');
                });
        }

        function configurarAgenda() {
            const unidade = document.getElementById('unidade').value;
            const especialidade = document.getElementById('especialidade').value;
            const medico = document.getElementById('medico').value;

            if (!unidade || !especialidade || !medico) {
                Swal.fire('Erro', 'Por favor, preencha todos os campos.', 'error');
                return;
            }

            // Abre nova janela com a página de criação de agenda
            const width = 800;
            const height = 600;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            
            const novaJanela = window.open(
                `criar_agenda.php?unidade=${unidade}&especialidade=${especialidade}&medico=${medico}&no_menu=1`,
                'CriarAgenda',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
            );

            // Adiciona um listener para quando a janela for fechada
            const verificarJanela = setInterval(() => {
                if (novaJanela.closed) {
                    clearInterval(verificarJanela);
                    carregarAgendas(); // Recarrega as agendas após fechar a janela
                }
            }, 500);
        }

        function limparFormulario() {
            document.getElementById('form-configuracao').reset();
            Swal.fire('Limpo', 'Formulário limpo com sucesso!', 'info');
        }

        function carregarAgendas() {
            fetch('../../api/listar_agendas.php')
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(text || 'Erro na resposta da API');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Resposta completa da API:', data);

                    // Verifica se a resposta contém dados válidos
                    if (!data || data.success === false || !data.data) {
                        throw new Error('Resposta inválida da API');
                    }

                    const lista = document.getElementById('lista-agendas').getElementsByTagName('tbody')[0];
                    lista.innerHTML = '';

                    // Filtra as agendas com vagas_disponiveis > 0
                    const agendasFiltradas = data.data.filter(agenda => agenda.vagas_disponiveis > 0);

                    if (agendasFiltradas.length === 0) {
                        lista.innerHTML = `
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                                    Não há agenda cadastrada com vagas disponíveis.
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    // Processa cada agenda filtrada
                    agendasFiltradas.forEach(agenda => {
                        const situacao = agenda.situacao === 1 ? 'Ativa' : 'Inativa';
                        const situacaoClass = situacao.toLowerCase();
                        const isAtiva = agenda.situacao === 1;

                        // Mapeia os dias da semana
                        const diasSemana = [];
                        if (agenda.seg === 1) diasSemana.push('Seg');
                        if (agenda.ter === 1) diasSemana.push('Ter');
                        if (agenda.qua === 1) diasSemana.push('Qua');
                        if (agenda.qui === 1) diasSemana.push('Qui');
                        if (agenda.sex === 1) diasSemana.push('Sex');
                        if (agenda.sab === 1) diasSemana.push('Sáb');

                        const row = document.createElement('tr');
                        row.setAttribute('data-id', agenda.id);
                        
                        row.innerHTML = `
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center; white-space: nowrap;">
                                ${agenda.vigencia_inicio ? formatarData(agenda.vigencia_inicio) : 'N/A'} - ${agenda.vigencia_fim ? formatarData(agenda.vigencia_fim) : 'N/A'}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">
                                ${agenda.periodo_formatado || 'Período não definido'}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">
                                ${diasSemana.join(', ') || 'N/A'}
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">${agenda.especialidade_nome || agenda.especialidade || 'Não especificada'}</td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">${agenda.procedimento || 'N/A'}</td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="status-badge ${situacaoClass}">${situacao}</span>
                                    ${isAtiva ? 
                                        `<button class="btn-status btn-status-desativar" onclick="toggleStatusAgenda(${agenda.id}, true)">
                                            <i class="fas fa-toggle-on"></i> Desativar
                                        </button>` :
                                        `<button class="btn-status btn-status-ativar" onclick="toggleStatusAgenda(${agenda.id}, false)">
                                            <i class="fas fa-toggle-off"></i> Ativar
                                        </button>`
                                    }
                                    <button class="btn-excluir" onclick="excluirAgenda(${agenda.id})">
                                        <i class="fas fa-trash"></i> Excluir
                                    </button>
                                </div>
                            </td>
                        `;
                        lista.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Erro completo:', error);
                    const lista = document.getElementById('lista-agendas').getElementsByTagName('tbody')[0];
                    lista.innerHTML = `
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                                Erro ao carregar agendas: ${error.message}
                            </td>
                        </tr>
                    `;
                });
        }

        function editarAgenda(id) {
            // Abre a janela de edição da agenda
            const width = 800;
            const height = 600;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            
            window.open(
                `editar_agenda.php?id=${id}`,
                'EditarAgenda',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
            );
        }

        function excluirAgenda(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação irá excluir permanentemente a agenda!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`../../api/excluir_agenda.php?id=${id}`, {
                        method: 'DELETE'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro na resposta da API');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.success) {
                            Swal.fire('Excluída!', 'A agenda foi excluída com sucesso.', 'success')
                            .then(() => {
                                // Recarrega a lista de agendas
                                carregarAgendas();
                            });
                        } else {
                            throw new Error(data?.message || 'Erro ao excluir agenda');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao excluir agenda:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: error.message || 'Não foi possível excluir a agenda',
                            footer: 'Verifique o console para mais detalhes'
                        });
                    });
                }
            });
        }

        // Função para formatar os dias da semana
        function formatarDiasSemana(dias) {
            if (!dias || dias.trim() === '') {
                return 'Não definido';
            }

            // Mapeamento de dias abreviados para completos
            const diasMap = {
                'seg': 'Segunda',
                'ter': 'Terça',
                'qua': 'Quarta',
                'qui': 'Quinta',
                'sex': 'Sexta',
                'sab': 'Sábado',
                'dom': 'Domingo'
            };

            // Separa os dias e formata
            const diasArray = dias.split(',').map(dia => {
                const diaTrim = dia.trim().toLowerCase();
                return diasMap[diaTrim] || diaTrim;
            });

            return diasArray.join(', ');
        }

        // Função para formatar datas no formato DD/MM/YYYY
        function formatarData(data) {
            if (!data) return 'N/A';
            const date = new Date(data);
            if (isNaN(date)) return 'N/A';
            
            const dia = String(date.getDate()).padStart(2, '0');
            const mes = String(date.getMonth() + 1).padStart(2, '0'); // Mês começa em 0
            const ano = date.getFullYear();
            
            return `${dia}/${mes}/${ano}`;
        }

        // Função para formatar horário (HH:MM)
        function formatarHorario(horario) {
            if (!horario) return 'N/A';
            return horario.substring(0, 5); // Pega apenas as horas e minutos
        }

        // Função para alternar o status da agenda
        function toggleStatusAgenda(id, isAtiva) {
            const endpoint = isAtiva ? '../../api/desativar_agenda.php' : '../../api/ativar_agenda.php';

            // Atualiza a interface imediatamente
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (!row) {
                Swal.fire('Erro', 'Agenda não encontrada.', 'error');
                return;
            }

            const statusBadge = row.querySelector('.status-badge');
            const btn = row.querySelector('.btn-status');
            const novoStatus = isAtiva ? 'Inativa' : 'Ativa';

            // Atualiza o status e o botão
            if (statusBadge) {
                statusBadge.textContent = novoStatus;
                statusBadge.className = `status-badge ${novoStatus.toLowerCase()}`;
            }

            if (btn) {
                if (novoStatus === 'Ativa') {
                    btn.innerHTML = `<i class="fas fa-toggle-on"></i> Desativar`;
                    btn.className = 'btn-status btn-status-desativar';
                    btn.onclick = () => toggleStatusAgenda(id, true);
                } else {
                    btn.innerHTML = `<i class="fas fa-toggle-off"></i> Ativar`;
                    btn.className = 'btn-status btn-status-ativar';
                    btn.onclick = () => toggleStatusAgenda(id, false);
                }
            }

            // Envia a requisição para atualizar o status no banco de dados
            fetch(`${endpoint}?id=${id}`, {
                method: 'POST'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta da API');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    Swal.fire('Sucesso!', `Status alterado para ${novoStatus}`, 'success');
                } else {
                    throw new Error(data?.message || 'Erro ao alterar status');
                }
            })
            .catch(error => {
                Swal.fire('Erro', error.message || 'Não foi possível alterar o status', 'error');
            });
        }

        // Carregar as agendas ao abrir a página
        document.addEventListener('DOMContentLoaded', function() {
            carregarAgendas();
            // Atualizar a lista a cada 3 segundos
            setInterval(carregarAgendas, 3000);
        });

        function salvarAgenda() {
            const form = document.getElementById('form-criar-agenda');
            if (!form) {
                console.error('Formulário não encontrado');
                return;
            }

            const formData = new FormData(form);

            // Verificar se todos os campos obrigatórios estão preenchidos
            const camposObrigatorios = [
                'vigencia_inicio', 'vigencia_fim', 'dia_agendamento', 
                'periodo_inicio', 'periodo_fim', 'duracao_consulta', 
                'quantidade_beneficiario', 'procedimento'
            ];

            for (const campo of camposObrigatorios) {
                if (!formData.get(campo)) {
                    Swal.fire('Erro', `Por favor, preencha o campo: ${campo}`, 'error');
                    return;
                }
            }

            const data = {
                medico_id: formData.get('medico'),
                especialidade_id: formData.get('especialidade'),
                unidade_id: formData.get('unidade'),
                vigencia_inicio: formData.get('vigencia_inicio'),
                vigencia_fim: formData.get('vigencia_fim'),
                dias_semana: formData.get('dia_agendamento'),
                periodo_inicio: formData.get('periodo_inicio'),
                periodo_fim: formData.get('periodo_fim'),
                duracao: formData.get('duracao_consulta'),
                quantidade_beneficiario: formData.get('quantidade_beneficiario'),
                procedimento: formData.get('procedimento'),
                especialidade_nome: document.getElementById('especialidade_nome').value,
                tipo: formData.get('tipo'),
                faixa_etaria_inicio: formData.get('faixa_etaria_inicio'),
                faixa_etaria_fim: formData.get('faixa_etaria_fim'),
                total_vagas: formData.get('quantidade_beneficiario'),
                vagas_disponiveis: formData.get('quantidade_beneficiario')
            };

            fetch('../../api/salvar_agenda.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(text || 'Erro na resposta da API');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    Swal.fire('Sucesso!', 'Agenda criada com sucesso.', 'success')
                    .then(() => {
                        window.close();
                    });
                } else {
                    throw new Error(data?.message || 'Erro ao criar agenda');
                }
            })
            .catch(error => {
                console.error('Erro detalhado:', error);
                Swal.fire('Erro', error.message || 'Erro ao conectar com o servidor', 'error');
            });
        }

        function sair() {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Você será desconectado do sistema!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, sair!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../../pages/logout.php';
                }
            });
        }
    </script>
</body>
</html> 
