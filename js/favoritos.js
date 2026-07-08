function alternarFavorito(botao) {
    const idAnuncio = botao.getAttribute('data-id');
    
    const formData = new FormData();
    formData.append('id_anuncio', idAnuncio);

    fetch('../php/tratar-favorito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(dados => {
        if (dados.status === 'adicionado') {
            const botoesCorrespondentes = document.querySelectorAll(`.btn-favorito[data-id="${idAnuncio}"]`);
            botoesCorrespondentes.forEach(btn => {
                btn.classList.add('favoritado');
                btn.innerHTML = '❤️';
                btn.title = 'Remover dos favoritos';
            });

            const abaFavoritos = document.querySelector('#tela-favoritos .grid-anuncios');
            if (abaFavoritos) {
                const avisoVazio = abaFavoritos.querySelector('.aviso-vazio');
                if (avisoVazio) avisoVazio.remove();

                const cardOriginal = botao.closest('.card-anuncio');
                const cardClonado = cardOriginal.cloneNode(true);

                cardClonado.setAttribute('data-id-anuncio', idAnuncio);

                const botaoNoClone = cardClonado.querySelector('.btn-favorito');
                if (botaoNoClone) botaoNoClone.remove();

                const badgeNoClone = cardClonado.querySelector('.badge-campus');
                if (badgeNoClone) badgeNoClone.innerHTML = 'Favoritado ❤️';

                abaFavoritos.insertBefore(cardClonado, abaFavoritos.firstChild);
            }
        } 
        else if (dados.status === 'removido') {
            const botoesCorrespondentes = document.querySelectorAll(`.btn-favorito[data-id="${idAnuncio}"]`);
            botoesCorrespondentes.forEach(btn => {
                btn.classList.remove('favoritado');
                btn.innerHTML = '🤍';
                btn.title = 'Favoritar item';
            });
            
            const abaFavoritos = document.querySelector('#tela-favoritos .grid-anuncios');
            if (abaFavoritos) {
                const cardNoFavoritos = abaFavoritos.querySelector(`[data-id-anuncio="${idAnuncio}"]`);
                if (cardNoFavoritos) {
                    cardNoFavoritos.style.transition = 'all 0.3s ease';
                    cardNoFavoritos.style.opacity = '0';
                    cardNoFavoritos.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        cardNoFavoritos.remove();
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