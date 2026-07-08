let idAnuncioAtivo = null;

function verDetalhes(idAnuncio, isAdmin = false) {
    idAnuncioAtivo = idAnuncio; 
    
    const modal = document.getElementById('modal-detalhes');
    const carregando = document.getElementById('modal-carregando');
    const dadosProduto = document.getElementById('modal-dados-produto');

    modal.style.display = 'flex';
    carregando.style.display = 'block';
    dadosProduto.style.display = 'none';

    const secaoAvaliar = document.querySelector('.rating-stars')?.parentElement;
    const btnDenunciar = document.querySelector("button[onclick='abrirJanelaDenuncia()']");
    const secaoAdmin = document.getElementById('secao-admin-excluir');

    if (isAdmin) {
        if(secaoAvaliar) secaoAvaliar.style.display = 'none';
        if(btnDenunciar) btnDenunciar.style.display = 'none';
        if(secaoAdmin) secaoAdmin.style.display = 'block';
    } else {
        if(secaoAvaliar) secaoAvaliar.style.display = 'block';
        if(btnDenunciar) btnDenunciar.style.display = 'block';
        if(secaoAdmin) secaoAdmin.style.display = 'none';
    }

    fetch(`buscar-detalhes.php?id=${idAnuncio}`)
        .then(response => response.json())
        .then(dados => {
            if (dados.status === 'sucesso') {
                document.getElementById('detalhe-titulo').innerText = dados.titulo;
                document.getElementById('detalhe-categoria').innerText = dados.categoria;
                document.querySelector('#modal-dados-produto .preco').innerText = `R$ ${dados.preco_formatado}`;
                document.getElementById('detalhe-descricao').innerText = dados.descricao || 'Nenhuma descrição informada.';
                
                document.getElementById('detalhe-media-nota').innerText = dados.media_nota;
                document.getElementById('detalhe-total-notas').innerText = `(${dados.total_avaliacoes} avaliações)`;

                if (!isAdmin && typeof destacarEstrelas === "function") {
                    destacarEstrelas(dados.nota_usuario);
                }

                const containerDenuncias = document.getElementById('lista-denuncias');
                containerDenuncias.innerHTML = '';
                const listaDeDenuncias = dados.denuncias || []; 
                if (listaDeDenuncias.length === 0) {
                    containerDenuncias.innerHTML = '<p class="msg-sem-denuncia">Nenhuma denúncia registrada.</p>';
                } else {
                    listaDeDenuncias.forEach(d => {
                        containerDenuncias.innerHTML += `<div class="item-denuncia">
                            <strong>${d.nome || 'Anônimo'}</strong> <span class="data-denuncia">(${d.data || ''})</span>: ${d.comentario}
                        </div>`;
                    });
                }

                const whatsVendedor = dados.whatsapp || 'Não informado';
                document.getElementById('detalhe-whatsapp').innerText = whatsVendedor;
                const linkWhats = document.getElementById('btn-link-whatsapp');
                const numLimpo = whatsVendedor.replace(/\D/g, '');
                
                if (numLimpo.length >= 10) {
                    linkWhats.href = `https://wa.me/55${numLimpo}`;
                    linkWhats.style.display = 'inline-block';
                } else {
                    linkWhats.style.display = 'none';
                }

                const statusElement = document.getElementById('detalhe-status');
                if (dados.status_anuncio === 'VENDIDO') { 
                    statusElement.innerHTML = "Status: <span class='status-vendido'>Vendido</span>";
                } else if (dados.status_anuncio === 'EM NEGOCIACAO') {
                    statusElement.innerHTML = "Status: <span class='status-negociacao'>Em Negociação</span>";
                } else {
                    statusElement.innerHTML = "Status: <span class='status-disponivel'>Disponível</span>";
                }

                const imgElement = document.getElementById('detalhe-imagem');
                if (dados.imagem && dados.imagem !== '') {
                    imgElement.src = `../${dados.imagem}`;
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
        });
}

function confirmarExclusaoAdmin() {
    const campoTexto = document.getElementById('txt-motivo-exclusao');
    
    if (!campoTexto) {
        alert('Erro crítico: O campo de texto "txt-motivo-exclusao" não foi encontrado no HTML.');
        return;
    }

    const justificativa = campoTexto.value.trim();

    if (justificativa === "") {
        alert('Por favor, descreva o motivo da exclusão para enviar ao aluno.');
        return;
    }

    if (confirm('Tem certeza de que deseja banir/deletar este anúncio permanentemente?')) {
        const formData = new FormData();
        formData.append('deletar-anuncio-admin', 'true');
        formData.append('id_anuncio', idAnuncioAtivo);
        formData.append('motivo', justificativa);

        fetch('index-admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            alert('Anúncio deletado com sucesso!');
            
            if (typeof fecharModalDetalhes === "function") {
                fecharModalDetalhes();
            } else {
                document.getElementById('modal-detalhes').style.display = 'none';
            }

            window.location.reload(); 
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Houve um erro ao tentar deletar o anúncio.');
        });
    }
}

function abrirJanelaDenuncia() { document.getElementById('pop-denuncia').style.display = 'flex'; }
function fecharJanelaDenuncia() { 
    document.getElementById('pop-denuncia').style.display = 'none'; 
    document.getElementById('txt-denuncia').value = '';
}

function enviarAvaliacao(nota) {
    destacarEstrelas(nota); 

    fetch('salvar-interacao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `acao=avaliar&id_anuncio=${idAnuncioAtivo}&nota=${nota}`
    })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'sucesso') {
            verDetalhes(idAnuncioAtivo); 
            
            if (res.nova_media) {
                const elementoMediaPerfil = document.querySelector('.media-perfil-aside');
                if (elementoMediaPerfil) {
                    elementoMediaPerfil.innerText = res.nova_media;
                }
            }
        } else {
            alert(res.mensagem);
        }
    });
}

