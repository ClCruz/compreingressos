<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 24, true)) {

if ($_GET['action'] == 'add') { /*------------ INSERT ------------*/
	
	$query = "INSERT INTO MW_EVENTO_PATROCINADO
					(ID_CARTAO_PATROCINADO, ID_BASE, CODPECA, DT_INICIO, DT_FIM)
					VALUES (?, ?, ?, CONVERT(DATETIME, ?, 103), CONVERT(DATETIME, ?, 103))";
	$params = array($_POST['idCartaoPatrocinado'], $_POST['teatro'], $_POST['codpeca'], $_POST['dtInicio'], $_POST['dtFim']);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true?idCartaoPatrocinado='.$_POST['idCartaoPatrocinado'].'&teatro='.$_POST['teatro'].'&codpeca='.$_POST['codpeca'];
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'update' and isset($_GET['idCartaoPatrocinado']) and isset($_GET['teatro']) and isset($_GET['codpeca'])) { /*------------ UPDATE ------------*/
	
	$query = "UPDATE MW_EVENTO_PATROCINADO
				 SET
				 ID_CARTAO_PATROCINADO = ?
				 ,ID_BASE = ?
				 ,CODPECA = ?
				 ,DT_INICIO = CONVERT(DATETIME, ?, 103)
				 ,DT_FIM = CONVERT(DATETIME, ?, 103)
				 WHERE ID_CARTAO_PATROCINADO = ? AND ID_BASE = ? AND CODPECA = ?";
	$params = array($_POST['idCartaoPatrocinado'], $_POST['teatro'], $_POST['codpeca'], $_POST['dtInicio'], $_POST['dtFim'],
					$_GET['idCartaoPatrocinado'], $_GET['teatro'], $_GET['codpeca']);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true?idCartaoPatrocinado='.$_POST['idCartaoPatrocinado'].'&teatro='.$_POST['teatro'].'&codpeca='.$_POST['codpeca'];
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'delete' and isset($_GET['idCartaoPatrocinado']) and isset($_GET['teatro']) and isset($_GET['codpeca'])) { /*------------ DELETE ------------*/
	
	$query = 'DELETE FROM MW_EVENTO_PATROCINADO WHERE ID_CARTAO_PATROCINADO = ? AND ID_BASE = ? AND CODPECA = ?';
	$params = array($_GET['idCartaoPatrocinado'], $_GET['teatro'], $_GET['codpeca']);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true';
	} else {
		$retorno = sqlErrors();
	}
	
}

if (is_array($retorno)) {
	if ($retorno[0]['code'] == 547) {
		echo utf8_encode('Não foi possível excluir!<br/><br/>Esse registro já está em uso.');
	} else {
		echo $retorno[0]['message'];
	}
} else {
	echo $retorno;
}

}
?>