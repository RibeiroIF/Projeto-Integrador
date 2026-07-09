<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_aluno'])) {
    exit("Sessão expirada.");
}

require_once "../includes/criar-banco-classificados.php";
require_once "../includes/criar-classe-anuncio.php";

$banco = new BancoDeDados("localhost", "root", "", "db_integrador", "admin", "aluno", "anuncio", "favoritos", "avaliacao", "denuncia", "feedback");
$conexao = $banco->criarConexao();
$banco->abrirBanco($conexao);
$banco->definirCharset($conexao);

$anuncios = new Anuncios();

$anuncios->listarAnunciosAtivos($conexao, $banco->anuncio, $_SESSION['id_aluno'], $_GET);
?>