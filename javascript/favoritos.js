function alternarFavorito(botao) {
    const idAnuncio = botao.getAttribute('data-id');
    
    // Prepara os dados para enviar via POST
    const formData = new FormData();
    formData.append('id_anuncio', idAnuncio);

    // Faz a requisição em segundo plano para o arquivo PHP
    // Nota: Ajuste o caminho se o seu arquivo 'tratar_favorito.php' estiver em outra pasta (ex: '../php/tratar_favorito.php')
    fetch('../php/tratar-favorito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(dados => {
            if (dados.status === 'adicionado') {
              botao.innerHTML = '❤️';
              botao.title = 'Remover dos favoritos';

              const abaFavoritos = document.querySelector('#tela-favoritos .grid-anuncios');
              if (abaFavoritos) {
                  const avisoVazio = abaFavoritos.querySelector('.aviso-vazio');
                  if (avisoVazio) avisoVazio.remove();

                  // Clona o card clicado
                  const cardOriginal = botao.closest('.card-anuncio');
                  const cardClonado = cardOriginal.cloneNode(true);

                  // Define o identificador no card clonado para exclusão futura
                  cardClonado.setAttribute('data-id-anuncio', idAnuncio);

                  // Remove o botão de favorito e troca o badge para virar apenas exibição
                  const botaoNoClone = cardClonado.querySelector('.btn-favorito');
                  if (botaoNoClone) botaoNoClone.remove();

                  const badgeNoClone = cardClonado.querySelector('.badge-campus');
                  if (badgeNoClone) badgeNoClone.innerHTML = 'Favoritado ❤️';

                  // Insere na aba de favoritos
                  abaFavoritos.insertBefore(cardClonado, abaFavoritos.firstChild);
              }
          } 
          else if (dados.status === 'removido') {
              botao.innerHTML = '🤍';
              botao.title = 'Favoritar item';
              
              // Se o usuário desfavoritou na Home ou Pesquisa, removemos o card correspondente lá da aba de favoritos
              const abaFavoritos = document.querySelector('#tela-favoritos .grid-anuncios');
              if (abaFavoritos) {
                  // Busca o card clonado pelo ID do anúncio
                  const cardNoFavoritos = abaFavoritos.querySelector(`[data-id-anuncio="${idAnuncio}"]`);
                  
                  if (cardNoFavoritos) {
                      cardNoFavoritos.style.transition = 'all 0.3s ease';
                      cardNoFavoritos.style.opacity = '0';
                      cardNoFavoritos.style.transform = 'scale(0.8)';
                      setTimeout(() => {
                          cardNoFavoritos.remove();
                          
                          // Se a lista esvaziou, exibe a mensagem de aviso
                          if (abaFavoritos.querySelectorAll('.card-anuncio').length === 0) {
                              abaFavoritos.innerHTML = "<p class='aviso-vazio' style='grid-column: 1/-1; text-align: center; color: #777; padding: 20px;'>Você ainda não favoritou nenhum anúncio.</p>";
                          }
                      }, 300);
                  }
              }
          } 
        else {
            alert('Ocorreu um erro ao processar a ação. Tente novamente.');
        }
    })
    .catch(erro => {
        console.error('Erro na requisição:', erro);
        alert('Erro de conexão com o servidor.');
    });
}