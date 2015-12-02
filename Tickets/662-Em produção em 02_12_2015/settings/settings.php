<?php
error_reporting(0);
$nomeSite = 'COMPREINGRESSOS.COM';
$homeSite = 'http://www.compreingressos.com/';
$title = $nomeSite;// . ' - Painel Administrativo';

$locale = setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");

$cookieExpireTime = time() + 60 * 20; //20min

$compraExpireTime = 15;//minutos

$maxIngressos = 16;//maximo por compra

$uploadPath = '../images/uploads/';

// $is_teste = '1';
$isContagemAcessos = true;
$is_manutencao = true;

$recaptcha = array(
	'private_key' => 'bafa46035b840f10dc064ebae573bfd5c2959b78',
	'public_key' => '7b7a9872fd5c7e434400b347ae315973579c21cc'
);

$recaptcha_cadastro = array(
	'private_key' => '2ad59bf7ce630c07e311f04a95b5af57030eff39',
	'public_key' => 'd4850db05abab66f0b86be092ad11c842883db9d'
);

$MailChimp = array(
	'api_key' => '60f379204b0c19ae5f88bf297ce73b4e-us4',
	'list_key' => 'e17c7d7d48'
);

// para obter o caminho de upload do background na edicao de plateia
if (isset($_REQUEST['var'])) {
	echo $$_REQUEST['var'];
}
?>