<?php
function mainConnection() {
	$host = '192.168.13.2';
	$port = '1433';
	$dbname = 'CI_MIDDLEWAY';
	$user = 'mw_user';
	$pass = 'cc2010@@xyz';
	
	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

function getConnection($teatroID) {
	$mainConnection = mainConnection();
	$rs = executeSQL($mainConnection, 'SELECT DS_NOME_BASE_SQL FROM MW_BASE WHERE ID_BASE = ?', array($teatroID), true);
	
	$host = '192.168.13.2';
	$port = '1433';
	$user = 'tsp';
	$pass = 'tsp';
	
	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $rs['DS_NOME_BASE_SQL']));
}

function getConnectionTsp(){
	$host = '192.168.13.2';
	$port = '1433';
	$dbname = 'tspweb';
	$user = 'tsp';
	$pass = 'tsp';
	
	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

function getConnectionDw(){
    $host = '192.168.13.3';
	$port = '1433';
	$dbname = 'CI_DW';
	$user = 'mstr';
	$pass = 'mstr';

	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

function getConnectionHome() {
    $host = '177.153.8.59';
	$port = '3306';
	$dbname = 'compreingressos_production';
	$user = 'ccmenu';
	$pass = 'GQMfwbGLnyuQ2Wur';

	try {
		$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (Exception $e) {
		$conn = false;
	}

	return $conn;
}
?>