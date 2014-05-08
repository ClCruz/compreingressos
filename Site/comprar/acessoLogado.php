<?php
require_once('../settings/settings.php');
require_once('../settings/functions.php');
session_start();

//ACESSO PERMITIDO APENAS PARA CLIENTES LOGADOS
if (isset($_SESSION['user'])) {
	setcookie('user', true, $cookieExpireTime);
} else {
	header("Location: login.php?redirect=" . urlencode(getCurrentUrl()));
}
?>