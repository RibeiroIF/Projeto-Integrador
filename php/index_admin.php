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
    $motivo = isset($_POST['motivo_exclusao']) ? trim($_POST['motivo_exclusao']) : 'Violação dos termos de uso.';

    // 1. CAPTURA DOS DADOS DO ALUNO E ANÚNCIO (Antes de desativar)
    $sql_busca_dono = "SELECT al.nome, al.whatsapp, a.titulo 
                       FROM `{$banco->anuncio}` a
                       INNER JOIN `{$banco->aluno}` al ON a.id_aluno = al.id 
                       WHERE a.id = $id_anuncio_del";
    
    $res_dono = $conexao->query($sql_busca_dono);
    
    $whatsapp = "";
    $nome_aluno = "";
    $titulo_anuncio = "";

    if ($res_dono && $res_dono->num_rows > 0) {
        $dados_dono = $res_dono->fetch_assoc();
        $nome_aluno = $dados_dono['nome'];
        $titulo_anuncio = $dados_dono['titulo'];
        $whatsapp = preg_replace('/\D/', '', $dados_dono['whatsapp']); 
    }

    // 2. EXCLUSÃO LÓGICA (Muda o status para deletado = 1)
    $conexao->query("UPDATE `{$banco->anuncio}` SET deletado = 1 WHERE id = $id_anuncio_del");

    // 3. REDIRECIONAMENTO COM DISPARO DO WHATSAPP
    if (!empty($whatsapp) && strlen($whatsapp) >= 10) {
        $texto_mensagem = "Olá, *{$nome_aluno}*!\n\n";
        $texto_mensagem .= "Informamos que o seu anúncio *\"{$titulo_anuncio}\"* foi removido da plataforma IFSC Classificados pela equipe de moderação.\n\n";
        $texto_mensagem .= "*Motivo da exclusão:*\n_{$motivo}_";

        $texto_url = urlencode($texto_mensagem);
        $link_whatsapp = "https://api.whatsapp.com/send?phone=55{$whatsapp}&text={$texto_url}";

        echo "<script>
            window.open('{$link_whatsapp}', '_blank');
            window.location.href = 'index_admin.php?removido=sucesso';
        </script>";
        exit();
    } else {
        header("Location: index_admin.php?removido=sucesso");
        exit();
    }
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
    <link rel="stylesheet" href="../css/pesquisar.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/perfil.css">
    <link rel="stylesheet" href="../css/login.css">
    <script src="../javascript/troca-de-tela.js"></script>
    <script src="../javascript/ver-detalhes.js"></script> 
    <script src="../javascript/feedbacks.js"></script> 
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
                        $contAnuncios = $conexao->query("SELECT COUNT(*) FROM " . $banco->anuncio);
                        echo $contAnuncios ? $contAnuncios->fetch_row()[0] : "0"; 
                        ?>
                    </h3>
                    <p>Total de Anúncios</p>
                </div>
                
                <div class="card-stat">
                    <h3>
                        <?php 
                        $contAlunos = $conexao->query("SELECT COUNT(*) FROM " . $banco->aluno);
                        echo $contAlunos ? $contAlunos->fetch_row()[0] : "0"; 
                        ?>
                    </h3>
                    <p>Usuários Ativos</p>
                </div>

                <div class="card-stat card-stat-feedback">
                    <div class="card-stat-feedback-wrapper">
                        <h3>
                            <?php 
                            $contFeedbacks = $conexao->query("SELECT COUNT(*) FROM feedback");
                            echo $contFeedbacks ? $contFeedbacks->fetch_row()[0] : "0"; 
                            ?>
                        </h3>
                        <p>Feedbacks Recebidos</p>
                    </div>
                    <button type="button" onclick="abrirListaFeedbacksAdmin()" class="btn-card-ver-feedbacks">
                        Ver Feedbacks
                    </button>
                </div>

                <div class="card-stat card-stat-denuncias">
                    <h3>
                        <?php 
                        $contDenuncias = $conexao->query("SELECT COUNT(*) FROM `{$banco->denuncia}`");
                        echo $contDenuncias ? $contDenuncias->fetch_row()[0] : "0"; 
                        ?>
                    </h3>
                    <p>Total de Denúncias</p>
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
                        <?php
                        $sql_lista_denuncias = "SELECT id_anuncio, comentario FROM `{$banco->denuncia}` ORDER BY id DESC";
                        $resultado_denuncias = $conexao->query($sql_lista_denuncias);

                        if ($resultado_denuncias && $resultado_denuncias->num_rows > 0) {
                            while ($linha = $resultado_denuncias->fetch_assoc()) {
                                $idAnuncio = intval($linha['id_anuncio']);
                                $comentario = htmlentities($linha['comentario'], ENT_QUOTES, "UTF-8");

                                echo "
                                <tr>
                                    <td>#$idAnuncio</td>
                                    <td class='tag-critica'>$comentario</td>
                                    <td>
                                        <button onclick='verDetalhes($idAnuncio, true)' class='btn-tabela btn-tabela-abrir'>
                                            Abrir Anúncio
                                        </button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='tabela-vazia-txt'>Nenhuma denúncia pendente na fila.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </div>

    </main> 

    <aside class="blocoDireito">
        <div class="perfil-resumo">
            <h3>Meu Perfil</h3>
            <div class="avatar-grande avatar-admin-moderador">🛡️</div>
            <p><strong>Moderador:</strong> <?php echo isset($_SESSION['login_admin']) ? $_SESSION['login_admin'] : 'Admin'; ?></p>
            <p class="avaliacao">Painel de Proteção</p>
        </div>

        <div class="feedback-section text-center modera-info-box">
            <h4>Modo de Moderação</h4>
            <p class="small text-muted">Use seus privilégios com cautela ao remover anúncios do banco de dados.</p>
        </div>
    </aside>

    <div id="modalListaFeedbacksAdmin" class="modal-feedback-overlay">
        <div class="modal-feedback-box modal-box-largo">
            <div class="modal-feedback-header">
                <h3>Feedbacks Enviados</h3>
                <button type="button" class="btn-fechar-x" onclick="fecharListaFeedbacksAdmin()">&times;</button>
            </div>
            
            <div class="modal-feedback-scroll">
                <table class="tabela-feedbacks-modal">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Usuário</th>
                            <th>Prévia</th> <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_feedbacks = "SELECT f.descricao, f.data_feedback, al.nome 
                                          FROM feedback f 
                                          INNER JOIN aluno al ON f.id_usuario = al.id 
                                          ORDER BY f.id DESC";
                        $resultado_feedbacks = $conexao->query($sql_feedbacks);

                        if ($resultado_feedbacks && $resultado_feedbacks->num_rows > 0) {
                            while ($f = $resultado_feedbacks->fetch_assoc()) {
                                $nomeUser = htmlspecialchars($f['nome'], ENT_QUOTES, "UTF-8");
                                $dataFmt = date("d/m/Y", strtotime($f['data_feedback']));
                                $descCompleta = htmlspecialchars($f['descricao'], ENT_QUOTES, "UTF-8");
                                
                                // Cria a prévia limitando a 35 caracteres e põe '...' se cortar
                                $previaTexto = mb_strimwidth($descCompleta, 0, 35, "...");

                                echo "
                                <tr>
                                    <td>$dataFmt</td>
                                    <td><strong>$nomeUser</strong></td>
                                    <td class='coluna-previa-fb'>$previaTexto</td> <td>
                                        <button type='button' onclick=\"exibirIndividualFeedback('$nomeUser', '$dataFmt', `{$descCompleta}`)\" class='btn-ver-fb-pequeno'>
                                            Ler Texto
                                        </button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='tabela-vazia-txt'>Nenhum feedback registrado.</td></tr>"; // Mudado para colspan='4'
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalLeituraIndividualFeedback" class="modal-feedback-overlay modal-index-superior">
        <div class="modal-feedback-box modal-box-medio">
            <div class="modal-feedback-header">
                <h3>Conteúdo do Feedback</h3>
                <button type="button" class="btn-fechar-x" onclick="fecharIndividualFeedback()">&times;</button>
            </div>
            <div class="modal-leitura-corpo">
                <p class="modal-leitura-meta">
                    <strong>Enviado por:</strong> <span id="ind-fb-autor">...</span> <br>
                    <strong>Data:</strong> <span id="ind-fb-data">...</span>
                </p>
                <div class="modal-leitura-texto" id="ind-fb-conteudo">...</div>
            </div>
            <div class="modal-feedback-botoes">
                <button type="button" class="btn-cancelar" onclick="fecharIndividualFeedback()">Voltar</button>
            </div>
        </div>
    </div>

    <div id="modal-detalhes" class="admin-modal-container">
        <div class="admin-modal-conteudo">
            <button onclick="document.getElementById('modal-detalhes').style.display = 'none'" class="admin-modal-fechar">&times;</button>
            
            <div id="modal-carregando" class="admin-modal-loading-box">
                <div class="spinner-border text-danger" role="status"></div>
                <p>Carregando detalhes...</p>
            </div>

            <div id="modal-dados-produto" class="admin-modal-dados">
                <h3 id="detalhe-titulo">Título</h3>
                <span id="detalhe-categoria" class="badge bg-secondary">Categoria</span>
                
                <p class="admin-modal-avaliacoes">
                    Nota: <span id="detalhe-media-nota">0.0</span> 
                    <span id="detalhe-total-notas">(0 avaliações)</span>
                </p>

                <p id="detalhe-status" class="admin-modal-status-txt"></p>
                
                <div class="admin-modal-img-wrapper">
                    <img id="detalhe-imagem" src="" alt="Produto">
                </div>
                
                <p class="preco admin-modal-preco">R$ 0,00</p>
                
                <div class="admin-modal-divider">
                    <h5>Descrição:</h5>
                    <p id="detalhe-descricao" class="admin-modal-desc-p"></p>
                </div>

                <div class="admin-modal-divider">
                    <h5>Contato do Vendedor:</h5>
                    <p class="admin-modal-whats-txt">
                        WhatsApp: <span id="detalhe-whatsapp">...</span>
                    </p>
                    <a id="btn-link-whatsapp" href="#" target="_blank" class="btn btn-success btn-sm admin-modal-whats-btn">Chamar no WhatsApp</a>
                </div>

                <div class="admin-modal-divider">
                    <div class="admin-modal-denuncia-header">
                        <h5>Denúncias / Reclamações:</h5>
                        <button onclick="abrirJanelaDenuncia()" class="admin-modal-btn-denunciar">Denunciar</button>
                    </div>
                    <div id="lista-denuncias" class="admin-modal-lista-denuncias"></div>
                </div>

                <div id="secao-admin-excluir" class="admin-painel-excluir-box">
                    <h5>Painel de Moderação</h5>
                    <p>Para excluir este anúncio, digite uma justificativa que será enviada ao aluno:</p>
                    <textarea id="txt-motivo-exclusao" placeholder="Ex: Seu anúncio viola as regras da instituição..."></textarea>
                    <button onclick="confirmarExclusaoAdmin()" class="admin-btn-deletar-master">Excluir Anúncio Permanentemente</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    if (isset($banco) && isset($conexao)) {
        $banco->desconectar($conexao);
    }
    ?>

</body>
</html>