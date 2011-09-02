<?php
require_once('../settings/settings.php');
require_once('../settings/functions.php');
session_start();

function str_replace_once($search, $replace, $subject) {
    if (($pos = strpos($subject, $search)) !== false) {
        $ret = substr($subject, 0, $pos) . $replace . substr($subject, $pos + strlen($search));
    } else {
        $ret = $subject;
    }
    return($ret);
}

if (isset($_POST['email']) and isset($_POST['senha'])) {
	$mainConnection = mainConnection();
	
	$query = 'SELECT ID_CLIENTE FROM MW_CLIENTE WHERE CD_EMAIL_LOGIN = ? AND CD_PASSWORD = ?';
	$params = array($_POST['email'], md5($_POST['senha']));
	
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	if ($rs['ID_CLIENTE']) {
		//setcookie('user', $rs['ID_CLIENTE'], $cookieExpireTime);
		$_SESSION['user'] = $rs['ID_CLIENTE'];

                if(isset($_GET['tag']) || isset($_POST['tag'])){
                    $url = (($_POST['from'] == 'cadastro') ? '&tag=3._Identificaçao_-_Cadastre-se-TAG' : '&tag=3._Identificaçao_-_Autentique-se-TAG');
                }else{
                    $url = '';
                }

		echo 'redirect.php?redirect=' . str_replace_once("&tag=2._Conferir_Itens_-_Avançar-TAG", "", $_GET['redirect']) . urlencode($url);
	} else {
		echo 'false';
	}
} else if (isset($_SESSION['operador']) and isset($_GET['id'])) {
	$_SESSION['user'] = $_GET['id'];
	echo 'true';
}
?>