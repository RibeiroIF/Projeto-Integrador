function filtrarSPA(event) {
    event.preventDefault(); 

    const form = document.getElementById('form-pesquisa-avancada');
    const formData = new FormData(form);
    
    const params = new URLSearchParams(formData).toString();

    fetch('buscar-anuncios-ajax.php?' + params)
        .then(response => response.text())
        .then(html => {
            document.querySelector('#tela-pesquisa .grid-anuncios').innerHTML = html;
        })
        .catch(error => console.error('Erro ao filtrar:', error));
}

function limparFiltrosSPA() {
    const form = document.getElementById('form-form-pesquisa-avancada');
    if(form) form.reset();
    
    filtrarSPA(new Event('submit'));
}