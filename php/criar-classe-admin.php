<?php
class Admin {
    public $login;
    public $senha;
 
    // Captura e limpa os dados enviados para a classe
    function receberDadosForm($conexao){
        // Se os dados vierem do script automático ou do formulário, limpamos o login
        $loginTratado = isset($_POST["email"]) ? $_POST["email"] : (isset($_POST["login"]) ? $_POST["login"] : "");
        $this->login = trim($conexao->escape_string($loginTratado));
        
        // Pega a senha enviada pelo POST
        $senhaPura = isset($_POST["senha"]) ? $_POST["senha"] : "";
        
        // Criptografa a senha usando o algoritmo ARGON2I
        $this->senha = password_hash($senhaPura, PASSWORD_ARGON2I);
    }

    // Insere o administrador no banco de dados
    function cadastrar($conexao, $tabelaAdmin){
        // Certifique-se de que sua tabela admin possua as colunas na ordem: id (auto_increment), login, senha
        $sql = "INSERT INTO $tabelaAdmin VALUES(
                null,
                '$this->login',
                '$this->senha')";

        $conexao->query($sql) or die($conexao->error);
    }
   
    // Realiza a verificação de login do administrador
    function logar($conexao, $tabelaAdmin){
        // Na sua tela de login, o campo de texto se chama "email", mas usaremos como o login do admin
        $login = trim($conexao->escape_string($_POST["email"]));
        $senha = trim($_POST["senha"]); 

        // BUSCA ATUALIZADA: Agora busca o id E a senha para salvar na sessão
        $sql = "SELECT id, senha FROM $tabelaAdmin WHERE login='$login'";
        $resultado = $conexao->query($sql) or die($conexao->error);

        $senhaCorreta = false;
        $id_admin = null;

        // Verifica se encontrou exatamente 1 registro
        if($resultado->num_rows > 0){
            $vetorRegistro = $resultado->fetch_array();
            $id_admin = $vetorRegistro['id']; // Pega o ID do admin
            $senhaCriptografadaDoBanco = $vetorRegistro['senha']; // Pega a senha por nome da coluna ou índice
            
            // O password_verify compara a senha digitada pura com o hash criptografado do banco
            $senhaCorreta = password_verify($senha, $senhaCriptografadaDoBanco);
        }

        if($senhaCorreta){
            // Inicializa a sessão se ela ainda não estiver ativa
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // 🌟 CHAVES CORRIGIDAS: Agora batem exatamente com o que o index_admin.php espera!
            $_SESSION["conectado"] = true;
            $_SESSION["usuario_tipo"] = "admin"; 
            $_SESSION["id_admin"] = $id_admin;          // O index_admin precisa desse!
            $_SESSION["login_admin"] = $login;        // O index_admin precisa desse!
            
            // 🌟 ATENÇÃO AQUI: Se o index_admin.php estiver na mesma pasta que o login.php, 
            // mude para: header("Location: index_admin.php");
            header("Location: index_admin.php");
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