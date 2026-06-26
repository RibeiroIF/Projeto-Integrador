<?php
 class Alunos {
  public $nome;
  public $email;
  public $senha;
  public $senha2;
  public $campus;
  public $whatsapp;
  public $bio;
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

    // ALTERAÇÃO: Adicionado 'id' e 'email' no SELECT além da senha
    // Nota: Ajuste o nome da coluna 'id' caso no seu banco seja 'id_aluno' ou similar
    $sql = "SELECT id, email, senha FROM $tabelaAluno WHERE email='$login'";
    $resultado = $conexao->query($sql) or die($conexao->error);

    $senhaDoBanco = false;
    $dadosUsuario = null; // Variável para guardar os dados temporariamente

    if($conexao->affected_rows != 0){
      $vetorRegistro = $resultado->fetch_array();
      
      // Armazenamos o registro completo para usar caso a senha esteja correta
      $dadosUsuario = $vetorRegistro; 
      
      // Como mudamos o SELECT, a senha agora está no índice [2] (id=0, email=1, senha=2)
      $senhaCriptografada = $vetorRegistro[2];
      $senhaDoBanco = password_verify($senha, $senhaCriptografada);
    }

    if($senhaDoBanco){
      // Se a sessão já não tiver sido iniciada em outro lugar, inicia aqui
      if (session_status() === PHP_SESSION_NONE) {
          session_start();
      }
      
      $_SESSION["conectado"] = true;
      
      // PASSO 2 INCLUÍDO AQUI: Guardando os dados reais do usuário logado na sessão
      $_SESSION["id_aluno"]    = $dadosUsuario['id'];    // ou $dadosUsuario[0]
      $_SESSION["email_aluno"] = $dadosUsuario['email']; // ou $dadosUsuario[1]
      $_SESSION["usuario_login"] = $login;

      $this->redirecionarPagina("../php/index.php");
    }
    else{
      echo "<script>
              alert('Informações de usuário incorretas. Por favor, tente novamente.');
              window.location.href = 'login.php';
          </script>";
    }
  }

  function carregarDadosPerfil($conexao, $tabelaAluno, $id_aluno) {
    $sql = "SELECT * FROM $tabelaAluno WHERE id = '$id_aluno'";
    $resultado = $conexao->query($sql) or die($conexao->error);
    
    if ($resultado->num_rows > 0) {
        $dados = $resultado->fetch_assoc();
        
        // Puxam do banco de dados (colunas reais)
        $this->nome  = htmlentities($dados['nome'] ?? '', ENT_QUOTES, "UTF-8");
        $this->email = htmlentities($dados['email'] ?? '', ENT_QUOTES, "UTF-8");
        
        // Como NÃO estão no banco, pegam do formulário (POST) se enviado, ou ficam vazios
        $this->campus   = $_POST['campus'] ?? ''; 
        $this->whatsapp = htmlentities($_POST['whatsapp'] ?? '', ENT_QUOTES, "UTF-8");
        $this->bio      = htmlentities($_POST['bio'] ?? '', ENT_QUOTES, "UTF-8");
        
        if (!empty($dados['data_cadastro'])) { 
            $data = new DateTime($dados['data_cadastro']);
            $this->dataCadastro = $data->format('d/m/Y');
        } else {
            $this->dataCadastro = "N/A";
        }
    }
}

  function atualizarPerfil($conexao, $tabelaAluno){
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(isset($_SESSION["id_aluno"])){
        $id_usuario = $_SESSION["id_aluno"];
        
        $this->nome     = trim($conexao->escape_string($_POST["nome"]));
        $this->campus   = trim($conexao->escape_string($_POST["campus"]));
        $this->whatsapp = trim($conexao->escape_string($_POST["whatsapp"]));
        $this->bio      = trim($conexao->escape_string($_POST["bio"]));

        // REMOVIDO o campus do UPDATE, atualizando APENAS o nome que existe no banco
        $sql = "UPDATE $tabelaAluno SET nome = '$this->nome' WHERE id = '$id_usuario'";
        
        if($conexao->query($sql)){
            // Removido o redirecionamento bruto por window.location para a página não perder o POST atualizado antes de renderizar
            echo "<script>alert('Perfil atualizado na interface!');</script>";
        } else {
            echo "<script>alert('Erro ao atualizar: " . $conexao->error . "');</script>";
        }
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