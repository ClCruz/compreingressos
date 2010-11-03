<?php
session_start();

if (isset($_GET['id']) and is_numeric($_GET['id']) and isset($_SESSION['user'])) {
	require_once('../settings/functions.php');
	
	$mainConnection = mainConnection();
	
	$query = 'SELECT F.VL_TAXA_FRETE
					FROM MW_TAXA_FRETE F
					INNER JOIN MW_REGIAO_GEOGRAFICA R ON R.ID_REGIAO_GEOGRAFICA = F.ID_REGIAO_GEOGRAFICA
					INNER JOIN MW_ESTADO E ON E.ID_REGIAO_GEOGRAFICA = R.ID_REGIAO_GEOGRAFICA ';
	if ($_GET['id'] != -1) {
		$query .= 'INNER JOIN MW_ENDERECO_CLIENTE EC ON EC.ID_ESTADO = E.ID_ESTADO
					WHERE EC.ID_CLIENTE = ? AND EC.ID_ENDERECO_CLIENTE = ?';
		$params = array($_SESSION['user'], $_GET['id']);
	} else {
		$query .= 'INNER JOIN MW_CLIENTE C ON C.ID_ESTADO = E.ID_ESTADO
					WHERE C.ID_CLIENTE = ?';
		$params = array($_SESSION['user']);
	}
	$query .= ' AND F.DT_INICIO_VIGENCIA <= GETDATE()
					ORDER BY F.DT_INICIO_VIGENCIA DESC';
	
	if ($rs = executeSQL($mainConnection, $query, $params, true)) {
		echo str_replace('.', ',', $rs[0]);
	}
} else if (isset($_GET['id']) and is_numeric($_GET['id'])) {
	require_once('../settings/functions.php');
	
	$mainConnection = mainConnection();
	
	$query = 'SELECT F.VL_TAXA_FRETE
					FROM MW_TAXA_FRETE F
					INNER JOIN MW_REGIAO_GEOGRAFICA R ON R.ID_REGIAO_GEOGRAFICA = F.ID_REGIAO_GEOGRAFICA
					INNER JOIN MW_ESTADO E ON E.ID_REGIAO_GEOGRAFICA = R.ID_REGIAO_GEOGRAFICA
					WHERE E.ID_ESTADO = ? AND F.DT_INICIO_VIGENCIA <= GETDATE()
					ORDER BY F.DT_INICIO_VIGENCIA DESC';
	$params = array($_GET['id']);
	
	if ($rs = executeSQL($mainConnection, $query, $params, true)) {
		echo str_replace('.', ',', $rs[0]);
	}
}

?>