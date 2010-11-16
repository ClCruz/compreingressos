<?php
function mainConnection() {
	$host = '11.11.11.218';
	$port = '1433';
	$dbname = 'CI_MIDDLEWAY';
	$user = 'tsp';
	$pass = 'tsp';
	
	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

function getConnection($teatroID) {
	$mainConnection = mainConnection();
	$rs = executeSQL($mainConnection, 'SELECT DS_NOME_BASE_SQL FROM MW_BASE WHERE ID_BASE = ?', array($teatroID), true);
	
	$host = '11.11.11.218';
	$port = '1433';
	$user = 'tsp';
	$pass = 'tsp';
	
	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $rs['DS_NOME_BASE_SQL']));
}

function getConnectionTsp(){
	$host = '11.11.11.218';
	$port = '1433';
	$dbname = 'tspweb';
	$user = 'tsp';
	$pass = 'tsp';
	
	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}
?>