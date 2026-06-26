<?php
require_once "criar-banco-classificados.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID não fornecido.']);
    exit();
}

$banco = new BancoDeDados("localhost", "root", "dadosmain", "db_integrador", "admin", "aluno", "anuncio", "favoritos", "avaliacao", "denuncia", "feedback");
$conexao = $banco->criarConexao();
$banco->abrirBanco($conexao);

$id_anuncio = intval($_GET['id']);

// Busca todas as informações do anúncio correspondente
$sql = "SELECT titulo, categoria, preco, imagem, descricao, status FROM $banco->anuncio WHERE id = $id_anuncio";
$resultado = $conexao->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    $dados = $resultado->fetch_assoc();
    
    // Trata a imagem caso esteja vazia
    if (empty($dados['imagem']) || !file_exists($dados['imagem'])) {
        $dados['imagem'] = ''; 
    }
    
    // Formata o preço para exibição amigável
    $dados['preco_formatado'] = number_format($dados['preco'], 2, ',', '.');
    $dados['status'] = 'sucesso';
    
    echo json_encode($dados);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Anúncio não encontrado.']);
}

$banco->desconectar($conexao);
?>