<h1>converter imagens</h1>
<?php
ini_set('max_execution_time', 600);//600 seconds = 10 minutes

require_once('functions.php');

$mainConnection = mainConnection();

$result = executeSQL($mainConnection, 'SELECT ID_BASE, DS_NOME_BASE_SQL FROM MW_BASE');

while ($rs = fetchResult($result)) {

	echo '<h2>'.$rs['DS_NOME_BASE_SQL'].'</h2>';

	$conn = getConnection($rs['ID_BASE']);

	// banco inexistente
	if (sqlErrors('code') == 18456) {
		echo '<br/>banco não encontrado';
		continue;
	}

	echo '<h3>imagens da plateia</h3>';

	$result2 = executeSQL($conn, "SELECT DISTINCT NOMEIMAGEMSITE FROM TABSALA WHERE NOMEIMAGEMSITE IS NOT NULL AND NOMEIMAGEMSITE != ''");

	while ($rs2 = fetchResult($result2)) {
		$path = '../images/uploads/'.$rs2['NOMEIMAGEMSITE'];

		if (!file_exists($path)) {
			echo '<br/>arquivo não encontrado: '.$path;
			continue;
		}

		$base64 = getBase64ImgString($path);

		echo '<br/><br/>'.$path;
		echo '<br/><img src="'.$base64.'"/>';

		executeSQL($conn, 'UPDATE TABSALA SET FOTOIMAGEMSITE = ? WHERE NOMEIMAGEMSITE = ?', array($base64, $rs2['NOMEIMAGEMSITE']));
	}

	echo '<h3>imagens dos lugares</h3>';

	$result2 = executeSQL($conn, "SELECT DISTINCT IMGVISAOLUGAR FROM TABSALDETALHE WHERE IMGVISAOLUGAR IS NOT NULL AND IMGVISAOLUGAR != ''");

	while ($rs2 = fetchResult($result2)) {
		$path = $rs2['IMGVISAOLUGAR'];

		if (!file_exists($path)) {
			echo '<br/>arquivo não encontrado: '.$path;
			continue;
		}

		$base64 = getBase64ImgString($path);

		echo '<br/><br/>'.$path;
		echo '<br/><img src="'.$base64.'"/>';

		executeSQL($conn, 'UPDATE TABSALDETALHE SET IMGVISAOLUGARFOTO = ? WHERE IMGVISAOLUGAR = ?', array($base64, $rs2['IMGVISAOLUGAR']));
	}
}