<?php
require_once "criar-banco-classificados.php";
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_aluno'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Você precisa estar logado.']);
    exit();
}

$banco = new BancoDeDados("localhost", "root", "dadosmain", "db_integrador", "admin", "aluno", "anuncio", "favoritos", "avaliacao", "denuncia", "feedback");
$conexao = $banco->criarConexao();
$banco->abrirBanco($conexao);

$id_aluno = intval($_SESSION['id_aluno']);
$id_anuncio = intval($_POST['id_anuncio'] ?? 0);
$acao = $_POST['acao'] ?? '';

if ($id_anuncio <= 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Anúncio inválido.']);
    exit();
}

if ($acao === 'avaliar') {
    $nota = intval($_POST['nota']);
    
    // Verifica se já avaliou
    $check = $conexao->query("SELECT id FROM {$banco->avaliacao} WHERE id_anuncio = $id_anuncio AND id_comprador = $id_aluno");
    
    if ($check->num_rows > 0) {
        // Atualiza a nota existente
        $sql = "UPDATE {$banco->avaliacao} SET nota = $nota, data_avaliacao = NOW() WHERE id_anuncio = $id_anuncio AND id_comprador = $id_aluno";
        $msg = "Sua avaliação foi atualizada!";
    } else {
        // Insere nova nota
        $sql = "INSERT INTO {$banco->avaliacao} (nota, data_avaliacao, id_anuncio, id_comprador) VALUES ($nota, NOW(), $id_anuncio, $id_aluno)";
        $msg = "Avaliação registrada com sucesso!";
    }
    
    if ($conexao->query($sql)) {
        echo json_encode(['status' => 'sucesso', 'mensagem' => $msg]);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao salvar avaliação.']);
    }
} 

elseif ($acao === 'denunciar') {
    $comentario = $conexao->real_escape_string($_POST['comentario']);
    
    $sql = "INSERT INTO {$banco->denuncia} (comentario, data_denuncia, id_anuncio, id_comprador) VALUES ('$comentario', NOW(), $id_anuncio, $id_aluno)";
    
    if ($conexao->query($sql)) {
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Denúncia registrada e enviada para análise.']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao processar denúncia.']);
    }
}

$banco->desconectar($conexao);
?>