<?php
 class Denuncias {
  public $id;
  public $comentario;
  public $dataDenuncia;
  public $idAnuncio;
  public $idComprador;
 
  function receberDadosForm($conexao)
   {
   $this->id           = null; 
   $this->comentario   = trim($conexao->escape_string($_POST["comentario-denuncia"]));
   $this->dataDenuncia = date("Y-m-d");
   $this->idAnuncio    = trim($conexao->escape_string($_POST["id-anuncio"]));
   $this->idComprador  = trim($conexao->escape_string($_POST["id-comprador"]));
   }

  function cadastrar($conexao, $nomeDaTabelaDenuncia)
   {
   $sql = "INSERT $nomeDaTabelaDenuncia VALUES(
             null,
            '$this->comentario',
            '$this->dataDenuncia',
            '$this->idAnuncio',
            '$this->idComprador')";

   $conexao->query($sql) or die($conexao->error);
   } 

  // Gera as linhas (<tr>) dinâmicas para a tabela do painel administrativo do seu HTML
  function exibirRelatorioAdmin($conexao, $nomeDaTabelaDenuncia)
   {
   $sql = "SELECT id, comentario, data_denuncia, id_anuncio FROM $nomeDaTabelaDenuncia ORDER BY data_denuncia DESC";
   $resultado = $conexao->query($sql) or die($conexao->error);

   if($conexao->affected_rows == 0)
    {
    echo "<tr><td colspan='5'>Nenhuma denúncia pendente de análise.</td></tr>";
    }
   else
    {
    while($vetorRegistro = $resultado->fetch_array())
     {
     $id       = htmlentities($vetorRegistro[0], ENT_QUOTES, "UTF-8");
     $coment   = htmlentities($vetorRegistro[1], ENT_QUOTES, "UTF-8");
     $data     = htmlentities($vetorRegistro[2], ENT_QUOTES, "UTF-8");
     $idAnun   = htmlentities($vetorRegistro[3], ENT_QUOTES, "UTF-8");

     $dataFormatada = date("d/m/Y", strtotime($data));

     echo "<tr>
            <td>#$id</td>
            <td>Anúncio ID: $idAnun</td>
            <td>$coment</td>
            <td class='tag-critica'>$dataFormatada</td>
            <td>
                <button class='btn-tabela ver' onclick='analisarDenuncia($id)'>Analisar</button>
                <button class='btn-tabela ban' onclick='removerDenuncia($id)'>Remover</button>
            </td>
           </tr>";
     }
    }
   }
 }
?>
