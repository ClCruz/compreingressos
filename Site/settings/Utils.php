<?php

/**
 * Conjunto de funções de uso geral.
 * @author Edicarlos Barbosa <edicarlosbarbosa@gmail.com>
 * @since 23-08-2011 14:00
 * @version 1.0.0
 * @license GNU GENERAL PUBLIC LICENSE
 */
function tratarData($data) {
  if ($data != "")
    $data = explode("/", $data);
  else
    $data = explode(date('d/m/Y'));
  return $data[2] . $data[1] . $data[0];
}

function formatarValor($value) {
  return (is_null($value)) ? '' : $value;
}

function formatarValorNumerico($value) {
  return (is_null($value)) ? '' : number_format($value, 2, ',', '.');
}

function gerarNotacaoIntervalo($arrayNums) {
  $str = '';
  $lastNum = '';

  foreach ($arrayNums as $i) {
    if ($lastNum === '') {
      $str .= $i;
    } else {
      if ($i === $lastNum + 1 && substr($str, -1) !== '-') {
        $str .= '-';
      } else if ($i !== $lastNum + 1 && substr($str, -1) === '-') {
        $str .= $lastNum . ', ' . $i;
      } else if ($i !== $lastNum + 1) {
        $str .= ', ' . $i;
      }
    }
    $lastNum = $i;
  }
  if (substr($str, -1) === '-')
    $str .= $lastNum;

  return $str;
}

function retornaData($Data) {
  if (!checkdate($Data))
    return "";
  else {
    $dia = $Data;
    $mes = $Data;
    $ano = $Data;
    return $ano . $mes . $dia;
  }
}

function textToDate($date) {
  $dia = substr($date, 6, 2);
  $mes = substr($date, 4, 2);
  $ano = substr($date, 0, 4);
  return $dia . "/" . $mes . "/" . $ano;
}

function DiaSemana($Data) {
  switch ($Data) {
    case 1:
      $DiaSemana = "SEGUNDA-FEIRA";
      break;
    case 2:
      $DiaSemana = "TERÇA-FEIRA";
      break;
    case 3:
      $DiaSemana = "QUARTA-FEIRA";
      break;
    case 4:
      $DiaSemana = "QUINTA-FEIRA";
      break;
    case 5:
      $DiaSemana = "SEXTA-FEIRA";
      break;
    case 6:
      $DiaSemana = "SÁBADO";
      break;
    case 7:
      $DiaSemana = "DOMINGO";
      break;
  }
  return $DiaSemana;
}

function formatarConteudoVazio($valor) {
  return empty($valor) ? '-' : $valor;
}

function search_value_presentation($apresentacoes, $date, $canal) {
  $resultado = array(0, 0);
  // $query .= "," . $canal;
  //$rs = executeSQL($conn, str_replace('SP_VEN_CON014', 'SP_VEN_CON014', $query), array());
  //while ($dados = fetchResult($rs)) {
  foreach($apresentacoes as $key => $apresentacao){
    //$dateDb = $dados["DATA_APRESENTACAO"] . $dados["HORSESSAO"];
    $dateDb = $apresentacao->data . $apresentacao->hora;
    if ((strcmp($dateDb, $date) == 0) && (strcmp($apresentacao->canal, $canal) == 0)) {
      $resultado[0] = $apresentacao->qtde;
      $resultado[1] = $apresentacao->valor;
    }
  }
  return $resultado;
}

/**
 * Verifica se o valor é diferente de vazio.
 * @param String $value
 * @return String
 */
function chk_value($value) {
  if ((isset($value)) && (!empty($value))) {
    return $value;
  } else {
    return "";
  }
}

/**
 * Verifica se o valor é diferente de vazio. <br/>
 * Porém retorna uma string "null" caso seja.
 * @param String $value
 * @return String
 */
function chk_null($value) {
  if ((isset($value)) && (!empty($value))) {
    return $value;
  } else {
    return "null";
  }
}

function arrayCopy(array $array) {
  $result = array();
  foreach ($array as $key => $val) {
    if (is_array($val)) {
      $result[$key] = arrayCopy($val);
    } elseif (is_object($val)) {
      $result[$key] = clone $val;
    } else {
      $result[$key] = $val;
    }
  }
  return $result;
}

?>
