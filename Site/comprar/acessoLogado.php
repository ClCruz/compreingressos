<?php
require_once('../settings/settings.php');
session_start();

//ACESSO PERMITIDO APENAS PARA CLIENTES LOGADOS
if (isset($_SESSION['user'])) {
	setcookie('user', $_SESSION['user'], $cookieExpireTime);
} else {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	header("Location: login.php?redirect=" . urlencode($pageURL));
}
?>