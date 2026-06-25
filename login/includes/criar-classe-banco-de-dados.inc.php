<?php
 class BancoDeDados 
  {
  public $nomeDoBanco;
  public $nomeDaTabela;
  public $servidor;
  public $usuario;
  public $senha;

  function __construct($servidorBanco, $usuarioBanco, $senhaBanco, $nomeBanco, $nomeTabela)
   {
   $this->servidor      = $servidorBanco;
   $this->usuario       = $usuarioBanco;
   $this->senha         = $senhaBanco;
   $this->nomeDoBanco   = $nomeBanco;
   $this->nomeDaTabela  = $nomeTabela;
   }

  function criarConexao()
   {
   $conexao = new mysqli($this->servidor, $this->usuario, $this->senha) OR die($conexao->error);
   return $conexao;
   }

  function criarBanco($conexao)
   {
   $sql = "CREATE DATABASE IF NOT EXISTS $this->nomeDoBanco";
   $conexao->query($sql) or die($conexao->error);
   }

  function abrirBanco($conexao)
   {
   $conexao->select_db($this->nomeDoBanco);
   }

  function definirCharset($conexao)
   {
   $conexao->set_charset("utf8");
   }

  function criarTabela($conexao)
   {
   $sql = "CREATE TABLE IF NOT EXISTS $this->nomeDaTabela (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            nome VARCHAR(300),
            email VARCHAR(300),
            usuario VARCHAR(300),
            senha VARCHAR(130)
            ) ENGINE=innoDB;";

   $conexao->query($sql) OR die($conexao->error);
   }

 
  function desconectar($conexao)
   {
   $conexao->close();
   }
  }