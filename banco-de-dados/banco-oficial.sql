drop database if exists db_integrador;
create database if not exists db_integrador;
use db_integrador;

create table aluno(
id int not null auto_increment,
nome varchar(40),
email varchar(60),
senha varchar(128),
data_cadastro date,
campus varchar(30),
constraint pk_usuario primary key(id)
) engine=innoDB;

create table administrador(
id int not null auto_increment,
login varchar(40),
senha varchar(128) not null,
constraint pk_administrador primary key(id)
) engine=innoDB;

create table anuncio(
id int not null auto_increment,
titulo varchar(30),
descricao varchar(200),
preco decimal(10,2) not null,
imagem longblob,
status enum ('EM ABERTO', 'EM NEGOCIACAO', 'VENDIDO') default 'EM ABERTO',
categoria enum ('MÓVEL', 'ELETRODOMÉSTICO', 'MATERIAL', 'UTENSÍLIOS', 'OUTROS', 'ROUPA') default 'MÓVEL',
data_publicacao date,
data_expiracao date,
visualizacoes int,
denuncias int,
id_vendedor int not null,
constraint pk_anuncio primary key(id),
constraint fk_anuncio_usuario foreign key(id_vendedor) references aluno(id)
	on delete cascade
    on update cascade
) engine=innoDB;

create table avaliacao(
id int not null auto_increment,
nota int not null,
comentario varchar(200),
data_avaliacao date,
resposta_vendedor text,
id_anuncio int not null,
id_comprador int not null,
constraint pk_avaliacao primary key(id),
constraint fk_avaliacao_anuncio foreign key(id_anuncio) references anuncio(id),
constraint fk_avaliacao_aluno foreign key(id_comprador) references aluno(id)
	on delete cascade
    on update cascade
) engine=innoDB;

create table denuncia(
id int not null auto_increment,
comentario varchar(200),
data_denuncia date,
id_anuncio int not null,
id_comprador int not null,
constraint pk_denuncia primary key(id),
constraint fk_denuncia_anuncio foreign key(id_anuncio) references anuncio(id),
constraint fk_denuncia_aluno foreign key(id_comprador) references aluno(id)
	on delete cascade
    on update cascade
) engine=InnoDB;

create table feedback(
id int not null auto_increment,
descricao text,
data_feedback date,
id_aluno int not null,
constraint pk_feedback primary key(id),
constraint fk_feedback_aluno foreign key(id_aluno) references aluno(id)
	on delete cascade
    on update cascade
) engine=InnoDB;

