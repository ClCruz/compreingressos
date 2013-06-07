<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 260, true)) {

$_POST['in_ativo'] = $_POST['in_ativo'] == 'on' ? 1 : 0;

if ($_GET['action'] == 'update' and isset($_GET['idMeioPagamento'])) { /*------------ UPDATE ------------*/
	
	$query = "UPDATE MW_MEIO_PAGAMENTO SET IN_ATIVO = ? WHERE ID_MEIO_PAGAMENTO = ?";
	$params = array($_POST['in_ativo'], $_GET['idMeioPagamento']);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true?idMeioPagamento='.$_POST['idMeioPagamento'];
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