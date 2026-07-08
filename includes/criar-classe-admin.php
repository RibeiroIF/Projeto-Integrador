<?php
class Admin {
    public $login;
    public $senha;
 
    function receberDadosForm($conexao){
        $loginTratado = isset($_POST["email"]) ? $_POST["email"] : (isset($_POST["login"]) ? $_POST["login"] : "");
        $this->login = trim($conexao->escape_string($loginTratado));
        
        $senhaPura = isset($_POST["senha"]) ? $_POST["senha"] : "";
        
        $this->senha = password_hash($senhaPura, PASSWORD_ARGON2I);
    }

    function cadastrar($conexao, $tabelaAdmin){
        $sql = "INSERT INTO $tabelaAdmin VALUES(
                null,
                '$this->login',
                '$this->senha')";

        $conexao->query($sql) or die($conexao->error);
    }
   
    function logar($conexao, $tabelaAdmin){
        $login = trim($conexao->escape_string($_POST["email"]));
        $senha = trim($_POST["senha"]); 

        $sql = "SELECT id, senha FROM $tabelaAdmin WHERE login='$login'";
        $resultado = $conexao->query($sql) or die($conexao->error);

        $senhaCorreta = false;
        $id_admin = null;

        if($resultado->num_rows > 0){
            $vetorRegistro = $resultado->fetch_array();
            $id_admin = $vetorRegistro['id']; 
            $senhaCriptografadaDoBanco = $vetorRegistro['senha']; 
                        $senhaCorreta = password_verify($senha, $senhaCriptografadaDoBanco);
        }

        if($senhaCorreta){
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION["conectado"] = true;
            $_SESSION["usuario_tipo"] = "admin"; 
            $_SESSION["id_admin"] = $id_admin;          
            $_SESSION["login_admin"] = $login;        
            
            header("Location: index-admin.php");
            exit(); 
        }
        else{
            echo "<script>
              alert('Informações de usuário incorretas. Por favor, tente novamente.');
              window.location.href = 'login.php';
          </script>";
        }
    }
}
?>