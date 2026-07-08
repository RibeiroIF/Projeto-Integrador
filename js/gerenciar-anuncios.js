function prepararEdicao(id, titulo, categoria, preco, descricao, imagem, status) {
    document.getElementById('titulo-tela-anuncio').innerText = 'Editar Anúncio';
    document.getElementById('btn-submit-anuncio').innerText = 'Salvar Alterações';
    
    document.getElementById('id_anuncio_edicao').value = id;
    document.getElementById('titulo').value = titulo;
    document.getElementById('categoria').value = categoria;
    document.getElementById('preco').value = preco;
    document.getElementById('descricao').value = descricao;
    
    const containerStatus = document.getElementById('container-status-anuncio');
    if (containerStatus) {
        containerStatus.style.display = 'block';
        document.getElementById('status-anuncio').value = status;
    }

    const preview = document.getElementById('preview-imagem');
    if(imagem && imagem !== '') {
        preview.src = `../${imagem}`;
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
    
    const containerStatus = document.getElementById('container-status-anuncio');
    if (containerStatus) containerStatus.style.display = 'none';

    document.querySelector('.form-anuncio').reset();
    document.getElementById('preview-imagem').style.display = 'none';
    
    navegar('tela-meusanuncios');
}

function confirmarExclusao(idAnuncio) {
    if (confirm("Tem certeza que deseja excluir permanentemente este anúncio? Esta ação não pode ser desfeita.")) {
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

    if (input.files && input.files.length > 0) {
        
        const leitor = new FileReader();
        leitor.onload = function(e) {
            previewImagem.src = e.target.result;
            previewImagem.style.display = 'block'; 
            
            textoUpload.innerHTML = ''; 
        }
        leitor.readAsDataURL(input.files[0]); 

    } else {
        textoUpload.innerHTML = 'Clique para selecionar fotos';
        previewImagem.style.display = 'none';
        previewImagem.src = "";
    }
}

function alunoConfirmarExclusaoDefinitiva(idAnuncio) {
    if (confirm('Deseja remover este anúncio permanentemente da sua lista? Esta ação não pode ser desfeita.')) {
        
        const formData = new FormData();
        formData.append('excluir-anuncio-definitivo-aluno', '1');
        formData.append('id_anuncio_aluno', idAnuncio);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(dados => {
            if (dados.sucesso) {
                const botaoClicado = document.querySelector(`button[onclick="alunoConfirmarExclusaoDefinitiva(${idAnuncio})"]`);
                if (botaoClicado) {
                    const wrapper = botaoClicado.closest('.wrapper-anuncio-gerenciar');
                    if (wrapper) {
                        wrapper.remove(); 
                    }
                }
                alert('Anúncio excluído com sucesso!');
            } else {
                alert('Erro ao excluir: ' + dados.erro);
            }
        })
        .catch(erro => console.error('Erro na requisição:', erro));
    }
}
