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

   function listarAnunciosAtivos($conexao, $anuncio, $id_aluno_logado){
    // 🔴 ALTERADO: Removido o "WHERE a.status = 'EM ABERTO'" para listar todos os estados
    $sql = "SELECT a.*, f.id_aluno AS favoritado 
            FROM $anuncio a 
            LEFT JOIN favoritos f ON a.id = f.id_anuncio AND f.id_aluno = $id_aluno_logado
            ORDER BY a.data_publicacao DESC";
            
    $resultado = $conexao->query($sql) or die($conexao->error);

    if($resultado->num_rows == 0){ 
        echo "<p class='aviso-vazio' style='grid-column: 1/-1; text-align: center; color: #777; padding: 20px;'>Nenhum anúncio disponível no momento.</p>";
    }
    else {
        while($vetorRegistro = $resultado->fetch_array()){
            $id        = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
            $titulo    = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
            $preco     = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
            $imagem    = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8"); 
            $descricao = htmlentities($vetorRegistro[5], ENT_QUOTES, "UTF-8"); 
            
            // 🟢 NOVO: Captura o status do ENUM (Posição 6 na tabela anuncio)
            $statusAnuncio = $vetorRegistro[6]; 
            
            $id_aluno  = htmlentities($vetorRegistro[10], ENT_QUOTES, "UTF-8");

            // 🟢 NOVO: Monta a estrutura visual do pequeno ícone/tag de status
            $badgeStatus = "";
            if ($statusAnuncio == "EM NEGOCIACAO") {
                $badgeStatus = "<span style='background: #ffc107; color: #000; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; display: inline-block; margin-bottom: 5px;'>🤝 Em Negociação</span>";
            } elseif ($statusAnuncio == "VENDIDO") {
                $badgeStatus = "<span style='background: #dc3545; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; display: inline-block; margin-bottom: 5px;'>💰 Vendido</span>";
            } else {
                $badgeStatus = "<span style='background: #28a745; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; display: inline-block; margin-bottom: 5px;'>🟢 Disponível</span>";
            }

            // Define se o coração começa cheio ou vazio baseando-se no retorno do JOIN
            $coracao = !empty($vetorRegistro['favoritado']) ? '❤️' : '🤍';

            $precoFormatado = number_format($preco, 2, ',', '.');

            $tagImagem = (!empty($imagem) && file_exists($imagem)) 
            ? "<img src='$imagem' alt='$titulo' style='width: 100%; height: 100%; object-fit: cover;'>" 
            : "<div class='foto-placeholder'>Sem Imagem</div>";

            // Inserido o $badgeStatus logo abaixo do título do anúncio
            echo "
            <article class='card-anuncio' style='position: relative;'>
                <button class='btn-favorito' data-id='$id' onclick='alternarFavorito(this)' title='Favoritar item' style='position: absolute; top: 10px; right: 10px; z-index: 10; background: rgba(255,255,255,0.8); border:none; border-radius:50%; width:35px; height:35px; cursor:pointer;'>
                    $coracao
                </button>
                
                <div class='badge-campus'>Câmpus Principal</div>
                
                <div class='foto-placeholder-container' style='height: 200px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f0f0f0;'>
                    $tagImagem
                </div>

                <div class='info-anuncio'>
                    <h4>$titulo</h4>
                    
                    <div>$badgeStatus</div>
                    
                    <p class='preco'>R$ $precoFormatado</p>
                    <p class='vendedor'>Vendedor ID: $id_aluno</p>
                    <button class='btn-detalhes' onclick='verDetalhes($id)'>Ver Detalhes</button>
                </div>
            </article>";
        }
    }
}

