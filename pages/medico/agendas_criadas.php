function adicionarAgendaCriada(agenda) {
    const tabela = document.getElementById('tabela-agendas');
    const novaLinha = document.createElement('tr');
    
    novaLinha.innerHTML = `
        <td>${agenda.vigencia}</td>
        <td>${agenda.horario}</td>
        <td>${agenda.dias}</td>
        <td>${agenda.especialidade}</td>
        <td>${agenda.procedimento}</td>
        <td><span class="status ativo">${agenda.status}</span></td>
    `;
    
    tabela.appendChild(novaLinha);
}

<table id="tabela-agendas">
    <thead>
        <tr>
            <th>Vigência</th>
            <th>Horário</th>
            <th>Dias</th>
            <th>Especialidade</th>
            <th>Procedimento</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <!-- As agendas serão adicionadas aqui dinamicamente -->
    </tbody>
</table>

<script>
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