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
  public $deletado;
 
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
        $this->deletado         = 0;
        
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
                     '$this->deletado',
                     '$this->dataPublicacao',
                     '$this->dataExpiracao',
                     '$this->visualizacoes',
                     '$this->idVendedor')";

        $conexao->query($sql) or die($conexao->error);
    }

   function listarAnunciosAtivos($conexao, $anuncio, $id_aluno_logado, $filtros = []){
    $filtroSQL = "WHERE a.deletado = 0";

    if (!is_array($filtros)) {
        $buscaEscapada = $conexao->escape_string(trim($filtros));
        if (!empty($buscaEscapada)) {
            $filtroSQL .= " AND (a.titulo LIKE '%$buscaEscapada%' OR a.categoria LIKE '%$buscaEscapada%')";
        }
    } else {
        if (!empty($filtros['busca'])) {
            $busca = $conexao->escape_string(trim($filtros['busca']));
            $filtroSQL .= " AND (a.titulo LIKE '%$busca%' OR a.categoria LIKE '%$busca%')";
        }
        if (!empty($filtros['status_item']) && $filtros['status_item'] !== 'todos') {
            $status = $conexao->escape_string($filtros['status_item']);
            $filtroSQL .= " AND a.status = '$status'";
        }
        if (!empty($filtros['preco_min'])) {
            $min = floatval($filtros['preco_min']);
            $filtroSQL .= " AND a.preco >= $min";
        }
        if (!empty($filtros['preco_max'])) {
            $max = floatval($filtros['preco_max']);
            $filtroSQL .= " AND a.preco <= $max";
        }
        if (!empty($filtros['data_inicio'])) {
            $data_ini = $conexao->escape_string($filtros['data_inicio']);
            $filtroSQL .= " AND a.data_publicacao >= '$data_ini'";
        }
        if (!empty($filtros['data_fim'])) {
            $data_fim = $conexao->escape_string($filtros['data_fim']);
            $filtroSQL .= " AND a.data_publicacao <= '$data_fim'";
        }
    }

    $sql = "SELECT a.*, al.whatsapp, f.id_aluno AS favoritado 
            FROM $anuncio a 
            INNER JOIN aluno al ON a.id_aluno = al.id
            LEFT JOIN favoritos f ON a.id = f.id_anuncio AND f.id_aluno = $id_aluno_logado
            $filtroSQL
            ORDER BY a.data_publicacao DESC";
            
    $resultado = $conexao->query($sql) or die($conexao->error);

    if($resultado->num_rows == 0){ 
        echo "<p class='aviso-vazio'>Nenhum anúncio condizente com os filtros aplicados.</p>";
    } else {
        while($vetorRegistro = $resultado->fetch_array()){
            $id            = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
            $titulo        = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
            $preco         = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
            $imagem        = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8"); 
            $statusAnuncio = $vetorRegistro[6]; 
            $id_aluno      = htmlentities($vetorRegistro[10], ENT_QUOTES, "UTF-8");
            $whatsappVendedor = isset($vetorRegistro['whatsapp']) ? htmlentities($vetorRegistro['whatsapp'], ENT_QUOTES, "UTF-8") : "Não informado";

            if ($statusAnuncio == "EM NEGOCIACAO") {
                $badgeStatus = "<span class='status-badge negociacao'>Em Negociação</span>";
            } elseif ($statusAnuncio == "VENDIDO") {
                $badgeStatus = "<span class='status-badge vendido'>Vendido</span>";
            } else {
                $badgeStatus = "<span class='status-badge ativo'>Disponível</span>";
            }

            $classeFavorito = !empty($vetorRegistro['favoritado']) ? 'favoritado' : '';
            $precoFormatado = number_format($preco, 2, ',', '.');

            $tagImagem = (!empty($imagem) && file_exists($imagem)) 
                ? "<img src='$imagem' alt='$titulo' class='img-card-cover'>" 
                : "<div class='foto-placeholder-txt'>Sem Imagem</div>";

            echo "
            <article class='card-anuncio'>
                <button class='btn-favorito $classeFavorito' data-id='$id' onclick='alternarFavorito(this)' title='Favoritar item'>" . (!empty($classeFavorito) ? "❤️" : "🤍") . "</button>
                <div class='foto-placeholder-container'>
                    $tagImagem
                </div>
                <div class='info-anuncio'>
                    <span class='badge-campus'>Câmpus Principal</span>
                    <h4>$titulo</h4>
                    <div class='status-wrapper'>$badgeStatus</div>
                    <p class='preco'>R$ $precoFormatado</p>
                    <p class='vendedor'>Vendedor: #$id_aluno</p>
                    <p class='vendedor-whats'>WhatsApp: $whatsappVendedor</p>
                    <button class='btn-detalhes' onclick='verDetalhes($id)'>Ver Detalhes</button>
                </div>
            </article>";
        }
    }
}

