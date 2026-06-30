<?php
// Inclui os arquivos necessários
require_once "criar-banco-classificados.php";
require_once "criar-classe-anuncio.php";
require_once "criar-classe-aluno.php";
require_once "criar-classe-avaliacao.php";
require_once "criar-classe-denuncia.php";
require_once "criar-classe-feedback.php";

// Certifica-se de que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Se o usuário não estiver logado, redireciona para a tela de login por segurança
if (!isset($_SESSION['id_aluno'])) {
    header("Location: login.php");
    exit();
}
// Cria a conexão (isso fica invisível para o navegador, não altera o visual)
$banco = new BancoDeDados("localhost", "root", "", "db_integrador", "admin", "aluno", "anuncio", "favoritos", "avaliacao", "denuncia", "feedback");
$conexao = $banco->criarConexao();
$banco->abrirBanco($conexao);
$banco->definirCharset($conexao);
$banco->criarTabelaAnuncio($conexao);
$banco->criarTabelaFavorito($conexao);
$banco->criarTabelaAvaliacao($conexao);
$banco->criarTabelaDenuncia($conexao);  
$banco->criarTabelaFeedback($conexao);

$anuncios = new Anuncios();
$alunos = new Alunos();


if ($_SERVER["REQUEST_METHOD"] === "POST" && (isset($_POST["nome"]) || isset($_POST['atualizar-perfil']) || isset($_FILES['foto_perfil']))) {
    // Passamos a superglobal $_FILES para o método conseguir ler a imagem enviada
    $alunos->atualizarPerfil($conexao, $banco->aluno, $_FILES); 
}

$alunos->carregarDadosPerfil($conexao, $banco->aluno, $_SESSION['id_aluno']);

if (isset($_POST['deletar_id_anuncio'])) {
    $id_del = intval($_POST['deletar_id_anuncio']);
    $id_user = intval($_SESSION['id_aluno']); // Segurança: garante que o aluno só delete o que é dele
    
    $conexao->query("DELETE FROM " . $banco->anuncio . " WHERE id = $id_del AND id_aluno = $id_user") or die($conexao->error);
    
    header("Location: index.php?status=excluido");
    exit();
}

// TRATAMENTO 2: AÇÃO DE PUBLICAR OU ATUALIZAR ANÚNCIO
if (isset($_POST['publicar-anuncio'])) {
    $anuncios->receberDadosForm($conexao);
    
    // Se o input de ID de edição estiver preenchido, faremos um UPDATE
    if (!empty($_POST['id_anuncio'])) {
        $id_editar = intval($_POST['id_anuncio']);
        
        // Crie o método correspondente ou faça a lógica do UPDATE aqui
        $anuncios->atualizar($conexao, $banco->anuncio, $id_editar);
        $mensagem_redirecionar = "atualizado";
    } else {
        // Caso contrário, segue o fluxo normal do seu INSERT antigo
        $anuncios->cadastrar($conexao, $banco->anuncio);
        $mensagem_redirecionar = "sucesso";
    }
    
    header("Location: index.php?cadastro=" . $mensagem_redirecionar);
    exit();
}

// CARREGA OS DADOS DO PERFIL AQUI NO TOPO (Garante prioridade na conexão)
$alunos->carregarDadosPerfil($conexao, $banco->aluno, $_SESSION['id_aluno']);

