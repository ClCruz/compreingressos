<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 280, true)) {

if ($_GET['action'] == 'add') { /*------------ INSERT ------------*/
	
	$query = "INSERT INTO MW_RESPONS_TEATRO (ID_USUARIO, ID_BASE) VALUES (?, ?)";
	$params = array($_POST['codUsuario'], $_POST['idBase']);
	
} else if ($_GET['action'] == 'update' and isset($_GET['codUsuario']) and isset($_GET['idBase'])) { /*------------ UPDATE ------------*/
	
	$query = "UPDATE MW_RESPONS_TEATRO SET
			  ID_USUARIO = ?
			  ,ID_BASE = ?
			  WHERE ID_USUARIO = ? AND ID_BASE = ?";
	$params = array($_POST['codUsuario'], $_POST['idBase'], $_GET['codUsuario'], $_GET['idBase']);
	
} else if ($_GET['action'] == 'delete' and isset($_GET['codUsuario']) and isset($_GET['idBase'])) { /*------------ DELETE ------------*/
	
	$query = 'DELETE FROM MW_RESPONS_TEATRO WHERE ID_USUARIO = ? AND ID_BASE = ?';
	$params = array($_GET['codUsuario'], $_GET['idBase']);
	
}
	
if (executeSQL($mainConnection, $query, $params)) {
	$retorno = true;

    $log = new Log($_SESSION['admin']);
    $log->__set('funcionalidade', 'Usuário Responsável pelo Teatro');
    $log->__set('parametros', $params);
    $log->__set('log', $query);
    $log->save($mainConnection);
} else {
	$retorno = sqlErrors();
}

if ($retorno === true) {
	if ($_GET['action'] == 'add' or $_GET['action'] == 'update') {
		$query = "INSERT INTO MW_ACESSO_CONCEDIDO (ID_USUARIO, ID_BASE, CODPECA)
				  SELECT R.ID_USUARIO, E.ID_BASE, E.CODPECA
				  FROM MW_EVENTO E
				  INNER JOIN MW_RESPONS_TEATRO R ON E.ID_BASE = R.ID_BASE
				  WHERE NOT EXISTS (SELECT * FROM MW_ACESSO_CONCEDIDO A WHERE A.ID_USUARIO = R.ID_USUARIO AND A.ID_BASE = E.ID_BASE AND A.CODPECA = E.CODPECA)
				  AND R.ID_USUARIO = ? AND R.ID_BASE = ?";
		$params = array($_POST['codUsuario'], $_POST['idBase']);
		
		if (executeSQL($mainConnection, $query, $params)) {
		    $log = new Log($_SESSION['admin']);
		    $log->__set('funcionalidade', 'Usuário Responsável pelo Teatro');
		    $log->__set('parametros', $params);
		    $log->__set('log', $query);
		    $log->save($mainConnection);

			$retorno = 'true?codUsuario='.$_POST['codUsuario'].'&idBase='.$_POST['idBase'];
		} else {
			$retorno = sqlErrors();
		}
	} else if ($_GET['action'] == 'delete') {
		$query = "DELETE FROM MW_ACESSO_CONCEDIDO WHERE ID_USUARIO = ? AND ID_BASE = ?";
		$params = array($_GET['codUsuario'], $_GET['idBase']);
		
		if (executeSQL($mainConnection, $query, $params)) {
		    $log = new Log($_SESSION['admin']);
		    $log->__set('funcionalidade', 'Usuário Responsável pelo Teatro');
		    $log->__set('parametros', $params);
		    $log->__set('log', $query);
		    $log->save($mainConnection);
		    
			$retorno = 'true';
		} else {
			$retorno = sqlErrors();
		}
	}
}

if (is_array($retorno)) {
	if ($retorno[0]['code'] == 2627) {
		echo 'Usuário/teatro já cadastrado.';
	} else {
		echo $retorno[0]['message'];
	}
} else {
	echo $retorno;
}

}
?>