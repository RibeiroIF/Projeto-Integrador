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
    public $motivo;

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
        $this->motivo           = null;
        
        $this->idVendedor       = isset($_POST["id_vendedor"]) ? trim($conexao->escape_string($_POST["id_vendedor"])) : 1;

        $this->imagem = "imagens/anuncios/padrao.jpg"; 

        if (isset($_FILES['foto-anuncio']) && $_FILES['foto-anuncio']['error'] === UPLOAD_ERR_OK) {
            $nomeOriginal = $_FILES['foto-anuncio']['name'];
            $arquivoTmp   = $_FILES['foto-anuncio']['tmp_name'];
            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
            $novoNome = uniqid() . "." . $extensao;
            
            $diretorioDestinoFisico = __DIR__ . "/../imagens/anuncios/";
            $caminhoBancoDados = "imagens/anuncios/" . $novoNome;

            if (move_uploaded_file($arquivoTmp, $diretorioDestinoFisico . $novoNome)) {
                $this->imagem = $caminhoBancoDados;
            }
        }
    }

    function cadastrar($conexao, $tabelaAnuncio){
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
                     '$this->idVendedor',
                     null)";

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

    $sql = "SELECT a.*, al.whatsapp, f.id_aluno AS status_favorito 
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
            $id_aluno      = htmlentities($vetorRegistro[11], ENT_QUOTES, "UTF-8");
            $whatsappVendedor = isset($vetorRegistro['whatsapp']) ? htmlentities($vetorRegistro['whatsapp'], ENT_QUOTES, "UTF-8") : "Não informado";

            if ($statusAnuncio == "EM NEGOCIACAO") {
                $badgeStatus = "<span class='status-badge negociacao'>Em Negociação</span>";
            } elseif ($statusAnuncio == "VENDIDO") {
                $badgeStatus = "<span class='status-badge vendido'>Vendido</span>";
            } else {
                $badgeStatus = "<span class='status-badge ativo'>Disponível</span>";
            }

            $classeFavorito = !empty($vetorRegistro['status_favorito']) ? 'favoritado' : '';
            $precoFormatado = number_format($preco, 2, ',', '.');

            $caminhoFisico = __DIR__ . '/../' . $imagem;
            
            $tagImagem = (!empty($imagem) && file_exists($caminhoFisico)) 
                ? "<img src='../$imagem' alt='$titulo' class='img-card-cover'>" 
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