// Processamento do Envio de Feedback
if (isset($_POST['enviar-feedback-sistema'])) {
    // Inclua o arquivo da classe se ele já não estiver incluído por herança
    // require_once "Feedbacks.php"; 
    
    $novoFeedback = new Feedbacks();
    $novoFeedback->receberDadosForm($conexao);
    
    // Altere para o nome exato da sua tabela de feedbacks no banco
    $nomeTabela = "feedback"; 
    
    $novoFeedback->cadastrar($conexao, $nomeTabela);
    
    // Retorno visual sóbrio para o usuário indicando sucesso
    echo "<script>alert('Obrigado pelo seu feedback!'); window.location.href='index.php';</script>";
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
    <link rel="stylesheet" href="../css/pesquisar.css">
    <link rel="stylesheet" href="../css/meus-anuncios.css">
    <link rel="stylesheet" href="../css/novo-anuncio.css">
    <link rel="stylesheet" href="../css/favoritos.css">
    <link rel="stylesheet" href="../css/perfil.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/extras.css">
    <link rel="stylesheet" href="../css/listagem-anuncios.css">
    <link rel="stylesheet" href="../css/feedback.css">
    
    <script src="../javascript/troca-de-tela.js"></script>
    <script src="../javascript/favoritos.js"></script>
    <script src="../javascript/gerenciar-anuncios.js"></script>
    <script src="../javascript/ver-detalhes.js"></script>
    <script src="../javascript/pesquisa.js"></script>
    <script src="../javascript/perfil.js"></script>
    <title>Classificados IFSC - Home</title>
</head>
<body>

    <aside class="blocoEsquerdo">
        <nav>
            <ul>
                <li><span class="logo">Classificados IFSC</span></li>
                <li><a href="#" id="menu-home" class="menu active" onclick="navegar('tela-home'); navegarMenu('menu-home')">Página Inicial</a></li>
                <li><a href="#" id="menu-pesquisar" class="menu" onclick="navegar('tela-pesquisa'); navegarMenu('menu-pesquisar')">Pesquisar</a></li>
                <li><a href="#" id="menu-anuncio" class="menu" onclick="navegar('tela-meusanuncios'); navegarMenu('menu-anuncio')">Meus Anúncios</a></li>
                <li><a href="#" id="menu-favoritos" class="menu" onclick="navegar('tela-favoritos'); navegarMenu('menu-favoritos')">Favoritos</a></li>
                <li><a href="#" id="menu-perfil" class="menu" onclick="navegar('tela-perfil'); navegarMenu('menu-perfil')">Perfil</a></li>
                <li><a href="login.php" class="logout">Sair</a></li>
            </ul>
        </nav>
    </aside>

    <div id="modal-detalhes" class="modal-container-custom">
        <div class="modal-conteudo-custom">
            
            <span class="btn-fechar-modal-custom" onclick="fecharModalDetalhes()">&times;</span>
            
            <div id="modal-carregando" class="modal-carregando-custom">
                <p>Carregando detalhes...</p>
            </div>

            <div id="modal-dados-produto" style="display: none;">
                <h3 id="detalhe-titulo" class="modal-titulo">Título do Produto</h3>
                <span id="detalhe-categoria" class="badge-campus modal-categoria-badge">Categoria</span>
              
                <p class="modal-nota-container">
                    Nota: <span id="detalhe-media-nota" class="modal-nota-valor">0.0</span> 
                    <span id="detalhe-total-notas" class="modal-nota-total">(0 avaliações)</span>
                </p>

                <p id="detalhe-status" class="modal-status-txt"></p>
              
                <div class="modal-img-container">
                    <img id="detalhe-imagem" src="" alt="Produto" class="modal-img-element">
                </div>
              
                <p class="preco modal-preco">R$ 0,00</p>
              
                <div class="modal-secao-div">
                    <h5>Descrição:</h5>
                    <p id="detalhe-descricao" class="modal-descricao-txt">Descrição detalhada aqui.</p>
                </div>

                <div class="modal-secao-div">
                    <h5>Contato do Vendedor:</h5>
                    <p class="modal-whatsapp-txt">
                        WhatsApp: <span id="detalhe-whatsapp">Carregando...</span>
                    </p>
                    <a id="btn-link-whatsapp" href="#" target="_blank" class="btn btn-success btn-sm modal-btn-whatsapp">Chamar no WhatsApp</a>
                </div>

                <div class="modal-secao-div">
                    <h5>Avaliar este anúncio:</h5>
                    <div class="rating-stars modal-rating-box">
                        <span class="star modal-star-item" onclick="enviarAvaliacao(1)">★</span><span class="star modal-star-item" onclick="enviarAvaliacao(2)">★</span><span class="star modal-star-item" onclick="enviarAvaliacao(3)">★</span><span class="star modal-star-item" onclick="enviarAvaliacao(4)">★</span><span class="star modal-star-item" onclick="enviarAvaliacao(5)">★</span>
                    </div>
                </div>

                <div class="modal-secao-div">
                    <div class="modal-denuncia-header">
                        <h5>Denúncias / Reclamações:</h5>
                        <button onclick="abrirJanelaDenuncia()" class="modal-btn-denunciar">Denunciar</button>
                    </div>
                    <div id="lista-denuncias" class="modal-lista-denuncias-box"></div>
                </div>
            </div>

            <div id="pop-denuncia" class="pop-denuncia-container">
                <div class="pop-denuncia-content">
                    <h4 class="pop-denuncia-titulo">Fazer Denúncia</h4>
                    <textarea id="txt-denuncia" placeholder="Explique o motivo da denúncia..." class="pop-denuncia-textarea"></textarea>
                    <div class="pop-denuncia-actions">
                        <button onclick="fecharJanelaDenuncia()" class="pop-btn-cancelar">Cancelar</button>
                        <button onclick="enviarDenuncia()" class="pop-btn-enviar">Enviar</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <main class="blocoCentral">
        <div id="tela-home" class="main tela-home tela show">
            <header class="header-busca">
                <h2>Explorar Anúncios</h2>
                <form action="index.php" method="GET" class="search-bar">
                    <input type="text" name="busca" placeholder="O que você procura hoje? (Livros, eletrônicos...)" value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <button type="submit">Buscar</button>
                </form>
            </header>

            <section class="filtros-rapidos">
                <a href="index.php?busca=Livros" class="btn filter-btn">Livros</a>
                <a href="index.php?busca=Eletrônicos" class="btn filter-btn">Eletrônicos</a>
                <a href="index.php?busca=Móveis" class="btn filter-btn">Móveis</a>
                <a href="index.php?busca=Serviços" class="btn filter-btn">Serviços</a>
                <a href="index.php" class="btn filter-btn btn-limpar-filtro-home">Limpar Filtro</a>
            </section>

            <div class="grid-anuncios">
                <?php 
                    $termo = isset($_GET['busca']) ? $_GET['busca'] : '';
                    $anuncios->listarAnunciosAtivos($conexao, $banco->anuncio, $_SESSION['id_aluno'], $termo); 
                ?>
            </div>
        </div>

        <div id="tela-pesquisa" class="tela collapse">
            <header class="header-pesquisa-avancada pesquisa-avancada-header">
                <h2>Busca Detalhada</h2>
                <p class="pesquisa-avancada-sub">Refine sua busca preenchendo os critérios abaixo:</p>
            </header>

            <form id="form-pesquisa-avancada" onsubmit="filtrarSPA(event)">
                <input type="hidden" name="tela" value="pesquisa">

                <section class="filtros-container filtros-grid-container">
                    
                    <div class="filtro-grupo filtro-campo-total">
                        <label class="filtro-label-padrao">O que você procura?</label>
                        <input type="text" name="busca" placeholder="Ex: Monitor Dell, Livro de Cálculo..." value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca'], ENT_QUOTES, 'UTF-8') : ''; ?>" class="filtro-input-text">
                    </div>

                    <div class="filtro-grupo">
                        <label class="filtro-label-padrao">Status do Item:</label>
                        <select name="status_item" class="filtro-select-padrao">
                            <option value="todos" <?php echo (isset($_GET['status_item']) && $_GET['status_item'] == 'todos') ? 'selected' : ''; ?>>Qualquer Status</option>
                            <option value="EM ABERTO" <?php echo (isset($_GET['status_item']) && $_GET['status_item'] == 'EM ABERTO') ? 'selected' : ''; ?>>Disponível</option>
                            <option value="EM NEGOCIACAO" <?php echo (isset($_GET['status_item']) && $_GET['status_item'] == 'EM NEGOCIACAO') ? 'selected' : ''; ?>>Em Negociação</option>
                            <option value="VENDIDO" <?php echo (isset($_GET['status_item']) && $_GET['status_item'] == 'VENDIDO') ? 'selected' : ''; ?>>Vendido</option>
                        </select>
                    </div>

                    <div class="filtro-grupo">
                        <label class="filtro-label-padrao">Faixa de Preço (R$):</label>
                        <div class="inputs-preco filtro-inputs-preco-box">
                            <input type="number" name="preco_min" placeholder="Min" min="0" value="<?php echo isset($_GET['preco_min']) ? htmlspecialchars($_GET['preco_min']) : ''; ?>" class="filtro-input-preco">
                            <input type="number" name="preco_max" placeholder="Max" min="0" value="<?php echo isset($_GET['preco_max']) ? htmlspecialchars($_GET['preco_max']) : ''; ?>" class="filtro-input-preco">
                        </div>
                    </div>

                    <div class="filtro-grupo filtro-campo-periodo">
                        <label class="filtro-label-padrao">Anunciado no Período:</label>
                        <div class="filtro-wrapper-datas">
                            <input type="date" name="data_inicio" value="<?php echo isset($_GET['data_inicio']) ? htmlspecialchars($_GET['data_inicio']) : ''; ?>" class="filtro-input-date">
                            <span class="filtro-span-separador">até</span>
                            <input type="date" name="data_fim" value="<?php echo isset($_GET['data_fim']) ? htmlspecialchars($_GET['data_fim']) : ''; ?>" class="filtro-input-date">
                        </div>
                    </div>

                    <div class="filtro-acoes-container">
                        <button type="button" onclick="limparFiltrosSPA()" class="btn-limpar-filtros-spa">Limpar Filtros</button>
                        <button type="submit" class="btn-busca-principal btn-buscar-filtros-spa">Aplicar Filtros Avançados</button>
                    </div>
                </section>
            </form>

            <div class="resultados-info resultados-info-container">
                <p class="resultados-txt">
                    <?php 
                        if (!empty($_GET['busca'])) {
                            echo 'Resultados para "<strong>' . htmlspecialchars($_GET['busca'], ENT_QUOTES, 'UTF-8') . '</strong>"';
                        } else {
                            echo 'Mostrando anúncios com base nos filtros selecionados';
                        }
                    ?>
                </p>
            </div>

            <div class="grid-anuncios resultados-grid-wrapper">
                <?php 
                    $anuncios->listarAnunciosAtivos($conexao, $banco->anuncio, $_SESSION['id_aluno'], $_GET); 
                ?>
            </div>
        </div>

        <div id="tela-meusanuncios" class="tela collapse">
            <header class="header-meus-anuncios">
                <div>
                    <h2>Meus Anúncios</h2>
                    <p>Gerencie seus itens anunciados no IFSC</p>
                </div>
                <a href="#" class="meus-anuncios-btn-topo-link">
                    <button class="btn-anunciar-topo" onclick="navegar('tela-novoanuncio')">+ Novo Anúncio</button>
                </a>
            </header>

            <section class="resumo-anuncios">
                <?php
                    $id_usuario_logado = $_SESSION['id_aluno']; 
                    $stats = $anuncios->obterContadoresUsuario($conexao, $banco->anuncio, $id_usuario_logado);
                ?>
                <div class="card-mini-stats">
                    <span>Ativos</span>
                    <strong><?php echo $stats['ativos']; ?></strong>
                </div>
                <div class="card-mini-stats">
                    <span>Vendidos</span>
                    <strong><?php echo $stats['vendidos']; ?></strong>
                </div>
                <div class="card-mini-stats stats-card-deletados">
                    <span class="stats-txt-deletados">Anúncios Deletados</span>
                    <strong class="stats-txt-deletados"><?php echo $stats['deletados']; ?></strong>
                </div>
            </section>

            <div class="lista-gerenciamento">
                <?php 
                    $id_usuario_logado = $_SESSION['id_aluno']; 
                    $anuncios->listarMeusAnuncios($conexao, $banco->anuncio, $id_usuario_logado); 
                ?>
            </div>
        </div>

        <div id="tela-novoanuncio" class="tela collapse">
            <header class="header-pagina">
                <h2 id="titulo-tela-anuncio">Criar Novo Anúncio</h2>
                <p>Preencha os detalhes abaixo para publicar seu item.</p>
            </header>

            <form class="form-anuncio" method="post" action="index.php" enctype="multipart/form-data">
                <input type="hidden" name="id_vendedor" value="<?php echo $_SESSION['id_aluno']; ?>">
                <input type="hidden" id="id_anuncio_edicao" name="id_anuncio" value="">

                <section class="sessao-form">
                    <h3>Informações Básicas</h3>
                    <div class="campo">
                        <label for="titulo">Título do Anúncio *</label>
                        <input type="text" id="titulo" name="titulo-anuncio" placeholder="Ex: Livro Cálculo A - Diva Flemming" required>
                    </div>

                    <div class="fila-campos">
                        <div class="campo">
                            <label for="categoria">Categoria</label>
                            <select id="categoria" name="categoria-anuncio">
                                <option value="LIVROS">Livros/Material Didático</option>
                                <option value="ELETRÔNICOS">Eletrônicos</option>
                                <option value="MÓVEIS">Móveis</option>
                                <option value="OUTROS">Outros</option>
                            </select>
                        </div>
                        <div class="campo" id="container-status-anuncio" class="cadastro-status-container" style="display: none;">
                            <label for="status-anuncio">Status do Anúncio</label>
                            <select id="status-anuncio" name="status-anuncio">
                                <option value="EM ABERTO">Em Aberto</option>
                                <option value="EM NEGOCIACAO">Em Negociação</option>
                                <option value="VENDIDO">Vendido</option>
                            </select>
                        </div>
                        <div class="campo">
                            <label for="preco">Preço (R$)*</label>
                            <input type="number" id="preco" name="preco-anuncio" step="0.01" placeholder="0,00" required>
                        </div>
                    </div>
                </section>

                <section class="sessao-form">
                    <h3>Imagens e Descrição</h3>
                    
                    <div class="sessao-form-midia">
                        
                        <div class="campo">
                            <label>Fotos do Produto</label>
                            <div class="upload-container">
                                <input type="file" id="fotos" name="foto-anuncio" accept="image/*" onchange="mostrarConfirmacao(this)">
                                <div class="upload-placeholder">
                                    <span id="texto-upload">Clique para selecionar fotos</span>
                                    <br>
                                    <img id="preview-imagem" src="" alt="Prévia" class="upload-preview-img">
                                </div>
                            </div>
                        </div>
                        
                        <div class="campo campo-descricao">
                            <label for="descricao">Descrição Detalhada</label>
                            <textarea id="descricao" name="descricao-anuncio" placeholder="Descreva o estado do item, tempo de uso, etc."></textarea>
                        </div>
                        
                    </div>
                </section>

                <div class="botoes-form">
                    <button type="button" class="btn-cancelar" onclick="cancelarEdicaoAnuncio()">Cancelar</button>
                    <button type="submit" id="btn-submit-anuncio" name="publicar-anuncio" class="btn-publicar">Publicar Anúncio</button>
                </div>
            </form>
        </div>
        
        <div id="tela-favoritos" class="tela collapse">
            <header class="header-favoritos">
                <h2>Meus Favoritos</h2>
                <p>Itens que você demonstrou interesse</p>
            </header>

            <div class="grid-anuncios">
                <?php 
                    $anuncios->listarFavoritosDoAluno($conexao, $banco->anuncio, $_SESSION['id_aluno']); 
                ?>
            </div>
        </div>
        
        <iframe name="retorno_foto_segredo" style="display: none;"></iframe>

        <div id="tela-perfil" class="tela collapse">
            <header class="header-perfil">
                <h2>Configurações do Perfil</h2>
                <p>Gerencie suas informações públicas e de segurança.</p>
            </header>
            <section class="secao-perfil">
                
                <form method="POST" action="" enctype="multipart/form-data" target="retorno_foto_segredo">
                    <div class="perfil-topo">
                        <div class="avatar-edit perfil-avatar-container">
                            <?php if (!empty($alunos->foto_perfil) && file_exists($alunos->foto_perfil)): ?>
                                <img id="img-perfil-preview" src="<?php echo $alunos->foto_perfil; ?>" alt="Foto de Perfil" class="perfil-avatar-img">
                            <?php else: ?>
                                <span id="avatar-letra-preview" class="avatar-letra perfil-avatar-letra-fallback">
                                    <?php echo !empty($alunos->nome) ? mb_strtoupper(mb_substr($alunos->nome, 0, 1, "UTF-8"), "UTF-8") : "A"; ?>
                                </span>
                            <?php endif; ?>
                            
                            <label for="input-foto" class="perfil-btn-upload-txt">Alterar Foto</label>
                            <input type="file" id="input-foto" name="foto_perfil" accept="image/*" class="perfil-input-file-hidden" onchange="atualizarFotoInstantanea(this)">
                        </div>
                        <div class="perfil-identidade">
                            <h3><?php echo $alunos->nome; ?></h3>
                            <p>Membro desde: <?php echo $alunos->dataCadastro; ?></p>
                        </div>
                    </div>
                </form>

                <form class="form-perfil" method="POST" action="">
                    <div class="grid-form">
                        <div class="campo">
                            <label for="perfil-nome">Nome Completo</label>
                            <input type="text" id="perfil-nome" name="nome" value="<?php echo $alunos->nome; ?>" required>
                        </div>
                        
                        <div class="campo">
                            <label>E-mail Institucional</label>
                            <input type="email" value="<?php echo $alunos->email; ?>" disabled>
                        </div>
                        
                        <div class="campo">
                            <label for="perfil-campus">Câmpus Principal</label>
                            <select id="perfil-campus" name="campus">
                                <option value="Florianópolis" <?php echo ($alunos->campus == 'Florianópolis') ? 'selected' : ''; ?>>Florianópolis</option>
                                <option value="São José" <?php echo ($alunos->campus == 'São José') ? 'selected' : ''; ?>>São José</option>
                                <option value="Palhoça" <?php echo ($alunos->campus == 'Palhoça') ? 'selected' : ''; ?>>Palhoça</option>
                                <option value="Garopaba" <?php echo ($alunos->campus == 'Garopaba') ? 'selected' : ''; ?>>Garopaba</option>
                            </select>
                        </div>
                        
                        <div class="campo">
                            <label for="perfil-whats">WhatsApp (Para contato)</label>
                            <input type="text" id="perfil-whats" name="whatsapp" placeholder="(48) 99999-9999" value="<?php echo $alunos->whatsapp; ?>">
                        </div>
                    </div>

                    <div class="campo">
                        <label for="perfil-bio">Bio / Descrição</label>
                        <textarea id="perfil-bio" name="bio" placeholder="Conte um pouco sobre você ou o que costuma vender/comprar..."><?php echo $alunos->bio; ?></textarea>
                    </div>

                    <div class="botoes-form">
                        <button type="button" class="btn-cancelar" onclick="window.location.reload()">Descartar Alterações</button>
                        <button type="submit" name="atualizar-perfil" class="btn-salvar">Salvar Alterações</button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <aside class="blocoDireito">
        <div class="perfil-resumo">
            <h3>Meu Perfil</h3>
            
            <div class="avatar-grande perfil-aside-avatar-box">
                <?php if (!empty($alunos->foto_perfil) && file_exists($alunos->foto_perfil)): ?>
                    <img src="<?php echo $alunos->foto_perfil; ?>" alt="Foto" class="perfil-aside-avatar-img">
                <?php else: ?>
                    <?php echo !empty($alunos->nome) ? mb_strtoupper(mb_substr($alunos->nome, 0, 1, "UTF-8"), "UTF-8") : "U"; ?>
                <?php endif; ?>
            </div>
            
            <p><strong>Estudante:</strong> <?php echo htmlspecialchars($alunos->nome); ?></p>
            <p class="perfil-aside-subtext"><?php echo htmlspecialchars($alunos->email); ?></p>
            <p class="avaliacao">Média de Avaliações: 4.8</p>
        </div>

        <div class="acoes-rapidas">
            <button class="btn-anunciar" onclick="navegar('tela-novoanuncio')">Criar Novo Anúncio</button>
        </div>

        <div class="feedback-section">
    <h4>Sugestões?</h4>
    <p>Ajude a melhorar o Classificados IFSC.</p>
    <button class="btn-feedback" onclick="abrirModalFeedback()">Enviar Feedback</button>
</div>

<div id="modalFeedback" class="modal-feedback-overlay">
    <div class="modal-feedback-box">
        
        <div class="modal-feedback-header">
            <h3>Enviar Feedback</h3>
            <button class="btn-fechar-x" onclick="fecharModalFeedback()">&times;</button>
        </div>
        
        <form action="index.php" method="POST" class="form-feedback-internor">
            <input type="hidden" name="id-usuario" value="<?php echo $_SESSION['id_aluno']; ?>">
            
            <div class="grupo-campo-feedback">
                <label for="descricao-feedback">Sua opinião ou relato de problema:</label>
                <textarea id="descricao-feedback" name="descricao-feedback" rows="5" required placeholder="Digite aqui sua sugestão para o sistema..."></textarea>
            </div>
            
            <div class="modal-feedback-botoes">
                <button type="button" class="btn-cancelar" onclick="fecharModalFeedback()">Cancelar</button>
                <button type="submit" name="enviar-feedback-sistema" class="btn-enviar">Enviar</button>
            </div>
        </form>
        
    </div>
</div>
    </aside>
    
    <?php
    if (isset($banco) && isset($conexao)) {
        $banco->desconectar($conexao);
    }
    ?>

</body>
</html>