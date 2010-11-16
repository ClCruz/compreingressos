<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();

// parametros para pesquisa dos dados
$Acao            = $_POST["Acao"];
$CodPeca         = $_POST["CodPeca"];
$DatApresentacao = "'" . $_POST["DatApresentacao"] . "'";
$Horario		 = "'" . $_POST["Horario"] . "'";

function mostraDataGeral($vData){
	return $vData->format("d/m/Y");
}

function mostraDataSimple($vData){
	return $vData->format("Ymd");
}

function buscarDatas(){
	$CodPeca = ($_REQUEST["CodPeca"] == "") ? "null" : $_REQUEST["CodPeca"];
	$gSQL =	"SP_PEC_CON009;2 'Emerson', ".$CodPeca;
	$conn = getConnection(13);
	$rsGeral = executeSQL($conn, $gSQL);
	if(!sqlErrors()){
		if(hasRows($rsGeral)){
			$html .= "<option value=\"\">Selecione...</option>";
			while($rs = fetchResult($rsGeral)){
				$html .= "<option value=\"". mostraDataSimple($rs["DatApresentacao"]) ."\">". mostraDataGeral($rs["DatApresentacao"]) ."</option>\n";
			}
			echo $html;
		}else{
			echo "Nenhum registro encontrado";	
		}
	}else{
		echo "<br>Erro #001:";
		print_r(sqlErrors());	
		echo "<br>".$gSQL;
	}	
}

function buscarHorarios(){
	$CodPeca = ($_REQUEST["CodPeca"] == "") ? "null" :  $_REQUEST["CodPeca"];
	$DatApresentacao = ($_REQUEST["DatApresentacao"] == "") ? "null" : $_REQUEST["DatApresentacao"];
	$gSQL = "SP_PEC_CON009;3 'Emerson', ". $CodPeca .", ". $DatApresentacao;
	$conn = getConnection(13);	
	$rsGeral = executeSQL($conn, $gSQL);
	if(!sqlErrors()){
		if(hasRows($rsGeral)){
			echo $gSQL;
			$html .= "<option value=\"\">Selecione...</option>\n";
			while($rs = fetchResult($rsGeral)){
				$html .= "<option value=\"". $rs["HorSessao"] ."\">". $rs["HorSessao"] ."</option>\n";
			}
			echo $html;
		}
	}else{
		print_r(sqlErrors());
		echo "<br>".$gSQL;	
	}
}

function buscarSala(){
	$CodPeca = ($_REQUEST["CodPeca"] == "") ?  "null" : $_REQUEST["CodPeca"];
	$DatApresentacao = ($_REQUEST["DatApresentacao"] == "") ? "null" : $_REQUEST["DatApresentacao"];
	$Horario = ($_REQUEST["Horario"] == "") ? "null" : $_REQUEST["Horario"];
	
	$gSQL = "SP_REL_BORDERO_VENDAS;7 '" . $DatApresentacao ."',". $CodPeca .",'". $Horario ."','CI_COLISEU'";
	$conn = getConnectionTsp();
	$rsGeral = executeSQL($conn, $gSQL);
	if(!sqlErrors()){
		if(hasRows($rsGeral)){
			$html .= "<option value=\"\">Selecione...</option>\n";
			while($rs = fetchResult($rsGeral)){
				$html .= "<option value=\"". $rs["codsala"] ."\">". utf8_encode($rs["nomSala"]) ."</option>\n";
			}
			echo $html;
		}
	}else{
		print_r(sqlErrors());
		echo "<br>".$gSQL;	
	}
}

if($_POST["NomeBase"] != "" && $_POST["Proc"] != "" && !isset($_REQUEST["Acao"])){
	$query = "EXEC ".$_POST['NomeBase']."".$_POST['Proc'] ." 'Emerson'";
	$result = executeSQL($mainConnection, $query);
	$html = "<select name=\"cboPeca\" id=\"cboPeca\" onchange=\"CarregaApresentacao()\">\n";
	while($rs = fetchResult($result)){
		$html .= "<option value=\"". $rs["CodPeca"] ."\">". utf8_encode($rs["nomPeca"]) ."</option>\n";	
	}
	$html .= '</select>';
	echo $html;
}

if(isset($_REQUEST["Acao"])){
	switch($_REQUEST["Acao"]){
		case 1:
			buscarDatas();
			break;
		case "2":
			buscarHorarios();
			break;
		case "3":
			buscarSala();
			break;
	}
}
?>