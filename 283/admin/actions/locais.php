<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 3, true)) {

if ($_GET['action'] != 'delete') {
	$_POST['ativo'] = $_POST['ativo'] == 'on' ? 1 : 0;
}

if ($_GET['action'] == 'add') { /*------------ INSERT ------------*/
	
	$query = 'SELECT 1 FROM MW_BASE WHERE DS_NOME_TEATRO = ? OR DS_NOME_BASE_SQL = ?';
	$params = array($_POST['nome'], $_POST['nomeSql']);
	$result = executeSQL($mainConnection, $query, $params);
	if (hasRows($result)) {
		echo 'Já existe um registro cadastrado com esse nome/nome de base.';
		exit();
	}
	
	$query = "INSERT INTO MW_BASE
					(DS_NOME_BASE_SQL, DS_NOME_TEATRO, IN_ATIVO)
					VALUES (?, ?, ?)";
	$params = array($_POST['nomeSql'], $_POST['nome'], $_POST['ativo']);

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Locais');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$query = 'SELECT ID_BASE FROM MW_BASE WHERE DS_NOME_TEATRO = ?';
		$params = array($_POST['nome']);
		
		$rs = executeSQL($mainConnection, $query, $params, true);
		
		$retorno = 'true?id='.$rs['ID_BASE'];
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'update' and isset($_GET['id'])) { /*------------ UPDATE ------------*/
	
	$query = "UPDATE MW_BASE SET
					DS_NOME_BASE_SQL = ?,
					DS_NOME_TEATRO = ?,
					IN_ATIVO = ?
				WHERE
					ID_BASE = ?";
	$params = array($_POST['nomeSql'], $_POST['nome'], $_POST['ativo'], $_GET['id']);

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Locais');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true?id='.$_GET['id'];
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'delete' and isset($_GET['id'])) { /*------------ DELETE ------------*/
	
	$query = 'DELETE FROM MW_BASE WHERE ID_BASE = ?';
	$params = array($_GET['id']);

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Locais');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true';
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