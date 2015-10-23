<?php
error_reporting(0);
$nomeSite = 'COMPREINGRESSOS.COM';
$homeSite = 'http://www.compreingressos.com/';
$title = $nomeSite;// . ' - Painel Administrativo';

$locale = setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");

$cookieExpireTime = time() + 60 * 20; //20min

$compraExpireTime = 15;//minutos

$maxIngressos = 4;//maximo por compra

$uploadPath = '../images/uploads/';

// $is_teste = '1';
$isContagemAcessos = true;
$is_manutencao = true;

$merchant_id_homologacao = 'AEDAFDE0-83A5-869F-214B-C8501B9C8697';
// $merchant_id_producao = 'BB478913-9023-931D-3EC9-D5DEA7DECD20'; // conta 1
$merchant_id_producao = '77915E2A-EFE7-418E-9250-B99DA8227129'; // conta 2
// $merchant_id_homologacao = $merchant_id_producao;

$url_braspag_homologacao = 'https://homologacao.pagador.com.br/webservice/pagadorTransaction.asmx?WSDL';
$url_braspag_producao = 'https://transaction.pagador.com.br/webservice/pagadorTransaction.asmx?WSDL';
// $url_braspag_homologacao = $url_braspag_producao;

$recaptcha = array(
	'private_key' => 'bafa46035b840f10dc064ebae573bfd5c2959b78',
	'public_key' => '7b7a9872fd5c7e434400b347ae315973579c21cc'
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