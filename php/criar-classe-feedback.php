<?php
 class Feedbacks {
  public $id;
  public $descricao;
  public $dataFeedback;
  public $idUsuario;
 
  function receberDadosForm($conexao)
   {
   $this->id           = null; 
   $this->descricao    = trim($conexao->escape_string($_POST["descricao-feedback"]));
   $this->dataFeedback = date("Y-m-d");
   $this->idUsuario    = trim($conexao->escape_string($_POST["id-usuario"]));
   }

  function cadastrar($conexao, $nomeDaTabelaFeedback)
   {
   $sql = "INSERT $nomeDaTabelaFeedback VALUES(
             null,
            '$this->descricao',
            '$this->dataFeedback',
            '$this->idUsuario')";

   $conexao->query($sql) or die($conexao->error);
   } 
 }
?>