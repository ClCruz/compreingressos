<?php
error_reporting(0);
$nomeSite = 'COMPREINGRESSOS.COM';
$homeSite = 'http://www.compreingressos.com/';
$title = $nomeSite;// . ' - Painel Administrativo';

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

$recaptcha = array(
	'private_key' => 'bafa46035b840f10dc064ebae573bfd5c2959b78',
	'public_key' => '7b7a9872fd5c7e434400b347ae315973579c21cc'
);

$recaptcha_cadastro = array(
	'private_key' => '2ad59bf7ce630c07e311f04a95b5af57030eff39',
	'public_key' => 'd4850db05abab66f0b86be092ad11c842883db9d'
);

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