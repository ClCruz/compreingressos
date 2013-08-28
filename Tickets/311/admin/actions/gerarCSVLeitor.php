<?php
require_once('../settings/functions.php');

$mainConnection = mainConnection();

session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 220, true)) {

	if ($_GET['action'] == 'csv') {

		$conn = getConnection($_GET['CodTeatro']);

		$query = "SELECT NOMPECA, CONVERT(VARCHAR(10), DATINIPECA, 120) DATINIPECA, CONVERT(VARCHAR(10), DATFINPECA, 120) DATFINPECA FROM TABPECA WHERE CODPECA = ?";
		$params = array($_GET['CodPeca']);
		$rs = executeSQL($conn, $query, $params, true);

		$file_name = $_GET['DatApresentacao'] . str_replace(':', '', $_GET['HorSessao']) . normalize_string(substr(utf8_encode($rs['NOMPECA']), 0, 15));
		$csv1_path = 'temp/EVE' . $file_name . '.csv';
		$csv2_path = 'temp/ING' . $file_name . '.csv';
		$zip_file = 'temp/' . $file_name . '.zip';



		$csv1 = fopen($csv1_path, 'wt');

		$data = substr($_GET['DatApresentacao'], 0, 4) . '-' . substr($_GET['DatApresentacao'], 4, 2) . '-' . substr($_GET['DatApresentacao'], -2);

		fwrite($csv1, "00;" . utf8_encode($rs['NOMPECA']) . ";" . $data . ";00:00:00;" . $data . ";23:59:59;\n");

		$query = "SELECT V.CODTIPBILHETE, B.TIPBILHETE FROM TABVALBILHETE V
				  INNER JOIN TABTIPBILHETE B ON V.CODTIPBILHETE = B.CODTIPBILHETE
				  WHERE CODPECA = ?";
		$params = array($_GET['CodPeca']);
		$result = executeSQL($conn, $query, $params);

		$bilhetes = array();

		while ($rs = fetchResult($result)) {
			fwrite($csv1, "01;" . $rs['CODTIPBILHETE'] . ";" . utf8_encode($rs['TIPBILHETE']) . "\n");
			$bilhetes[$rs['CODTIPBILHETE']] = utf8_encode($rs['TIPBILHETE']);
		}

		fclose($csv1);



		$csv2 = fopen($csv2_path, 'wt');

		$query = "SELECT A.CODAPRESENTACAO, L.CODSALA, L.CODSETOR, COUNT(INDICE) AS TOTAL FROM TABSALDETALHE L
				  INNER JOIN TABAPRESENTACAO A ON L.CODSALA = A.CODSALA
				  WHERE A.CODPECA = ? AND A.DATAPRESENTACAO = ? AND A.HORSESSAO = ? AND TIPOBJETO <> 'I'
				  GROUP BY A.CODAPRESENTACAO, L.CODSALA, L.CODSETOR";
		$params = array($_GET['CodPeca'], $_GET['DatApresentacao'], $_GET['HorSessao']);
		$result = executeSQL($conn, $query, $params);

		while ($rs = fetchResult($result)) {
			$cod_apresentacao	=	substr('00000' . $rs['CODAPRESENTACAO'], -5);
			$cod_setor			=	$rs['CODSETOR'];
			$data_apresentacao	=	substr($_GET['DatApresentacao'], -4);
			$hora_apresentacao	=	str_replace(':', '', $_GET['HorSessao']);

			foreach ($bilhetes as $id => $name) {
				$cod_bilhete = substr('000' . $id, -3);

				for ($i = 1; $i <= $rs['TOTAL'] * 1.2; $i++) {
					$sequencia_bilhete = substr('00000' . $i, -5);
					fwrite($csv2, "02;" . $cod_apresentacao . $cod_setor . $data_apresentacao . $hora_apresentacao . $cod_bilhete . $sequencia_bilhete . ";" . $id . ";;;;;I; \n");
				}
			}
		}

		fclose($csv2);



		$zip = new ZipArchive;
		$zip->open($zip_file, ZipArchive::CREATE);
		$zip->addFile($csv1_path);
		$zip->addFile($csv2_path);
		$zip->close();

		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="' . $file_name . '.zip"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($zip_file));

		ob_clean();
		flush();

		readfile($zip_file);

		unlink($csv1_path);
		unlink($csv2_path);
		unlink($zip_file);

		exit();
	}

}