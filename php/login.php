<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="https://rwnobrega.page/_assets/ifsc-logo.png">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/login.css">
    <title>Login - Classificados IFSC</title>
</head>
<body class="body-login">

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="https://rwnobrega.page/_assets/ifsc-logo.png" alt="Logo IFSC" class="login-logo">
                <h1>Classificados IFSC</h1>
                <p>Acesse com suas credenciais do SIGAA</p>
            </div>

            <form action="login.php" class="login-form" method="post">
                <div class="input-group">
                    <label for="email">E-mail Institucional</label>
                    <input type="email" id="email" name="email" placeholder="exemplo@aluno.ifsc.edu.br" required>
                </div>

                <div class="input-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Sua senha institucional" required>
                </div>

                <div class="login-options">
                    <label><input type="checkbox"> Lembrar de mim</label>
                    <a href="#">Esqueceu a senha?</a>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-login" name="logar-aluno">Entrar (Aluno)</button>
                    <button type="submit" class="btn-login" name="logar-admin">Entrar (Admin)</button>
                </div>
            </form>

            <div class="divisor">
                <span>ou</span>
            </div>

            <button type="button" class="btn-cadastro" id="btnAbrirModal">Criar Nova Conta</button>

            <div class="login-footer">
                <p>O acesso é automático para alunos ativos.</p>
            </div>
        </div>
    </div>

    <div id="modalCadastro" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Cadastro Externo</h2>
                <button class="btn-fechar" id="btnFecharModal">&times;</button>
            </div>
            <p class="modal-desc">Para usuários sem e-mail @aluno.ifsc.edu.br</p>

            <form action="login.php" class="login-form" method="post">
                <div class="input-group">
                    <label>Nome:</label>
                    <input type="text" name="nome" placeholder="Seu nome aqui.." required>
                </div>
                <div class="input-group">
                    <label>E-mail (Gmail, Hotmail, etc)</label>
                    <input type="email" name="email" placeholder="seuemail@exemplo.com" required>
                </div>
                
                <div class="input-group">
                    <label>WhatsApp / Telefone</label>
                    <input type="tel" name="whatsapp" placeholder="(48) 99999-9999" required>
                </div>

                <div class="input-group">
                    <label>Crie uma Senha</label>
                    <input type="password" name="senha" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div class="input-group">
                    <label>Confirme a Senha</label>
                    <input type="password" name="senha2" placeholder="Repita a senha" required>
                </div>
                <button type="submit" class="btn-login" name="cadastrar">Finalizar Cadastro</button>
            </form>
        </div>
    </div>

    <script src="../javascript/login.js"></script>

    <?php
    
    require "criar-banco-classificados.php";
    require "criar-classe-aluno.php";
    require "criar-classe-admin.php";

    $banco = new BancoDeDados("localhost", "root", "", "db_integrador", "admin", "aluno", "anuncio", "favoritos", "avaliacao", "denuncia", "feedback");

    $conexao = $banco->criarConexao();
    $banco->criarBanco($conexao);
    $banco->abrirBanco($conexao);
    $banco->definirCharset($conexao);
    $banco->criarTabelaAluno($conexao);
    $banco->criarTabelaAdmin($conexao);

    $alunos = new Alunos();
    $admins = new Admin();

    // ==========================================
    // SISTEMA DE SEED (Criação automática dos Admins)
    // ==========================================
    $resultadoAdmins = $conexao->query("SELECT COUNT(*) FROM " . $banco->admin);
    $totalAdmins = $resultadoAdmins->fetch_row()[0];

    // Se a tabela de admins estiver zerada, cria o Gabriel e a Clara
    if ($totalAdmins == 0) {
        // Criando Gabriel
        $_POST['login'] = 'gabriel.souza.0702@gmail.com';
        $_POST['senha'] = 'gab123'; // Defina a senha real dele aqui
        $admins->receberDadosForm($conexao);
        $admins->cadastrar($conexao, $banco->admin);

        // Criando Clara
        $_POST['login'] = 'clara@gmail.com';
        $_POST['senha'] = 'clara123'; // Defina a senha real dela aqui
        $admins->receberDadosForm($conexao);
        $admins->cadastrar($conexao, $banco->admin);
    }
    // ==========================================

    if(isset($_POST['logar-aluno'])){
        $alunos->logar($conexao, $banco->aluno);
    }
    if(isset($_POST['logar-admin'])){
        $admins->logar($conexao, $banco->admin);
    }

    if(isset($_POST['cadastrar'])){
        $alunos->receberDadosForm($conexao);
        $alunos->cadastrar($conexao, $banco->aluno);
        $alunos->redirecionarPagina("../php/index.php");
    }

    $banco->desconectar($conexao);

    ?>
</body>
</html>