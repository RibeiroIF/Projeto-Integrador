function verDetalhes(idAnuncio) {
    const modal = document.getElementById('modal-detalhes');
    const carregando = document.getElementById('modal-carregando');
    const dadosProduto = document.getElementById('modal-dados-produto');

    modal.style.display = 'flex';
    carregando.style.display = 'block';
    dadosProduto.style.display = 'none';

    fetch(`buscar-detalhes.php?id=${idAnuncio}`)
        .then(response => response.json())
        .then(dados => {
            if (dados.status === 'sucesso') {
                document.getElementById('detalhe-titulo').innerText = dados.titulo;
                document.getElementById('detalhe-categoria').innerText = dados.categoria;
                document.querySelector('#modal-dados-produto .preco').innerText = `R$ ${dados.preco_formatado}`;
                document.getElementById('detalhe-descricao').innerText = dados.descricao || 'Nenhuma descrição informada.';
                
                // 🟢 INSERIDO AQUI: Lógica que analisa o status vindo do banco e estiliza o HTML
                const statusElement = document.getElementById('detalhe-status');
                if (dados.status_anuncio === 'VENDIDO' || dados.status === 'VENDIDO') { 
                    statusElement.innerHTML = "Status: <span style='color: #dc3545;'>Vendido 🔴</span>";
                } else if (dados.status_anuncio === 'EM NEGOCIACAO' || dados.status === 'EM NEGOCIACAO') {
                    statusElement.innerHTML = "Status: <span style='color: #ffc107;'>Em Negociação 🟡</span>";
                } else {
                    statusElement.innerHTML = "Status: <span style='color: #28a745;'>Disponível 🟢</span>";
                }

                const imgElement = document.getElementById('detalhe-imagem');
                if (dados.imagem !== '') {
                    imgElement.src = dados.imagem;
                    imgElement.style.display = 'block';
                } else {
                    imgElement.style.display = 'none';
                }

                carregando.style.display = 'none';
                dadosProduto.style.display = 'block';
            } else {
                alert('Erro ao carregar detalhes: ' + dados.mensagem);
                fecharModalDetalhes();
            }
        })
        .catch(erro => {
            console.error('Erro na requisição:', erro);
            alert('Erro de conexão ao buscar detalhes do item.');
            fecharModalDetalhes();
        });
}

function fecharModalDetalhes() {
    document.getElementById('modal-detalhes').style.display = 'none';
}

// Fechar o modal caso o usuário clique fora da caixa branca de conteúdo
window.onclick = function(event) {
    const modal = document.getElementById('modal-detalhes');
    if (event.target === modal) {
        fecharModalDetalhes();
    }
}