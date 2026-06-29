function filtrarSPA(event) {
    // 🛑 Impede o formulário de recarregar a página e ir para a index
    event.preventDefault(); 

    // Coleta todos os dados digitados no formulário
    const form = document.getElementById('form-pesquisa-avancada');
    const formData = new FormData(form);
    
    // Transforma os dados em parâmetros de URL (ex: busca=monitor&preco_min=10)
    const params = new URLSearchParams(formData).toString();

    // Faz a requisição em segundo plano para um arquivo PHP que só renderiza os cards
    fetch('buscar-anuncios-ajax.php?' + params)
        .then(response => response.text())
        .then(html => {
            // Substitui apenas o conteúdo do grid de anúncios da tela de pesquisa
            document.querySelector('#tela-pesquisa .grid-anuncios').innerHTML = html;
        })
        .catch(error => console.error('Erro ao filtrar:', error));
}

function limparFiltrosSPA() {
    const form = document.getElementById('form-form-pesquisa-avancada');
    if(form) form.reset(); // Limpa todos os inputs
    
    // Dispara o filtro vazio para trazer todos os anúncios de volta
    filtrarSPA(new Event('submit'));
}