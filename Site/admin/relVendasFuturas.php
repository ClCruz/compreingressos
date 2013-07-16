<?php

require_once("../settings/functions.php");
require_once('../settings/Template.class.php');
require_once('../settings/Utils.php');

$tpl = new Template("relVendasFuturas.html");

session_start();

$connGeral = getConnectionTsp();
$conn = getConnection($_SESSION["IdBase"]);

// Variaveis passadas por parametro pela url
$codApresentacao = $_GET["CodApresentacao"];
$codPeca = (isset($_GET["CodPeca"]) && !empty($_GET["CodPeca"])) ? $_GET["CodPeca"] : "";
$codSala = (isset($_GET["Sala"]) && !empty($_GET["Sala"])) ? $_GET["Sala"] : "";
$dataIni = (isset($_GET["DataIni"]) && !empty($_GET["DataIni"])) ? $_GET["DataIni"] : "null";
$dataFim = (isset($_GET["DataFim"]) && !empty($_GET["DataFim"])) ? $_GET["DataFim"] : "null";
$horSessao = (isset($_GET["HorSessao"]) && !empty($_GET["HorSessao"])) ? $_GET["HorSessao"] : "null";
$resumido = $_GET["Resumido"];

if (isset($_GET["imagem"]) && $_GET["imagem"] == "logo") {
  $strSql = "SP_REL_BORDERO_VENDAS;2 ?, ?, ?";
  $pRSBordero = executeSQL($connGeral, $strSql, array($codPeca, $codApresentacao, "'" . $_SESSION["NomeBase"] . "'"));
  if (sqlErrors ())
    $err = "Erro #001 " . print_r(sqlErrors());
}

// Monta e executa query principal do relatÃ³rio
$params = array('Emerson', $codPeca, $codSala, tratarData($dataIni), tratarData($dataFim), '--', $_SESSION["NomeBase"]);
$strGeral = "SP_REL_BORDERO_VENDAS;";
if ($codSala == 'TODOS') {
  $strGeral .= "10 'Emerson'," . $codPeca . ",'" . $codSala . "','" . tratarData($dataIni) . "','" . tratarData($dataFim) . "','--','" . $_SESSION["NomeBase"] . "'";
} else {
  //$strGeral .= "1 ?, ?, ?, ?, ?, ?, ?";
  $strGeral .= "10 'Emerson'," . $codPeca . ",'" . $codSala . "','" . tratarData($dataIni) . "','" . tratarData($dataFim) . "','--','" . $_SESSION["NomeBase"] . "'";
}
//$strGeral = "SP_REL_BORDERO_VENDAS;" . (($codSala == 'TODOS') ? '10' : '1') . " 'Emerson', " . $codPeca . "," . $codSala . "," . $dataIni . "," . $dataFim . ",'" . (($_GET['Small'] == '1') ? '--' : $horSessao) . "','" . $_SESSION["NomeBase"] . "'";

$pRSGeral = executeSQL($connGeral, $strGeral, array(), true);

/**
  if(!hasRows($pRSGeral)){
  print($strGeral);
  }* */
if (sqlErrors ()) {
  $err = "Erro #002 <br>" . var_dump($params) . "<br>" . $strGeral . "<br>";
}

$array = explode(":", $pRSGeral["NomResPeca"]);
$PPArray = ($array[0] != "") ? $array[0] : "N&atilde;o Cadastrado";
$SPArray = ($array[1] != "") ? $array[1] : "N&atilde;o Cadastrado";
$TPArray = ($array[2] != "") ? $array[2] : "N&atilde;o Cadastrado";

if (isset($err) && $err != "") {
  echo $err . "<br>";
  //print_r(sqlErrors());
}


$strBordero = "SP_REL_BORDERO_VENDAS;14 'Emerson', " . $codPeca . "," . $codSala . ",'" . tratarData($dataIni) . "','" . tratarData($dataFim) . "','--','" . $_SESSION["NomeBase"] . "'";
$resultBordero = executeSQL($connGeral, $strBordero, array());

