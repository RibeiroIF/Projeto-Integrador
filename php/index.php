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
$banco = new BancoDeDados("localhost", "root", "dadosmain", "db_integrador", "admin", "aluno", "anuncio", "favoritos", "avaliacao", "denuncia", "feedback");
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
    <link rel="stylesheet" href="../css/meus-anuncios.css">
    <link rel="stylesheet" href="../css/novo-anuncio.css">
    <link rel="stylesheet" href="../css/favoritos.css">
    <link rel="stylesheet" href="../css/perfil.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/login.css">
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
                <li ><a href="#" id="menu-home" class="menu active" onclick="navegar('tela-home'); navegarMenu('menu-home')">Página Inicial</a></li>
                <li><a href="#" id="menu-pesquisar" class="menu" onclick="navegar('tela-pesquisa'); navegarMenu('menu-pesquisar')">Pesquisar</a></li>
                <li><a href="#" id="menu-anuncio" class="menu" onclick="navegar('tela-meusanuncios'); navegarMenu('menu-anuncio')">Meus Anúncios</a></li>
                <li><a href="#" id="menu-favoritos" class="menu" onclick="navegar('tela-favoritos'); navegarMenu('menu-favoritos')">Favoritos</a></li>
                <li><a href="#" id="menu-perfil" class="menu" onclick="navegar('tela-perfil'); navegarMenu('menu-perfil')">Perfil</a></li>
                <li><a href="login.php" class="logout">Sair</a></li>
            </ul>
        </nav>
    </aside>

    <div id="modal-detalhes" class="modal-container" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; justify-content: center; align-items: center;">
        <div class="modal-conteudo" style="background: #fff; padding: 25px; border-radius: 8px; max-width: 500px; width: 90%; position: relative; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            
            <span class="btn-fechar-modal" onclick="fecharModalDetalhes()" style="position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer; color: #777;">&times;</span>
            
            <div id="modal-carregando" style="text-align: center; padding: 20px;">
                <p>Carregando detalhes...</p>
            </div>

            <div id="modal-dados-produto" style="display: none;">
              <h3 id="detalhe-titulo" style="margin-top: 0; color: #333;">Título do Produto</h3>
              <span id="detalhe-categoria" class="badge-campus" style="position: static; display: inline-block; margin-bottom: 15px;">Categoria</span>
              
              <p style="margin: 0 0 10px 0; font-size: 15px; color: #ff9800;">
                  ⭐ <span id="detalhe-media-nota" style="font-weight: bold;">0.0</span> 
                  <span id="detalhe-total-notas" style="color: #777; font-size: 12px;">(0 avaliações)</span>
              </p>

              <p id="detalhe-status" style="font-weight: bold; margin: 5px 0 15px 0; font-size: 14px;"></p>
              
              <div style="height: 250px; display: flex; align-items: center; justify-content: center; background: #f5f5f5; border-radius: 4px; overflow: hidden; margin-bottom: 15px;">
                  <img id="detalhe-imagem" src="" alt="Produto" style="max-width: 100%; max-height: 100%; object-fit: contain;">
              </div>
              
              <p class="preco" style="font-size: 20px; color: #28a745; font-weight: bold; margin: 10px 0;">R$ 0,00</p>
              
              <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                  <h5>Descrição:</h5>
                  <p id="detalhe-descricao" style="color: #666; font-size: 14px; line-height: 1.5; max-height: 100px; overflow-y: auto;">Descrição detalhada aqui.</p>
              </div>

              <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                  <h5>Contato do Vendedor:</h5>
                  <p style="font-size: 16px; font-weight: bold; color: #198754; margin: 5px 0;">
                      📱 <span id="detalhe-whatsapp">Carregando...</span>
                  </p>
                  <a id="btn-link-whatsapp" href="#" target="_blank" class="btn btn-success btn-sm" style="font-weight: bold; display: none;">Chamar no WhatsApp</a>
              </div>

              <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 15px;">
                <h5>Avaliar este anúncio:</h5>
                <div class="rating-stars" style="font-size: 28px; cursor: pointer; color: #ccc; display: inline-block;">
                    <span class="star" onclick="enviarAvaliacao(1)" style="transition: color 0.2s;">★</span><span class="star" onclick="enviarAvaliacao(2)" style="transition: color 0.2s;">★</span><span class="star" onclick="enviarAvaliacao(3)" style="transition: color 0.2s;">★</span><span class="star" onclick="enviarAvaliacao(4)" style="transition: color 0.2s;">★</span><span class="star" onclick="enviarAvaliacao(5)" style="transition: color 0.2s;">★</span>
                </div>
            </div>

              <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 15px;">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                      <h5>Denúncias / Reclamações:</h5>
                      <button onclick="abrirJanelaDenuncia()" style="background: #dc3545; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; font-size: 12px; cursor: pointer;">🚨 Denunciar</button>
                  </div>
                  <div id="lista-denuncias" style="max-height: 120px; overflow-y: auto; margin-top: 10px; background: #fafafa; padding: 8px; border-radius: 4px; font-size: 13px; color: #555;">
                      </div>
              </div>
          </div>

          <div id="pop-denuncia" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 3000; justify-content: center; align-items: center;">
              <div style="background: #fff; padding: 20px; border-radius: 6px; width: 90%; max-width: 400px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                  <h4 style="margin-top: 0; color: #dc3545;">Fazer Denúncia</h4>
                  <textarea id="txt-denuncia" placeholder="Explique o motivo da denúncia..." style="width: 100%; height: 80px; padding: 6px; border: 1px solid #ccc; border-radius: 4px; resize: none; box-sizing: border-box;"></textarea>
                  <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 12px;">
                      <button onclick="fecharJanelaDenuncia()" style="background: #bbb; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">Cancelar</button>
                      <button onclick="enviarDenuncia()" style="background: #dc3545; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">Enviar</button>
                  </div>
              </div>
          </div>
          </div>
        </div>
    </div>

    <main class="blocoCentral">
    <!--Tela inicial-->
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
              <a href="index.php" class="btn filter-btn" style="background: #6c757d; color: #fff;">Limpar Filtro</a>
          </section>

          <div class="grid-anuncios">
              <?php 
                  // Captura o termo caso venha na URL, senão passa vazio
                  $termo = isset($_GET['busca']) ? $_GET['busca'] : '';
                  $anuncios->listarAnunciosAtivos($conexao, $banco->anuncio, $_SESSION['id_aluno'], $termo); 
              ?>
          </div>
      </div>

      <div id="tela-pesquisa" class="tela collapse">
          <header class="header-pesquisa-avancada" style="margin-bottom: 20px;">
              <h2>Busca Detalhada</h2>
              <p style="color: #666; font-size: 14px;">Refine sua busca preenchendo os critérios abaixo:</p>
          </header>

          <form id="form-pesquisa-avancada" onsubmit="filtrarSPA(event)">
              <input type="hidden" name="tela" value="pesquisa">

              <section class="filtros-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                  
                  <div class="filtro-grupo" style="grid-column: 1 / -1;">
                      <label style="font-weight: bold; display: block; margin-bottom: 5px; color: #333;">O que você procura?</label>
                      <input type="text" name="busca" placeholder="Ex: Monitor Dell, Livro de Cálculo..." value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca'], ENT_QUOTES, 'UTF-8') : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                  </div>

                  <div class="filtro-grupo">
                      <label style="font-weight: bold; display: block; margin-bottom: 5px; color: #333;">Status do Item:</label>
                      <select name="status_item" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                          <option value="todos" <?php echo (isset($_GET['status_item']) && $_GET['status_item'] == 'todos') ? 'selected' : ''; ?>>Qualquer Status</option>
                          <option value="EM ABERTO" <?php echo (isset($_GET['status_item']) && $_GET['status_item'] == 'EM ABERTO') ? 'selected' : ''; ?>>🟢 Disponível</option>
                          <option value="EM NEGOCIACAO" <?php echo (isset($_GET['status_item']) && $_GET['status_item'] == 'EM NEGOCIACAO') ? 'selected' : ''; ?>>🤝 Em Negociação</option>
                          <option value="VENDIDO" <?php echo (isset($_GET['status_item']) && $_GET['status_item'] == 'VENDIDO') ? 'selected' : ''; ?>>💰 Vendido</option>
                      </select>
                  </div>

                  <div class="filtro-grupo">
                      <label style="font-weight: bold; display: block; margin-bottom: 5px; color: #333;">Faixa de Preço (R$):</label>
                      <div class="inputs-preco" style="display: flex; gap: 10px;">
                          <input type="number" name="preco_min" placeholder="Min" min="0" value="<?php echo isset($_GET['preco_min']) ? htmlspecialchars($_GET['preco_min']) : ''; ?>" style="width: 50%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                          <input type="number" name="preco_max" placeholder="Max" min="0" value="<?php echo isset($_GET['preco_max']) ? htmlspecialchars($_GET['preco_max']) : ''; ?>" style="width: 50%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                      </div>
                  </div>

                  <div class="filtro-grupo" style="grid-column: span 2;">
                      <label style="font-weight: bold; display: block; margin-bottom: 5px; color: #333;">Anunciado no Período:</label>
                      <div style="display: flex; gap: 10px; align-items: center;">
                          <input type="date" name="data_inicio" value="<?php echo isset($_GET['data_inicio']) ? htmlspecialchars($_GET['data_inicio']) : ''; ?>" style="width: 48%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                          <span style="color: #777;">até</span>
                          <input type="date" name="data_fim" value="<?php echo isset($_GET['data_fim']) ? htmlspecialchars($_GET['data_fim']) : ''; ?>" style="width: 48%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
                      </div>
                  </div>

                  <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; margin-top: 10px;">
                      <button type="button" onclick="limparFiltrosSPA()" style="background: #6c757d; color: #fff; padding: 10px 20px; border-radius: 4px; border:none; margin-right: 10px; font-weight: bold; font-size: 14px; cursor:pointer;">Limpar Filtros</button>
                      <button type="submit" class="btn-busca-principal" style="background: #28a745; color: white; border: none; padding: 10px 30px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 14px;">🔍 Aplicar Filtros Avançados</button>
                  </div>
              </section>
          </form>

          <div class="resultados-info" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
              <p style="color: #555;">
                  <?php 
                      if (!empty($_GET['busca'])) {
                          echo 'Resultados para "<strong>' . htmlspecialchars($_GET['busca'], ENT_QUOTES, 'UTF-8') . '</strong>"';
                      } else {
                          echo 'Mostrando anúncios com base nos filtros selecionados';
                      }
                  ?>
              </p>
          </div>

          <div class="grid-anuncios" style="margin-top: 20px;">
              <?php 
                  // Dispara o método atualizado passando o array completo do GET como filtros
                  $anuncios->listarAnunciosAtivos($conexao, $banco->anuncio, $_SESSION['id_aluno'], $_GET); 
              ?>
          </div>
      </div>

        <!--Tela de meus anuncios-->
        <div id="tela-meusanuncios" class="tela collapse">
          <header class="header-meus-anuncios">
              <div>
                  <h2>Meus Anúncios</h2>
                  <p>Gerencie seus itens anunciados no IFSC</p>
              </div>
              <a href="#" style="text-decoration: none;">
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
            <div class="card-mini-stats" style="border-left: 4px solid #dc3545;">
                <span style="color: #dc3545;">Anúncios Deletados</span>
                <strong style="color: #dc3545;"><?php echo $stats['deletados']; ?></strong>
            </div>
        </section>

          <div class="lista-gerenciamento">
              <?php 
                  $id_usuario_logado = $_SESSION['id_aluno']; 
                  // Esta função vai renderizar os anúncios normais + os deletados com visual apagado
                  $anuncios->listarMeusAnuncios($conexao, $banco->anuncio, $id_usuario_logado); 
              ?>
          </div>
      </div>


        <!--Tela de novo anuncio-->
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
                      <div class="campo" id="container-status-anuncio" style="display: none; margin-top: 15px;">
                        <label for="status-anuncio">Status do Anúncio</label>
                        <select id="status-anuncio" name="status-anuncio">
                            <option value="EM ABERTO">Em Aberto 🟢</option>
                            <option value="EM NEGOCIACAO">Em Negociação 🟡</option>
                            <option value="VENDIDO">Vendido 🔴</option>
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
                  <div class="campo">
                      <label>Fotos do Produto</label>
                      <div class="upload-container">
                          <input type="file" id="fotos" name="foto-anuncio" accept="image/*" onchange="mostrarConfirmacao(this)">
                          
                          <div class="upload-placeholder">
                              <span id="texto-upload">📷 Clique para selecionar fotos</span>
                              <br>
                              <img id="preview-imagem" src="" alt="Prévia" style="display: none; max-width: 120px; max-height: 120px; margin-top: 10px; border-radius: 4px;">
                          </div>
                      </div>
                  </div>
                  <div class="campo">
                      <label for="descricao">Descrição Detalhada</label>
                      <textarea id="descricao" rows="5" name="descricao-anuncio" placeholder="Descreva o estado do item, tempo de uso, etc."></textarea>
                  </div>
              </section>

              <div class="botoes-form">
                  <button type="button" class="btn-cancelar" onclick="cancelarEdicaoAnuncio()">Cancelar</button>
                  
                  <button type="submit" id="btn-submit-anuncio" name="publicar-anuncio" class="btn-publicar">Publicar Anúncio</button>
              </div>
          </form>
      </div>
        
        <!--Tela favoritos -->
        <div id="tela-favoritos" class="tela collapse">
          <header class="header-favoritos">
              <h2>Meus Favoritos</h2>
              <p>Itens que você demonstrou interesse</p>
          </header>

          <div class="grid-anuncios">
              <?php 
                  // Atualizado: Limpo os exemplos estáticos e inserida a chamada dinâmica dos favoritos
                  $anuncios->listarFavoritosDoAluno($conexao, $banco->anuncio, $_SESSION['id_aluno']); 
              ?>
          </div>
      </div>
        
        <!--Tela perfil-->
        <iframe name="retorno_foto_segredo" style="display: none;"></iframe>

        <div id="tela-perfil" class="tela collapse">
            <header class="header-perfil">
                <h2>Configurações do Perfil</h2>
                <p>Gerencie suas informações públicas e de segurança.</p>
            </header>
            <section class="secao-perfil">
                
                <form method="POST" action="" enctype="multipart/form-data" target="retorno_foto_segredo">
                    <div class="perfil-topo">
                        <div class="avatar-edit" style="position: relative; width: 80px; height: 80px;">
                            <?php if (!empty($alunos->foto_perfil) && file_exists($alunos->foto_perfil)): ?>
                                <img id="img-perfil-preview" src="<?php echo $alunos->foto_perfil; ?>" alt="Foto de Perfil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <span id="avatar-letra-preview" class="avatar-letra" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; background: #007bff; color: #fff; font-size: 2rem; border-radius: 50%;">
                                    <?php echo !empty($alunos->nome) ? mb_strtoupper(mb_substr($alunos->nome, 0, 1, "UTF-8"), "UTF-8") : "A"; ?>
                                </span>
                            <?php endif; ?>
                            
                            <label for="input-foto" class="btn-trocar-foto" style="position: absolute; bottom: 0; right: 0; background: #28a745; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px;">📷</label>
                            <input type="file" id="input-foto" name="foto_perfil" accept="image/*" style="display: none;" onchange="atualizarFotoInstantanea(this)">
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
              
              <div class="avatar-grande" style="display: flex; align-items: center; justify-content: center; background: #28a745; color: #fff; font-size: 2rem; font-weight: bold; border-radius: 50%; width: 70px; height: 70px; margin: 0 auto 10px auto; overflow: hidden;">
                  <?php if (!empty($alunos->foto_perfil) && file_exists($alunos->foto_perfil)): ?>
                      <img src="<?php echo $alunos->foto_perfil; ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                  <?php else: ?>
                      <?php echo !empty($alunos->nome) ? mb_strtoupper(mb_substr($alunos->nome, 0, 1, "UTF-8"), "UTF-8") : "👤"; ?>
                  <?php endif; ?>
              </div>
              
              <p><strong>Estudante:</strong> <?php echo htmlspecialchars($alunos->nome); ?></p>
              <p style="font-size: 0.85rem; color: #666;"><?php echo htmlspecialchars($alunos->email); ?></p>
              <p class="avaliacao">Média: ⭐ 4.8</p>
          </div>

          <div class="acoes-rapidas">
              <button class="btn-anunciar" onclick="navegar('tela-novoanuncio')"> + Criar Novo Anúncio</button>
          </div>

          <div class="feedback-section">
              <h4>Sugestões?</h4>
              <p>Ajude a melhorar o Classificados IFSC.</p>
              <button class="btn-feedback">Enviar Feedback</button>
          </div>
      </aside>
    
    <?php

    if (isset($banco) && isset($conexao)) {
        $banco->desconectar($conexao);
    }
    ?>

</body>
</html>