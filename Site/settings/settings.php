<?php
$nomeSite = 'COMPREINGRESSOS.COM';
$title = $nomeSite;// . ' - Painel Administrativo';

$cookieExpireTime = time() + 60 * 20; //20min

$compraExpireTime = 15;//minutos

$maxIngressos = 10;//maximo por compra

$uploadPath = '../images/uploads/';

if (isset($_REQUEST['var'])) {
	echo $$_REQUEST['var'];
}
?>