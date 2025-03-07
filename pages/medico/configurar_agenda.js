function carregarMedicos() {
    fetch('../../controller/ajaxCarregarMedicos.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao carregar médicos');
            }
            return response.json();
        })
        .then(data => {
            const selectMedicos = document.getElementById('medico');
            selectMedicos.innerHTML = '<option value="">Selecione o médico</option>';

            // Verifica se os dados são um array
            if (Array.isArray(data)) {
                data.forEach(medico => {
                    const option = document.createElement('option');
                    option.value = medico.id;
                    option.textContent = medico.nome;
                    selectMedicos.appendChild(option);
                });
            } else if (data && typeof data === 'object') {
                // Se for um objeto, converte para array
                const medicosArray = Object.values(data);
                medicosArray.forEach(medico => {
                    const option = document.createElement('option');
                    option.value = medico.id;
                    option.textContent = medico.nome;
                    selectMedicos.appendChild(option);
                });
            } else {
                console.error('Dados recebidos não são um array ou objeto:', data);
                const option = document.createElement('option');
                option.textContent = 'Erro ao carregar médicos';
                selectMedicos.appendChild(option);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            const selectMedicos = document.getElementById('medico');
            selectMedicos.innerHTML = '<option value="">Erro ao carregar médicos</option>';
        });
} 