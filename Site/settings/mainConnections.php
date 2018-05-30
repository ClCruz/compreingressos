<?php
function mainConnection() {
        $host = '192.168.91.17';
        $port = '1433';
        $dbname = 'CI_MIDDLEWAY';
        $user = 'dev';
        $pass = '!ci@dev@2018!';
        // $pass = 'dev';

        return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $dbname));
}

function getConnection($teatroID) {
        $mainConnection = mainConnection();
        $rs = executeSQL($mainConnection, 'SELECT DS_NOME_BASE_SQL FROM MW_BASE WHERE ID_BASE = ?', array($teatroID), true);

        $host = '192.168.91.17';
        $port = '1433';
        $user = 'dev';
        $pass = '!ci@dev@2018!';
        // $pass = 'dev';

        return sqlsrv_connect($host.','.$port, array("UID" => $user, "PWD" => $pass, "Database" => $rs['DS_NOME_BASE_SQL']));
}

function getConnectionTsp() {
        $host = '192.168.91.17';
        $port = '1433';
        $dbname = 'tspweb';
        $user = 'dev';
        $pass = '!ci@dev@2018!';
        // $pass = 'dev';

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

	$host = '192.168.91.15';
	$port = '3307';
	$dbname = 'compreingressos_production';
	$user = 'php';
	$pass = 'SNq3mhh5Tyb59J';

	try {
		$conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (Exception $e) {
		$conn = false;
	}

	return $conn;
}
?>