function enviarDenuncia() {
    const texto = document.getElementById('txt-denuncia').value.trim();
    if(!texto) return alert('Digite o motivo da denúncia.');

    fetch('salvar-interacao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `acao=denunciar&id_anuncio=${idAnuncioAtivo}&comentario=${encodeURIComponent(texto)}`
    })
    .then(r => r.json())
    .then(res => {
        alert(res.mensagem);
        if(res.status === 'sucesso') {
            fecharJanelaDenuncia();
            verDetalhes(idAnuncioAtivo);
        }
    });
}

function fecharModalDetalhes() {
    document.getElementById('modal-detalhes').style.display = 'none';
}

function destacarEstrelas(nota) {
    const estrelas = document.querySelectorAll('.rating-stars .star');
    estrelas.forEach((estrela, indice) => {
        if (indice < nota) {
            estrela.style.color = '#ff9800'; 
        } else {
            estrela.style.color = '#ccc'; 
        }
    });
}
/*aa*/

window.onclick = function(event) {
    const modal = document.getElementById('modal-detalhes');
    if (event.target === modal) {
        fecharModalDetalhes();
    }
}

function verMotivoDelecao(idAnuncio, motivo) {
    idAnuncioAtivo = idAnuncio; 

    const modal = document.getElementById('modal-detalhes');
    const carregando = document.getElementById('modal-carregando');
    const dadosProduto = document.getElementById('modal-dados-produto');

    modal.style.display = 'flex';
    carregando.style.display = 'none';
    dadosProduto.style.display = 'block';

    const secaoAvaliar = document.querySelector('.rating-stars')?.parentElement;
    const btnDenunciar = document.querySelector("button[onclick='abrirJanelaDenuncia()']");
    const secaoAdmin = document.getElementById('secao-admin-excluir');
    const linkWhats = document.getElementById('btn-link-whatsapp');

    if(secaoAvaliar) secaoAvaliar.style.display = 'none';
    if(btnDenunciar) btnDenunciar.style.display = 'none';
    if(secaoAdmin) secaoAdmin.style.display = 'none';
    if(linkWhats) linkWhats.style.display = 'none';

    document.getElementById('detalhe-titulo').innerText = "Anúncio Removido";
    document.getElementById('detalhe-categoria').innerText = "-";
    document.querySelector('#modal-dados-produto .preco').innerText = "";
    document.getElementById('detalhe-status').innerHTML = "Status: <span class='status-vendido'>Removido pela Moderação</span>";
    document.getElementById('detalhe-descricao').innerHTML = `
        <div class="alerta-remocao">
            <strong>Motivo da remoção:</strong><br><br>
            ${motivo}
        </div>
    `;

    const imgElement = document.getElementById('detalhe-imagem');
    if (imgElement) imgElement.style.display = 'none';
}