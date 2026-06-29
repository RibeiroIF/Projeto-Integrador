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
  public $foto_perfil;
 
  function receberDadosForm($conexao){
   // O ID é nulo no cadastro pois é auto_increment no banco
   $this->nome         = trim($conexao->escape_string($_POST["nome"]));
   $this->email        = trim($conexao->escape_string($_POST["email"]));
   // Criptografando a senha em SHA-256 para segurança antes de salvar
   $this->senha      = trim($conexao->escape_string($_POST["senha"]));
   $this->senha      = password_hash($this->senha, PASSWORD_ARGON2I);
   
   $this->whatsapp = isset($_POST["whatsapp"]) ? trim($conexao->escape_string($_POST["whatsapp"])) : '';

   $this->dataCadastro = date("Y-m-d"); // Capta a data atual do sistema
   }

  function cadastrar($conexao, $tabelaAluno){
   $sql = "INSERT $tabelaAluno VALUES(
             null,
            '$this->nome',
            '$this->email',
            '$this->senha',
            '$this->whatsapp',
            '$this->dataCadastro',
            null)";

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
        
        $this->nome  = htmlentities($dados['nome'] ?? '', ENT_QUOTES, "UTF-8");
        $this->email = htmlentities($dados['email'] ?? '', ENT_QUOTES, "UTF-8");
        
        // AGORA PUXA DIRETO DO BANCO DE DADOS
        $this->whatsapp = htmlentities($dados['whatsapp'] ?? '', ENT_QUOTES, "UTF-8");
        
        // 🟢 Mantido aqui a leitura da nova coluna do banco de dados
        $this->foto_perfil  = $dados['foto_perfil'] ?? null;
        
        // Mantidos temporários exatamente conforme seu código original
        $this->campus   = $_POST['campus'] ?? ($this->campus ?? 'Florianópolis'); 
        $this->bio      = htmlentities($_POST['bio'] ?? ($dados['bio'] ?? ''), ENT_QUOTES, "UTF-8");
        
        if (!empty($dados['data_cadastro'])) { 
            $data = new DateTime($dados['data_cadastro']);
            $this->dataCadastro = $data->format('d/m/Y');
        } else {
            $this->dataCadastro = "N/A";
        }
    }
}

  public function atualizarPerfil($conexao, $tabelaAluno, $arquivosEnviados = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $id_aluno = $_SESSION['id_aluno'];

    // 📸 CENÁRIO A: Upload automático da foto pelo iframe oculto
    if ($arquivosEnviados && isset($arquivosEnviados['foto_perfil']) && $arquivosEnviados['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($arquivosEnviados['foto_perfil']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extensao, $extensoes_permitidas)) {
            $diretorio_destino = 'php/uploads';
            if (!is_dir($diretorio_destino)) {
                mkdir($diretorio_destino, 0777, true);
            }

            // Busca a foto antiga para deletar do servidor
            $busca_foto = $conexao->query("SELECT foto_perfil FROM $tabelaAluno WHERE id = $id_aluno");
            $resultado_foto = $busca_foto->fetch_assoc();
            $foto_antiga = $resultado_foto['foto_perfil'] ?? null;

            $novo_nome_arquivo = $diretorio_destino . "/perfil_" . $id_aluno . "_" . time() . "." . $extensao;

            if (move_uploaded_file($arquivosEnviados['foto_perfil']['tmp_name'], $novo_nome_arquivo)) {
                if (!empty($foto_antiga) && file_exists($foto_antiga)) {
                    unlink($foto_antiga);
                }
                
                // Grava APENAS a nova foto no banco de dados
                $conexao->query("UPDATE $tabelaAluno SET foto_perfil = '$novo_nome_arquivo' WHERE id = $id_aluno");
            }
        }
        // Matamos a execução aqui! Como foi enviado pelo iframe oculto, 
        // o PHP responde em branco e a página principal NÃO sofre reload.
        exit(); 
    }

    // 📝 CENÁRIO B: Salvamento textual (Quando clica em "Salvar Alterações")
    if (isset($_POST['nome'])) {
        $nome     = $conexao->escape_string(trim($_POST['nome']));
        $whatsapp = $conexao->escape_string(trim($_POST['whatsapp']));

        // Mantém campus e bio no escopo do objeto
        if (isset($_POST['campus'])) $this->campus = $_POST['campus'];
        if (isset($_POST['bio'])) $this->bio = htmlentities($_POST['bio'], ENT_QUOTES, "UTF-8");

        // Atualiza apenas os dados textuais, preservando a foto que já está no banco
        $sql = "UPDATE $tabelaAluno SET 
                    nome = '$nome', 
                    whatsapp = '$whatsapp'
                WHERE id = $id_aluno";

        if ($conexao->query($sql)) {
            // Agora sim, redireciona limpando o POST e aplicando o parâmetro de tela para a sua SPA
            echo "<script>window.location.href = 'index.php';</script>";
            exit();
        } else {
            die("Erro ao atualizar perfil: " . $conexao->error);
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