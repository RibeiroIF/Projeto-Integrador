<!DOCTYPE html>
<html lang="pt-BR">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title> Conteúdo restrito - página 1 </title>
 <link rel="stylesheet" href="../css/formata-login.css">
</head>

<body>
 <?php
  //antes do início do conteúdo de cada página restrita, vamos acrescentar este trecho de código
  require "../includes/criar-classe-usuario.inc.php";
  $usuario = new Usuarios();
  $usuario->testarSessao();
 ?>
 
 <h1> Bem-vindo, caro usuário! Sinta-se à vontade para explorar e utilizar todo o conteúdo restrito, que você acessa neste momento, de nossa aplicação web. </h1>

 <form action="../php/logout.php" method="post">
  <fieldset>
   <legend> Desconectar usuário </legend>
   <button> Logout do sistema </button>
  </fieldset>
 </form>
</body>
</html>