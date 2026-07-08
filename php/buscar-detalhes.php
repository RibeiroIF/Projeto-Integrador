<?php
require_once "../includes/criar-banco-classificados.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID não fornecido.']);
    exit();
}

$banco = new BancoDeDados("localhost", "root", "dadosmain", "db_integrador", "admin", "aluno", "anuncio", "favoritos", "avaliacao", "denuncia", "feedback");
$conexao = $banco->criarConexao();
$banco->abrirBanco($conexao);

$id_anuncio = intval($_GET['id']);

$sql = "SELECT a.titulo, a.categoria, a.preco, a.imagem, a.descricao, a.status, al.whatsapp 
        FROM {$banco->anuncio} a
        INNER JOIN {$banco->aluno} al ON a.id_aluno = al.id 
        WHERE a.id = $id_anuncio";

$resultado = $conexao->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    $dados = $resultado->fetch_assoc();
    
    if (empty($dados['imagem'])) {
    $dados['imagem'] = ''; 
}
    
    $dados['preco_formatado'] = number_format($dados['preco'], 2, ',', '.');
    $dados['status_anuncio'] = $dados['status'];

    $sql_Media = "SELECT AVG(nota) as media, COUNT(id) as total FROM {$banco->avaliacao} WHERE id_anuncio = $id_anuncio";
    $res_media = $conexao->query($sql_Media);
    $arr_media = $res_media->fetch_assoc();
    $dados['media_nota'] = $arr_media['media'] ? round($arr_media['media'], 1) : "Sem avaliações";
    $dados['total_avaliacoes'] = $arr_media['total'];
    $id_usuario_logado = isset($_SESSION['id_aluno']) ? intval($_SESSION['id_aluno']) : 0;

    $dados['nota_usuario'] = 0; 

    if ($id_usuario_logado > 0) {
        $sql_UserNota = "SELECT nota FROM `{$banco->avaliacao}` WHERE id_anuncio = $id_anuncio AND id_comprador = $id_usuario_logado";
        $res_usernota = $conexao->query($sql_UserNota);
        if ($res_usernota && $res_usernota->num_rows > 0) {
            $user_nota_row = $res_usernota->fetch_assoc();
            $dados['nota_usuario'] = intval($user_nota_row['nota']);
        }
    }

    $dados['denuncias'] = [];
    $sql_Denuncias = "SELECT d.comentario, DATE_FORMAT(d.data_denuncia, '%d/%m/%Y') as data, al.nome 
                      FROM {$banco->denuncia} d
                      INNER JOIN {$banco->aluno} al ON d.id_comprador = al.id
                      WHERE d.id_anuncio = $id_anuncio ORDER BY d.id DESC";
    $res_denuncias = $conexao->query($sql_Denuncias);
    while($den = $res_denuncias->fetch_assoc()) {
        $dados['denuncias'][] = $den;
    }

    $dados['status'] = 'sucesso';
    echo json_encode($dados);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Anúncio não encontrado.']);
}

$banco->desconectar($conexao);
?>