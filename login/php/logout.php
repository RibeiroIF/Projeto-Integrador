<?php
 //inserimos a include com a classe usuário, para podermos instanciar um objeto usuario e invocarmos seu método logout
 require "../includes/criar-classe-usuario.inc.php";
 $usuario = new Usuarios();
 $usuario->logout();