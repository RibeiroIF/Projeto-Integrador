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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pesquisar.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/perfil.css">
    <link rel="stylesheet" href="../css/login.css">
    <script src="../javascript/troca-de-tela.js"></script>
    <script src="../javascript/ver-detalhes.js"></script> 
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

                <div class="card-stat" style="border-left: 5px solid #dc3545;">
                    <h3 style="color: #dc3545;">
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
                      // Busca dinâmica trazendo a lista de denúncias salvas no banco
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
                                      <button onclick='verDetalhes($idAnuncio, true)' class='btn-tabela' style='background-color: #0d6efd; color: white; border: none; padding: 5px 10px; border-radius: 4px;'>
                                          🔍 Abrir Anúncio
                                      </button>
                                  </td>
                              </tr>";
                          }
                      } else {
                          echo "<tr><td colspan='3' style='text-align:center; color: #777; padding: 15px;'>Nenhuma denúncia pendente na fila.</td></tr>";
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
            <div class="avatar-grande" style="background-color: #dc3545; color: white;">🛡️</div>
            <p><strong>Moderador:</strong> <?php echo isset($_SESSION['login_admin']) ? $_SESSION['login_admin'] : 'Admin'; ?></p>
            <p class="avaliacao">Painel de Proteção</p>
        </div>

        <div class="feedback-section text-center">
            <h4 style="color: #dc3545;">Modo de Moderação</h4>
            <p class="small text-muted">Use seus privilégios com cautela ao remover anúncios do banco de dados.</p>
        </div>
    </aside>

    <div id="modal-detalhes" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; justify-content: center; align-items: center;">
    
        <div style="background: #fff; padding: 25px; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); position: relative; max-height: 90vh; overflow-y: auto;">
            
            <button onclick="document.getElementById('modal-detalhes').style.display = 'none'" style="position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 22px; cursor: pointer; color: #999;">&times;</button>
            
            <div id="modal-carregando" style="text-align: center; padding: 30px 0;">
                <div class="spinner-border text-danger" role="status"></div>
                <p style="margin-top: 10px; color: #666;">Carregando detalhes...</p>
            </div>

            <div id="modal-dados-produto" style="display: none;">
                <h3 id="detalhe-titulo" style="margin-top: 0; color: #333;">Título</h3>
                <span id="detalhe-categoria" class="badge bg-secondary" style="margin-bottom: 15px;">Categoria</span>
                
                <p style="margin: 0 0 10px 0; font-size: 15px; color: #ff9800;">
                    ⭐ <span id="detalhe-media-nota" style="font-weight: bold;">0.0</span> 
                    <span id="detalhe-total-notas" style="color: #777; font-size: 12px;">(0 avaliações)</span>
                </p>

                <p id="detalhe-status" style="font-weight: bold; margin: 5px 0 15px 0; font-size: 14px;"></p>
                
                <div style="height: 200px; display: flex; align-items: center; justify-content: center; background: #f5f5f5; border-radius: 4px; overflow: hidden; margin-bottom: 15px;">
                    <img id="detalhe-imagem" src="" alt="Produto" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                
                <p class="preco" style="font-size: 20px; color: #28a745; font-weight: bold; margin: 10px 0;">R$ 0,00</p>
                
                <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                    <h5>Descrição:</h5>
                    <p id="detalhe-descricao" style="color: #666; font-size: 14px; line-height: 1.5; max-height: 80px; overflow-y: auto;"></p>
                </div>

                <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                    <h5>Contato do Vendedor:</h5>
                    <p style="font-size: 16px; font-weight: bold; color: #198754; margin: 5px 0;">
                        📱 <span id="detalhe-whatsapp">...</span>
                    </p>
                    <a id="btn-link-whatsapp" href="#" target="_blank" class="btn btn-success btn-sm" style="font-weight: bold; display: none;">Chamar no WhatsApp</a>
                </div>

                <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h5>Denúncias / Reclamações:</h5>
                        <button onclick="abrirJanelaDenuncia()" style="background: #dc3545; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; font-size: 12px; cursor: pointer;">🚨 Denunciar</button>
                    </div>
                    <div id="lista-denuncias" style="max-height: 100px; overflow-y: auto; margin-top: 10px; background: #fafafa; padding: 8px; border-radius: 4px; font-size: 13px; color: #555;"></div>
                </div>

                <div id="secao-admin-excluir" style="display: none; border-top: 2px solid #dc3545; padding-top: 15px; margin-top: 15px; background: #fff5f5; padding: 10px; border-radius: 4px;">
                    <h5 style="color: #dc3545; margin-top: 0;">Painel de Moderação</h5>
                    <p style="font-size: 13px; color: #555; margin-bottom: 8px;">Para excluir este anúncio, digite uma justificativa que será enviada ao aluno:</p>
                    <textarea id="txt-motivo-exclusao" placeholder="Ex: Seu anúncio viola as regras da instituição por conter itens não permitidos..." style="width: 100%; height: 70px; padding: 6px; border: 1px solid #dc3545; border-radius: 4px; resize: none; box-sizing: border-box; font-size: 13px;"></textarea>
                    <button onclick="confirmarExclusaoAdmin()" style="background: #dc3545; color: #fff; border: none; width: 100%; padding: 8px; margin-top: 8px; border-radius: 4px; font-weight: bold; cursor: pointer;">❌ Excluir Anúncio Permanentemente</button>
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