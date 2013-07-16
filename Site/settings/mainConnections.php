<?php
function mainConnection() {
	$host = 'localhost';
	$port = '1433';
	$dbname = 'CI_MIDDLEWAY';
	$user = 'tsp';
	$pass = 'tsp';
	
	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

function getConnection($teatroID) {
	$mainConnection = mainConnection();
	$rs = executeSQL($mainConnection, 'SELECT DS_NOME_BASE_SQL FROM MW_BASE WHERE ID_BASE = ?', array($teatroID), true);
	
	$host = 'localhost';
	$port = '1433';
	$user = 'tsp';
	$pass = 'tsp';
	
	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $rs['DS_NOME_BASE_SQL']));
}

function getConnectionTsp(){
	$host = 'localhost';
	$port = '1433';
	$dbname = 'tspweb';
	$user = 'tsp';
	$pass = 'tsp';
	
	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

function getConnectionDw(){
    $host = 'localhost';
	$port = '1433';
	$dbname = 'CI_DW';
	$user = 'sa';
	$pass = 'sa';

	return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

?>