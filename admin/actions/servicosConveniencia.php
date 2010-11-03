<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 6, true)) {

if (isset($_POST['valor'])) {
	$_POST['valor'] = str_replace(',', '.', $_POST['valor']);
	if (!is_numeric($_POST['valor'])) {
		echo 'Favor informar um valor válido para a taxa.';
		exit();
	}
}

if ($_GET['action'] == 'add') { /*------------ INSERT ------------*/
	
	$query = "INSERT INTO MW_TAXA_CONVENIENCIA
					(ID_EVENTO, DT_INICIO_VIGENCIA, VL_TAXA_CONVENIENCIA)
					VALUES (?, CONVERT(DATETIME, ?, 103), ?)";
	$params = array($_POST['idEvento'], $_POST['data'], $_POST['valor']);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$query = 'SELECT DS_EVENTO FROM MW_EVENTO WHERE ID_EVENTO = ?';
		$params = array($_POST['idEvento']);
		
		$rs = executeSQL($mainConnection, $query, $params, true);
		
		$retorno = 'true?idEvento='.$rs['DS_EVENTO'].'&data='.$_POST['data'];
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'update' and isset($_GET['idEvento']) and isset($_GET['data'])) { /*------------ UPDATE ------------*/
	
	$data = strtotime(str_replace('/', '-', $_GET['data']));
	$hoje = strtotime(date('d-m-Y'));
	
	if ($data >= $hoje) {
		$_GET['idEvento'] = utf8_decode($_GET['idEvento']);
		
		$query = "UPDATE T SET
						T.ID_EVENTO = ?,
						T.DT_INICIO_VIGENCIA = CONVERT(DATETIME, ?, 103),
						T.VL_TAXA_CONVENIENCIA = ?
					FROM
						MW_TAXA_CONVENIENCIA T
						INNER JOIN MW_EVENTO R ON R.ID_EVENTO = T.ID_EVENTO
					WHERE
						R.DS_EVENTO = ?
						AND T.DT_INICIO_VIGENCIA = CONVERT(DATETIME, ?, 103)";
		$params = array($_POST['idEvento'], $_POST['data'], $_POST['valor'], $_GET['idEvento'], $_GET['data']);
		
		if (executeSQL($mainConnection, $query, $params)) {
			$query = 'SELECT DS_EVENTO FROM MW_EVENTO WHERE ID_EVENTO = ?';
			$params = array($_POST['idEvento']);
			
			$rs = executeSQL($mainConnection, $query, $params, true);
			
			$retorno = 'true?idEvento='.utf8_encode($rs['DS_EVENTO']).'&data='.$_POST['data'];
		} else {
			$retorno = sqlErrors();
		}
	} else {
		$retorno = 'Este registro ainda está em uso!';
	}
	
} else if ($_GET['action'] == 'delete' and isset($_GET['idEvento']) and isset($_GET['data'])) { /*------------ DELETE ------------*/
	
	$data = strtotime(str_replace('/', '-', $_GET['data']));
	$hoje = strtotime(date('d-m-Y'));
	
	if ($data >= $hoje) {
		$query = 'DELETE T FROM MW_TAXA_CONVENIENCIA T INNER JOIN MW_EVENTO R ON R.ID_EVENTO = T.ID_EVENTO WHERE
						R.DS_EVENTO = ? AND T.DT_INICIO_VIGENCIA = CONVERT(DATETIME, ?, 103)';
		$params = array($_GET['idEvento'], $_GET['data']);
		
		if (executeSQL($mainConnection, $query, $params)) {
			$retorno = 'true';
		} else {
			$retorno = sqlErrors();
		}
	} else {
		$retorno = 'Este registro ainda está em uso!';
	}
	
}

if (is_array($retorno)) {
	if ($retorno[0]['code'] == 2627) {
		echo 'Já existe um registro cadastrado com essas informações.';
	} else {
		echo $retorno[0]['message'];
	}
} else {
	echo $retorno;
}

}
?>