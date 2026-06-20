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
                <li class="admin-link"><a href="#" id="menu-adm" class="menu" onclick="navegar('tela-admin'); navegarMenu('menu-adm')">Painel Adm</a></li>
                <li><a href="login.php" class="logout">Sair</a></li>
            </ul>
        </nav>
    </aside>

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
                <article class="card-anuncio">
                    <div class="badge-campus">Câmpus Florianópolis</div>
                    <div class="foto-placeholder">Imagem do Produto</div>
                    <div class="info-anuncio">
                        <h4>Cálculo A - Diva Flemming</h4>
                        <p class="preco">R$ 45,00</p>
                        <p class="vendedor">Vendido por: Maria Silva</p>
                        <button class="btn-detalhes">Ver Detalhes</button>
                    </div>
                </article>
                
                <article class="card-anuncio">
                    <div class="badge-campus">Câmpus São José</div>
                    <div class="foto-placeholder">Imagem do Produto</div>
                    <div class="info-anuncio">
                        <h4>Monitor 24' Dell</h4>
                        <p class="preco">R$ 600,00</p>
                        <p class="vendedor">Vendido por: João Pedro</p>
                        <button class="btn-detalhes">Ver Detalhes</button>
                    </div>
                </article>
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
                <article class="card-anuncio">
                    <div class="badge-campus">Câmpus Florianópolis</div>
                    <div class="foto-placeholder">Imagem</div>
                    <div class="info-anuncio">
                        <h4>Monitor 24' Dell</h4>
                        <p class="preco">R$ 600,00</p>
                        <button class="btn-detalhes">Ver Detalhes</button>
                    </div>
                </article>
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
                <div class="card-mini-stats">
                    <span>Ativos</span>
                    <strong>03</strong>
                </div>
                <div class="card-mini-stats">
                    <span>Vendidos</span>
                    <strong>12</strong>
                </div>
                <div class="card-mini-stats">
                    <span>Visualizações</span>
                    <strong>142</strong>
                </div>
            </section>

            <div class="lista-gerenciamento">
                <article class="item-anuncio-gerenciar">
                    <div class="img-preview">📸</div>
                    <div class="detalhes-item">
                        <h4>Cálculo A - Diva Flemming</h4>
                        <p class="preco">R$ 45,00</p>
                        <span class="status-badge ativo">Ativo</span>
                    </div>
                    <div class="acoes-item">
                        <button class="btn-acao editar">Editar</button>
                        <button class="btn-acao excluir">Excluir</button>
                    </div>
                </article>

                <article class="item-anuncio-gerenciar">
                    <div class="img-preview">📸</div>
                    <div class="detalhes-item">
                        <h4>Monitor 24' Dell</h4>
                        <p class="preco">R$ 600,00</p>
                        <span class="status-badge vendido">Vendido</span>
                    </div>
                    <div class="acoes-item">
                        <button class="btn-acao ver">Ver Comprador</button>
                        <button class="btn-acao excluir">Remover</button>
                    </div>
                </article>
            </div>
        </div>


        <!--Tela de novo anuncio-->
        <div id="tela-novoanuncio" class="tela collapse">
            <header class="header-pagina">
                <h2>Criar Novo Anúncio</h2>
                <p>Preencha os detalhes abaixo para publicar seu item.</p>
            </header>

            <form class="form-anuncio">
                <section class="sessao-form">
                    <h3>Informações Básicas</h3>
                    <div class="campo">
                        <label for="titulo">Título do Anúncio *</label>
                        <input type="text" id="titulo" placeholder="Ex: Livro Cálculo A - Diva Flemming" required>
                    </div>

                    <div class="fila-campos">
                        <div class="campo">
                            <label for="categoria">Categoria</label>
                            <select id="categoria">
                                <option value="livros">Livros / Material Didático</option>
                                <option value="eletronicos">Eletrônicos</option>
                                <option value="moveis">Móveis</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div class="campo">
                            <label for="preco">Preço (R$)*</label>
                            <input type="number" id="preco" step="0.01" placeholder="0,00" required>
                        </div>
                    </div>
                </section>

                <section class="sessao-form">
                    <h3>Imagens e Descrição</h3>
                    <div class="campo">
                        <label>Fotos do Produto</label>
                        <div class="upload-container">
                            <input type="file" id="fotos" multiple accept="image/*">
                            <div class="upload-placeholder">
                                <span>📷 Clique para selecionar fotos</span>
                            </div>
                        </div>
                    </div>
                    <div class="campo">
                        <label for="descricao">Descrição Detalhada</label>
                        <textarea id="descricao" rows="5" placeholder="Descreva o estado do item, tempo de uso, etc."></textarea>
                    </div>
                </section>

                <div class="botoes-form">
                    <button type="button" class="btn-cancelar" onclick="history.back()">Cancelar</button>
                    <button type="submit" class="btn-publicar">Publicar Anúncio</button>
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
                
                <article class="card-anuncio">
                    <button class="btn-remover-favorito" title="Remover dos favoritos">❤️</button>
                    <div class="badge-campus">Câmpus São José</div>
                    <div class="foto-placeholder">Imagem</div>
                    <div class="info-anuncio">
                        <h4>Arduíno Uno R3</h4>
                        <p class="preco">R$ 50,00</p>
                        <p class="vendedor">Vendido por: Carlos Tech</p>
                        <button class="btn-detalhes">Ver Detalhes</button>
                    </div>
                </article>

                <article class="card-anuncio">
                    <button class="btn-remover-favorito" title="Remover dos favoritos">❤️</button>
                    <div class="badge-campus">Câmpus Palhoça</div>
                    <div class="foto-placeholder">Imagem</div>
                    <div class="info-anuncio">
                        <h4>Mesa Digitalizadora</h4>
                        <p class="preco">R$ 250,00</p>
                        <p class="vendedor">Vendido por: Ana Design</p>
                        <button class="btn-detalhes">Ver Detalhes</button>
                    </div>
                </article>
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
                        <span class="avatar-letra">A</span>
                        <button class="btn-trocar-foto">📷</button>
                    </div>
                    <div class="perfil-identidade">
                        <h3>Aluno Exemplo</h3>
                        <p>Membro desde: Março 2024</p>
                    </div>
                </div>

                <form class="form-perfil">
                    <div class="grid-form">
                        <div class="campo">
                            <label>Nome Completo</label>
                            <input type="text" value="Aluno Exemplo de Souza">
                        </div>
                        <div class="campo">
                            <label>E-mail Institucional</label>
                            <input type="email" value="aluno.souza@aluno.ifsc.edu.br" disabled>
                        </div>
                        <div class="campo">
                            <label>Câmpus Principal</label>
                            <select>
                                <option>Florianópolis</option>
                                <option>São José</option>
                                <option>Palhoça</option>
                                <option>Garopaba</option>
                            </select>
                        </div>
                        <div class="campo">
                            <label>WhatsApp (Para contato)</label>
                            <input type="text" placeholder="(48) 99999-9999">
                        </div>
                    </div>

                    <div class="campo">
                        <label>Bio / Descrição</label>
                        <textarea placeholder="Conte um pouco sobre você ou o que costuma vender/comprar..."></textarea>
                    </div>

                    <div class="botoes-form">
                        <button type="button" class="btn-cancelar">Descartar Alterações</button>
                        <button type="submit" class="btn-salvar">Salvar Alterações</button>
                    </div>
                </form>
            </section>
        </div>
        
        <!--Tela adm-->
        <div id="tela-admin" class="tela collapse">
            <header class="header-admin">
                <h2>Painel de Controle Administrativo</h2>
                <p>Monitoramento de anúncios e usuários do sistema.</p>
            </header>

            <section class="cards-estatisticas">
                <div class="card-stat">
                    <h3>1.240</h3>
                    <p>Total de Anúncios</p>
                </div>
                <div class="card-stat">
                    <h3>850</h3>
                    <p>Usuários Ativos</p>
                </div>
                <div class="card-stat alerta">
                    <h3>12</h3>
                    <p>Denúncias Pendentes</p>
                </div>
            </section>

            <section class="tabela-moderacao">
                <h3>Fila de Moderação (Denúncias)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Anúncio</th>
                            <th>Vendedor</th>
                            <th>Motivo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#452</td>
                            <td>iPhone 12 Trincado</td>
                            <td>aluno.teste@...</td>
                            <td class="tag-critica">Preço Abusivo</td>
                            <td>
                                <button class="btn-tabela ver">Analisar</button>
                                <button class="btn-tabela ban">Remover</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#455</td>
                            <td>Respostas Cálculo 1</td>
                            <td>estudante.x@...</td>
                            <td class="tag-aviso">Conteúdo Impróprio</td>
                            <td>
                                <button class="btn-tabela ver">Analisar</button>
                                <button class="btn-tabela ban">Remover</button>
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
            <div class="avatar-grande">👤</div>
            <p><strong>Estudante:</strong> @aluno.ifsc.edu.br</p>
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
    
    require_once "executar-banco.php";
    //require "criar-banco-classificados.php";

    ?>
    
</body>
</html>