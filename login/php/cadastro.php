<!DOCTYPE html>
<html lang="pt-BR">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title> Sistema de login com sessões em PHP </title>
 <link rel="stylesheet" href="../css/formata-login.css">
</head>

<body>
 <h1> Cadastro de usuário </h1>

 <form action="cadastro.php" method="post">
  <fieldset>
   <legend> Módulo de cadastro de usuário </legend>

   <label class="alinha"> Nome completo: </label>
   <input type="text" name="nome" autofocus> <br>

   <label class="alinha"> E-mail: </label>
   <input type="email" name="email"> <br>

   <label class="alinha"> Nome de usuário: </label>
   <input type="text" name="login"> <br>

   <label class="alinha"> Senha do usuário: </label>
   <input type="password" name="senha"> 

   <div>
    <button name="cadastrar"> Cadastrar usuário </button>
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

  if(isset($_POST['cadastrar']))
   {
   $usuario->receberDados($conexao);
   $usuario->cadastrar($conexao, $banco->nomeDaTabela);

   //vamos redirecionar o usuário para a primeira página de conteúdo restrito
   $usuario->redirecionarPagina("protegida1.php");
   }

   $banco->desconectar($conexao);
 ?>
 
</body>
</html>