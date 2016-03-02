<?php

echo_header();

if (isset($_GET['RESPAG'])) {

	$mainConnection = mainConnection();

	$query = "SELECT M.CD_MEIO_PAGAMENTO FROM MW_PEDIDO_VENDA P INNER JOIN MW_MEIO_PAGAMENTO M ON M.ID_MEIO_PAGAMENTO = P.ID_MEIO_PAGAMENTO WHERE P.ID_PEDIDO_VENDA = ?";
	$rs = executeSQL($mainConnection, $query, array($_GET['pedido']), true);

	// se for cartao de credito ou debito usar o estorno do pos
	if (in_array($rs['CD_MEIO_PAGAMENTO'], array(888, 889))) {
		if ($_GET['RESPAG'] == 'APROVADO') {
			executeSQL($mainConnection, "UPDATE MW_PEDIDO_VENDA SET DS_ESTORNO_POS = ? WHERE ID_PEDIDO_VENDA = ?", array(json_encode($_GET), $_GET['pedido']));
			
			echo "<GET TYPE=HIDDEN NAME=reset VALUE=1>";

			echo "<CONSOLE><BR> Finalizado!</CONSOLE>";
		} else {
			echo "<PAGAMENTO IPTEF=$ip_tef PORTATEF=$porta_tef CODLOJA=00000000 IDTERM=ID000001 TIPO=ESTORNO VALOR= PAGRET=RESPAG BIN=BINCARTAO NINST=NOMEINST NSU=NSUAUT AUT=CAUT NPAR=PARC MODPAG=TIPOTRANS>";
			echo "<GET TYPE=HIDDEN NAME=pedido VALUE={$_GET['pedido']}>";
		}
	} else {
		echo "<CONSOLE><BR> Finalizado!</CONSOLE>";
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