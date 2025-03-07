<?php
session_start();
date_default_timezone_set('America/Sao_Paulo'); // Configura o fuso horário para Brasília
require '../../includes/config.php';

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Receber parâmetros da URL
$unidade_id = $_GET['unidade'] ?? null;
$especialidade_id = $_GET['especialidade'] ?? null;
$medico_id = $_GET['medico'] ?? null;
$no_menu = $_GET['no_menu'] ?? 0;

// Buscar nome do médico
$medico_nome = '';
if ($medico_id) {
    $stmt = $pdo->prepare("SELECT nome FROM medicos WHERE id = ?");
    $stmt->execute([$medico_id]);
    $medico = $stmt->fetch();
    $medico_nome = $medico['nome'] ?? '';
}

// Buscar nome da especialidade
$especialidade_nome = '';
if ($especialidade_id) {
    $stmt = $pdo->prepare("SELECT nome FROM especialidades WHERE id = ?");
    $stmt->execute([$especialidade_id]);
    $especialidade = $stmt->fetch();
    $especialidade_nome = $especialidade['nome'] ?? '';
}

// Ao gerar a guia, use a função date() com o formato completo
$data_emissao = date('Y-m-d H:i:s');  // Isso captura a data e hora atuais

// Gera o número da guia automaticamente
$numero_guia = gerarNumeroGuia();

function gerarNumeroGuia() {
    return 'GUIA-' . date('YmdHis') . '-' . rand(1000, 9999);
}

function gerarVigencia() {
    return [
        'inicio' => date('Y-m-d'), // Exemplo: data atual
        'fim' => date('Y-m-d', strtotime('+1 month')) // Exemplo: 1 mês a partir de hoje
    ];
}

function calcularHorariosConsulta($periodo_inicio, $periodo_fim, $duracao_consulta) {
    $horarios = [];
    $current_time = strtotime($periodo_inicio);
    $end_time = strtotime($periodo_fim);
    
    while ($current_time < $end_time) {
        $horarios[] = date('H:i', $current_time);
        $current_time = strtotime("+$duracao_consulta minutes", $current_time);
    }
    
    return $horarios;
}

function gerarAgendaCompleta($dias_semana, $vigencia_inicio, $vigencia_fim, $periodo_inicio, $periodo_fim, $duracao_consulta) {
    $dias_atendimento = explode(',', $dias_semana);
    $data_inicio = new DateTime($vigencia_inicio);
    $data_fim = new DateTime($vigencia_fim);
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($data_inicio, $interval, $data_fim->modify('+1 day'));
    
    $agenda_completa = [];
    $horarios_por_dia = calcularHorariosConsulta($periodo_inicio, $periodo_fim, $duracao_consulta);
    
    foreach ($period as $date) {
        $dia_semana = strtolower(substr($date->format('D'), 0, 3));
        if (in_array($dia_semana, $dias_atendimento)) {
            $agenda_completa[$date->format('Y-m-d')] = $horarios_por_dia;
        }
    }
    
    return $agenda_completa;
}

