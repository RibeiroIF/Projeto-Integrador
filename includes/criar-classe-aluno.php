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
   $this->nome         = trim($conexao->escape_string($_POST["nome"]));
   $this->email        = trim($conexao->escape_string($_POST["email"]));
   $this->senha      = trim($conexao->escape_string($_POST["senha"]));
   $this->senha      = password_hash($this->senha, PASSWORD_ARGON2I);
   
   $this->whatsapp = isset($_POST["whatsapp"]) ? trim($conexao->escape_string($_POST["whatsapp"])) : '';

   $this->dataCadastro = date("Y-m-d"); 
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

    $sql = "SELECT id, email, senha FROM $tabelaAluno WHERE email='$login'";
    $resultado = $conexao->query($sql) or die($conexao->error);

    $senhaDoBanco = false;
    $dadosUsuario = null; 

    if($conexao->affected_rows != 0){
      $vetorRegistro = $resultado->fetch_array();
      
      $dadosUsuario = $vetorRegistro; 
      
      $senhaCriptografada = $vetorRegistro[2];
      $senhaDoBanco = password_verify($senha, $senhaCriptografada);
    }

    if($senhaDoBanco){
      if (session_status() === PHP_SESSION_NONE) {
          session_start();
      }
      
      $_SESSION["conectado"] = true;
      
      $_SESSION["id_aluno"]    = $dadosUsuario['id'];    
      $_SESSION["email_aluno"] = $dadosUsuario['email']; 
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
        
        $nome_bruto = $dados['nome'] ?? '';
        $this->nome = mb_check_encoding($nome_bruto, 'UTF-8') ? $nome_bruto : utf8_encode($nome_bruto);
        
        $email_bruto = $dados['email'] ?? '';
        $this->email = mb_check_encoding($email_bruto, 'UTF-8') ? $email_bruto : utf8_encode($email_bruto);
        
        $this->whatsapp = $dados['whatsapp'] ?? '';
        $this->foto_perfil = $dados['foto_perfil'] ?? null;
        $this->campus   = $_POST['campus'] ?? ($this->campus ?? 'Florianópolis'); 
        
        $bio_bruta = $dados['bio'] ?? '';
        $this->bio = mb_check_encoding($bio_bruta, 'UTF-8') ? $bio_bruta : utf8_encode($bio_bruta);
        
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
    
    $id_aluno = (int)$_SESSION['id_aluno'];

    if ($arquivosEnviados && isset($arquivosEnviados['foto_perfil']) && $arquivosEnviados['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($arquivosEnviados['foto_perfil']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extensao, $extensoes_permitidas)) {
            $diretorio_destino = __DIR__ . '/../imagens/perfil/';
            
            if (!is_dir($diretorio_destino)) {
                mkdir($diretorio_destino, 0777, true);
            }

            $busca_foto = $conexao->query("SELECT foto_perfil FROM $tabelaAluno WHERE id = $id_aluno");
            $resultado_foto = $busca_foto->fetch_assoc();
            $foto_antiga = $resultado_foto['foto_perfil'] ?? null;

            $nome_base = "perfil_" . $id_aluno . "_" . time() . "." . $extensao;
            $caminho_servidor = $diretorio_destino . $nome_base;
            $caminho_banco = "imagens/perfil/" . $nome_base; 

            if (move_uploaded_file($arquivosEnviados['foto_perfil']['tmp_name'], $caminho_servidor)) {
                if (!empty($foto_antiga)) {
                    $caminho_deletar = __DIR__ . '/../' . $foto_antiga;
                    if (file_exists($caminho_deletar)) {
                        @unlink($caminho_deletar);
                    }
                }
                
                $conexao->query("UPDATE $tabelaAluno SET foto_perfil = '$caminho_banco' WHERE id = $id_aluno");
                
                $caminho_navegador = "../" . $caminho_banco;

                echo "<script>
                    const imgPreview = window.parent.document.getElementById('img-perfil-preview');
                    const letraPreview = window.parent.document.getElementById('avatar-letra-preview');
                    if (imgPreview) {
                        imgPreview.src = '$caminho_navegador';
                    } else if (letraPreview) {
                        const novaImg = window.parent.document.createElement('img');
                        novaImg.id = 'img-perfil-preview';
                        novaImg.src = '$caminho_navegador';
                        novaImg.className = 'perfil-avatar-img';
                        letraPreview.parentNode.replaceChild(novaImg, letraPreview);
                    }
                    
                    // Sincroniza a imagem interna do bloco aside lateral usando as suas classes oficiais
                    const imgAside = window.parent.document.querySelector('.avatar-grande img');
                    const boxAside = window.parent.document.querySelector('.avatar-grande');
                    if (imgAside) {
                        imgAside.src = '$caminho_navegador';
                    } else if (boxAside) {
                        boxAside.innerHTML = `<img src=\"$caminho_navegador\" alt=\"Foto\" class=\"perfil-aside-avatar-img\">`;
                    }
                </script>";
            }
        }
        exit();
    }

    if (isset($_POST['nome'])) {
        $nome     = $conexao->escape_string(trim($_POST['nome']));
        $whatsapp = $conexao->escape_string(trim($_POST['whatsapp']));

        if (isset($_POST['campus'])) $this->campus = $_POST['campus'];
        if (isset($_POST['bio'])) $this->bio = htmlentities($_POST['bio'], ENT_QUOTES, "UTF-8");

        $sql = "UPDATE $tabelaAluno SET 
                    nome = '$nome', 
                    whatsapp = '$whatsapp'
                WHERE id = $id_aluno";

        if ($conexao->query($sql)) {
            echo "<script>window.location.href = 'index.php?tela=home';</script>";
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