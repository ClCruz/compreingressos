<?php
function mainConnection() {
        $host = '192.168.91.17';
        $port = '1433';
        $dbname = 'CI_MIDDLEWAY';
        $user = 'web';
		$pass = '!ci@web@2018!';

        return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

function getConnection($teatroID) {
        $mainConnection = mainConnection();
        $rs = executeSQL($mainConnection, 'SELECT DS_NOME_BASE_SQL FROM MW_BASE WHERE ID_BASE = ?', array($teatroID), true);

        $host = '192.168.91.17';
        $port = '1433';
        $user = 'web';
        $pass = '!ci@web@2018!';

        return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $rs['DS_NOME_BASE_SQL']));
}

function getConnectionTsp() {
        $host = '192.168.91.17';
        $port = '1433';
        $dbname = 'tspweb';
        $user = 'web';
        $pass = '!ci@web@2018!';

        return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

function getConnectionDw() {
    $host = 'localhost\\sql2008';
        $port = '1433';
        $dbname = 'CI_DW';
        $user = 'sa';
        $pass = 'sa';

        return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

function getConnectionHome() {

	if ($_ENV['IS_TEST']) return false;
	
	/** Conexao Mysql Locaweb 
    $host = '186.202.34.139';
	$port = '3306';
	$dbname = 'compreingressos_development';
	$user = 'ccmenu';
	$pass = 'GQMfwbGLnyuQ2Wur';

	try {
		$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (Exception $e) {
		$conn = false;
	} **/

	$host = '10.0.37.5';
	$port = '3306';
	$dbname = 'compreingressos_production';
	$user = 'compreingressos';
	$pass = 'SNq3mhh5Tyb59J';

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