function distribuirVagas($dias_semana, $vigencia_inicio, $vigencia_fim, $periodo_inicio, $periodo_fim, $duracao_consulta, $quantidade_beneficiario) {
    $dias_atendimento = explode(',', $dias_semana);
    $data_inicio = new DateTime($vigencia_inicio);
    $data_fim = new DateTime($vigencia_fim);
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($data_inicio, $interval, $data_fim->modify('+1 day'));
    
    // Calcular o número de consultas por dia
    $horario_inicio = new DateTime($periodo_inicio);
    $horario_fim = new DateTime($periodo_fim);
    $intervalo_consultas = new DateInterval('PT' . $duracao_consulta . 'M');
    
    $consultas_por_dia = 0;
    $horario_atual = clone $horario_inicio;
    while ($horario_atual <= $horario_fim) {
        $consultas_por_dia++;
        $horario_atual->add($intervalo_consultas);
    }
    
    // Calcular o número de dias úteis
    $dias_uteis = 0;
    foreach ($period as $date) {
        $dia_semana = strtolower(substr($date->format('D'), 0, 3));
        if (in_array($dia_semana, $dias_atendimento)) {
            $dias_uteis++;
        }
    }
    
    // Calcular o número de consultas necessárias por dia
    $consultas_necessarias = ceil($quantidade_beneficiario / $dias_uteis);
    
    // Verificar se o número de consultas por dia é suficiente
    if ($consultas_por_dia < $consultas_necessarias) {
        throw new Exception("O período de atendimento não é suficiente para atender todos os beneficiários.");
    }
    
    // Distribuir as vagas
    $agendas = [];
    foreach ($period as $date) {
        $dia_semana = strtolower(substr($date->format('D'), 0, 3));
        if (in_array($dia_semana, $dias_atendimento)) {
            $horario_atual = clone $horario_inicio;
            for ($i = 0; $i < $consultas_necessarias; $i++) {
                $agendas[] = [
                    'data' => $date->format('Y-m-d'),
                    'horario_inicio' => $horario_atual->format('H:i'),
                    'horario_fim' => $horario_atual->add($intervalo_consultas)->format('H:i'),
                    'vagas_disponiveis' => 1
                ];
            }
        }
    }
    
    return $agendas;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Agenda Médica</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/criar_agenda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <?php if (!$no_menu): ?>
            <?php include '../../includes/menu_lateral.php'; ?>
        <?php endif; ?>
        <main class="content">
            <h1>Criar Agenda Médica</h1>
            
            <div class="configuracao-form">
                <form id="form-criar-agenda">
                    <div class="form-group">
                        <label for="medico">Médico Profissional de Saúde:</label>
                        <input type="text" id="medico" name="medico" value="<?= htmlspecialchars($medico_nome) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <div class="vigencia-container">
                            <label for="vigencia_inicio">Vigência:</label>
                            <input type="date" id="vigencia_inicio" name="vigencia_inicio" value="<?= date('Y-m-d', strtotime('today')) ?>" required>
                            <span class="vigencia-separator">até</span>
                            <input type="date" id="vigencia_fim" name="vigencia_fim" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Dia de Agendamento:</label>
                        <div class="dias-semana">
                            <button type="button" class="dia-btn" data-dia="seg">Seg</button>
                            <button type="button" class="dia-btn" data-dia="ter">Ter</button>
                            <button type="button" class="dia-btn" data-dia="qua">Qua</button>
                            <button type="button" class="dia-btn" data-dia="qui">Qui</button>
                            <button type="button" class="dia-btn" data-dia="sex">Sex</button>
                            <button type="button" class="dia-btn" data-dia="sab">Sáb</button>
                        </div>
                        <input type="hidden" id="dia_agendamento" name="dia_agendamento" required>
                    </div>

                    <div class="form-group">
                        <label for="periodo_atendimento">Período de Atendimento:</label>
                        <div class="time-range">
                            <input type="time" id="periodo_inicio" name="periodo_inicio" required>
                            <span>às</span>
                            <input type="time" id="periodo_fim" name="periodo_fim" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo:</label>
                        <select id="tipo" name="tipo" required>
                            <option value="hora_marcada">Hora Marcada</option>
                            <option value="outro_tipo">Outro Tipo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="duracao_consulta">Duração da Consulta (minutos):</label>
                        <input type="number" id="duracao_consulta" name="duracao_consulta" min="10" max="120" step="5" required>
                    </div>

                    <div class="form-group">
                        <label for="faixa_etaria">Faixa etária:</label>
                        <div class="age-range">
                            <input type="number" id="faixa_etaria_inicio" name="faixa_etaria_inicio" min="0" required>
                            <span>até</span>
                            <input type="number" id="faixa_etaria_fim" name="faixa_etaria_fim" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="quantidade_beneficiario">Quantidade de Beneficiário:</label>
                        <input type="number" id="quantidade_beneficiario" name="quantidade_beneficiario" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="quantidade_extra">Quantidade Extra:</label>
                        <input type="number" id="quantidade_extra" name="quantidade_extra" min="0" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                    </div>

                    <div class="form-group">
                        <label for="especialidade">Especialidade:</label>
                        <input type="text" id="especialidade" name="especialidade" value="<?= htmlspecialchars($especialidade_nome) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="procedimento">Procedimento:</label>
                        <select id="procedimento" name="procedimento" required>
                            <option value="">Selecione o procedimento</option>
                            <option value="50000560">50000560 - Consulta ambulatorial por nutricionista</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="button" id="btn-salvar" class="btn-primary">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                        <button type="button" class="btn-secondary" onclick="window.close()">
                            <i class="fas fa-times"></i> Fechar
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Seleção de dias da semana
        const diasButtons = document.querySelectorAll('.dia-btn');
        const diaAgendamentoInput = document.getElementById('dia_agendamento');
        let diasSelecionados = [];

        diasButtons.forEach(button => {
            button.addEventListener('click', () => {
                button.classList.toggle('active');
                const dia = button.dataset.dia;
                
                if (diasSelecionados.includes(dia)) {
                    diasSelecionados = diasSelecionados.filter(d => d !== dia);
                } else {
                    diasSelecionados.push(dia);
                }
                
                diaAgendamentoInput.value = diasSelecionados.join(',');
            });
        });

        // Validação da vigência
        document.getElementById('vigencia_fim').addEventListener('change', function() {
            const vigenciaInicio = document.getElementById('vigencia_inicio').value;
            const vigenciaFim = this.value;

            if (new Date(vigenciaFim) < new Date(vigenciaInicio)) {
                Swal.fire('Erro', 'A data final da vigência não pode ser anterior à data inicial.', 'error');
                this.value = vigenciaInicio;
            }
        });

        // Atualizar a função validarCampos
        function validarCampos() {
            const camposObrigatorios = [
                { id: 'vigencia_inicio', nome: 'Vigência Início' },
                { id: 'vigencia_fim', nome: 'Vigência Fim' },
                { id: 'dia_agendamento', nome: 'Dia de Agendamento' },
                { id: 'periodo_inicio', nome: 'Período Início' },
                { id: 'periodo_fim', nome: 'Período Fim' },
                { id: 'duracao_consulta', nome: 'Duração da Consulta' },
                { id: 'quantidade_beneficiario', nome: 'Quantidade de Beneficiário' },
                { id: 'procedimento', nome: 'Procedimento' },
                { id: 'tipo', nome: 'Tipo' },
                { id: 'faixa_etaria_inicio', nome: 'Faixa Etária Início' },
                { id: 'faixa_etaria_fim', nome: 'Faixa Etária Fim' }
            ];

            let camposFaltantes = [];

            // Verificar se todos os campos obrigatórios estão preenchidos
            for (const campo of camposObrigatorios) {
                const elemento = document.getElementById(campo.id);
                if (!elemento.value || (typeof elemento.value === 'string' && elemento.value.trim() === '')) {
                    camposFaltantes.push(campo);
                }
            }

            // Verificar se a faixa etária é válida
            const faixaInicio = parseInt(document.getElementById('faixa_etaria_inicio').value);
            const faixaFim = parseInt(document.getElementById('faixa_etaria_fim').value);
            const faixaValida = !isNaN(faixaInicio) && !isNaN(faixaFim) && faixaInicio >= 0 && faixaFim >= 0 && faixaInicio <= faixaFim;

            if (!faixaValida) {
                camposFaltantes.push({ id: 'faixa_etaria_inicio', nome: 'Faixa Etária' });
            }

            // Exibir SweetAlert se houver campos faltantes
            if (camposFaltantes.length > 0) {
                const mensagemErro = camposFaltantes.map(campo => campo.nome).join(', ');
                Swal.fire({
                    icon: 'error',
                    title: 'Campos obrigatórios',
                    text: `Por favor, preencha os seguintes campos: ${mensagemErro}`,
                    confirmButtonText: 'OK'
                });
            }

            return { valido: camposFaltantes.length === 0 && faixaValida, camposFaltantes };
        }

        // Atualizar a função salvarAgenda
        function salvarAgenda() {
            // Remover a classe 'campo-faltante' de todos os campos antes de validar
            const camposObrigatorios = document.querySelectorAll('#form-criar-agenda input, #form-criar-agenda select');
            camposObrigatorios.forEach(campo => campo.classList.remove('campo-faltante'));

            const validacao = validarCampos();
            
            if (!validacao.valido) {
                const mensagemErro = validacao.camposFaltantes.map(campo => campo.nome).join(', ');
                Swal.fire({
                    icon: 'error',
                    title: 'Campos obrigatórios',
                    text: `Por favor, preencha os seguintes campos: ${mensagemErro}`,
                    confirmButtonText: 'OK'
                });
                
                // Adicionar a classe 'campo-faltante' aos campos faltantes
                validacao.camposFaltantes.forEach(campo => {
                    const elemento = document.getElementById(campo.id);
                    if (elemento) {
                        elemento.classList.add('campo-faltante');
                    }
                });

                // Focar no primeiro campo faltante
                if (validacao.camposFaltantes.length > 0) {
                    const primeiroCampo = document.getElementById(validacao.camposFaltantes[0].id);
                    primeiroCampo.focus();
                }
                return;
            }

            const formData = new FormData(document.getElementById('form-criar-agenda'));

            const data = {
                medico_id: <?= $medico_id ?>,
                especialidade_id: <?= $especialidade_id ?>,
                unidade_id: <?= $unidade_id ?>,
                vigencia_inicio: formData.get('vigencia_inicio'),
                vigencia_fim: formData.get('vigencia_fim'),
                dias_semana: formData.get('dia_agendamento'),
                periodo_inicio: formData.get('periodo_inicio'),
                periodo_fim: formData.get('periodo_fim'),
                duracao: formData.get('duracao_consulta'),
                quantidade_beneficiario: formData.get('quantidade_beneficiario'),
                procedimento: formData.get('procedimento'),
                especialidade_nome: '<?= $especialidade_nome ?>',
                tipo: formData.get('tipo'),
                faixa_etaria_inicio: parseInt(formData.get('faixa_etaria_inicio')) || 0,
                faixa_etaria_fim: parseInt(formData.get('faixa_etaria_fim')),
                total_vagas: formData.get('quantidade_beneficiario'),
                vagas_disponiveis: formData.get('quantidade_beneficiario')
            };

            console.log('Dados a serem enviados:', data); // Adicionado para depuração

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

        // Atualizar o listener do botão salvar
        document.getElementById('btn-salvar').addEventListener('click', function(e) {
            e.preventDefault();
            salvarAgenda();
        });

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
                    window.location.href = '../../includes/logout.php';
                }
            });
        }
    </script>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dias_semana = $_POST['dia_agendamento'];
        $vigencia_inicio = $_POST['vigencia_inicio'];
        $vigencia_fim = $_POST['vigencia_fim'];
        $periodo_inicio = $_POST['periodo_inicio'];
        $periodo_fim = $_POST['periodo_fim'];
        $duracao_consulta = $_POST['duracao_consulta'];
        
        $agenda_completa = gerarAgendaCompleta($dias_semana, $vigencia_inicio, $vigencia_fim, $periodo_inicio, $periodo_fim, $duracao_consulta);
        
        // Salvar a agenda no banco de dados ou exibir
        $_SESSION['agenda_gerada'] = $agenda_completa;
        header('Location: visualizar_agenda.php');
        exit();
    }
    ?>
</body>
</html> 