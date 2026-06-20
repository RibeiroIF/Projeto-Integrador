<?php
 class Alunos {
  public $nome;
  public $email;
  public $senha;
  public $senha2;
  public $dataCadastro;
 
  function receberDadosForm($conexao){
   // O ID é nulo no cadastro pois é auto_increment no banco
   $this->nome         = trim($conexao->escape_string($_POST["nome"]));
   $this->email        = trim($conexao->escape_string($_POST["email"]));
   // Criptografando a senha em SHA-256 para segurança antes de salvar
   $this->senha        = hash("sha256", trim($conexao->escape_string($_POST["senha"])));
   $this->senha2       = hash("sha256", trim($conexao->escape_string($_POST["senha2"])));
   
   if($this->senha = $this->senha2){
    $this->senha        = password_hash($this->senha, PASSWORD_ARGON2I);
   }

   $this->dataCadastro = date("Y-m-d"); // Capta a data atual do sistema
   }

  function cadastrar($conexao, $tabelaAluno){
   $sql = "INSERT $tabelaAluno VALUES(
             null,
            '$this->nome',
            '$this->email',
            '$this->senha',
            '$this->dataCadastro')";

   $conexao->query($sql) or die($conexao->error);
   }
   
   function logar($conexao, $tabelaAluno){

   $login = trim($conexao->escape_string($_POST["login"]));
   $senha = trim($conexao->escape_string($_POST["senha"]));
   $senhaCriptografada = password_hash($senha, PASSWORD_ARGON2I);

   $sql = "SELECT senha FROM $tabelaAluno WHERE email='$login'";
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
    header("../php/tela/index.php");
   }
   else{
    echo "<p> Informações de usuário incorretas. </p>";
   }
  }
 }
?>