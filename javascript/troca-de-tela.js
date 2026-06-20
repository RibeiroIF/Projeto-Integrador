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