function listarAnunciosAdmin($conexao, $anuncio, $termo = ''){
    // Escapa o termo para evitar quebras de sintaxe ou SQL Injection
    $termoEscapado = $conexao->real_escape_string($termo);

    // SQL Base
    $sql = "SELECT a.*, al.whatsapp 
            FROM $anuncio a 
            INNER JOIN aluno al ON a.id_aluno = al.id
            WHERE a.deletado = 0";
            
    // Se um termo foi enviado via GET (barra de pesquisa ou botões), aplica o filtro
    if (!empty($termoEscapado)) {
        $sql .= " AND (a.titulo LIKE '%{$termoEscapado}%' 
                    OR a.descricao LIKE '%{$termoEscapado}%' 
                    OR a.categoria LIKE '%{$termoEscapado}%')";
    }

    $sql .= " ORDER BY a.data_publicacao DESC";
            
    $resultado = $conexao->query($sql) or die($conexao->error);

    if($resultado->num_rows == 0){ 
        echo "<p class='aviso-vazio'>Nenhum anúncio correspondente foi encontrado.</p>";
    } else {
        while($vetorRegistro = $resultado->fetch_array()){
            $id        = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
            $titulo    = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
            $preco     = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
            $imagem    = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8"); 
            $id_aluno  = htmlentities($vetorRegistro[11], ENT_QUOTES, "UTF-8");
            $whatsappVendedor = isset($vetorRegistro['whatsapp']) ? htmlentities($vetorRegistro['whatsapp'], ENT_QUOTES, "UTF-8") : "Não informado";

            $precoFormatado = number_format($preco, 2, ',', '.');

            $caminhoFisico = __DIR__ . '/../' . $imagem;
            $tagImagem = (!empty($imagem) && file_exists($caminhoFisico)) 
                ? "<img src='../$imagem' alt='$titulo' class='img-card-cover'>" 
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
        while ($vetorRegistro = $resultado->fetch_assoc()) {
            $id         = htmlentities($vetorRegistro['id'], ENT_QUOTES, "UTF-8");
            $titulo     = htmlentities($vetorRegistro['titulo'], ENT_QUOTES, "UTF-8");
            $categoria  = htmlentities($vetorRegistro['categoria'], ENT_QUOTES, "UTF-8"); 
            $preco      = htmlentities($vetorRegistro['preco'], ENT_QUOTES, "UTF-8");
            $imagem     = htmlentities($vetorRegistro['imagem'], ENT_QUOTES, "UTF-8");
            $descricao  = htmlentities($vetorRegistro['descricao'], ENT_QUOTES, "UTF-8"); 
            $status     = htmlentities($vetorRegistro['status'], ENT_QUOTES, "UTF-8"); 
            
            $foiDeletado = isset($vetorRegistro['deletado']) ? intval($vetorRegistro['deletado']) : 0;
            
            $campoMotivo = (isset($vetorRegistro['motivo']) && $vetorRegistro['motivo'] !== null) ? trim($vetorRegistro['motivo']) : "";
            $motivo = !empty($campoMotivo) ? htmlentities($campoMotivo, ENT_QUOTES, "UTF-8") : "Violação dos termos de uso do sistema.";

            $precoFormatado = number_format($preco, 2, ',', '.');

            $classeBloqueio = "";
            if ($foiDeletado === 1) {
                $classeBloqueio = "item-banido";
                $classeStatus = 'deletado'; 
                $textoStatus  = 'Removido pela Moderação';
            } else {
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
            }

            $caminhoFisico = __DIR__ . '/../' . $imagem;
            $tagImagem = (!empty($imagem) && file_exists($caminhoFisico)) 
                ? "<img src='../$imagem' alt='$titulo' class='img-mini-gerenciar'>" 
                : "<div class='foto-placeholder-txt'>Sem Foto</div>";

            echo "<div class='wrapper-anuncio-gerenciar'>";
                echo "
                <article class='item-anuncio-gerenciar $classeBloqueio'>
                    <div class='img-preview'>
                        $tagImagem
                    </div>
                    <div class='detalhes-item'>
                        <h4>$titulo</h4>
                        <p class='preco'>R$ $precoFormatado</p>
                        <span class='status-badge $classeStatus'>$textoStatus</span>
                    </div>
                    <div class='acoes-item'>";
                    
                    if ($foiDeletado !== 1) {
                        echo "
                        <button class='btn-acao editar' onclick='prepararEdicao($id, \"$titulo\", \"$categoria\", $preco, \"$descricao\", \"$imagem\", \"$status\")'>Editar</button>
                        <button class='btn-acao excluir' onclick='confirmarExclusao($id)'>Excluir</button>";
                    } else {
                        echo "<span class='item-desabilitado-txt'>Inativo</span>";
                    }

                echo "
                    </div>
                </article>";

                if ($foiDeletado === 1) {
                  $motivoFormatadoJS = str_replace(array("\r", "\n"), array('', '\n'), addslashes($motivo)); 
                  
                  echo "
                  <div class='container-acoes-banido'>
                      <button class='btn-motivo-externo' type='button' onclick='alert(\"Motivo da Remoção:\\n\\n$motivoFormatadoJS\")'>
                          Ver Motivo
                      </button>
                      <button class='btn-aluno-deletar-definitivo' type='button' onclick='alunoConfirmarExclusaoDefinitiva($id)'>
                          Excluir Anúncio
                      </button>
                  </div>";
              }

            echo "</div>"; 
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

            $caminhoFisico = __DIR__ . '/../' . $imagem;
            $tagImagem = (!empty($imagem) && file_exists($caminhoFisico)) 
                ? "<img src='../$imagem' alt='$titulo' class='img-card-cover'>" 
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
    $titulo    = $this->titulo;
    $categoria = $this->categoria;
    $preco     = $this->preco;
    $descricao = $this->descricao;
    
    $status    = isset($_POST['status-anuncio']) ? $conexao->escape_string($_POST['status-anuncio']) : 'EM ABERTO';
    
    if ($this->imagem === "imagens/anuncios/padrao.jpg") {
        $sql_busca = "SELECT imagem FROM $nomeTabela WHERE id = $id_anuncio";
        $resultado = $conexao->query($sql_busca);
        $anuncio_atual = $resultado->fetch_assoc();
        $imagem = $anuncio_atual['imagem'] ?? "imagens/anuncios/padrao.jpg"; 
    } else {
        $imagem = $this->imagem;
        
        $sql_busca = "SELECT imagem FROM $nomeTabela WHERE id = $id_anuncio";
        $resultado = $conexao->query($sql_busca);
        if ($anuncio_atual = $resultado->fetch_assoc()) {
            $foto_velha = __DIR__ . '/../' . $anuncio_atual['imagem'];
            if (!empty($anuncio_atual['imagem']) && file_exists($foto_velha) && !str_contains($foto_velha, 'padrao.jpg')) {
                @unlink($foto_velha);
            }
        }
    }

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
        $sqlAtivos = "SELECT COUNT(*) as total FROM $anuncio WHERE id_aluno = '$id_usuario_logado' AND status = 'EM ABERTO' AND deletado = 0";
        $resAtivos = $conexao->query($sqlAtivos);
        $ativos = str_pad($resAtivos->fetch_assoc()['total'], 2, "0", STR_PAD_LEFT);

        $sqlVendidos = "SELECT COUNT(*) as total FROM $anuncio WHERE id_aluno = '$id_usuario_logado' AND status = 'VENDIDO' AND deletado = 0";
        $resVendidos = $conexao->query($sqlVendidos);
        $vendidos = str_pad($resVendidos->fetch_assoc()['total'], 2, "0", STR_PAD_LEFT);

        $sqlDeletados = "SELECT COUNT(*) as total FROM $anuncio WHERE id_aluno = '$id_usuario_logado' AND deletado = 1";
        $resDeletados = $conexao->query($sqlDeletados);
        $deletados = str_pad($resDeletados->fetch_assoc()['total'], 2, "0", STR_PAD_LEFT);

        return [
            'ativos' => $ativos,
            'vendidos' => $vendidos,
            'deletados' => $deletados 
        ];
    }
 }
?>