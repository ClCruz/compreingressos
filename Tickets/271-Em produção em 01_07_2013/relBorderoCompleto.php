<?php
require_once("../settings/functions.php");
require_once("../settings/Utils.php");

$connGeral = getConnectionTsp();
session_start();

// Variaveis passadas por parametro pela url
$CodApresentacao = $_GET["CodApresentacao"];
$CodPeca = (isset($_GET["CodPeca"]) && !empty($_GET["CodPeca"])) ? $_GET["CodPeca"] : "";
$CodSala = (isset($_GET["Sala"]) && !empty($_GET["Sala"])) ? $_GET["Sala"] : "";
$DataIni = (isset($_GET["DataIni"]) && !empty($_GET["DataIni"])) ? $_GET["DataIni"] : "null";
$DataFim = (isset($_GET["DataFim"]) && !empty($_GET["DataFim"])) ? $_GET["DataFim"] : "null";
$HorSessao = (isset($_GET["HorSessao"]) && !empty($_GET["HorSessao"])) ? $_GET["HorSessao"] : "null";
$Resumido = $_GET["Resumido"];

if (isset($_GET["imagem"]) && $_GET["imagem"] == "logo") {
  $strSql = "SP_REL_BORDERO ?, ?, ?";
  $pRSBordero = executeSQL($connGeral, $strSql, array($CodPeca, $CodApresentacao, "'" . $_SESSION["NomeBase"] . "'"));
  if (sqlErrors ())
    $err = "Erro #001 " . print_r(sqlErrors());
}

// Monta e executa query principal do relatório
$strGeral = "SP_REL_BORDERO" . (($CodSala == 'TODOS') ? '10' : '01') . " 'Emerson', " . $CodPeca . "," . $CodSala . "," . $DataIni . "," . $DataFim . ",'" . (($_GET['Small'] == '1') ? '--' : $HorSessao) . "','" . $_SESSION["NomeBase"] . "'";
$pRSGeral = executeSQL($connGeral, $strGeral, array(), true);
if (sqlErrors ())
  $err = "Erro #002 <br/>" . var_dump($paramsGeral) . "<br/>" . $strGeral . "<br/>";

$array = explode(":", $pRSGeral["NomResPeca"]);
$PPArray = ($array[0] != "") ? $array[0] : "N&atilde;o Cadastrado";
$SPArray = ($array[1] != "") ? $array[1] : "N&atilde;o Cadastrado";
$TPArray = ($array[2] != "") ? $array[2] : "N&atilde;o Cadastrado";

if (isset($err) && $err != "") {
  echo $err . "<br/>";
  print_r(sqlErrors());
}

if ($_GET['Small'] == '1') {
  $strBordero = "SP_REL_BORDERO14 'Emerson', " . $CodPeca . "," . $CodSala . "," . $DataIni . "," . $DataFim . ",'--','" . $_SESSION["NomeBase"] . "'";
  $resultBordero = executeSQL($connGeral, $strBordero, array());

  if (hasRows($resultBordero)) {
    $numsArray = array();
    while ($rsBordero = fetchResult($resultBordero)) {
      $numsArray[] = $rsBordero['NumBordero'];
    }
    $pRSGeral['NumBordero'] = gerarNotacaoIntervalo($numsArray);
  }
}

