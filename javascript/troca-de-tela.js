let telaAnterior = 'tela-home'
let telaAtual = 'tela-home'
let menuAnterior = 'menu-home'
let menuAtual = 'menu-home'

function navegar(destino){
    let telas = document.getElementsByClassName('tela')
    Array.from(telas).forEach(element => {
        element.classList.remove('show')
        element.classList.add('collapse')
    });
    document.getElementById(destino).classList.remove('collapse')
    document.getElementById(destino).classList.add('show')
    telaAnterior = telaAtual
    telaAtual = destino
}

function navegarMenu(destinoMenu){
    let menus = document.getElementsByClassName('menu')
    Array.from(menus).forEach(element => {
        element.classList.add('active')
        element.classList.remove('active')
    });
    document.getElementById(destinoMenu).classList.add('active')
    menuAnterior = menuAtual
    menuAtual = destinoMenu
}

function voltar() {
    navegar(telaAnterior)
}

function voltarMenu() {
    navegarMenu(menuAnterior)
}

function mostrarConfirmacao(input) {
    const textoUpload = document.getElementById('texto-upload');
    const previewImagem = document.getElementById('preview-imagem');

    // Verifica se o usuário realmente selecionou algum arquivo
    if (input.files && input.files.length > 0) {
        const quantidade = input.files.length;
        
        // 1. Atualiza o texto de confirmação
        if (quantidade === 1) {
            textoUpload.innerHTML = `✅ 1 imagem selecionada!`;
        } else {
            textoUpload.innerHTML = `✅ ${quantidade} imagens selecionadas!`;
        }
        
        // 2. Gera a prévia da primeira imagem
        const leitor = new FileReader();
        leitor.onload = function(e) {
            previewImagem.src = e.target.result;
            previewImagem.style.display = 'block'; // Torna a imagem visível
        }
        leitor.readAsDataURL(input.files[0]); // Lê o primeiro arquivo do input

    } else {
        // Se o usuário abrir a janela e cancelar, volta ao estado original
        textoUpload.innerHTML = `📷 Clique para selecionar fotos`;
        previewImagem.style.display = 'none';
        previewImagem.src = "";
    }
}