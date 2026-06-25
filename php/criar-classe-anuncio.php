<?php
 class Anuncios {
  public $id;
  public $titulo;
  public $categoria;
  public $preco;
  public $imagem;
  public $descricao;
  public $statusAnuncio;
  public $dataPublicacao;
  public $dataExpiracao;
  public $visualizacoes;
  public $idVendedor;
 
  function receberDadosForm($conexao){
        $this->id               = null; 
        $this->titulo           = trim($conexao->escape_string($_POST["titulo-anuncio"]));
        $this->categoria        = trim($conexao->escape_string($_POST["categoria-anuncio"]));
        $this->preco            = trim($conexao->escape_string($_POST["preco-anuncio"]));
        $this->descricao        = trim($conexao->escape_string($_POST["descricao-anuncio"]));
        $this->statusAnuncio    = "EM ABERTO";
        $this->dataPublicacao   = date("Y-m-d");
        $this->dataExpiracao    = date("Y-m-d", strtotime("+30 days")); 
        $this->visualizacoes    = 0;
        
        // Se você não tiver o campo id_vendedor ainda no form, defina um valor padrão temporário (ex: 1)
        $this->idVendedor       = isset($_POST["id_vendedor"]) ? trim($conexao->escape_string($_POST["id_vendedor"])) : 1;

        // --- TRATAMENTO DO UPLOAD DA IMAGEM ---
        $this->imagem = "uploads/padrao.jpg"; // Imagem padrão caso o usuário não envie nenhuma

        // Verifica se o arquivo foi enviado sem erros através do formulário
        if (isset($_FILES['foto-anuncio']) && $_FILES['foto-anuncio']['error'] === UPLOAD_ERR_OK) {
            
            $nomeOriginal = $_FILES['foto-anuncio']['name'];
            $arquivoTmp   = $_FILES['foto-anuncio']['tmp_name'];
            
            // Pega a extensão do arquivo (png, jpg, jpeg)
            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
            
            // Gera um nome único criptografado para evitar que imagens com o mesmo nome se sobrescrevam
            $novoNome = uniqid() . "." . $extensao;
            
            // Pasta onde as fotos serão guardadas no seu servidor
            $diretorioDestino = "uploads/";

            // Move o arquivo temporário para a pasta definitiva
            if (move_uploaded_file($arquivoTmp, $diretorioDestino . $novoNome)) {
                // Guarda o caminho completo da string que será salvo na coluna do banco de dados
                $this->imagem = $diretorioDestino . $novoNome;
            }
        }
    }

    function cadastrar($conexao, $tabelaAnuncio){
        // Escapa a string do caminho da imagem por segurança
        $imagemEscapada = $conexao->escape_string($this->imagem);

        $sql = "INSERT INTO $tabelaAnuncio VALUES(
                     null,
                     '$this->titulo',
                     '$this->categoria',
                     '$this->preco',
                     '$imagemEscapada',
                     '$this->descricao',
                     '$this->statusAnuncio',
                     '$this->dataPublicacao',
                     '$this->dataExpiracao',
                     '$this->visualizacoes',
                     '$this->idVendedor')";

        $conexao->query($sql) or die($conexao->error);
    }

   function listarAnunciosAtivos($conexao, $anuncio){
    // Ajustado o nome da coluna para 'status' conforme o seu CREATE TABLE
    $sql = "SELECT * FROM $anuncio WHERE status = 'EM ABERTO' ORDER BY data_publicacao DESC";
    $resultado = $conexao->query($sql) or die($conexao->error);

    if($resultado->num_rows == 0){ // Corrigido para num_rows que avalia linhas retornadas no SELECT
        echo "<p class='aviso-vazio' style='grid-column: 1/-1; text-align: center; color: #777; padding: 20px;'>Nenhum anúncio disponível no momento.</p>";
    }
    else {
        // Varre o banco de dados montando a estrutura de cards igual ao seu index
        while($vetorRegistro = $resultado->fetch_array()){
            $id        = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
            $titulo    = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
            $preco     = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
            $imagem    = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8"); // ÍNDICE CORRIGIDO
            $descricao = htmlentities($vetorRegistro[5], ENT_QUOTES, "UTF-8"); // ÍNDICE CORRIGIDO
            $id_aluno  = htmlentities($vetorRegistro[10], ENT_QUOTES, "UTF-8");

            $precoFormatado = number_format($preco, 2, ',', '.');

            // Verifica se o arquivo de imagem realmente existe, senão usa uma padrão
            $tagImagem = (!empty($imagem) && file_exists($imagem)) 
                ? "<img src='$imagem' alt='$titulo' style='width: 100%; height: 100%; object-fit: cover;'>" 
                : "<div class='foto-placeholder'>Sem Imagem</div>";

            // Mantendo as classes CSS originais do seu grid anterior ('card-anuncio', 'badge-campus', 'info-anuncio', etc.)
            echo "
            <article class='card-anuncio'>
                <div class='badge-campus'>Câmpus Principal</div>
                
                <div class='foto-placeholder-container' style='height: 200px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f0f0f0;'>
                    $tagImagem
                </div>

                <div class='info-anuncio'>
                    <h4>$titulo</h4>
                    <p class='preco'>R$ $precoFormatado</p>
                    <p class='vendedor'>Vendedor ID: $id_aluno</p>
                    <button class='btn-detalhes' onclick='verDetalhes($id)'>Ver Detalhes</button>
                </div>
            </article>";
        }
    }
}

   function listarTodos($conexao, $tabelaAnuncio) {
    // Busca todos os anúncios do banco ordenados pelos mais recentes
    $sql = "SELECT * FROM $tabelaAnuncio ORDER BY id DESC";
    $resultado = $conexao->query($sql);
    
    return $resultado; // Retorna o conjunto de dados do MySQL
    }

    function listarMeusAnuncios($conexao, $anuncio, $id_usuario_logado) {
    // Busca TODOS os anúncios criados por aquele ID de aluno específico
    // NOTA: Ajuste o nome da coluna 'id_aluno' se no seu banco for diferente (ex: id_vendedor, fk_aluno...)
        $sql = "SELECT * FROM $anuncio WHERE id_aluno = '$id_usuario_logado' ORDER BY data_publicacao DESC";
        $resultado = $conexao->query($sql) or die($conexao->error);

        if ($resultado->num_rows == 0) {
            echo "<p class='aviso-vazio' style='grid-column: 1/-1; text-align: center; color: #777; padding: 20px;'>Você ainda não publicou nenhum anúncio.</p>";
        } else {
            while ($vetorRegistro = $resultado->fetch_array()) {
                $id        = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
                $titulo    = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
                $preco     = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
                $imagem    = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8");
                $status    = htmlentities($vetorRegistro[6], ENT_QUOTES, "UTF-8"); // Supondo que status esteja no índice 6

                $precoFormatado = number_format($preco, 2, ',', '.');

                // Define a classe do badge com base no status (Ex: EM ABERTO vira 'ativo', VENDIDO vira 'vendido')
                $classeStatus = (strtolower($status) == 'em aberto') ? 'ativo' : 'vendido';
                $textoStatus  = (strtolower($status) == 'em aberto') ? 'Ativo' : 'Vendido';

                $tagImagem = (!empty($imagem) && file_exists($imagem)) 
                    ? "<img src='$imagem' alt='$titulo' style='width: 40px; height: 40px; object-fit: cover; border-radius: 4px;'>" 
                    : "📸";

                // Renderiza o HTML usando as classes da sua "lista-gerenciamento"
                echo "
                <article class='item-anuncio-gerenciar'>
                    <div class='img-preview'>$tagImagem</div>
                    <div class='detalhes-item'>
                        <h4>$titulo</h4>
                        <p class='preco'>R$ $precoFormatado</p>
                        <span class='status-badge $classeStatus'>$textoStatus</span>
                    </div>
                    <div class='acoes-item'>
                        <button class='btn-acao editar' onclick='editarAnuncio($id)'>Editar</button>
                        <button class='btn-acao excluir' onclick='excluirAnuncio($id)'>Excluir</button>
                    </div>
                </article>";
            }
        }
    }

    function obterContadoresUsuario($conexao, $anuncio, $id_usuario_logado) {
        // 1. Conta anúncios ativos
        $sqlAtivos = "SELECT COUNT(*) as total FROM $anuncio WHERE id_aluno = '$id_usuario_logado' AND status = 'EM ABERTO'";
        $resAtivos = $conexao->query($sqlAtivos);
        $dadosAtivos = $resAtivos->fetch_assoc();
        $ativos = str_pad($dadosAtivos['total'], 2, "0", STR_PAD_LEFT);

        // 2. Conta anúncios vendidos
        $sqlVendidos = "SELECT COUNT(*) as total FROM $anuncio WHERE id_aluno = '$id_usuario_logado' AND status = 'VENDIDO'";
        $resVendidos = $conexao->query($sqlVendidos);
        $dadosVendidos = $resVendidos->fetch_assoc();
        $vendidos = str_pad($dadosVendidos['total'], 2, "0", STR_PAD_LEFT);

        // 3. Soma as visualizações (se a coluna não existir, o IF trata retornando 0)
        $sqlViews = "SELECT SUM(visualizacoes) as total FROM $anuncio WHERE id_aluno = '$id_usuario_logado'";
        $resViews = $conexao->query($sqlViews);
        $dadosViews = $resViews->fetch_assoc();
        $views = $dadosViews['total'] ? $dadosViews['total'] : 0;

        // Retorna os três valores organizados em um array (vetor)
        return [
            'ativos' => $ativos,
            'vendidos' => $vendidos,
            'visualizacoes' => $views
        ];
    }
 }
?>