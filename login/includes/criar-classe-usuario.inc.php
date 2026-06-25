<?php
 class Usuarios
  {
  public $nome;
  public $email;
  public $usuario;
  public $senha;

  function receberDados($conexao)
   {
   $this->nome       = trim($conexao->escape_string($_POST["nome"]));
   $this->email      = trim($conexao->escape_string($_POST["email"]));
   $this->usuario    = trim($conexao->escape_string($_POST["login"]));
   $this->senha      = trim($conexao->escape_string($_POST["senha"]));

   $this->senha      = password_hash($this->senha, PASSWORD_ARGON2I);
   }

  function cadastrar($conexao, $nomeDaTabela)
   {
   $sql = "INSERT $nomeDaTabela VALUES(
            null,
            '$this->nome',
            '$this->email',
            '$this->usuario',
            '$this->senha')";

   $conexao->query($sql) or die($conexao->error);
   //deposi de efetuado o cadastro do usuário, criamos a variável de sessão para permitir seu acesso ao conteúdo restrito
   session_start();
   $_SESSION["conectado"] = true;
   }

  function logar($conexao, $nomeDaTabela)
   {
   $login = trim($conexao->escape_string($_POST["login"]));
   $senha = trim($conexao->escape_string($_POST["senha"]));

   //vamos buscar a senha do usuário, no banco, que já está criptografada. Para isso, pesquisamos, antes, pelo nome de usuário no banco
   $sql = "SELECT senha FROM $nomeDaTabela WHERE usuario='$login'";
   $resultado = $conexao->query($sql) or die($conexao->error);

   $senhaDoBanco = false;

   if($conexao->affected_rows != 0)
    {
    //entrando aqui, o MySQL encontrou o usuário registrado no banco
    $vetorRegistro = $resultado->fetch_array();
    $senhaCriptografada = $vetorRegistro[0];

    //agora, vamos solicitar ao PHP que verifique se a senha regular e a senha criptografada são iguais
    $senhaDoBanco = password_verify($senha, $senhaCriptografada);
    }

   //testando se a comparação entre as duas senhas é verdadeira ou falsa
   if($senhaDoBanco)
    {
    //aqui, temos login bem-sucedido
    session_start();
    $_SESSION["conectado"] = true;

    //redirecionamos o usuário para o conteúdo restrito
    $this->redirecionarPagina("../php/protegida1.php");
    }
   else
    {
    //login falhou
    echo "<p> Credenciais de autenticação de usuário incorretas. </p>";
    }
   }

  function redirecionarPagina($endereco)
   {
   //comando de redirecionamento automático do PHP. Isso substitui o html com links
   header("location: $endereco");
   }

  function testarSessao()
   {
   //este método é invocado toda vez que um usuário tenta acessar, por qualquer caminho, o conteúdo protegido de nossa aplicação
   session_start();
   if(!isset($_SESSION) OR !isset($_SESSION["conectado"]) OR $_SESSION["conectado"] != true)
    {
    die("<p> Você não está logado! <a href='../php/login.php'> Efetuar login </a> </p>");
    }
   }

  function logout()
   {
   //desconectar o usuário de nossa aplicação
   session_start();
   $_SESSION = [];
   session_destroy();
   $this->redirecionarPagina("../php/login.php");
   }
 }