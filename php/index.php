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

$anuncios = new Anuncios();
$alunos = new Alunos();


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nome"])) {
      $alunos->atualizarPerfil($conexao, $banco->aluno);
      // Recarrega os dados atualizados caso o formulário tenha sido enviado
      $alunos->carregarDadosPerfil($conexao, $banco->aluno, $_SESSION['id_aluno']);
}

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
    
    $banco->desconectar($conexao);
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
              
              <p id="detalhe-status" style="font-weight: bold; margin: 5px 0 15px 0; font-size: 14px;"></p>
              
              <div style="height: 250px; display: flex; align-items: center; justify-content: center; background: #f5f5f5; border-radius: 4px; overflow: hidden; margin-bottom: 15px;">
                  <img id="detalhe-imagem" src="" alt="Produto" style="max-width: 100%; max-height: 100%; object-fit: contain;">
              </div>
              
              <p class="preco" style="font-size: 20px; color: #28a745; font-weight: bold; margin: 10px 0;">R$ 0,00</p>
              
              <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                  <h5>Descrição:</h5>
                  <p id="detalhe-descricao" style="color: #666; font-size: 14px; line-height: 1.5; max-height: 100px; overflow-y: auto;">Descrição detalhada aqui.</p>
              </div>
          </div>
        </div>
    </div>

    <main class="blocoCentral">
    <!--Tela inicial-->
        <div id="tela-home" class="main tela-home tela show">
          <header class="header-busca">
              <h2>Explorar Anúncios</h2>
              <div class="search-bar">
                  <input type="text" placeholder="O que você procura hoje? (Livros, eletrônicos...)">
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
                  // Atualizado: Passando o ID do aluno logado para a checagem dos corações
                  $anuncios->listarAnunciosAtivos($conexao, $banco->anuncio, $_SESSION['id_aluno']); 
              ?>
          </div>
      </div>

        <!--Tela de pesquisa-->
        <div id="tela-pesquisa" class="tela collapse">
          <header class="header-pesquisa-avancada">
              <h2>Busca Detalhada</h2>
              <div class="search-box-large">
                  <input type="text" placeholder="Ex: Monitor Dell 24 polegadas...">
                  <button class="btn-busca-principal">Pesquisar</button>
              </div>
          </header>

          <section class="filtros-container">
              <div class="filtro-grupo">
                  <label>Câmpus:</label>
                  <select>
                      <option value="todos">Todos os Câmpus</option>
                      <option value="fpolis">Florianópolis</option>
                      <option value="sj">São José</option>
                      <option value="palhoca">Palhoça</option>
                  </select>
              </div>

              <div class="filtro-grupo">
                  <label>Faixa de Preço:</label>
                  <div class="inputs-preco">
                      <input type="number" placeholder="Min" min="0" step="5">
                      <input type="number" placeholder="Max" max="20000" step="5">
                  </div>
              </div>

              <div class="filtro-grupo">
                  <label>Condição:</label>
                  <select>
                      <option value="todos">Qualquer uma</option>
                      <option value="novo">Novo</option>
                      <option value="usado">Usado - Excelente</option>
                      <option value="usado-bom">Usado - Bom</option>
                  </select>
              </div>
          </section>

          <div class="resultados-info">
              <p>Mostrando 12 resultados para "Eletrônicos"</p>
              <select class="ordenacao">
                  <option>Mais recentes</option>
                  <option>Menor Preço</option>
                  <option>Maior Preço</option>
              </select>
          </div>

          <div class="grid-anuncios">
              <?php 
                  // Atualizado: Passando o ID do aluno logado para a checagem dos corações
                  $anuncios->listarAnunciosAtivos($conexao, $banco->anuncio, $_SESSION['id_aluno']); 
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
                    // Define o ID do usuário logado (exemplo)
                    $id_usuario_logado = $_SESSION['id_aluno']; 

                    // Executa a função do arquivo externo e recebe os dados
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
                <div class="card-mini-stats">
                    <span>Visualizações</span>
                    <strong><?php echo $stats['visualizacoes']; ?></strong>
                </div>
            </section>

            <div class="lista-gerenciamento">
                <?php 
                    $id_usuario_logado = $_SESSION['id_aluno']; 
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
        <div id="tela-perfil" class="tela collapse">
            <header class="header-perfil">
                <h2>Configurações do Perfil</h2>
                <p>Gerencie suas informações públicas e de segurança.</p>
            </header>
            <section class="secao-perfil">
                <div class="perfil-topo">
                    <div class="avatar-edit">
                        <span class="avatar-letra">
                            <?php echo !empty($alunos->nome) ? mb_strtoupper(mb_substr($alunos->nome, 0, 1, "UTF-8"), "UTF-8") : "A"; ?>
                        </span>
                        <button class="btn-trocar-foto">📷</button>
                    </div>
                    <div class="perfil-identidade">
                        <h3><?php echo $alunos->nome; ?></h3>
                        <p>Membro desde: <?php echo $alunos->dataCadastro; ?></p>
                    </div>
                </div>



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
                            <button type="submit" class="btn-salvar">Salvar Alterações</button>
                        </div>
                    </form>
            </section>
        </div>
    </main>

    <aside class="blocoDireito">
        <div class="perfil-resumo">
            <h3>Meu Perfil</h3>
            <div class="avatar-grande">👤</div>
            <p><strong>Estudante:</strong> <?php echo $_SESSION['email_aluno']; ?></p>
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