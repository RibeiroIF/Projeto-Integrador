<?php
/*  require_once "executar-banco.php";

 $banco = new BancoDeDados("localhost", "root", "", "db_integrador", "usuario", "administrador", "aluno", "categoria", "anuncio", "avaliacao", "denuncia", "feedback");
 $conexao = $banco->criarConexao();
 $banco->abrirBanco($conexao);

 $aluno = new Alunos();

 // 1. Ambas as classes capturam o que precisam do $_POST
 $usuario->receberDadosForm($conexao);
 //$aluno->receberDadosForm($conexao);

 // 2. Cadastra na tabela pai (usuario) e armazena o ID gerado
 $idGerado = $usuario->cadastrar($conexao, $banco->usuario);

 // 3. Cadastra na tabela filha (aluno) associando ao ID criado
 $aluno->cadastrar($conexao, $banco->aluno, $idGerado);

 echo "<p>Aluno cadastrado com sucesso!</p>";
 $banco->desconectar($conexao); */
?>