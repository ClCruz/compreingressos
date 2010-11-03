<?php
require_once('../settings/settings.php');
require_once('../settings/functions.php');
session_start();

if (isset($_POST['email']) and isset($_POST['senha'])) {
	$mainConnection = mainConnection();
	
	$query = 'SELECT ID_CLIENTE FROM MW_CLIENTE WHERE CD_EMAIL_LOGIN = ? AND CD_PASSWORD = ?';
	$params = array($_POST['email'], md5($_POST['senha']));
	
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	if ($rs['ID_CLIENTE']) {
		//setcookie('user', $rs['ID_CLIENTE'], $cookieExpireTime);
		$_SESSION['user'] = $rs['ID_CLIENTE'];
		
		echo 'redirect.php?redirect=' . $_GET['redirect'];
	} else {
		echo 'false';
	}
} else if (isset($_SESSION['operador']) and isset($_GET['id'])) {
	$_SESSION['user'] = $_GET['id'];
	echo 'true';
}
?>