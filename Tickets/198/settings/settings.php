<?php
$nomeSite = 'COMPREINGRESSOS.COM';
$title = $nomeSite;// . ' - Painel Administrativo';

$cookieExpireTime = time() + 60 * 20; //20min

$compraExpireTime = 15;//minutos

$maxIngressos = 10;//maximo por compra

$uploadPath = '../images/uploads/';

$is_teste = '0';

$isContagemAcessos = true;

$url_braspag_homologacao = 'https://homologacao.pagador.com.br/webservice/pagadorTransaction.asmx?WSDL';
$url_braspag_producao = '';

if (isset($_REQUEST['var'])) {
	echo $$_REQUEST['var'];
}
?>