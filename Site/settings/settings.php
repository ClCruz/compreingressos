<?php
$nomeSite = 'COMPREINGRESSOS.COM';
$title = $nomeSite;// . ' - Painel Administrativo';

$cookieExpireTime = time() + 60 * 20; //20min

$compraExpireTime = 150;//minutos

$maxIngressos = 10;//maximo por compra

$uploadPath = '../images/uploads/';

$is_teste = '1';
$isContagemAcessos = true;
$is_manutencao = false;

$merchant_id_homologacao = 'AEDAFDE0-83A5-869F-214B-C8501B9C8697';
$merchant_id_producao = 'BB478913-9023-931D-3EC9-D5DEA7DECD20';
$merchant_id_homologacao = $merchant_id_producao;

$url_braspag_homologacao = 'https://homologacao.pagador.com.br/webservice/pagadorTransaction.asmx?WSDL';
$url_braspag_producao = 'https://transaction.pagador.com.br/webservice/pagadorTransaction.asmx?WSDL';
$url_braspag_homologacao = $url_braspag_producao;

/** PRODUCAO ---------------------------------------------------
$recaptcha = array(
	'public_key' => '6LehdNkSAAAAAAk9orcTupYUPYngqXfn1Kdf6fDs',
	'private_key' => '6LehdNkSAAAAAPgQZU83DKb0L7Wu8RI4Bvy7oYZq'
);
// ----------------------------------------------------------- */

//** ENDERECO 201.48.139.201 -----------------------------------
$recaptcha = array(
	'private_key' => '6LefZdkSAAAAAGZcDHifNvtk54IzJNQ_mS0gGRZI',
	'public_key' => '6LefZdkSAAAAAPdM1TYpaRWNy4BWVN0o9HDoPRL2'
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