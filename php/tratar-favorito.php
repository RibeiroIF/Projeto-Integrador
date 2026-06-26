<?php
// Garante que o arquivo saiba ler a classe do Banco se ela não tiver sido carregada
require_once "criar-banco-classificados.php";

// Configura o cabeçalho para responder estritamente em formato JSON (evita problemas no JS)
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Segurança básica: valida se a sessão do aluno e os dados do POST existem
if (!isset($_SESSION['id_aluno']) || !isset($_POST['id_anuncio'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados incompletos ou usuário deslogado.']);
    exit();
}

try {
    // Instancia o banco com as credenciais padrões que você configurou
    $banco = new BancoDeDados("localhost", "root", "dadosmain", "db_integrador", "admin", "aluno", "anuncio", "favoritos", "avaliacao", "denuncia", "feedback");
    $conexao = $banco->criarConexao();
    $banco->abrirBanco($conexao);
    $banco->definirCharset($conexao);

    $id_aluno = intval($_SESSION['id_aluno']);
    $id_anuncio = intval($_POST['id_anuncio']);
    $tabela_favoritos = $banco->favorito;

    // 1. Verifica se já está favoritado
    $sql_check = "SELECT 1 FROM $tabela_favoritos WHERE id_aluno = $id_aluno AND id_anuncio = $id_anuncio";
    $resultado = $conexao->query($sql_check);

    if ($resultado && $resultado->num_rows > 0) {
        // Se já existe, deleta (desfavorita)
        $sql_delete = "DELETE FROM $tabela_favoritos WHERE id_aluno = $id_aluno AND id_anuncio = $id_anuncio";
        if ($conexao->query($sql_delete)) {
            echo json_encode(['status' => 'removido']);
        } else {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao remover dos favoritos.']);
        }
    } else {
        // Se não existe, insere (favorita)
        $sql_insert = "INSERT INTO $tabela_favoritos (id_aluno, id_anuncio) VALUES ($id_aluno, $id_anuncio)";
        if ($conexao->query($sql_insert)) {
            echo json_encode(['status' => 'adicionado']);
        } else {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao adicionar aos favoritos.']);
        }
    }

    $banco->desconectar($conexao);

} catch (Exception $e) {
    // Caso ocorra qualquer erro de conexão, o PHP avisa o JS de forma controlada
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
exit();