function listarAnunciosAdmin($conexao, $anuncio){
    $sql = "SELECT a.*, al.whatsapp 
            FROM $anuncio a 
            INNER JOIN aluno al ON a.id_aluno = al.id
            WHERE a.deletado = 0
            ORDER BY a.data_publicacao DESC";
            
    $resultado = $conexao->query($sql) or die($conexao->error);

    if($resultado->num_rows == 0){ 
        echo "<p class='aviso-vazio'>Nenhum anúncio disponível no momento.</p>";
    } else {
        while($vetorRegistro = $resultado->fetch_array()){
            $id        = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
            $titulo    = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
            $preco     = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
            $imagem    = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8"); 
            $id_aluno  = htmlentities($vetorRegistro[10], ENT_QUOTES, "UTF-8");
            $whatsappVendedor = isset($vetorRegistro['whatsapp']) ? htmlentities($vetorRegistro['whatsapp'], ENT_QUOTES, "UTF-8") : "Não informado";

            $precoFormatado = number_format($preco, 2, ',', '.');

            $tagImagem = (!empty($imagem) && file_exists($imagem)) 
                ? "<img src='$imagem' alt='$titulo' class='img-card-cover'>" 
                : "<div class='foto-placeholder-txt'>Sem Imagem</div>";

            echo "
            <article class='card-anuncio'>
                <div class='foto-placeholder-container'>
                    $tagImagem
                </div>
                <div class='info-anuncio'>
                    <span class='badge-campus'>Câmpus Principal</span>
                    <h4>$titulo</h4>
                    <p class='preco'>R$ $precoFormatado</p>
                    <p class='vendedor'>Vendedor: #$id_aluno</p>
                    <p class='vendedor-whats'>WhatsApp: $whatsappVendedor</p>
                    <button class='btn-detalhes' onclick='verDetalhes($id, true)'>Ver Detalhes</button>
                </div>
            </article>";
        }
    }
}

