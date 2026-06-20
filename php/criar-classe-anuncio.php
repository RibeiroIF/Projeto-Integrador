<?php
 class Anuncios {
  public $id;
  public $titulo;
  public $descricao;
  public $preco;
  public $imagem;
  public $statusAnuncio;
  public $dataPublicacao;
  public $dataExpiracao;
  public $visualizacoes;
  public $idVendedor;
  public $idCategoria;
 
  function receberDadosForm($conexao)
   {
   $this->id             = null; 
   $this->titulo         = trim($conexao->escape_string($_POST["titulo"]));
   $this->descricao      = trim($conexao->escape_string($_POST["descricao"]));
   $this->preco          = trim($conexao->escape_string($_POST["preco"]));
   $this->imagem         = $_POST["fotos"];
   $this->statusAnuncio  = "EM ABERTO"; // Status padrão inicial
   $this->dataPublicacao = date("Y-m-d");
   // Define expiração para 30 dias após a publicação
   $this->dataExpiracao  = date("Y-m-d", strtotime("+30 days")); 
   $this->visualizacoes  = 0;
   
   // Esses IDs virão da sessão do usuário logado e do select do formulário
   $this->idVendedor     = trim($conexao->escape_string($_POST["id_vendedor"]));
   $this->idCategoria    = trim($conexao->escape_string($_POST["id_categoria"]));
   }

  function cadastrar($conexao, $anuncio)
   {
   $sql = "INSERT $anuncio VALUES(
             null,
            '$this->titulo',
            '$this->descricao',
            '$this->preco',
            '$this->imagem',
            '$this->statusAnuncio',
            '$this->dataPublicacao',
            '$this->dataExpiracao',
            '$this->visualizacoes',
            '$this->idVendedor',
            '$this->idCategoria')";

   $conexao->query($sql) or die($conexao->error);
   } 

   function listarAnunciosAtivos($conexao, $anuncio)
   {
   $sql = "SELECT * FROM $anuncio WHERE status_anuncio = 'EM ABERTO' ORDER BY data_publicacao DESC";
   $resultado = $conexao->query($sql) or die($conexao->error);

   if($conexao->affected_rows == 0)
    {
    echo "<p class='aviso-vazio'>Nenhum anúncio disponível no momento.</p>";
    }
   else
    {
    // Varre o banco de dados montando a estrutura de cards do seu index.html
    while($vetorRegistro = $resultado->fetch_array())
     {
     $id        = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
     $titulo    = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
     $descricao = htmlentities($vetorRegistro[2], ENT_QUOTES, "UTF-8");
     $preco     = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");
     
     $precoFormatado = number_format($preco, 2, ',', '.');

     echo "<div class='card-anuncio'>
            <div class='img-placeholder'>📷</div>
            <div class='conteudo-card'>
                <span class='preco-tag'>R$ $precoFormatado</span>
                <h3 class='titulo-anuncio'>$titulo</h3>
                <p class='descricao-anuncio'>$descricao</p>
                <button class='btn-detalhes' onclick='verDetalhes($id)'>Ver Detalhes</button>
            </div>
           </div>";
     }
    }
   }
 }
?>