if (isset($err) && $err != "") {
  echo $err . "<br/>";
  print_r(sqlErrors());
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="pt-BR" xmlns="http://www.w3.org/1999/xhtml">
  <head>    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="Content-Language" content="pt-Br" />
    <meta name="Copyright" content="Copyright &copy; 2013" />

    <title>Borderô de Vendas</title>
    <link rel="stylesheet" type="text/css" href="../stylesheets/estilos_ra.css" />
    <link rel="stylesheet" type="text/css" href="../stylesheets/padraoRelat.CSS" />
    <link rel="stylesheet" type="text/css" href="../stylesheets/relBorderoCompleto.css" />
  </head>
  <body leftmargin="0" topmargin="0">
    <script language="VBScript">
      function ZeroData(data) {
        ZeroData = Right(("0" & day(data)),2) & "/" & Right(("0" & month(data)),2) & "/" & year(data);
      }
    </script>
    <table width=650 class="tabela" border="0">
      <tr>
        <td colspan="1" rowspan="2"><img alt="Compreingressos.com" align="left" border="0" src="../images/logo.jpg" /></td>
        <td colspan="1" height="15"></td>
      </tr>
      <tr>
        <td class="tabela" align="center" bgcolor="LightGrey"><b><font size=2 face="tahoma,verdana,arial">Borderô de Vendas</font><br/>Contabilização dos Ingressos</b></td>
      </tr>
      <tr>
        <td colspan="2">
          <table class="tabela" width="648">
            <tr>
              <td align="right" width="70"><font size=1 face="tahoma,verdana,arial"><b>Evento:</b></font></td>
              <td align="left" width="370"><?php echo utf8_encode($pRSGeral["NomPeca"]); ?></td>
              <td align="right" width="120"><font size=1 face="tahoma,verdana,arial"><b>Borderô nº</b></font></td>
              <td align="left" width="220"><?php echo $pRSGeral["NumBordero"]; ?></td>
            </tr>
            <tr>
              <td align="right"><font size=1 face="tahoma,verdana,arial"><b>Responsável:</b></font></td>
              <td align="left"><?php echo utf8_encode($PPArray); ?></td>
              <td align="right"><font size=1 face="tahoma,verdana,arial"><b>Apresentação nº</b></font></td>
              <td align="left"><?php echo $pRSGeral["NumBordero"]; ?></td>
            </tr>
            <tr>
              <td align="right"><font size=1 face="tahoma,verdana,arial"><b>CNPJ/CPF:</b></font></td>
              <td align="left"><?php echo $SPArray; ?></td>
              <?php
              $DataIni2 = substr($DataIni, -2, 2) . '/' . substr($DataIni, -4, 2) . '/' . substr($DataIni, 0, 4);
              $DataFim2 = substr($DataFim, -2, 2) . '/' . substr($DataFim, -4, 2) . '/' . substr($DataFim, 0, 4);
              if ($_GET['Small'] != '1') {
              ?>
                <td align="right"><font size=1 face="tahoma,verdana,arial"><b>Data e Horário:</b></font></td>
                <td align="left"><?php echo $pRSGeral["DatApresentacao"]->format("d/m/Y") . " | " . $pRSGeral["HorSessao"]; ?></td>
              <?php } else {
              ?>
                <td align="right"><font size=1 face="tahoma,verdana,arial"><b>Datas:</b></font></td>
                <td align="left"><?php echo $DataIni2 . " à " . $DataFim2; ?></td>
              <?php } ?>
            </tr>
            <tr>
              <td align="right" rowspan="3" valign="top"><font size=1 face="tahoma,verdana,arial"><b>Endereço:</b></font></td>
              <td align="left" rowspan="3" valign="top"><?php echo utf8_encode($TPArray); ?></td>
              <td align="right"><font size=1 face="tahoma,verdana,arial"><b>Dia:</b></font></td>
              <td align="left">
                <?php
                if ($pRSGeral["DatApresentacao"] != '--') {
                  $data = $pRSGeral["DatApresentacao"]->format("d/m/Y");
                  $datas = explode("/", $data);
                  $weekDay = date("N", mktime(0, 0, 0, $datas[1], $datas[0], $datas[2]));
                  echo DiaSemana($weekDay);
                } else {
                  echo 'TODOS';
                }
                ?></td>
            </tr>
            <tr>
              <td align="right"><font size=1 face="tahoma,verdana,arial"><b>Local:</b></font></td>
              <td align="left"><?php echo utf8_encode($pRSGeral["NomSala"]); ?></td>
            </tr>
            <tr>
              <td align="right"><font size=1 face="tahoma,verdana,arial"><b>Lotação/Capacidade:</b></font></td>
              <td align="left"><?php echo $pRSGeral["Lugares"]; ?></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <br/>

    <?php
                $lotacao = $pRSGeral["Lugares"];
                $totNVendidos = 0;
                $totPagantes = 0;
                $totNPagantes = 0;
                $totPublico = 0;
                $query = executeSQL($connGeral, $strGeral, $paramsGeral);
                while ($pRSBordero = fetchResult($query)) {
                  $nPag = 1;
                  $nLin = 0;
                  $totTransacoes = 0;
                  $totNVendidos = $totNVendidos + ($pRSBordero["Lugares"] - $pRSBordero["PubTotal"]);
                  $totNPagantes = $totNPagantes + ($pRSBordero["PubTotal"] - $pRSBordero["Pagantes"]);
                  $totPagantes = $totPagantes + $pRSBordero["Pagantes"];
                  $totPublico = $totPublico + $pRSBordero["PubTotal"];
                  if ($Resumido == 0) {
    ?>
                    <table width="656" class="tabela tblResumo" border="0">
                      <tr>
                        <td align=center width="162" class="tabela"><b>Ingressos Não Vendidos:</b><?php echo $totNVendidos; ?></td>
                        <td align=center width="162" class="tabela"><b>Público Convidado:</b><?php echo $totNPagantes; ?></td>
                        <td align=center width="162" class="tabela"><b>Público Pagante:</b><?php echo $totPagantes; ?></td>
                        <td align=center width="163" class="tabela"><b>Público Total:</b><?php echo $totPublico; ?></td>
                      </tr>
                    </table>
                    <br/>
                    <table width="656" class="tabela" border="0" bgcolor="LightGrey">
                      <tr>
                        <td align="center" colspan="7"><font size="2" face="tahoma,verdana,arial"><b>1 - VENDAS BORDERÔ</b></font></td>
                      </tr>
                      <tr>
                        <td	align="left" width="104" class="titulogrid">Setor</td>
                        <td	align="left" width="240" class="titulogrid">Tipo de Ingressos</td>
                        <td	align="right" width="104" class="titulogrid">Qtde Estornados</td>
                        <td	align="right" width="104" class="titulogrid">Qtde Vendidos</td>
                        <td	align="right" width="104" class="titulogrid">Acessados Urna</td>
                        <td	align="right" width="104" class="titulogrid">Preço</td>
                        <td	align="right" width="104" class="titulogrid">Sub Total</td>
                      </tr>
      <?php
                    $strSqlBilhete = ($CodSala == 'TODOS') ? "SP_REL_BORDERO05 '" . $DataIni . "','" . $DataFim . "'," . $CodPeca . ",'" . $HorSessao . "','" . $_SESSION["NomeBase"] . "'" : "SP_REL_BORDERO04 " . $pRSBordero["CodApresentacao"] . ",'" . $_SESSION["NomeBase"] . "'";
                    $queryBilhete = executeSQL($connGeral, $strSqlBilhete);
                    if (sqlErrors ()) {
                      echo "Erro #003: ";
                      print_r(sqlErrors());
                      echo "<br/>" . $strSqlBilhete;
                    }
                    while ($pRSBilhete = fetchResult($queryBilhete)) {
                      if ($Resumido == "0") {
      ?>
                        <tr>
                          <td	align=left class=texto><?php echo formatarConteudoVazio(utf8_encode($pRSBilhete["NomSetor"])); ?></td>
                          <td	align=left  class=texto><?php echo formatarConteudoVazio(utf8_encode($pRSBilhete["TipBilhete"])); ?></td>
                          <td	align=right  class=texto><?php echo formatarConteudoVazio($pRSBilhete["QtdeEstornados"]); ?></td>
                          <td	align=right  class=texto><?php echo formatarConteudoVazio($pRSBilhete["QtdeVendidos"]); ?></td>
                          <td	align=right class=texto></td>
                          <td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSBilhete["Preco"], 2, ",", "."); ?></td>
                          <td	align=right class=texto >R$&nbsp;<?php echo number_format($pRSBilhete["Total"], 2, ",", "."); ?></td>
                        </tr>
      <?php
                      }
                      $nTotalVendas = $nTotalVendas + $pRSBilhete["Total"];
                    }
                    if ($Resumido == "0") {
      ?>
                      <tr>
                        <td colspan="5" bgcolor="#FFFFFF" rowspan="2" align="center" class="tabela"><font size=2 face="tahoma,verdana,arial"><b>Taxa de Ocupação:</b>&nbsp;&nbsp;  <?php echo number_format((($totPublico / $lotacao) * 100), 2, ",", "."); ?> %</font></td>
                        <td bgcolor="LightGrey" colspan="3" align="center" class="label"><b>TOTAL DE VENDAS</b></td>
                      </tr>
                      <tr>
                        <td bgcolor="LightGrey" colspan="2" align="right" class="label"><b>R$&nbsp;&nbsp;<?php echo number_format($nTotalVendas, 2, ",", "."); ?></b></td>
                      </tr>
                    </table>
                    <br clear="all"/>

                    <table width=656 class="tabela" border="0" bgcolor="LightGrey">
                      <tr>
                        <td align="center" colspan="5"><font size=2 face="tahoma,verdana,arial"><b>2 - DESCONTOS BORDERÔ</b></font></td>
                      </tr>
                      <tr>
                        <td	align="left" width="219" class="titulogrid">Tipo de Débito</td>
                        <td	align="right" width="219" class="titulogrid">% ou R$ Fixo</td>
                        <td	align="right" width="219" class="titulogrid">Valor</td>
                      </tr>
      <?php
                    }
                    $nTotalDesp = 0;
                    $nLin = $nLin + 4;

                    if ($CodSala == 'TODOS') {
                      $gSQL = "select CodApresentacao from " . $_SESSION["NomeBase"] . "..tabapresentacao where
							    datapresentacao between ? and ?
							    and codpeca = ?" . (($_GET['Small'] != '1') ? ' and horsessao = ?' : '');
                      $paramsApresentacoes = (($_GET['Small'] == '1') ? array($DataIni, $DataFim, $CodPeca) : array($DataIni, $DataFim, $CodPeca, $HorSessao));
                      $resultApresentacoes = executeSQL($connGeral, $gSQL, $paramsApresentacoes);
                      $rsApresentacoes = fetchResult($resultApresentacoes);
                    }

                    $despesas = array();

                    do {
                      //Query utilizada para novo relatório
                      //$strSqlDebito = ($CodSala == 'TODOS') ? "SP_REL_BORDERO06 " . $CodPeca . "," . $rsApresentacoes["CodApresentacao"] . ",'" . $DataIni . "','" . $_SESSION["NomeBase"] . "'" : "SP_REL_BORDERO06 " . $pRSBordero["CodPeca"] . "," . $pRSBordero["CodApresentacao"] . ",'" . $pRSBordero["DatApresentacao"]->format("Ymd") . "','" . $_SESSION["NomeBase"] . "'";
                      $strSqlDebito = ($CodSala == 'TODOS') ? "SP_REL_BORDERO_VENDAS_2 " . $CodPeca . "," . $rsApresentacoes["CodApresentacao"] . ",'" . $DataIni . "','" . $_SESSION["NomeBase"] . "'" : "SP_REL_BORDERO_VENDAS_2 " . $pRSBordero["CodPeca"] . "," . $pRSBordero["CodApresentacao"] . ",'" . $pRSBordero["DatApresentacao"]->format("Ymd") . "','" . $_SESSION["NomeBase"] . "'";
                      $queryDebito = executeSQL($connGeral, $strSqlDebito);

                      while ($pRSDebito = fetchResult($queryDebito)) {
                        if ($pRSDebito["TipValor"] == "P")
                          $simbolo = "%";
                        else
                          $simbolo = "R$";

                        $nTotalDesp += $pRSDebito["Valor"];
                        $despesas[$pRSDebito["CodTipDebBordero"]]['nome'] = $pRSDebito["DebBordero"];
                        $despesas[$pRSDebito["CodTipDebBordero"]]['tipoValor'] = $simbolo . " " . number_format($pRSDebito["PerDesconto"], 2, ",", ".");
                        $despesas[$pRSDebito["CodTipDebBordero"]]['valor'] += $pRSDebito["Valor"];
                        $despesas[$pRSDebito["CodTipDebBordero"]]['valor_real'] += $pRSDebito["ValorReal"];
                        $despesas[$pRSDebito["CodTipDebBordero"]]['limite'] += $pRSDebito["VlMinimoDebBordero"];
                      }
                    } while ($rsApresentacoes = fetchResult($resultApresentacoes));

                    if (!empty($forma_pagamento)) {
                      foreach ($forma_pagamento as $forma) {
                        $despesas[] = array(
                            'nome' => $forma['nome'],
                            'valor' => $forma['valor'],
                            'tipoValor' => ' - '
                        );

                        $nTotalDesp += $forma['valor'];
                      }
                    }

                    $strSqlDetTemp = "SP_REL_BORDERO_VENDAS;" . (($CodSala == 'TODOS') ? '11' : '5') . " '" . $DataIni . "','" . $DataFim . "'," . $CodPeca . "," . $CodSala . ",'" . $HorSessao . "','" . $_SESSION["NomeBase"] . "'";
                    $queryDetTemp = executeSQL($connGeral, $strSqlDetTemp);
                    while ($pRSDetalhamento = fetchResult($queryDetTemp)) {
                      $nBrutoTot += $pRSDetalhamento["totfat"];
                      $nTotLiqu += $pRSDetalhamento["liquido"];
                    }

                    $taxaDosCartoes = $nBrutoTot - $nTotLiqu;
                    $nTotalDesp += $taxaDosCartoes;

                    foreach ($despesas as $desp) {
                      if ($desp["limite"] > 0) {
                        $nTotalDesp -= $desp["valor"];

                        if ($desp["limite"] > $desp["valor_real"]) {
                          $nTotalDesp += $desp["limite"];
                          $desp["valor"] = $desp["limite"];
                        } else {
                          $nTotalDesp += $desp["valor_real"];
                          $desp["valor"] = $desp["valor_real"];
                        }
                      }
                      if ($Resumido == "0") {
      ?>
                        <tr>
                          <td	align=left  class=texto><?php echo utf8_encode($desp["nome"]); ?></td>
                          <td	align=right class=texto><?php echo $desp["tipoValor"]; ?></td>
                          <td	align=right class=texto><?php echo number_format($desp["valor"], 2, ",", "."); ?></td>
                        </tr>
<?php
                      }
                    }
?>
                    <tr>
                      <td	align=left  class=texto>TAXA DOS CARTÕES (DÉBITO E CRÉDITO)</td>
                      <td	align=right class=texto> - </td>
                      <td	align=right class=texto><?php echo number_format($taxaDosCartoes, 2, ",", "."); ?></td>
                    </tr>
                    <tr>
                      <td bgcolor="#FFFFFF" align="left" valign="top" rowspan="3" colspan="2"><font size=1 face="tahoma,verdana,arial">assinaturas dos responsáveis, <?php echo date("d/m/Y G:i:s"); ?></font></td>
                      <td bgcolor="LightGrey" colspan="2" align="center" class="label"><b>TOTAL DESCONTOS</b></td>
                    </tr>
                    <tr>
                      <td align="right" bgcolor="LightGrey" class="label">R$&nbsp;&nbsp;&nbsp;<?php echo number_format($nTotalDesp, 2, ",", "."); ?><br/>
                        <br/>
                      </td>
                    </tr>
                    <tr>
                      <td bgcolor="LightGrey" align="center" class="label"><b>(VENDAS - DESCONTOS)</b></td>
                    </tr>
                    <tr>
                      <td width="440" bgcolor="#FFFFFF" colspan="2">
                        <table border="0">
                          <tr>
                            <td class="linha_assinatura" width="200">_______________________</td>
                            <td class="linha_assinatura" width="200">_______________________</td>
                            <td class="linha_assinatura" width="200">_______________________</td>
                          </tr>
                          <tr>
                            <td align="center">BILHETERIA</td>
                            <td align="center">LOCAL</td>
                            <td align="center">PRODUÇÃO</td>
                          </tr>
                        </table>
                      </td>
                      <td bgcolor="LightGrey" align="right" class="label" valign="top"><b>R$&nbsp;&nbsp;&nbsp;<?php echo number_format(($nTotalVendas - $nTotalDesp), 2, ",", "."); ?></b></td>
                    </tr>
                    <tr>
                      <td colspan="4" bgcolor="#FFFFFF" width="650"><font size=1 face="tahoma,verdana,arial">
                                                                                                                                            			    			O Borderô de vendas assinados pelas partes envolvidas, dará a plena  quitação dos valores pagos em dinheiro no momento do fechamento,  portanto, confira atentamente os valores recebidos em dinheiro, vales/recibos de saques e comprovantes de depósito.<br/>
                                                                                                                                            			    			Os valores vendidos através dos cartões de crédito e débito serão  repassados aos favorecidos de acordo com os prazos firmados  através do contrato prestação de serviços assinado pelas partes.</font>
                      </td>
                    </tr>
                  </table>
                  <br clear="all"/>

                  <table width="656" class="tabela" border="0" bgcolor="LightGrey">
                    <tr>
                      <td align="center" colspan="7"><font size=2 face="tahoma,verdana,arial"><b>3 - DETALHAMENTO POR FORMA DE PAGAMENTO<br/>(apenas para conferência de valores e quantidades)</b></font></td>
                    </tr>
                    <tr>
                      <td	align="left" width="200" class="titulogrid">Tipo de Forma de Pagamento</td>
                      <td	align="right" width="96" class="titulogrid">Qtde Transações</td>
                      <td	align="right" width="76" class="titulogrid">Valores Brutos</td>
                      <td	align="right" width="46" class="titulogrid">Taxa</td>
                      <td	align="right" width="76" class="titulogrid">Desconto Taxa</td>
                      <td	align="right" width="66" class="titulogrid">Repasses</td>
                      <td	align="right" width="96" class="titulogrid">Data do Repasse</td>
                    </tr>
<?php
                    $strSqlDet = "SP_REL_BORDERO" . (($CodSala == 'TODOS') ? '11' : '07') . " '" . $DataIni . "','" . $DataFim . "'," . $CodPeca . "," . $CodSala . ",'" . $HorSessao . "','" . $_SESSION["NomeBase"] . "'";
                    $queryDet = executeSQL($connGeral, $strSqlDet);
                    $paramsDet = array($DataIni, $DataFim, $CodPeca, $CodSala, $HorSessao, "'" . $_SESSION["NomeBase"] . "'");
                    if (sqlErrors ()) {
                      echo $strSqlDet . "<br/>";
                      print_r($paramsDet);
                      echo "Erro #004: <br/>";
                      die(print_r(sqlErrors()));
                    } else {
                      while ($pRSDetalhamento = fetchResult($queryDet)) {
?>
                        <tr>
                          <td	align=left  class=texto><?php echo utf8_encode($pRSDetalhamento["forpagto"]); ?></td>
                          <td	align=right class=texto><?php echo $pRSDetalhamento["qtdBilh"]; ?></td>
                          <td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSDetalhamento["totfat"], 2, ",", "."); ?></td>
                          <td	align=right class=texto>%&nbsp;<?php echo number_format($pRSDetalhamento["taxa"], 2, ",", "."); ?></td>
                          <td	align=right class=texto><?php echo number_format($pRSDetalhamento["descontos"], 2, ",", "."); ?></td>
                          <td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSDetalhamento["liquido"], 2, ",", "."); ?></td>
<?php
                        $dataRepasseTemp = explode("/", $DataFim2);
                        $dataRepasse = mktime(24 * $pRSDetalhamento["PrzRepasseDias"], 0, 0, $dataRepasseTemp["1"], $dataRepasseTemp["0"], $dataRepasseTemp["2"]) . "  " . $pRSDetalhamento["PrzRepasseDias"];
?>
                        <td	align=right class=texto><?php echo date("d/m/Y", $dataRepasse); ?></td>
                      </tr>
<?php
                        $nQt += $pRSDetalhamento["qtdBilh"];
                        $nBrutoTot += $pRSDetalhamento["totfat"];
                        $nTotDesc += $pRSDetalhamento["descontos"];
                        $nTotLiqu += $pRSDetalhamento["liquido"];
                      }
                    }
