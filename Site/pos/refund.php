<?php

echo_header();

if (isset($_GET['RESPAG'])) {

	if ($_GET['RESPAG'] == 'APROVADO') {
		$mainConnection = mainConnection();

		executeSQL($mainConnection, "UPDATE MW_PEDIDO_VENDA SET DS_ESTORNO_POS = ? WHERE ID_PEDIDO_VENDA = ?", array(json_encode($_GET), $_GET['pedido']));
		
		echo "<GET TYPE=HIDDEN NAME=reset VALUE=1>";
	} else {
		echo "<PAGAMENTO IPTEF=184.172.45.130 PORTATEF=4096 CODLOJA=00000000 IDTERM=ID000001 TIPO=ESTORNO VALOR= PAGRET=RESPAG BIN=BINCARTAO NINST=NOMEINST NSU=NSUAUT AUT=CAUT NPAR=PARC MODPAG=TIPOTRANS>";
		echo "<GET TYPE=HIDDEN NAME=pedido VALUE={$_GET['pedido']}>";
	}
	echo "<POST>";

} else {

	if (isset($_GET['pedido'])) {
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		$strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';

		$post_data = http_build_query(array('pedido' => $_GET['pedido'], 'justificativa' => 'Estorno pela máquina POS'));
		$url = 'http'.($_SERVER["HTTPS"] == "on" ? 's' : '').'://'.$_SERVER['SERVER_NAME'].($is_teste === '1' ? '/compreingressos2' : '').'/admin/estorno.php';

		session_write_close();

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_COOKIE, $strCookie);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch); 
		curl_close($ch);

		session_start();

		if ($response == 'ok') {
			echo "<GET TYPE=HIDDEN NAME=RESPAG VALUE=ESTORNO>";
			echo "<GET TYPE=HIDDEN NAME=pedido VALUE={$_GET['pedido']}>";
		} else {
			display_error($response);
		}

		echo "<POST>";

	} else {
		
		echo utf8_decode("<WRITE_AT LINE=5 COLUMN=0> Informe o Nº do Pedido:</WRITE_AT>");

		echo "<GET TYPE=FIELD NAME=pedido SIZE=10 COL=2 LIN=9>";

		echo "<POST>";
	}
}