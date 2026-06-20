<?php
 class Avaliacoes {
  public $id;
  public $nota;
  public $comentario;
  public $dataAvaliacao;
  public $respostaVendedor;
  public $idAnuncio;
  public $idComprador;
 
  function receberDadosForm($conexao)
   {
   $this->id               = null; 
   $this->nota             = trim($conexao->escape_string($_POST["nota"]));
   $this->comentario       = trim($conexao->escape_string($_POST["comentario-aval"]));
   $this->dataAvaliacao    = date("Y-m-d");
   $this->respostaVendedor = null; // Começa sem resposta
   $this->idAnuncio        = trim($conexao->escape_string($_POST["id-anuncio"]));
   $this->idComprador      = trim($conexao->escape_string($_POST["id-comprador"]));
   }

  function cadastrar($conexao, $nomeDaTabelaAvaliacao)
   {
   $sql = "INSERT $nomeDaTabelaAvaliacao VALUES(
             null,
            '$this->nota',
            '$this->comentario',
            '$this->dataAvaliacao',
            null, 
            '$this->idAnuncio',
            '$this->idComprador')";

   $conexao->query($sql) or die($conexao->error);
   } 

  // Método para o vendedor responder a avaliação recebida (usando padrão de UPDATE)
  function responderAvaliacao($conexao, $nomeDaTabelaAvaliacao)
   {
   $idAvaliacao = trim($conexao->escape_string($_POST["id-avaliacao"]));
   $resposta    = trim($conexao->escape_string($_POST["resposta-vendedor"]));

   $sql = "UPDATE $nomeDaTabelaAvaliacao SET resposta_vendedor = '$resposta' WHERE id = '$idAvaliacao'";
   $conexao->query($sql) or die($conexao->error);
   }
 }
?>