?>
                    <tr>
                      <td bgcolor="LightGrey" align="left" class="label"><b>TOTAL</b></td>
                      <td bgcolor="LightGrey" align="right" class="label"><b><?php echo $nQt; ?></b></td>
                      <td bgcolor="LightGrey" align="right" class="label"><b>R$&nbsp;&nbsp;<?php echo number_format($nBrutoTot, 2, ",", "."); ?></b></td>
                      <td bgcolor="LightGrey" align="right" class="label"><b></b></td>
                      <td bgcolor="LightGrey" align="right" class="label"><b><?php echo number_format($nTotDesc, 2, ",", "."); ?></b></td>
                      <td bgcolor="LightGrey" align="right" class="label"><b>R$&nbsp;<?php echo number_format($nTotLiqu, 2, ",", "."); ?></b></td>
                      <td bgcolor="LightGrey" align="right" class="label"><b></b></td>
                    </tr>
                  </table>
                  <br clear="all"/>
<?php
                    if ($_REQUEST['Small'] != '2') {

                      echo $table3;
?>
                      <table width=656 class="tabela" border="0" bgcolor="LightGrey">
                        <tr>
                          <td align="center" colspan="4"><font size=2 face="tahoma,verdana,arial"><b>4 - DETALHAMENTO POR CANAL DE VENDA</b></font></td>
                        </tr>
                        <tr>
                          <td	align="left" width="162" class="titulogrid">Canais de Venda</td>
                          <td	align="right" width="162" class="titulogrid">Qtde Transações</td>
                          <td	align="right" width="162" class="titulogrid">Total</td>
                          <td	align="right" width="163" class="titulogrid">% do Total de Transações</td>
                        </tr>
<?php
                      $strSqlDet = "SP_REL_BORDERO" . (($CodSala == 'TODOS') ? '12' : '09') . " '" . $DataIni . "','" . $DataFim . "'," . $CodPeca . "," . $CodSala . ",'" . $HorSessao . "','" . $_SESSION["NomeBase"] . "'";
                      $queryDet2 = executeSQL($connGeral, $strSqlDet);
                      $queryDet3 = executeSQL($connGeral, $strSqlDet);
                      $nQt = 0;
                      $nBrutoTot = 0;
                      $cont = 0;
                      if ($totPublico == 0) {
                        $totPublico = 1;
                      }

                      while ($pRSDet2 = fetchResult($queryDet3)) {
                        $totTransacoes += $pRSDet2["Quant"];
                      }

                      while ($pRSDet = fetchResult($queryDet2)) {
?>
                        <tr>
                          <td	align=left  class=texto><?php echo utf8_encode($pRSDet["Venda"]); ?></td>
                          <td	align=right  class=texto><?php echo $pRSDet["Quant"]; ?></td>
                          <td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSDet["Total"], 2, ",", "."); ?></td>
                          <td	align=right class=texto><?php echo number_format(($pRSDet["Quant"] / $totTransacoes) * 100, 2, ",", "."); ?>%</td>
                        </tr>
<?php
                        $nQt = $nQt + $pRSDet["Quant"];
                        $nBrutoTot = $nBrutoTot + $pRSDet["Total"];
                        $cont = $cont + number_format(($pRSDet["Quant"] / $totTransacoes ) * 100, 2);
                      }
?>
                      <tr>
                        <td bgcolor="LightGrey" align="left" class="label"><b>TOTAL DE VENDAS</b></td>
                        <td bgcolor="LightGrey" align="right" class="label"><b><?php echo $nQt; ?></b></td>
                        <td bgcolor="LightGrey" align="right" class="label"><b>R$&nbsp;&nbsp;<?php echo number_format($nBrutoTot, 2, ",", "."); ?></b></td>
                        <td bgcolor="LightGrey" align="right" class="label"><b><?php echo number_format($cont, 0); ?>%</b></td>
                      </tr>

                    </table>
                    <br clear="all"/>

<?php } ?>
                  <table width="656" border=0>
                    <tr>
                      <td align="middle">
                        <br/>
                        <input class="botao" type="button" value="Imprimir Relatório" name="cmdImprimi" onClick="javascript:window.print();"/>
                        <input class="botao" type="button" value="Fechar Janela" name="cmdFecha" onClick="javascript:window.close()"/>
                      </td>
                    </tr>
                  </table>
<?php
                  }
                }
?>
  </body>
</html>