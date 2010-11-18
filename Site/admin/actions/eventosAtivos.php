<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 19, true)) {

if ($_GET['action'] == 'update' and isset($_GET['codevento'])) { /*------------ UPDATE ------------*/
	$_POST["in_ativo"] = ($_POST["in_ativo"] == 'on') ? 1 : 0;
	
	$query = "UPDATE MW_APRESENTACAO SET IN_ATIVO = ? WHERE ID_APRESENTACAO = ?";
	$params = array($_POST['in_ativo'], $_POST['codevento']);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true?codevento='.$_GET['codevento'];
	} else {
		$retorno = sqlErrors();
	}
	
} 

if (is_array($retorno)) {
	echo $retorno[0]['message'];
} else {
	echo $retorno;
}

}
?>