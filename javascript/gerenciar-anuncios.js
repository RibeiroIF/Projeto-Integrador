function prepararEdicao(id, titulo, categoria, preco, descricao, imagem, status) {
    document.getElementById('titulo-tela-anuncio').innerText = 'Editar Anúncio';
    document.getElementById('btn-submit-anuncio').innerText = 'Salvar Alterações';
    
    document.getElementById('id_anuncio_edicao').value = id;
    document.getElementById('titulo').value = titulo;
    document.getElementById('categoria').value = categoria;
    document.getElementById('preco').value = preco;
    document.getElementById('descricao').value = descricao;
    
    // NOVO: Exibe o select de status e define o valor atual do banco
    const containerStatus = document.getElementById('container-status-anuncio');
    if (containerStatus) {
        containerStatus.style.display = 'block';
        document.getElementById('status-anuncio').value = status;
    }

    const preview = document.getElementById('preview-imagem');
    if(imagem && imagem !== '') {
        preview.src = imagem;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
    
    navegar('tela-novoanuncio');
}

function cancelarEdicaoAnuncio() {
    document.getElementById('titulo-tela-anuncio').innerText = 'Criar Novo Anúncio';
    document.getElementById('btn-submit-anuncio').innerText = 'Publicar Anúncio';
    document.getElementById('id_anuncio_edicao').value = '';
    
    // NOVO: Esconde o campo de status para o próximo novo anúncio
    const containerStatus = document.getElementById('container-status-anuncio');
    if (containerStatus) containerStatus.style.display = 'none';

    document.querySelector('.form-anuncio').reset();
    document.getElementById('preview-imagem').style.display = 'none';
    
    navegar('tela-meusanuncios');
}

// FUNÇÃO DE EXCLUSÃO: Exibe o pop-up nativo e envia a remoção
function confirmarExclusao(idAnuncio) {
    if (confirm("Tem certeza que deseja excluir permanentemente este anúncio? Esta ação não pode ser desfeita.")) {
        // Cria um formulário temporário para enviar o ID via POST com segurança
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php';

        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'deletar_id_anuncio';
        inputId.value = idAnuncio;

        form.appendChild(inputId);
        document.body.appendChild(form);
        form.submit();
    }
}

function mostrarConfirmacao(input) {
    const textoUpload = document.getElementById('texto-upload');
    const previewImagem = document.getElementById('preview-imagem');

    // Verifica se o usuário realmente selecionou algum arquivo
    if (input.files && input.files.length > 0) {
        
        // 1. Gera a prévia da primeira imagem
        const leitor = new FileReader();
        leitor.onload = function(e) {
            previewImagem.src = e.target.result;
            previewImagem.style.display = 'block'; // Torna a imagem visível
            
            // 2. Limpa o texto informativo para NÃO acumular "1 imagem selecionada" com a foto
            textoUpload.innerHTML = ''; 
        }
        leitor.readAsDataURL(input.files[0]); // Lê o primeiro arquivo do input

    } else {
        // Se o usuário abrir a janela e cancelar, volta ao estado original sóbrio
        textoUpload.innerHTML = 'Clique para selecionar fotos';
        previewImagem.style.display = 'none';
        previewImagem.src = "";
    }
}
