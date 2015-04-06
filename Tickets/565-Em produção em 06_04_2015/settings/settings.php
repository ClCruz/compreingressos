<?php
error_reporting(0);
$nomeSite = 'COMPREINGRESSOS.COM';
$homeSite = 'http://www.compreingressos.com/';
$title = $nomeSite;// . ' - Painel Administrativo';

$locale = setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");

$cookieExpireTime = time() + 60 * 20; //20min

$compraExpireTime = 15;//minutos

$maxIngressos = 10;//maximo por compra

$uploadPath = '../images/uploads/';

// $is_teste = '1';
$isContagemAcessos = true;
$is_manutencao = false;

$merchant_id_homologacao = 'AEDAFDE0-83A5-869F-214B-C8501B9C8697';
$merchant_id_producao = 'BB478913-9023-931D-3EC9-D5DEA7DECD20';
// $merchant_id_homologacao = $merchant_id_producao;

$url_braspag_homologacao = 'https://homologacao.pagador.com.br/webservice/pagadorTransaction.asmx?WSDL';
$url_braspag_producao = 'https://transaction.pagador.com.br/webservice/pagadorTransaction.asmx?WSDL';
// $url_braspag_homologacao = $url_braspag_producao;

$proxy_homologacao = array(
	'host'	=>	'10.0.9.1',
	'port'	=>	8080
);
$proxy_producao = array(
	'host'	=>	'192.168.13.1',
	'port'	=>	8080
);
// $proxy_homologacao = $proxy_producao;

//** PRODUCAO ---------------------------------------------------
$recaptcha = array(
	'public_key' => '6LehdNkSAAAAAAk9orcTupYUPYngqXfn1Kdf6fDs',
	'private_key' => '6LehdNkSAAAAAPgQZU83DKb0L7Wu8RI4Bvy7oYZq'
);
// ----------------------------------------------------------- */

/** ENDERECO 186.237.201.132 -----------------------------------
$recaptcha = array(
	'private_key' => '6LdstukSAAAAAJ5P_ZVwxKvbnhnq7pmbwI1SAFId',
	'public_key' => '6LdstukSAAAAAM6ccRL9u4ngToheQ_56GVQQPQU9'
);
// ----------------------------------------------------------- */

$MailChimp = array(
	'api_key' => '60f379204b0c19ae5f88bf297ce73b4e-us4',
	'list_key' => 'e17c7d7d48'
);

// para obter o caminho de upload do background na edicao de plateia
if (isset($_REQUEST['var'])) {
	echo $$_REQUEST['var'];
}
?>