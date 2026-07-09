<?php
require_once "../includes/criar-banco-classificados.php";
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_aluno'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Você precisa estar logado.']);
    exit();
}

$banco = new BancoDeDados("localhost", "root", "", "db_integrador", "admin", "aluno", "anuncio", "favoritos", "avaliacao", "denuncia", "feedback");
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
    $check = $conexao->query("SELECT id FROM {$banco->avaliacao} WHERE id_anuncio = $id_anuncio AND id_comprador = $id_aluno");
    
    if ($check->num_rows > 0) {
        $sql = "UPDATE {$banco->avaliacao} SET nota = $nota, data_avaliacao = NOW() WHERE id_anuncio = $id_anuncio AND id_comprador = $id_aluno";
        $msg = "Sua avaliação foi atualizada!";
    } else {
        $sql = "INSERT INTO {$banco->avaliacao} (nota, data_avaliacao, id_anuncio, id_comprador) VALUES ($nota, NOW(), $id_anuncio, $id_aluno)";
        $msg = "Avaliação registrada com sucesso!";
    }
    
    if ($conexao->query($sql)) {
        $busca_vendedor = $conexao->query("SELECT id_aluno FROM {$banco->anuncio} WHERE id = $id_anuncio");
        $vendedor = $busca_vendedor->fetch_assoc();
        $id_vendedor = intval($vendedor['id_aluno'] ?? 0);
        $nova_media_formatada = "N/A";
        
        if ($id_vendedor > 0) {
            $sql_nova_media = "
                SELECT AVG(a.nota) as media_real 
                FROM {$banco->avaliacao} a
                INNER JOIN {$banco->anuncio} an ON a.id_anuncio = an.id
                WHERE an.id_aluno = $id_vendedor
            ";
            $res_media = $conexao->query($sql_nova_media);
            $row_media = $res_media->fetch_assoc();
            
            if ($row_media['media_real'] !== null) {
                $nova_media_formatada = number_format($row_media['media_real'], 1, '.', '');
            }
        }

        $nova_media_perfil = null; 
        $sql_media_perfil = "
            SELECT AVG(a.nota) as media_real 
            FROM {$banco->avaliacao} a
            INNER JOIN {$banco->anuncio} an ON a.id_anuncio = an.id
            WHERE an.id_aluno = $id_aluno
        ";
        $res_perfil = $conexao->query($sql_media_perfil);
        if ($res_perfil) {
            $row_perfil = $res_perfil->fetch_assoc();
            if ($row_perfil['media_real'] !== null) {
                $nova_media_perfil = number_format($row_perfil['media_real'], 1, '.', '');
            }
        }

        echo json_encode([
            'status' => 'sucesso', 
            'mensagem' => $msg,
            'nova_media' => $nova_media_formatada,
            'nova_media_perfil' => $nova_media_perfil
        ]);
        exit();

    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao salvar avaliação.']);
        exit();
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