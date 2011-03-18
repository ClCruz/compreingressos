<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 9, true)) {

if ($_GET['action'] != 'delete') {
	$_POST['admin'] = $_POST['admin'] == 'on' ? 1 : 0;
	$_POST['ativo'] = $_POST['ativo'] == 'on' ? 1 : 0;
}

if ($_GET['action'] == 'add') { /*------------ INSERT ------------*/
	
	$query = 'SELECT 1 FROM MW_USUARIO_ITAU WHERE CD_LOGIN = ?';
	$params = array($_POST['login']);
	$result = executeSQL($mainConnection, $query, $params);
	if (hasRows($result)) {
		echo 'Já existe um usuário cadastrado com esse login.';
		exit();
	}
	
	$query = "INSERT INTO MW_USUARIO_ITAU
					(CD_LOGIN, DS_NOME, DS_EMAIL, IN_ATIVO, IN_ADMIN, CD_PWW)
					VALUES (?, ?, ?, ?, ?, '". md5('123456') . "')";
	$params = array($_POST['login'], $_POST['nome'], $_POST['email'], $_POST['ativo'], $_POST['admin']);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$query = 'SELECT ID_USUARIO FROM MW_USUARIO_ITAU WHERE CD_LOGIN = ?';
		$params = array($_POST['login']);
		
		$rs = executeSQL($mainConnection, $query, $params, true);
		
		$retorno = 'true?codusuario='.$rs['ID_USUARIO'];
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'update' and isset($_GET['codusuario'])) { /*------------ UPDATE ------------*/
	
	$query = "UPDATE MW_USUARIO_ITAU SET
					DS_NOME = ?,
					DS_EMAIL = ?,
					IN_ATIVO = ?,
					IN_ADMIN = ?
				WHERE
					ID_USUARIO = ?";
	$params = array($_POST['nome'], $_POST['email'], $_POST['ativo'], $_POST['admin'], $_GET['codusuario']);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true?codusuario='.$_GET['codusuario'];
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'delete' and isset($_GET['codusuario'])) { /*------------ DELETE ------------*/
	
	$query = 'DELETE FROM MW_USUARIO_ITAU WHERE ID_USUARIO = ?';
	$params = array($_GET['codusuario']);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true';
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'reset' and isset($_GET['codusuario'])) { /*------------ RESET PWW ------------*/
	
	$query = "UPDATE MW_USUARIO_ITAU SET
					CD_PWW = '". md5('123456') . "'
				WHERE
					ID_USUARIO = ?";
	$params = array($_GET['codusuario']);
	
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