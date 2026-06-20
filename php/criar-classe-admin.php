<?php
 class Admin {
  public $login;
  public $senha;
 
  function receberDadosForm($conexao){
   $this->login = trim($conexao->escape_string($_POST["login"]));
   $senha = "123";
   $senhaCriptografada = password_hash($senha, PASSWORD_ARGON2I);
   $this->senha = $senhaCriptografada;
   }

  function cadastrar($conexao, $tabelaAdmin){
   $sql = "INSERT $tabelaAdmin VALUES(
              null,
            '$this->login',
            '$this->senha')";

   $conexao->query($sql) or die($conexao->error);
   }
   
   function logar($conexao, $tabelaAdmin){

   $login = trim($conexao->escape_string($_POST["login"]));
   $senha = trim($conexao->escape_string($_POST["senha"]));
   $senhaCriptografada = password_hash($senha, PASSWORD_ARGON2I);

   $sql = "SELECT senha FROM $tabelaAdmin WHERE login='$login'";
   $resultado = $conexao->query($sql) or die($conexao->error);

   $senhaDoBanco = false;

   if($conexao->affected_rows != 0){
    $vetorRegistro = $resultado->fetch_array();
    $senhaCriptografada = $vetorRegistro[0];
    $senhaDoBanco = password_verify($senha, $senhaCriptografada);
   }

   if($senhaDoBanco){
    session_start();
    $_SESSION["conectado"] = true;
    header("../php/homepage.php");
   }
   else{
    echo "<p> Informações de usuário incorretas. </p>";
   }
  }
 }
?>