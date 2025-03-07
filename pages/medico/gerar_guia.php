<?php
session_start();
require '../../includes/config.php';

// Verificar se é médico ou admin
if ($_SESSION['user_type'] != 'medico' && $_SESSION['user_type'] != 'admin') {
    header('Location: ../../pages/login.php');
    exit();
}

// Buscar especialidades para o select
try {
    $stmt = $pdo->query("SELECT id, nome FROM especialidades");
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erro ao buscar especialidades: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerar Guia</title>
    <link rel="stylesheet" href="../../assets/css/gerar_guia.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    <div class="container">
        <main class="content">
            <button class="btn-sair" onclick="sair()">
                <i class="fas fa-sign-out-alt"></i> Sair
            </button>
            <h1>Gerar Guia Médica</h1>
            <form id="formGuia">
                <input type="hidden" name="geracao_manual" value="true">
                <input type="hidden" name="data_emissao" id="data_emissao">
                <div class="form-group">
                    <label for="cpf_paciente">CPF do Paciente:</label>
                    <input type="text" id="cpf_paciente" name="cpf_paciente" placeholder="Digite o CPF do paciente" required>
                    <div id="resultadoPaciente" class="resultado-paciente"></div>
                    <input type="hidden" id="paciente_id" name="paciente_id">
                </div>
                <div class="form-group">
                    <label for="tipo_atendimento">Tipo de Atendimento:</label>
                    <select id="tipo_atendimento" name="tipo_atendimento" required>
                        <option value="">Selecione o tipo de atendimento</option>
                        <option value="Consulta">Consulta</option>
                        <option value="Exame">Exame</option>
                        <option value="Procedimento">Procedimento</option>
                        <option value="Terapia">Terapia</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="especialidade">Especialidade:</label>
                    <select id="especialidade" name="especialidade" required>
                        <option value="">Selecione uma especialidade</option>
                        <?php foreach ($especialidades as $especialidade): ?>
                            <option value="<?= $especialidade['id'] ?>"><?= $especialidade['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="medico_responsavel">Médico Responsável:</label>
                    <select id="medico_responsavel" name="medico_responsavel" required>
                        <option value="">Selecione um médico</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="crm_medico">CRM do Médico:</label>
                    <input type="text" id="crm_medico" name="crm_medico" required>
                </div>
                <div class="form-group">
                    <label for="data_consulta">Data da Consulta:</label>
                    <input type="date" id="data_consulta" name="data_consulta" required>
                </div>
                <div class="form-group">
                    <label for="hora_consulta">Hora da Consulta:</label>
                    <input type="time" id="hora_consulta" name="hora_consulta" required>
                </div>
                <div class="form-group">
                    <label for="codigo_procedimento">Código do Procedimento:</label>
                    <input type="text" id="codigo_procedimento" name="codigo_procedimento" required>
                </div>
                <div class="form-group">
                    <label for="motivo_consulta">Motivo da Consulta:</label>
                    <textarea id="motivo_consulta" name="motivo_consulta"></textarea>
                </div>
                <div class="button-group">
                    <button type="button" onclick="window.history.back();" class="btn voltar">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                    <button type="submit" class="btn">
                        <i class="fas fa-file-medical"></i> Gerar Guia
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Função para formatar o CPF automaticamente
        function formatarCPF(cpf) {
            cpf = cpf.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona o primeiro ponto
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona o segundo ponto
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Adiciona o hífen
            return cpf;
        }

        // Aplicar a formatação ao digitar o CPF
        document.getElementById('cpf_paciente').addEventListener('input', function () {
            this.value = formatarCPF(this.value);

            const cpf = this.value.replace(/\D/g, ''); // Remove caracteres não numéricos

            // Verifica se o CPF tem 11 dígitos
            if (cpf.length === 11) {
                fetch(`../../api/buscar_paciente.php?cpf=${cpf}`)
                    .then(response => response.json())
                    .then(data => {
                        const resultado = document.getElementById('resultadoPaciente');
                        if (data.success) {
                            resultado.innerHTML = `
                                <p><strong>Paciente:</strong> ${data.paciente.nome_completo}</p>
                            `;
                            document.getElementById('paciente_id').value = data.paciente.id;
                        } else {
                            resultado.innerHTML = `<p style="color: red;">${data.message || 'Paciente não encontrado.'}</p>`;
                            document.getElementById('paciente_id').value = '';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar paciente:', error);
                    });
            } else {
                document.getElementById('resultadoPaciente').innerHTML = '';
                document.getElementById('paciente_id').value = '';
            }
        });

        // Função para carregar médicos de acordo com a especialidade selecionada
        document.getElementById('especialidade').addEventListener('change', function () {
            const especialidadeId = this.value;

            if (especialidadeId) {
                fetch(`../../api/buscar_medicos.php?especialidade_id=${especialidadeId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro na requisição');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const selectMedico = document.getElementById('medico_responsavel');
                        selectMedico.innerHTML = '<option value="">Selecione um médico</option>';

                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(medico => {
                                const option = document.createElement('option');
                                option.value = medico.id;
                                option.textContent = `${medico.nome}`;
                                selectMedico.appendChild(option);
                            });
                        } else {
                            selectMedico.innerHTML = '<option value="">Nenhum médico encontrado para esta especialidade</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar médicos:', error);
                        const selectMedico = document.getElementById('medico_responsavel');
                        selectMedico.innerHTML = '<option value="">Erro ao carregar médicos</option>';
                    });
            } else {
                document.getElementById('medico_responsavel').innerHTML = '<option value="">Selecione um médico</option>';
            }
        });

        // Função para buscar o CRM do médico selecionado
        document.getElementById('medico_responsavel').addEventListener('change', function() {
            const medicoId = this.value;

            if (medicoId) {
                fetch(`../../api/buscar_crm_medico.php?medico_id=${medicoId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('crm_medico').value = data.crm;
                        } else {
                            document.getElementById('crm_medico').value = '';
                            console.error('Erro ao buscar CRM:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar CRM:', error);
                        document.getElementById('crm_medico').value = '';
                    });
            } else {
                document.getElementById('crm_medico').value = '';
            }
        });

        // Função para enviar o formulário
        document.getElementById('formGuia').addEventListener('submit', function (e) {
            e.preventDefault();

            // Preenche o campo data_emissao com a data e hora atuais
            const dataEmissao = new Date().toISOString().slice(0, 19).replace('T', ' ');
            document.getElementById('data_emissao').value = dataEmissao;

            // Coleta todos os dados do formulário
            const formData = {
                paciente_id: document.getElementById('paciente_id').value,
                tipo_atendimento: document.getElementById('tipo_atendimento').value,
                especialidade: document.getElementById('especialidade').value,
                medico_responsavel: document.getElementById('medico_responsavel').value,
                crm_medico: document.getElementById('crm_medico').value,
                data_consulta: document.getElementById('data_consulta').value,
                hora_consulta: document.getElementById('hora_consulta').value,
                codigo_procedimento: document.getElementById('codigo_procedimento').value,
                motivo_consulta: document.getElementById('motivo_consulta').value,
                data_emissao: dataEmissao, // Inclui a data de emissão
                geracao_manual: document.querySelector('input[name="geracao_manual"]').value // Inclui o valor de geracao_manual
            };

            // Verifica se todos os campos obrigatórios estão preenchidos
            const requiredFields = ['paciente_id', 'tipo_atendimento', 'especialidade', 
                                  'medico_responsavel', 'crm_medico', 'data_consulta', 
                                  'hora_consulta', 'codigo_procedimento'];

            let isValid = true;
            requiredFields.forEach(field => {
                if (!formData[field]) {
                    isValid = false;
                    document.getElementById(field).classList.add('error');
                } else {
                    document.getElementById(field).classList.remove('error');
                }
            });

            if (!isValid) {
                Swal.fire('Erro', 'Por favor, preencha todos os campos obrigatórios.', 'error');
                return;
            }

            // Envia os dados
            fetch('../../api/gerar_guia.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Sucesso!', 'Guia gerada com sucesso.', 'success').then(() => {
                        const newWindow = window.open(
                            `gerar_guia_html.php?numero_guia=${data.numero_guia}`,
                            'GuiaMedica',
                            'width=800,height=600,scrollbars=yes,resizable=yes'
                        );
                        if (newWindow) {
                            newWindow.focus();
                        }
                        document.getElementById('formGuia').reset();
                        document.getElementById('resultadoPaciente').innerHTML = '';
                        document.getElementById('medico_responsavel').innerHTML = '<option value="">Selecione um médico</option>';
                    });
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao gerar guia', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Erro', 'Erro ao enviar dados.', 'error');
                console.error('Erro:', error);
            });
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
                    window.location.href = '../../pages/logout.php';
                }
            });
        }
    </script>
</body>
</html> 