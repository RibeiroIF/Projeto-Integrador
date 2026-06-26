<?php
// Inclui os arquivos necessários
require_once "criar-banco-classificados.php";
require_once "criar-classe-anuncio.php";
require_once "criar-classe-admin.php"; 
require_once "criar-classe-aluno.php";
require_once "criar-classe-avaliacao.php";
require_once "criar-classe-denuncia.php";
require_once "criar-classe-feedback.php";

// Certifica-se de que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SEGURANÇA: Se o administrador não estiver logado, redireciona para a tela de login
if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit();
}

// Cria a conexão com o banco
$banco = new BancoDeDados("localhost", "root", "dadosmain", "db_integrador", "admin", "aluno", "anuncio", "favorito", "avaliacao", "denuncia", "feedback");
$conexao = $banco->criarConexao();
$banco->abrirBanco($conexao);
$banco->definirCharset($conexao);

$anuncios = new Anuncios();

// AÇÃO DE MODERAÇÃO: Se o Admin clicar para deletar um anúncio
if (isset($_POST['deletar-anuncio-admin'])) {
    $id_anuncio_del = intval($_POST['id_anuncio']);
    $conexao->query("DELETE FROM " . $banco->anuncio . " WHERE id = $id_anuncio_del");
    header("Location: index_admin.php?removido=sucesso");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="https://rwnobrega.page/_assets/ifsc-logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pesquisar.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/perfil.css">
    <link rel="stylesheet" href="../css/login.css">
    <script src="../javascript/troca-de-tela.js"></script>
    <title>Classificados IFSC - Painel Admin</title>
</head>
<body>

    <aside class="blocoEsquerdo">
        <nav>
            <ul>
                <li><span class="logo">IFSC Admin</span></li>
                <li><a href="#" id="menu-home" class="menu active" onclick="navegar('tela-home'); navegarMenu('menu-home')">Página Inicial</a></li>
                <li class="admin-link"><a href="#" id="menu-adm" class="menu" onclick="navegar('tela-admin'); navegarMenu('menu-adm')">Painel Adm</a></li>
                <li><a href="login.php" class="logout">Sair</a></li>
            </ul>
        </nav>
    </aside>

    <main class="blocoCentral">
        
        <div id="tela-home" class="main tela-home tela show">
            <header class="header-busca">
                <h2>Explorar Anúncios (Modo Moderador)</h2>
                <div class="search-bar">
                    <input type="text" placeholder="Buscar anúncios para moderar...">
                    <button>Buscar</button>
                </div>
            </header>

            <section class="filtros-rapidos">
                <button class="filter-btn">Livros</button>
                <button class="filter-btn">Eletrônicos</button>
                <button class="filter-btn">Móveis</button>
                <button class="filter-btn">Serviços</button>
            </section>

            <div class="grid-anuncios">
                <?php 
                    // Lista os mesmos anúncios que os alunos veem
                    $anuncios->listarAnunciosAdmin($conexao, $banco->anuncio); 
                ?>
            </div>
        </div>
        
        <div id="tela-admin" class="tela collapse">
            <header class="header-admin">
                <h2>Painel de Controle Administrativo</h2>
                <p>Monitoramento de anúncios e usuários do sistema.</p>
            </header>

            <section class="cards-estatisticas">
                <div class="card-stat">
                    <h3>
                        <?php 
                        // Esta tabela já existe, então funciona perfeitamente!
                        $contAnuncios = $conexao->query("SELECT COUNT(*) FROM " . $banco->anuncio);
                        echo $contAnuncios ? $contAnuncios->fetch_row()[0] : "0"; 
                        ?>
                    </h3>
                    <p>Total de Anúncios</p>
                </div>
                <div class="card-stat">
                    <h3>
                        <?php 
                        // Esta tabela também já existe
                        $contAlunos = $conexao->query("SELECT COUNT(*) FROM " . $banco->aluno);
                        echo $contAlunos ? $contAlunos->fetch_row()[0] : "0"; 
                        ?>
                    </h3>
                    <p>Usuários Ativos</p>
                </div>

                </section>

            <section class="tabela-moderacao">
                <h3>Fila de Moderação (Denúncias)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID Anúncio</th>
                            <th>Motivo / Comentário</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#12</td>
                            <td class="tag-critica">Produto fora das normas institucionais</td>
                            <td>
                                <form method="post" action="index_admin.php" style="display:inline;">
                                    <input type="hidden" name="id_anuncio" value="12"> 
                                    <button type="submit" name="deletar-anuncio-admin" class="btn-tabela ban">Deletar Anúncio</button>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>

    </main> 
    <aside class="blocoDireito">
        <div class="perfil-resumo">
            <h3>Meu Perfil</h3>
            <div class="avatar-grande" style="background-color: #dc3545; color: white;">🛡️</div>
            <p><strong>Moderador:</strong> <?php echo $_SESSION['login_admin']; ?></p>
            <p class="avaliacao">Painel de Proteção</p>
        </div>

        <div class="feedback-section text-center">
            <h4 style="color: #dc3545;">Modo de Moderação</h4>
            <p class="small text-muted">Use seus privilégios com cautela ao remover anúncios do banco de dados.</p>
        </div>
    </aside>
    
    <?php
    if (isset($banco) && isset($conexao)) {
        $banco->desconectar($conexao);
    }
    ?>

</body>
</html>