INSERT INTO aluno (nome, email, senha, data_cadastro, campus) VALUES
('Ana Silva', 'ana.silva@email.com', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', '2024-01-10', 'Campus Centro'),
('Bruno Costa', 'bruno.c@email.com', '7d471556e431a499a0937a4a905a5f11e95669f66560410427b1ee5013aa7356', '2024-01-12', 'Campus Sul'),
('Carla Souza', 'carla.souza@email.com', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', '2024-01-15', 'Campus Norte'),
('Diego Lima', 'diego.l@email.com', 'f0e4c2f76c58916ec258f246851bea091d14d4247a2fc3e18694461b1816e13b', '2024-01-20', 'Campus Centro'),
('Elena Pires', 'elena.p@email.com', '1213456789abcdef1213456789abcdef1213456789abcdef1213456789abcdef', '2024-02-01', 'Campus Leste'),
('Fabio Melo', 'fabio.melo@email.com', '89abcdef1213456789abcdef1213456789abcdef1213456789abcdef12134567', '2024-02-05', 'Campus Sul'),
('Gisele Ramos', 'gisele.r@email.com', 'abcdef1213456789abcdef1213456789abcdef1213456789abcdef1213456789', '2024-02-10', 'Campus Norte'),
('Heitor Neto', 'heitor.n@email.com', '456789abcdef1213456789abcdef1213456789abcdef1213456789abcdef1213', '2024-02-15', 'Campus Centro'),
('Igor Dias', 'igor.dias@email.com', 'senha_hash_9', '2024-03-01', 'Campus Sul'),
('Julia Ferraz', 'julia.f@email.com', 'senha_hash_10', '2024-03-05', 'Campus Norte'),
('Kevin Luz', 'kevin.luz@email.com', 'senha_hash_11', '2024-03-10', 'Campus Centro'),
('Laura Fontes', 'laura.f@email.com', 'senha_hash_12', '2024-03-15', 'Campus Leste'),
('Marcos Viana', 'marcos.v@email.com', 'senha_hash_13', '2024-03-20', 'Campus Sul'),
('Nina Rosa', 'nina.rosa@email.com', 'senha_hash_14', '2024-03-25', 'Campus Norte'),
('Otavio Paz', 'otavio.paz@email.com', 'senha_hash_15', '2024-04-01', 'Campus Centro'),
('Paula Reis', 'paula.r@email.com', 'senha_hash_16', '2024-04-05', 'Campus Leste');

INSERT INTO anuncio (titulo, descricao, preco, status, categoria, data_publicacao, data_expiracao, visualizacoes, denuncias, id_vendedor) VALUES
('Cálculo Vol 1 Stewart', 'Livro em bom estado, pouco usado.', 80.00, 'EM ABERTO', 'TECNOLOGIA', '2024-04-10', '2024-07-10', 45, 1, 9),
('Mouse Gamer RGB', 'Mouse com 6 meses de uso, 3200 DPI.', 50.00, 'EM NEGOCIACAO', 'TECNOLOGIA', '2024-04-12', '2024-05-12', 120, 2, 10),
('Escrivaninha Madeira', 'Mesa de estudos compacta para quarto.', 150.00, 'EM ABERTO', 'MÓVEL', '2024-04-15', '2024-06-15', 30, 3, 11),
('Jaleco Branco G', 'Jaleco de algodão, usado apenas 1 semestre.', 40.00, 'VENDIDO', 'ROUPA', '2024-03-01', '2024-04-01', 88, 4, 12),
('Calculadora HP 12C', 'Calculadora financeira original.', 120.00, 'EM ABERTO', 'TECNOLOGIA', '2024-04-18', '2024-07-18', 60, 2, 13),
('Vade Mecum 2023', 'Atualizado até dezembro de 2023.', 90.00, 'EM ABERTO', 'MATERIAL', '2024-04-20', '2024-06-20', 150, 1, 14),
('Violão Acústico', 'Acompanha capa e palheta.', 300.00, 'EM NEGOCIACAO', 'OUTROS', '2024-04-22', '2024-05-22', 200, 0, 15),
('Monitor 24 Pol', 'Monitor Full HD em perfeito estado.', 450.00, 'EM ABERTO', 'TECNOLOGIA', '2024-04-25', '2024-07-25', 95, 2, 16);

INSERT INTO avaliacao (nota, comentario, data_avaliacao, resposta_vendedor, id_anuncio, id_comprador) VALUES
(5, 'Livro impecável!', '2024-04-15', 'Obrigado pela compra!', 1, 1),
(4, 'Produto bom, mas demorou a responder.', '2024-04-20', 'Desculpe, estava em semana de provas.', 2, 2),
(5, 'Mesa muito resistente.', '2024-04-25', NULL, 3, 3),
(3, 'Tamanho ficou um pouco justo.', '2024-03-10', 'As medidas estavam na descrição.', 4, 4),
(5, 'Funcionando perfeitamente.', '2024-04-22', 'Sucesso nos estudos!', 5, 5),
(2, 'O livro veio com algumas páginas riscadas.', '2024-04-26', 'Puxa, não tinha notado.', 6, 6),
(4, 'Ótimo som.', '2024-04-28', NULL, 7, 7),
(5, 'Monitor excelente, brilho ótimo.', '2024-05-01', 'Fico feliz que gostou!', 8, 8);

INSERT INTO denuncia (comentario, data_denuncia, id_anuncio, id_comprador) VALUES
('Preço abusivo para produto usado.', '2024-04-11', 1, 2),
('Anúncio repetido.', '2024-04-13', 2, 3),
('Categoria errada.', '2024-04-16', 3, 4),
('Vendedor não responde chat.', '2024-04-20', 4, 5),
('Informação falsa na descrição.', '2024-04-21', 6, 9),
('Suspeita de perfil fake.', '2024-04-22', 5, 10),
('Linguagem imprópria no anúncio.', '2024-04-23', 7, 11),
('Produto parece falsificado.', '2024-04-24', 8, 12);

INSERT INTO feedback (descricao, data_feedback, id_aluno) VALUES
('O site está muito lento no mobile.', '2024-05-01', 9),
('Gostei muito da facilidade de anunciar.', '2024-05-02', 10),
('Poderia ter filtro por campus.', '2024-05-03', 1),
('Sugiro incluir pagamento via PIX direto.', '2024-05-04', 11),
('O botão de upload de fotos trava as vezes.', '2024-05-05', 2),
('Sistema de categorias muito bem organizado.', '2024-05-06', 12),
('Faltam mais opções de cursos no cadastro.', '2024-05-07', 13),
('Excelente plataforma para a comunidade acadêmica.', '2024-05-08', 3);

select * from aluno;
select * from administrador;
select * from anuncio;
select * from avaliacao;
select * from denuncia;
select * from feedback;