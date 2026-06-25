<!DOCTYPE html>
<html lang="pt-BR">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title> Sistema de login com sessões em PHP </title>
 <link rel="stylesheet" href="../css/formata-login.css">
</head>

<body>
 <h1> Login de usuário </h1>

 <form action="login.php" method="post">
  <fieldset>
   <legend> Módulo de login de usuário </legend>

   <label class="alinha"> Login: </label>
   <input type="text" name="login" autofocus> <br>

   <label class="alinha"> Senha: </label>
   <input type="password" name="senha"> 

   <div>
    <button name="logar"> Logar usuário </button>
   </div>
  </fieldset>
 </form>

 <?php
  require "../includes/criar-classe-banco-de-dados.inc.php";
  require "../includes/criar-classe-usuario.inc.php";

  $banco = new BancoDeDados("localhost", "root", "", "LOGIN", "usuarios");

  $conexao = $banco->criarConexao();
  $banco->criarBanco($conexao);
  $banco->abrirBanco($conexao);
  $banco->definirCharset($conexao);
  $banco->criarTabela($conexao);

  $usuario = new Usuarios();

  if(isset($_POST['logar']))
   {
   $usuario->logar($conexao, $banco->nomeDaTabela);
   }

   $banco->desconectar($conexao);
 ?>
 
</body>
</html>