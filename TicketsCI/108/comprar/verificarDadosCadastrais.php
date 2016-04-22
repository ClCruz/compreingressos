<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');
session_start();

$query = "SELECT COUNT(1) AS IN_ANTI_FRAUDE FROM MW_RESERVA R
            INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
            INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
            WHERE R.ID_SESSION = ? AND E.IN_ANTI_FRAUDE = 1";
$rs = executeSQL($mainConnection, $query, array(session_id()), true);

if ($rs['IN_ANTI_FRAUDE']) {

	$query = "SELECT NR_ENDERECO FROM MW_CLIENTE WHERE ID_CLIENTE = ?";
	$params = array($_SESSION['user']);
	$rs = executeSQL($mainConnection, $query, $params, true);

	if ($rs['NR_ENDERECO'] == NULL) {
		$redirect = 'minha_conta.php?atualizar_dados=1';
	}

	if (isset($_COOKIE['entrega']) and $_COOKIE['entrega'] != -1) {
		$query = "SELECT NR_ENDERECO FROM MW_ENDERECO_CLIENTE WHERE ID_ENDERECO_CLIENTE = ?";
		$params = array($_COOKIE['entrega']);
		$rs = executeSQL($mainConnection, $query, $params, true);

		if ($rs['NR_ENDERECO'] == NULL) {
			$redirect = ($redirect ? $redirect.'&' : 'minha_conta.php?').'atualizar_endereco='.$_COOKIE['entrega'];
		}
	}

	if ($redirect) {
		$redirect .= '&redirect=etapa5.php';

		header("Location: $redirect");
	}
}