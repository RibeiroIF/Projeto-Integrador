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
   $this->senha      = trim($conexao->escape_string($_POST["senha"]));
   $this->senha      = password_hash($this->senha, PASSWORD_ARGON2I);
/*    $this->senha2       = hash("sha256", trim($conexao->escape_string($_POST["senha2"])));
   
   if($this->senha = $this->senha2){
    $this->senha        = password_hash($this->senha, PASSWORD_ARGON2I);
   } */

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

   $login = trim($conexao->escape_string($_POST["email"]));
   $senha = trim($conexao->escape_string($_POST["senha"]));

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
    $this->redirecionarPagina("../php/index.php");
   }
   else{
    echo "<script>
            alert('Informações de usuário incorretas. Por favor, tente novamente.');
            window.location.href = 'login.php';
        </script>";
   }
  }

  function redirecionarPagina($endereco){
    header("location: $endereco");
  }

  function testarSessao(){
   session_start();
   if(!isset($_SESSION) OR !isset($_SESSION["conectado"]) OR $_SESSION["conectado"] != true){
      die("<p> Você não está logado! <a href='../php/login.php'> Efetuar login </a> </p>");
    }
  }

  function logout(){
    session_start();
    $_SESSION = [];
    session_destroy();
    $this->redirecionarPagina("../php/login.php");
  }
 }
?>