<?php

require_once($_SERVER['DOCUMENT_ROOT']."/settings/multisite/unique.php");

error_reporting(0);
$nomeSite = multiSite_getName();
$homeSite = multiSite_getURI("URI_SSL");
$title = "";// . ' - Painel Administrativo';

$locale = setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");

if ( isset($_ENV['IS_TEST']) )
{
	$cookieExpireTime = time() + 60 * 120; //20min
	$compraExpireTime = 120;//minutos
}
else
{
	$cookieExpireTime = time() + 60 * 20; //20min
	$compraExpireTime = 15;//minutos
}

$uploadPath = '../images/uploads/';

$isContagemAcessos = true;
$is_manutencao = false;

if (getCurrentSite() == "ingressoslitoral") {
	$recaptcha = array(
		'private_key' => '6LcTvmIUAAAAAN5jtYXEYGw9JUN-uBl5jbBC0j2A',
		'public_key' => '6LcTvmIUAAAAANATrzg4N3ZHi1TzjBfPSVDMeEtn'
	);
	
	$recaptcha_cadastro = array(
		'private_key' => '6LcTvmIUAAAAAN5jtYXEYGw9JUN-uBl5jbBC0j2A',
		'public_key' => '6LcTvmIUAAAAANATrzg4N3ZHi1TzjBfPSVDMeEtn'
	);	
}
else {
	$recaptcha = array(
		'private_key' => '6LehdNkSAAAAAPgQZU83DKb0L7Wu8RI4Bvy7oYZq',
		'public_key' => '6LehdNkSAAAAAAk9orcTupYUPYngqXfn1Kdf6fDs'
	);
	
	$recaptcha_cadastro = array(
		'private_key' => '6LehdNkSAAAAAPgQZU83DKb0L7Wu8RI4Bvy7oYZq',
		'public_key' => '6LehdNkSAAAAAAk9orcTupYUPYngqXfn1Kdf6fDs'
	);
}


$mail_mkt = array(
	'login' => 'WScompreingr',
	'senha' => '13042015XY',
	'lista' => 'Clientes'
);

// para obter o caminho de upload do background na edicao de plateia
if (isset($_REQUEST['var'])) {
	echo $$_REQUEST['var'];
}
?>