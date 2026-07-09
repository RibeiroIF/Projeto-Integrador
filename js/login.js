const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('logout')) {
    alert("Logout concluído com sucesso! Até logo.");
    window.history.replaceState({}, document.title, window.location.pathname);
}

const modal = document.getElementById('modalCadastro');
const btnAbrir = document.getElementById('btnAbrirModal');
const btnFechar = document.getElementById('btnFecharModal');

function abrirModal() {
    modal.style.display = 'flex';
}

function fecharModal() {
    modal.style.display = 'none';
}

btnAbrir.addEventListener('click', abrirModal);
btnFechar.addEventListener('click', fecharModal);

window.addEventListener('click', (event) => {
    if (event.target === modal) {
        fecharModal();
    }
});

const formCadastro = document.querySelector('#modalCadastro form');

formCadastro.addEventListener('submit', function(event) {
    const camposSenha = formCadastro.querySelectorAll('input[type="password"]');
    const senha = camposSenha[0].value;
    const confirmaSenha = camposSenha[1].value;

    if (senha !== confirmaSenha) {
        event.preventDefault();
        alert("As senhas não coincidem! Por favor, verifique.");
        
        camposSenha[0].value = "";
        camposSenha[1].value = "";
        camposSenha[0].focus();
    }
});

