function abrirListaFeedbacksAdmin() {
    document.getElementById('modalListaFeedbacksAdmin').style.display = 'flex';
}
function fecharListaFeedbacksAdmin() {
    document.getElementById('modalListaFeedbacksAdmin').style.display = 'none';
}

function exibirIndividualFeedback(autor, data, texto) {
    document.getElementById('ind-fb-autor').innerText = autor;
    document.getElementById('ind-fb-data').innerText = data;
    document.getElementById('ind-fb-conteudo').innerText = texto;
    document.getElementById('modalLeituraIndividualFeedback').style.display = 'flex';
}
function fecharIndividualFeedback() {
    document.getElementById('modalLeituraIndividualFeedback').style.display = 'none';
}