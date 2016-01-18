<?php
if (isset($_GET['user']) && isset($_GET['password'])) {

	if ($_GET['password'] == '123456') {

		display_error("Sua senha não foi trocada. Acesse o site e informe uma nova senha numérica.");

	} else {

		$mainConnection = mainConnection();

		$rs = executeSQL($mainConnection, "SELECT ID_USUARIO, DS_NOME FROM MW_USUARIO WHERE ((CD_LOGIN = ? OR ID_USUARIO = ?) AND CD_PWW = ?) AND IN_ATIVO = 1 AND IN_POS = 1", array($_GET['user'], $_GET['user'], md5($_GET['password'])), true);

		if ($rs['ID_USUARIO']) {			
			$_SESSION['pos_user']['id'] = $rs['ID_USUARIO'];
			$_SESSION['pos_user']['name'] = $rs['DS_NOME'];

			executeSQL($mainConnection, "UPDATE MW_POS SET LAST_ACCESS = GETDATE() WHERE SERIAL = ?", array($_SESSION['pos_user']['serial']));

			// para permissao do uso de estorno
			$_SESSION['admin'] = $rs['ID_USUARIO'];

		} else {
			display_error("Usuário/senha inválidos!");
		}
	}
}

if (!isset($_SESSION['pos_user']['id'])) {

	echo "<INIT KEEP_COOKIES=1>";

	echo_header(false);

	echo utf8_decode("<WRITE_AT LINE=12 COLUMN=0> Informe o usuário:</WRITE_AT>");

	echo "<GET TYPE=FIELD ALPHA=1 NAME=user SIZE=18 COL=1 LIN=15>";

	echo_header(false);

	echo utf8_decode(unblock_words("<WRITE_AT LINE=12 COLUMN=0> Informe a senha:</WRITE_AT>"));

	echo "<GET TYPE=PASS ALPHA=1 NAME=password SIZE=18 COL=1 LIN=15>";

	echo_header(false);
	echo "<WRITE_AT LINE=9 COLUMN=0>          Aguarde...</WRITE_AT>";

	echo "<POST>";

	die();
}