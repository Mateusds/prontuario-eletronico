document.addEventListener('DOMContentLoaded', function() {
    // Converte o campo Nome para maiúsculas
    const nomeInput = document.querySelector('input[name="nome_completo"]');
    nomeInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase(); // Converte o texto para maiúsculas
    });

    // Máscara para CPF (111.039.024-66)
    const cpfInput = document.querySelector('input[name="cpf"]');
    cpfInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não for dígito

        // Aplica a formatação correta
        if (value.length > 9) {
            value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'); // Formato 111.039.024-66
        } else if (value.length > 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3'); // Formato 111.039.024
        } else if (value.length > 3) {
            value = value.replace(/(\d{3})(\d{3})/, '$1.$2'); // Formato 111.039
        } else if (value.length > 0) {
            value = value.replace(/(\d{3})/, '$1'); // Formato 111
        }

        e.target.value = value.substring(0, 14); // Limita a 14 caracteres (111.039.024-66)
    });

    // Máscara para telefone ((xx) xxxxx-xxxx)
    const telefoneInput = document.querySelector('input[name="telefone"]');
    telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não for dígito
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2'); // Coloca parênteses e espaço após o DDD
        value = value.replace(/(\d{5})(\d)/, '$1-$2'); // Coloca hífen após os cinco primeiros dígitos
        e.target.value = value.substring(0, 15); // Limita a 15 caracteres ((xx) xxxxx-xxxx)
    });

    // Máscara para CEP (xxxxx-xxx)
    const cepInput = document.querySelector('input[name="cep"]');
    cepInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não for dígito
        value = value.replace(/(\d{5})(\d)/, '$1-$2'); // Coloca hífen após os cinco primeiros dígitos
        e.target.value = value.substring(0, 9); // Limita a 9 caracteres (xxxxx-xxx)
    });

    // Função para buscar CEP
    const btnBuscarCep = document.getElementById('btn-buscar-cep');
    btnBuscarCep.addEventListener('click', function() {
        const cepInput = document.querySelector('input[name="cep"]');
        const cep = cepInput.value.replace(/\D/g, ''); // Remove caracteres não numéricos

        if (cep.length !== 8) {
            alert('CEP inválido. Digite um CEP com 8 dígitos.');
            return;
        }

        // Faz a requisição à API do ViaCEP
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (data.erro) {
                    alert('CEP não encontrado.');
                } else {
                    // Preenche os campos com os dados do CEP
                    document.querySelector('input[name="endereco"]').value = data.logradouro || '';
                    document.querySelector('input[name="bairro"]').value = data.bairro || '';
                    document.querySelector('input[name="cidade"]').value = data.localidade || '';
                    document.querySelector('input[name="estado"]').value = data.uf || '';
                }
            })
            .catch(error => {
                console.error('Erro ao buscar CEP:', error);
                alert('Erro ao buscar CEP. Tente novamente.');
            });
    });
}); 