function listarMeusAnuncios($conexao, $anuncio, $id_usuario_logado) {
    $sql = "SELECT * FROM $anuncio WHERE id_aluno = '$id_usuario_logado' ORDER BY data_publicacao DESC";
    $resultado = $conexao->query($sql) or die($conexao->error);

    if ($resultado->num_rows == 0) {
        echo "<p class='aviso-vazio'>Você ainda não publicou nenhum anúncio.</p>";
    } else {
        while ($vetorRegistro = $resultado->fetch_array()) {
            $id         = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
            $titulo     = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
            $categoria  = htmlentities($vetorRegistro[2], ENT_QUOTES, "UTF-8"); 
            $preco      = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
            $imagem     = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8");
            $descricao  = htmlentities($vetorRegistro[5], ENT_QUOTES, "UTF-8"); 
            $status     = htmlentities($vetorRegistro[6], ENT_QUOTES, "UTF-8"); 
            $foiDeletado = isset($vetorRegistro['deletado']) ? intval($vetorRegistro['deletado']) : 0;

            $precoFormatado = number_format($preco, 2, ',', '.');

            $tagBloqueio = "";
            $classeBloqueio = "";
            if ($foiDeletado === 1) {
                $classeBloqueio = "item-banido";
                $tagBloqueio = "<div class='badge-moderacao-aviso'>Removido pela Moderação</div>";
            }

            if ($status === 'EM NEGOCIACAO') {
                $classeStatus = 'negociacao'; 
                $textoStatus  = 'Em Negociação';
            } elseif ($status === 'VENDIDO') {
                $classeStatus = 'vendido'; 
                $textoStatus  = 'Vendido';
            } else {
                $classeStatus = 'ativo';
                $textoStatus  = 'Disponível';
            }

            $tagImagem = (!empty($imagem) && file_exists($imagem)) 
                ? "<img src='$imagem' alt='$titulo' class='img-mini-gerenciar'>" 
                : "<div class='foto-placeholder-txt'>Sem Foto</div>";

            echo "
            <article class='item-anuncio-gerenciar $classeBloqueio'>
                $tagBloqueio
                <div class='img-preview'>
                    $tagImagem
                </div>
                <div class='detalhes-item'>
                    <h4>$titulo</h4>
                    <p class='preco'>R$ $precoFormatado</p>
                    <span class='status-badge $classeStatus'>$textoStatus</span>
                </div>
                <div class='acoes-item'>
                    <button class='btn-acao editar' onclick='prepararEdicao($id, \"$titulo\", \"$categoria\", $preco, \"$descricao\", \"$imagem\", \"$status\")'>Editar</button>
                    <button class='btn-acao excluir' onclick='confirmarExclusao($id)'>Excluir</button>
                </div>
            </article>";
        }
    }
}

function listarFavoritosDoAluno($conexao, $anuncio, $id_aluno_logado){
    $sql = "SELECT a.*, al.whatsapp FROM $anuncio a 
            INNER JOIN favoritos f ON a.id = f.id_anuncio 
            INNER JOIN aluno al ON a.id_aluno = al.id
            WHERE f.id_aluno = $id_aluno_logado AND a.status = 'EM ABERTO'
            ORDER BY f.data_favoritado DESC";
            
    $resultado = $conexao->query($sql) or die($conexao->error);

    if($resultado->num_rows == 0){ 
        echo "<p class='aviso-vazio'>Você ainda não favoritou nenhum anúncio.</p>";
    } else {
        while($vetorRegistro = $resultado->fetch_array()){
            $id        = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
            $titulo    = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
            $preco     = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
            $imagem    = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8"); 
            $id_aluno  = htmlentities($vetorRegistro[10], ENT_QUOTES, "UTF-8");
            $whatsappVendedor = isset($vetorRegistro['whatsapp']) ? htmlentities($vetorRegistro['whatsapp'], ENT_QUOTES, "UTF-8") : "Não informado";

            $precoFormatado = number_format($preco, 2, ',', '.');

            $tagImagem = (!empty($imagem) && file_exists($imagem)) 
                ? "<img src='$imagem' alt='$titulo' class='img-card-cover'>" 
                : "<div class='foto-placeholder-txt'>Sem Imagem</div>";

            echo "
            <article class='card-anuncio' data-id-anuncio='$id'>
                <div class='foto-placeholder-container'>
                    $tagImagem
                </div>
                <div class='info-anuncio'>
                    <span class='badge-campus favoritado'>Favoritado</span>
                    <h4>$titulo</h4>
                    <p class='preco'>R$ $precoFormatado</p>
                    <p class='vendedor'>Vendedor: #$id_aluno</p>
                    <p class='vendedor-whats'>WhatsApp: $whatsappVendedor</p>
                    <button class='btn-detalhes' onclick='verDetalhes($id)'>Ver Detalhes</button>
                </div>
            </article>";
        }
    }
}

