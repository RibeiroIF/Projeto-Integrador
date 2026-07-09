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
    const form = document.getElementById('form-pesquisa-avancada');
    if (form) {
        form.querySelectorAll('input:not([type="hidden"])').forEach(input => {
            input.value = '';
        });
        const select = form.querySelector('select[name="status_item"]');
        if (select) {
            select.value = 'todos';
        }
        if (window.history.pushState) {
            const novaUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.pushState({ path: novaUrl }, '', novaUrl);
        }
        const eventoSubmit = new Event('submit', { cancelable: true });
        form.dispatchEvent(eventoSubmit); 
    }
}