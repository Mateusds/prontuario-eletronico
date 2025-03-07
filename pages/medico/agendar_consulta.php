<?php
session_start();
require '../../includes/config.php';

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Verificar se o paciente_id foi passado
if (!isset($_GET['paciente_id'])) {
    header('Location: agenda.php');
    exit();
}

$paciente_id = intval($_GET['paciente_id']);

// Buscar informações do paciente
$stmt = $pdo->prepare("SELECT nome_completo FROM pacientes WHERE id = ?");
$stmt->execute([$paciente_id]);
$paciente = $stmt->fetch();

if (!$paciente) {
    header('Location: agenda.php'); // Redireciona se o paciente não for encontrado
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agendar Consulta</title>
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

        .btn-agendar {
            background-color: #28a745;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-agendar:hover {
            background-color: #218838;
        }

        /* Adicionar estilos para a tabela */
        .tabela-agendas {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 20px;
        }
        
        .tabela-agendas th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .tabela-agendas td {
            padding: 12px 15px;
            background-color: white;
            border-bottom: 1px solid #e9ecef;
        }
        
        .tabela-agendas tr {
            transition: all 0.2s ease;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .tabela-agendas tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .tabela-agendas td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        
        .tabela-agendas td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        
        .tabela-agendas .vaga-info {
            color: #28a745;
            font-weight: 500;
        }
        
        .tabela-agendas .na-info {
            color: #6c757d;
            font-style: italic;
        }

        .acordeon {
            border: 1px solid #ccc;
            margin: 10px 0;
            border-radius: 5px;
            background-color: #f9f9f9; /* Cor de fundo do acordeon */
            overflow: hidden; /* Esconde o conteúdo que excede a altura */
        }

        .acordeon-header {
            background-color: #e9ecef; /* Cor de fundo do cabeçalho */
            padding: 10px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 5px 5px 0 0; /* Bordas arredondadas no topo */
            transition: background-color 0.3s ease; /* Efeito de transição */
            text-align: center; /* Centraliza o texto do cabeçalho */
        }

        .acordeon-header:hover {
            background-color: #d1d1d1; /* Cor de fundo ao passar o mouse */
        }

        .acordeon-content {
            display: block; /* Inicialmente visível */
            max-height: 0; /* Inicialmente oculto */
            transition: max-height 0.3s ease; /* Efeito de transição */
            overflow: hidden; /* Esconde o conteúdo que excede a altura */
        }

        .acordeon.active .acordeon-content {
            display: block; /* Exibe quando ativo */
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); /* Ajuste o tamanho mínimo conforme necessário */
            gap: 10px; /* Espaçamento entre os itens */
            margin-top: 10px; /* Margem superior */
        }

        .grid-item {
            background-color: #f8f9fa; /* Cor de fundo dos itens */
            padding: 10px; /* Padding interno */
            border-radius: 5px; /* Bordas arredondadas */
            text-align: center; /* Centraliza o texto */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombra leve */
        }

        .btn-agendar {
            background-color: #28a745; /* Cor de fundo */
            color: white; /* Cor do texto */
            padding: 4px 8px; /* Ajuste o tamanho do botão */
            border-radius: 5px; /* Bordas arredondadas */
            border: none; /* Sem borda */
            font-size: 14px; /* Tamanho da fonte */
            cursor: pointer; /* Cursor de mão ao passar o mouse */
            transition: background-color 0.3s ease; /* Efeito de transição */
            width: 100px; /* Defina uma largura fixa, ajuste conforme necessário */
        }

        .btn-agendar:hover {
            background-color: #218838; /* Cor de fundo ao passar o mouse */
        }

        .input-disabled {
            background-color: #e9ecef; /* Cor de fundo para indicar que está desabilitado */
            color: #6c757d; /* Cor do texto para indicar que está desabilitado */
            border: 1px solid #ced4da; /* Borda padrão */
            border-radius: 0.25rem; /* Bordas arredondadas */
            padding: 0.375rem 0.75rem; /* Padding padrão */
            width: 40%; /* Largura total */
            cursor: not-allowed; /* Cursor para indicar que não é editável */
        }
    </style>
</head>
<body>
    <div class="main-container">
        <main class="content">
            <h1>Agendar Consulta</h1>
            
            <div class="filtros">
                <div class="form-group">
                    <label for="paciente">Paciente:</label>
                    <input type="text" id="paciente" name="paciente" value="<?php echo htmlspecialchars($paciente['nome_completo']); ?>" class="input-disabled" disabled>
                </div>

                <div class="form-group">
                    <label for="especialidade">Especialidade:</label>
                    <select id="especialidade" name="especialidade"></select>
                </div>
                
                <div class="form-group">
                    <label for="medico">Médico:</label>
                    <select id="medico" name="medico"></select>
                </div>
            </div>

            <div class="lista-agendas">
                <div id="lista-agendas"></div>
            </div>
        </main>
    </div>

    <script>
        let especialidades = [];
        let medicos = [];
        let agendas = [];
        let paciente_id = <?php echo $paciente_id; ?>; // Definir o paciente_id diretamente no carregamento da página

        function carregarEspecialidades() {
            console.log('Iniciando carregamento de especialidades...');
            
            const apiUrl = '../../api/listar_especialidades.php';
            console.log('Fazendo requisição para:', apiUrl);
            
            fetch(apiUrl)
                .then(response => {
                    console.log('Resposta recebida:', response);
                    if (!response.ok) {
                        throw new Error(`Erro HTTP: ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Dados recebidos da API:', data);
                    
                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro na API',
                            html: `Erro ao carregar especialidades:<br><br>
                                   <b>Código:</b> ${data.code || 'N/A'}<br>
                                   <b>Mensagem:</b> ${data.message || 'Erro desconhecido'}<br>
                                   <b>Detalhes:</b> ${data.details || 'Nenhum detalhe adicional'}`,
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    if (!data.data || data.data.length === 0) {
                        const select = document.getElementById('especialidade');
                        select.innerHTML = '<option value="">Nenhuma especialidade disponível</option>';
                        return;
                    }

                    // Array para armazenar especialidades com agendas
                    const especialidadesComAgendas = [];

                    // Função recursiva para processar especialidades
                    const processarEspecialidade = (index) => {
                        if (index >= data.data.length) {
                            // Todas as especialidades foram processadas
                            const select = document.getElementById('especialidade');
                            select.innerHTML = '<option value="">Selecione uma especialidade</option>';
                            
                            if (especialidadesComAgendas.length === 0) {
                                select.innerHTML = '<option value="">Nenhuma especialidade com agendas disponíveis</option>';
                                return;
                            }

                            especialidadesComAgendas.forEach(especialidade => {
                                select.innerHTML += `<option value="${especialidade.id}">${especialidade.nome}</option>`;
                            });
                            return;
                        }

                        const especialidade = data.data[index];
                        fetch(`../../api/listar_agendas.php?especialidade_id=${especialidade.id}`)
                            .then(response => response.json())
                            .then(agendasData => {
                                if (agendasData.success && agendasData.data && agendasData.data.length > 0) {
                                    especialidadesComAgendas.push(especialidade);
                                }
                                // Processa a próxima especialidade
                                processarEspecialidade(index + 1);
                            })
                            .catch(error => {
                                console.error('Erro ao verificar agendas:', error);
                                // Continua processando mesmo com erro
                                processarEspecialidade(index + 1);
                            });
                    };

                    // Inicia o processamento
                    processarEspecialidade(0);
                })
                .catch(error => {
                    console.error('Erro ao carregar especialidades:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de Conexão',
                        html: `Não foi possível carregar as especialidades:<br><br>
                               <b>Erro:</b> ${error.message || 'Erro desconhecido'}<br>
                               <b>Detalhes:</b> Verifique sua conexão com a internet e tente novamente.`,
                        confirmButtonText: 'OK'
                    });
                });
        }

        function carregarMedicos(especialidade_id) {
            console.log('Carregando médicos para especialidade:', especialidade_id);
            
            // Limpar a lista de agendas
            const lista = document.getElementById('lista-agendas');
            lista.innerHTML = '<div class="na-info">Selecione um médico</div>';

            fetch(`../../api/listar_medicos.php?especialidade_id=${especialidade_id}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Médicos recebidos:', data);
                    const select = document.getElementById('medico');
                    select.innerHTML = '<option value="">Selecione um médico</option>';
                    
                    if (data.success && data.data.length > 0) {
                        data.data.forEach(medico => {
                            select.innerHTML += `<option value="${medico.id}">${medico.nome}</option>`;
                        });
                    } else {
                        select.innerHTML = '<option value="">Nenhum médico disponível</option>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar médicos:', error);
                });
        }

        function calcularVagas(agenda, quantidadeBeneficiarios) {
            const dataInicio = new Date(agenda.vigencia_inicio);
            const dataFim = new Date(agenda.vigencia_fim);
            const diasSemana = agenda.dias_semana.toLowerCase().split(',');

            let totalDiasDisponiveis = 0;

            // Calcular o total de dias disponíveis dentro do período de vigência
            for (let data = new Date(dataInicio); data <= dataFim; data.setDate(data.getDate() + 1)) {
                const diaSemana = data.toLocaleDateString('pt-BR', { weekday: 'short' })
                    .toLowerCase()
                    .replace('.', '');
                
                if (diasSemana.includes(diaSemana)) {
                    totalDiasDisponiveis++;
                }
            }

            // Usar o valor de vagas_disponiveis do banco de dados
            const vagasDisponiveis = agenda.vagas_disponiveis;

            // Calcular vagas por dia
            const vagasPorDia = Math.floor(vagasDisponiveis / totalDiasDisponiveis);
            return vagasPorDia;
        }

        function calcularHorariosConsulta(periodo_inicio, periodo_fim, duracao) {
            const horarios = [];
            let horarioAtual = new Date(`1970-01-01T${periodo_inicio}`);
            const horarioFim = new Date(`1970-01-01T${periodo_fim}`);

            // Adicione logs para depuração
            console.log('Início do período:', horarioAtual);
            console.log('Fim do período:', horarioFim);
            console.log('Duração da consulta (minutos):', duracao);

            while (horarioAtual <= horarioFim) {
                // Verifique se o horário atual está dentro do intervalo desejado
                if (horarioAtual.getHours() >= 14 && horarioAtual.getHours() < 21) { // Exclui 21:00
                    horarios.push(horarioAtual.toTimeString().substring(0, 5));
                }
                horarioAtual.setMinutes(horarioAtual.getMinutes() + duracao); // Usar a duração da consulta
            }

            // Adicione um log para verificar os horários gerados
            console.log('Horários disponíveis:', horarios);
            return horarios;
        }

        // Função para obter o nome do mês
        function obterNomeMes(mes) {
            const meses = [
                'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ];
            return meses[mes - 1]; // Meses começam em 0
        }

        function carregarAgendas(medico_id) {
            console.log('Carregando agendas para médico:', medico_id);
            
            const lista = document.getElementById('lista-agendas');
            lista.innerHTML = '<div class="na-info">Carregando agendas...</div>';

            const quantidadeBeneficiarios = 100; // Substitua por valor real

            fetch(`../../api/listar_agendas.php?medico_id=${medico_id}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Agendas recebidas:', data);
                    lista.innerHTML = '';

                    if (!data.success || !data.data || data.data.length === 0) {
                        lista.innerHTML = `<div class="na-info">Nenhuma agenda disponível</div>`;
                        return;
                    }

                    // Agrupar agendas por mês
                    const agendasPorMes = {};

                    data.data.forEach(agenda => {
                        const dataInicio = new Date(agenda.vigencia_inicio);
                        const dataFim = new Date(agenda.vigencia_fim);
                        
                        // Obter os meses de vigência
                        const mesInicio = dataInicio.getMonth() + 1; // +1 porque os meses começam em 0
                        const mesFim = dataFim.getMonth() + 1;
                        const ano = dataInicio.getFullYear();

                        // Agrupar por todos os meses entre o início e o fim
                        for (let mes = mesInicio; mes <= mesFim; mes++) {
                            const mesAno = `${obterNomeMes(mes)}/${ano}`; // Formato Nome do Mês/AAAA

                            // Log para verificar as datas
                            console.log(`Processando agenda: ${agenda.id}, Vigência: ${agenda.vigencia_inicio} a ${agenda.vigencia_fim}, Mês/Ano: ${mesAno}`);

                            // Verifique se o mês já existe no objeto
                            if (!agendasPorMes[mesAno]) {
                                agendasPorMes[mesAno] = [];
                            }

                            // Calcular vagas disponíveis
                            const vagasDisponiveis = agenda.vagas_disponiveis; // Use o valor diretamente do banco
                            const duracaoConsulta = agenda.duracao; // Obter a duração da consulta do banco de dados
                            const horariosDisponiveis = calcularHorariosConsulta(agenda.periodo_inicio, agenda.periodo_fim, duracaoConsulta); // Usar a duração da consulta

                            // Adicionar cada horário disponível ao mês correspondente
                            horariosDisponiveis.forEach(horario => {
                                agendasPorMes[mesAno].push({
                                    data: formatarData(dataInicio),
                                    horario: horario,
                                    agenda_id: agenda.id // Adicionando o ID da agenda para referência
                                });
                            });
                        }
                    });

                    // Log para verificar os meses agrupados
                    console.log('Agendas agrupadas por mês:', agendasPorMes);

                    // Criar acordeon
                    for (const mesAno in agendasPorMes) {
                        const divMes = document.createElement('div');
                        divMes.classList.add('acordeon');

                        const header = document.createElement('div');
                        header.classList.add('acordeon-header');
                        header.innerText = mesAno;
                        header.onclick = () => {
                            const content = divMes.querySelector('.acordeon-content');
                            const isActive = divMes.classList.toggle('active');

                            // Efeito de transição
                            if (isActive) {
                                content.style.maxHeight = content.scrollHeight + "px"; // Define a altura máxima para abrir
                            } else {
                                content.style.maxHeight = null; // Reseta a altura para fechar
                            }
                        };

                        const content = document.createElement('div');
                        content.classList.add('acordeon-content');
                        content.style.maxHeight = null; // Inicialmente oculto

                        // Criar um layout em grid para os horários
                        const gridContainer = document.createElement('div');
                        gridContainer.classList.add('grid-container');

                        agendasPorMes[mesAno].forEach(item => {
                            const gridItem = document.createElement('div');
                            gridItem.classList.add('grid-item');
                            gridItem.innerHTML = `${item.data} ${item.horario} 
                            <button class="btn-agendar" onclick="agendarConsulta(${item.agenda_id}, paciente_id, '${item.horario}')">Agendar</button>`;
                            gridContainer.appendChild(gridItem);
                        });

                        content.appendChild(gridContainer);
                        divMes.appendChild(header);
                        divMes.appendChild(content);
                        lista.appendChild(divMes);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar agendas:', error);
                    lista.innerHTML = `<div class="na-info">Erro ao carregar agendas</div>`;
                });
        }

        // Função para formatar o horário de fim
        function formatarHorarioFim(horarioInicio, duracao) {
            const horario = new Date(`1970-01-01T${horarioInicio}`);
            horario.setMinutes(horario.getMinutes() + duracao);
            return horario.toTimeString().substring(0, 5);
        }

        // Função para formatar datas no formato DD/MM/YYYY
        function formatarData(data) {
            if (!data) return 'N/A'; // Se a data for nula ou indefinida, retorna "N/A"
            const date = new Date(data);
            if (isNaN(date)) return 'N/A'; // Se a data for inválida, retorna "N/A"
            
            const dia = String(date.getDate()).padStart(2, '0');
            const mes = String(date.getMonth() + 1).padStart(2, '0'); // Mês começa em 0
            const ano = date.getFullYear();
            
            return `${dia}/${mes}/${ano}`;
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', () => {
            carregarEspecialidades();
            
            const especialidadeSelect = document.getElementById('especialidade');
            const medicoSelect = document.getElementById('medico');
            const pacienteSelect = document.getElementById('paciente');

            if (especialidadeSelect) {
                especialidadeSelect.addEventListener('change', (e) => {
                    if (e.target.value) {
                        carregarMedicos(e.target.value);
                    }
                });
            }

            if (medicoSelect) {
                medicoSelect.addEventListener('change', (e) => {
                    if (e.target.value) {
                        carregarAgendas(e.target.value);
                    }
                });
            }

            if (pacienteSelect) {
                pacienteSelect.addEventListener('change', (e) => {
                    paciente_id = e.target.value; // Atribui o valor selecionado
                    console.log('ID do Paciente:', paciente_id); // Verifique se o ID está correto
                });
            }
        });

        function agendarConsulta(agenda_id, horario) {
        const medico_id = document.getElementById('medico').value; // Obter o ID do médico selecionado
        const data_consulta = new Date().toISOString().split('T')[0]; // Obter a data atual no formato YYYY-MM-DD
        const data_hora_consulta = `${data_consulta} ${horario}:00`; // Combina a data e o horário
        const data_hora_consulta_codificada = encodeURIComponent(data_hora_consulta); // Codifica o valor para a URL

        console.log('ID do Paciente:', paciente_id); // Verifique o ID do paciente
        console.log('ID do Médico:', medico_id); // Verifique o ID do médico
        console.log('Data e Hora da Consulta:', data_hora_consulta); // Verifique a data e o horário da consulta

        Swal.fire({
            title: 'Confirmar Agendamento',
            text: "Deseja realmente agendar esta consulta?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Sim, agendar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`../../api/agendar_consulta.php?agenda_id=${agenda_id}&paciente_id=${paciente_id}&medico_id=${medico_id}&data_consulta=${data_hora_consulta_codificada}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Erro na resposta da API');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        Swal.fire('Sucesso!', 'Consulta agendada com sucesso.', 'success')
                        .then(() => {
                            window.close();
                            window.opener.location.reload();
                        });
                    } else {
                        throw new Error(data?.message || 'Erro ao agendar consulta');
                    }
                })
                .catch(error => {
                    console.error('Erro detalhado:', error);
                    Swal.fire('Erro', error.message || 'Não foi possível agendar a consulta. Verifique sua conexão com a internet.', 'error');
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

        // Quando você carrega os pacientes
        function carregarPacientes() {
            console.log('Carregando pacientes...');
            // ... lógica para carregar pacientes ...
        }
    </script>
</body>
</html> 