function atualizar($conexao, $nomeTabela, $id_anuncio) {
    // 1. Coleta os dados novos do formulário
    $titulo    = $this->titulo;
    $categoria = $this->categoria;
    $preco     = $this->preco;
    $descricao = $this->descricao;
    
    // 🆕 NOVO: Captura o status vindo do select do formulário. 
    // Se não for enviado (caso o campo estivesse oculto), define como 'EM ABERTO' por padrão.
    $status    = isset($_POST['status-anuncio']) ? $_POST['status-anuncio'] : 'EM ABERTO';
    
    // 2. Busca a imagem antiga diretamente no banco, caso o usuário não tenha enviado uma nova
    $sql_busca = "SELECT imagem FROM $nomeTabela WHERE id = $id_anuncio";
    $resultado = $conexao->query($sql_busca);
    $anuncio_atual = $resultado->fetch_assoc();
    $imagem = $anuncio_atual['imagem']; 

    // 3. Processa a nova imagem apenas se o usuário tiver feito o upload de uma nova
    if (isset($_FILES['foto-anuncio']) && $_FILES['foto-anuncio']['error'] === UPLOAD_ERR_OK) {
        $diretorio = "../uploads/"; 
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        $extensao = pathinfo($_FILES['foto-anuncio']['name'], PATHINFO_EXTENSION);
        $novoNome = uniqid("img_") . "." . $extensao;
        $caminhoCompleto = $diretorio . $novoNome;

        if (move_uploaded_file($_FILES['foto-anuncio']['tmp_name'], $caminhoCompleto)) {
            if (!empty($imagem) && file_exists($imagem)) {
                unlink($imagem);
            }
            $imagem = $caminhoCompleto; 
        }
    }

    // 4. 🆕 ATUALIZADO: Executa a query de atualização incluindo a coluna 'status'
    $sql = "UPDATE $nomeTabela SET 
            titulo = '$titulo', 
            categoria = '$categoria', 
            preco = $preco, 
            descricao = '$descricao', 
            imagem = '$imagem',
            status = '$status' 
            WHERE id = $id_anuncio";

    if ($conexao->query($sql)) {
        return true;
    } else {
        die("Erro ao atualizar o anúncio: " . $conexao->error);
    }
}

    function obterContadoresUsuario($conexao, $anuncio, $id_usuario_logado) {
        // 1. Conta anúncios ativos que NÃO foram deletados
        $sqlAtivos = "SELECT COUNT(*) as total FROM $anuncio WHERE id_aluno = '$id_usuario_logado' AND status = 'EM ABERTO' AND deletado = 0";
        $resAtivos = $conexao->query($sqlAtivos);
        $ativos = str_pad($resAtivos->fetch_assoc()['total'], 2, "0", STR_PAD_LEFT);

        // 2. Conta anúncios vendidos que NÃO foram deletados
        $sqlVendidos = "SELECT COUNT(*) as total FROM $anuncio WHERE id_aluno = '$id_usuario_logado' AND status = 'VENDIDO' AND deletado = 0";
        $resVendidos = $conexao->query($sqlVendidos);
        $vendidos = str_pad($resVendidos->fetch_assoc()['total'], 2, "0", STR_PAD_LEFT);

        // 3. 🟢 NOVO: Substituído visualizações pela contagem de anúncios deletados pela moderação
        $sqlDeletados = "SELECT COUNT(*) as total FROM $anuncio WHERE id_aluno = '$id_usuario_logado' AND deletado = 1";
        $resDeletados = $conexao->query($sqlDeletados);
        $deletados = str_pad($resDeletados->fetch_assoc()['total'], 2, "0", STR_PAD_LEFT);

        return [
            'ativos' => $ativos,
            'vendidos' => $vendidos,
            'deletados' => $deletados // Retorna os deletados em vez das views!
        ];
    }
    
 }
?>