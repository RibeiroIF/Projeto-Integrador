let idAnuncioAtivo = null; // Guarda o ID aberto no momento

function verDetalhes(idAnuncio, isAdmin = false) {
    idAnuncioAtivo = idAnuncio; 
    
    const modal = document.getElementById('modal-detalhes');
    const carregando = document.getElementById('modal-carregando');
    const dadosProduto = document.getElementById('modal-dados-produto');

    modal.style.display = 'flex';
    carregando.style.display = 'block';
    dadosProduto.style.display = 'none';

    // Captura os elementos que mudam conforme o tipo de usuário
    const secaoAvaliar = document.querySelector('.rating-stars')?.parentElement;
    const btnDenunciar = document.querySelector("button[onclick='abrirJanelaDenuncia()']");
    const secaoAdmin = document.getElementById('secao-admin-excluir');

    // Controla visibilidade com base no privilégio de Admin
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

                // Só pinta estrelas se o elemento estiver visível/existir
                if (!isAdmin && typeof destacarEstrelas === "function") {
                    destacarEstrelas(dados.nota_usuario);
                }

                // Lista de Denúncias
                const containerDenuncias = document.getElementById('lista-denuncias');
                containerDenuncias.innerHTML = '';
                const listaDeDenuncias = dados.denuncias || []; 
                if (listaDeDenuncias.length === 0) {
                    containerDenuncias.innerHTML = '<p style="color: #999; margin:0;">Nenhuma denúncia registrada.</p>';
                } else {
                    listaDeDenuncias.forEach(d => {
                        containerDenuncias.innerHTML += `<div style="border-bottom: 1px solid #eee; padding: 4px 0;">
                            <strong>${d.nome || 'Anônimo'}</strong> <span style="font-size:10px; color:#aaa;">(${d.data || ''})</span>: ${d.comentario}
                        </div>`;
                    });
                }

                // WhatsApp
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

                // Status
                const statusElement = document.getElementById('detalhe-status');
                if (dados.status_anuncio === 'VENDIDO') { 
                    statusElement.innerHTML = "Status: <span style='color: #dc3545;'>Vendido 🔴</span>";
                } else if (dados.status_anuncio === 'EM NEGOCIACAO') {
                    statusElement.innerHTML = "Status: <span style='color: #ffc107;'>Em Negociação 🟡</span>";
                } else {
                    statusElement.innerHTML = "Status: <span style='color: #28a745;'>Disponível 🟢</span>";
                }

                // Imagem
                const imgElement = document.getElementById('detalhe-imagem');
                if (dados.imagem && dados.imagem !== '') {
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
        });
}

// 🟢 NOVO: Ação simulada de exclusão pelo administrador
function confirmarExclusaoAdmin() {
    const justificativa = document.getElementById('txt-motivo-exclusao').value.trim();
    if (!justificativa) {
        return alert('Por favor, descreva o motivo da exclusão para enviar ao aluno.');
    }

    if (confirm('Tem certeza de que deseja banir/deletar este anúncio permanentemente?')) {
        // Envio do formulário tradicional simulado via JavaScript utilizando o POST já existente no PHP
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index_admin.php';

        const inputAcao = document.createElement('input');
        inputAcao.type = 'hidden';
        inputAcao.name = 'deletar-anuncio-admin';
        form.appendChild(inputAcao);

        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id_anuncio';
        inputId.value = idAnuncioAtivo;
        form.appendChild(inputId);

        // Preparado para o futuro: passa o motivo junto no POST
        const inputMotivo = document.createElement('input');
        inputMotivo.type = 'hidden';
        inputMotivo.name = 'motivo_exclusao';
        inputMotivo.value = justificativa;
        form.appendChild(inputMotivo);

        document.body.appendChild(form);
        form.submit();
    }
}

// Funções para controle do pop-up de denúncia
function abrirJanelaDenuncia() { document.getElementById('pop-denuncia').style.display = 'flex'; }
function fecharJanelaDenuncia() { 
    document.getElementById('pop-denuncia').style.display = 'none'; 
    document.getElementById('txt-denuncia').value = '';
}

// Envia a avaliação via AJAX
function enviarAvaliacao(nota) {
    // Pinta visualmente na hora do clique para dar feedback instantâneo
    destacarEstrelas(nota); 

    fetch('salvar-interacao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `acao=avaliar&id_anuncio=${idAnuncioAtivo}&nota=${nota}`
    })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'sucesso') {
            // Atualiza a média geral do cabeçalho sem fechar o modal
            verDetalhes(idAnuncioAtivo); 
        } else {
            alert(res.mensagem);
        }
    });
}

// Envia a denúncia via AJAX
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
            verDetalhes(idAnuncioAtivo); // Atualiza lista de comentários
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
            estrela.style.color = '#ff9800'; // Amarelo/Laranja para estrelas preenchidas
        } else {
            estrela.style.color = '#ccc'; // Cinza para vazias
        }
    });
}

// Fechar o modal caso o usuário clique fora da caixa branca de conteúdo
window.onclick = function(event) {
    const modal = document.getElementById('modal-detalhes');
    if (event.target === modal) {
        fecharModalDetalhes();
    }
}