if (hasRows($resultBordero)) {
  $numsArray = array();
  while ($rsBordero = fetchResult($resultBordero)) {
    $numsArray[] = $rsBordero['NumBordero'];
  }
  $pRSGeral['NumBordero'] = gerarNotacaoIntervalo($numsArray);
} else {
  echo $strBordero;
}


if (isset($err) && $err != "") {
  echo $err . "<br>";
  print_r(sqlErrors());
}

$tpl->evento = utf8_encode($pRSGeral["NomPeca"]);
$tpl->numBordero = $pRSGeral["NumBordero"];
$tpl->responsavel = utf8_encode($PPArray);
$tpl->cpfCnpj = $SPArray;

$tpl->dtInicio = $dataIni;
$tpl->dtFim = $dataFim;

$tpl->endereco = utf8_encode($TPArray);

// Obtem o nome da sala
if($codSala != "TODOS"){
  $querySala = "SELECT NOMSALA FROM TABSALA WHERE CODSALA = ?";
  $rsSala = executeSQL($conn, $querySala, array($codSala), true);
  $nome_sala = $rsSala["NOMSALA"];
}else{
  $nome_sala = "TODOS";
}

$tpl->local = utf8_encode($nome_sala);
$tpl->lugares = $pRSGeral["Lugares"];

// Obtem os dados dos canais de vendas
$codSala = ($codSala == "TODOS") ? 'NULL' : $codSala;
$query = "SP_VEN_CON014 ";
$query .= $codSala . "," . $codPeca . ",'" . tratarData($dataIni) . " 00:01:00','" . tratarData($dataFim) . " 23:59:00'";
$result = executeSQL($conn, $query, array());

$canais = array();
while ($apresentacao = fetchResult($result)) {
  $canais[] = $apresentacao["CANAL_VENDA"];
}

$canais = array_unique($canais);
sort($canais);
foreach ($canais as $key => $value) {
  $tpl->canal = utf8_encode($value);
  $tpl->parseBlock("BLOCK_CANAL", true);
  $tpl->parseBlock("BLOCK_HEADER_CANAL", true);
}

$resultApre = executeSQL($conn, $query, array());
while ($total = fetchResult($resultApre)) {
  $tpl->data = $total["DATA_APRESENTACAO"];
  $tpl->hora = $total["HORSESSAO"];

  $total_qtde = 0;
  $total_valor = 0;
  foreach ($canais as $key => $value) {
    if (strcmp($value, $total["CANAL_VENDA"]) == 0) {
      $tpl->qtde = $total["QTDE"];
      $tpl->valor = formatarValorNumerico($total["PAGTO"]);
      $tpl->parseBlock("BLOCK_ITENS", true);

      $total_qtde += $total["QTDE"];
      $total_valor += $total["PAGTO"];
    } else {
      $tpl->qtde = 0;
      $tpl->valor = "-";
      $tpl->parseBlock("BLOCK_ITENS", true);
    }
  }

  $tpl->total_qtde = $total_qtde;
  $tpl->total_valor = formatarValorNumerico($total_valor);
  $tpl->parseBlock("BLOCK_TOTAL", true);
  $tpl->parseBlock("BLOCK_APRESENTACAO", true);
}

$query = "SP_VEN_CON015 ";
$query .= $codSala . "," . $codPeca . ",'" . tratarData($dataIni) . " 00:01:00','" . tratarData($dataFim) . " 23:59:00'";
$resultTotalGeral = executeSQL($conn, $query, array());
$total_qtde_geral = 0;
$total_valor_geral = 0;
while ($total = fetchResult($resultTotalGeral)) {
  $total_qtde_geral += $total["QTDE"];
  $total_valor_geral += $total["PAGTO"];
  $tpl->total_qtde_geral = $total["QTDE"];
  $tpl->total_valor_geral = formatarValorNumerico($total["PAGTO"]);
  $tpl->parseBlock("BLOCK_TOTAL_GERAL", true);
}
$tpl->total_qtde_geral_final = $total_qtde_geral;
$tpl->total_valor_geral_final = formatarValorNumerico($total_valor_geral);

$tpl->show();
?>