function listarAnunciosAdmin($conexao, $anuncio){
    // Query limpa, sem JOINs, pois admin não tem favoritos
    $sql = "SELECT * FROM $anuncio WHERE status = 'EM ABERTO' ORDER BY data_publicacao DESC";
    $resultado = $conexao->query($sql) or die($conexao->error);

    if($resultado->num_rows == 0){ 
        echo "<p class='aviso-vazio' style='grid-column: 1/-1; text-align: center; color: #777; padding: 20px;'>Nenhum anúncio disponível no momento.</p>";
    }
    else {
        while($vetorRegistro = $resultado->fetch_array()){
            $id        = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
            $titulo    = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
            $preco     = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
            $imagem    = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8"); 
            $descricao = htmlentities($vetorRegistro[5], ENT_QUOTES, "UTF-8"); 
            $id_aluno  = htmlentities($vetorRegistro[10], ENT_QUOTES, "UTF-8");

            $precoFormatado = number_format($preco, 2, ',', '.');

            $tagImagem = (!empty($imagem) && file_exists($imagem)) 
                ? "<img src='$imagem' alt='$titulo' style='width: 100%; height: 100%; object-fit: cover;'>" 
                : "<div class='foto-placeholder'>Sem Imagem</div>";

            // HTML idêntico, mas SEM o botão de coração
            echo "
            <article class='card-anuncio' style='position: relative;'>
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
                $id         = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
                $titulo     = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
                $categoria  = htmlentities($vetorRegistro[2], ENT_QUOTES, "UTF-8"); 
                $preco      = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
                $imagem     = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8");
                $descricao  = htmlentities($vetorRegistro[5], ENT_QUOTES, "UTF-8"); 
                $status     = htmlentities($vetorRegistro[6], ENT_QUOTES, "UTF-8"); 

                $precoFormatado = number_format($preco, 2, ',', '.');

                // 🟢 ETAPA 2 ATUALIZADA: Nova lógica para os 3 status do ENUM
                if ($status === 'EM NEGOCIACAO') {
                    $classeStatus = 'negociacao'; 
                    $textoStatus  = '🤝 Em Negociação';
                } elseif ($status === 'VENDIDO') {
                    $classeStatus = 'vendido'; 
                    $textoStatus  = '💰 Vendido';
                } else {
                    $classeStatus = 'ativo'; // Representa o 'EM ABERTO'
                    $textoStatus  = '🟢 Disponível';
                }

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
                          <button class='btn-acao editar' onclick='prepararEdicao($id, \"$titulo\", \"$categoria\", $preco, \"$descricao\", \"$imagem\", \"$status\")'>Editar</button>
                          <button class='btn-acao excluir' onclick='confirmarExclusao($id)'>Excluir</button>
                      </div>
                  </article>";
            }
        }
    }

    function listarFavoritosDoAluno($conexao, $anuncio, $id_aluno_logado){
    $sql = "SELECT a.* FROM $anuncio a 
            INNER JOIN favoritos f ON a.id = f.id_anuncio 
            WHERE f.id_aluno = $id_aluno_logado AND a.status = 'EM ABERTO'
            ORDER BY f.data_favoritado DESC";
            
    $resultado = $conexao->query($sql) or die($conexao->error);

    if($resultado->num_rows == 0){ 
        echo "<p class='aviso-vazio' style='grid-column: 1/-1; text-align: center; color: #777; padding: 20px;'>Você ainda não favoritou nenhum anúncio.</p>";
    }
    else {
        while($vetorRegistro = $resultado->fetch_array()){
            $id        = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
            $titulo    = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
            $preco     = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
            $imagem    = htmlentities($vetorRegistro[4], ENT_QUOTES, "UTF-8"); 
            $id_aluno  = htmlentities($vetorRegistro[10], ENT_QUOTES, "UTF-8");

            $precoFormatado = number_format($preco, 2, ',', '.');

            $tagImagem = (!empty($imagem) && file_exists($imagem)) 
                ? "<img src='$imagem' alt='$titulo' style='width: 100%; height: 100%; object-fit: cover;'>" 
                : "<div class='foto-placeholder'>Sem Imagem</div>";

            // REMOVIDO o <button class='btn-favorito'> para ser apenas exibição
            echo "
            <article class='card-anuncio' style='position: relative;' data-id-anuncio='$id'>
                <div class='badge-campus'>Favoritado ❤️</div>
                
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