<?php
// 1. Certifica-se de que a sessão está ativa para pegar o ID do aluno logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se por acaso a sessão expirou, corta a execução do AJAX
if (!isset($_SESSION['id_aluno'])) {
    exit("Sessão expirada.");
}

// 2. Inclui exatamente os mesmos arquivos que a sua index precisa
require_once "criar-banco-classificados.php";
require_once "criar-classe-anuncio.php";

// 3. Cria a conexão idêntica à da sua tela inicial
$banco = new BancoDeDados("localhost", "root", "", "db_integrador", "admin", "aluno", "anuncio", "favoritos", "avaliacao", "denuncia", "feedback");
$conexao = $banco->criarConexao();
$banco->abrirBanco($conexao);
$banco->definirCharset($conexao);

// 4. Instancia a classe de anúncios
$anuncios = new Anuncios();

// 5. Executa a listagem filtrada. O método lê o $_GET enviado pelo JavaScript e cospe o HTML dos cards
$anuncios->listarAnunciosAtivos($conexao, $banco->anuncio, $_SESSION['id_aluno'], $_GET);
?>