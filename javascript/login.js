// 1. Lógica para o Alerta de Logout
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('logout')) {
    alert("Logout concluído com sucesso! Até logo.");
    // Remove o ?logout da URL para não repetir o alerta ao dar F5
    window.history.replaceState({}, document.title, window.location.pathname);
}

// 2. Elementos do Modal
const modal = document.getElementById('modalCadastro');
const btnAbrir = document.getElementById('btnAbrirModal');
const btnFechar = document.getElementById('btnFecharModal');

// 3. Funções para Abrir e Fechar
function abrirModal() {
    modal.style.display = 'flex';
}

function fecharModal() {
    modal.style.display = 'none';
}

// 4. Eventos
btnAbrir.addEventListener('click', abrirModal);
btnFechar.addEventListener('click', fecharModal);

// Fecha o modal ao clicar fora da área branca
window.addEventListener('click', (event) => {
    if (event.target === modal) {
        fecharModal();
    }
});

// 5. Validação de Senhas Idênticas (Cadastro Externo)
const formCadastro = document.querySelector('#modalCadastro form');

formCadastro.addEventListener('submit', function(event) {
    // Captura os campos de senha dentro do modal
    const camposSenha = formCadastro.querySelectorAll('input[type="password"]');
    const senha = camposSenha[0].value;
    const confirmaSenha = camposSenha[1].value;

    if (senha !== confirmaSenha) {
        // Bloqueia o envio do formulário
        event.preventDefault();
        alert("As senhas não coincidem! Por favor, verifique.");
        
        // Limpa apenas os campos de senha para o usuário tentar de novo
        camposSenha[0].value = "";
        camposSenha[1].value = "";
        camposSenha[0].focus();
    } else {
        alert("Cadastro realizado com sucesso!");
        // Aqui o formulário seguiria para o processamento real
    }
});

