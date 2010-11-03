<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 11, true)) {

if ($_GET['action'] == 'load') { /*------------ LOAD ------------*/
	$conn = getConnection($_POST['teatro']);
	
	$query = 'SELECT NOMEIMAGEMSITE, ALTURASITE, LARGURASITE FROM TABSALA WHERE CODSALA = ?';
	$params = array($_POST['sala']);
	$rs = executeSQL($conn, $query, $params, true);
	
	if ($rs[0] != '') {
		$imagem = $rs[0];
	}
	if ($rs[1] != '') {
		$yScale = $rs[1];
	}
	if ($rs[2] != '') {
		$xScale = $rs[2];
	}
	
	$query = 'SELECT MAX(POSX) MAXX, MAX(POSY) MAXY, MAX(POSXSITE) MAXXSITE, MAX(POSYSITE) MAXYSITE FROM TABSALDETALHE WHERE CODSALA = ? AND TIPOBJETO = \'C\'';
	$params = array($_POST['sala']);
	$rs = executeSQL($conn, $query, $params, true);
	
	$query = 'SELECT S.INDICE, S.NOMOBJETO, S.CODSETOR, SE.NOMSETOR, ';
	
	if ($rs['MAXXSITE'] == '' or $rs['MAXYSITE'] == '' or $_POST['reset']) {
		$query .= '(((S.POSX * ?) / ?) + ?) POSXSITE, (((S.POSY * ?) / ?) + ?) POSYSITE';
		$params = array(	
								1 - $_POST['xmargin'],
								$rs['MAXX'],
								$_POST['xmargin'],
								
								1 - $_POST['ymargin'],
								$rs['MAXY'],
								$_POST['ymargin'],
								
								$_POST['sala']
							);
	} else {
		$query .= 'S.POSXSITE, S.POSYSITE';
		$params = array($_POST['sala']);
	}
	
	$query .= ' FROM TABSALDETALHE S
					INNER JOIN TABSETOR SE ON SE.CODSALA = S.CODSALA AND SE.CODSETOR = S.CODSETOR
					WHERE S.CODSALA = ? AND S.TIPOBJETO = \'C\'';
	
	$result = executeSQL($conn, $query, $params);
	
	$cadeiras = '[';
	
	while ($rs = fetchResult($result)) {
		$cadeiras .= "{" . 
						"id:'" . $rs['INDICE'] . "'" .
						",name:'" . $rs['NOMOBJETO'] . "'" .
						",setor:'" . utf8_encode($rs['NOMSETOR']) . "'" .
						",codSetor:'" . $rs['CODSETOR'] . "'" .
						",x:" . $rs['POSXSITE'] .
						",y:" . $rs['POSYSITE'] .
						"},";
	}
	
	header("Content-type: text/txt");
	
	echo substr($cadeiras, 0, -1) . ']' . '||' . $imagem . '||' . $xScale . '||' . $yScale;
} else if ($_GET['action'] == 'save') {
	$conn = getConnection($_POST['teatro']);
	
	$query = 'UPDATE TABSALA SET NOMEIMAGEMSITE = ?, LARGURASITE = ?, ALTURASITE = ? WHERE CODSALA = ?';
	$params = array(
							(isset($_POST['image'])) ? $_POST['image'] : '',
							(isset($_POST['xScale'])) ? $_POST['xScale'] : '',
							(isset($_POST['yScale'])) ? $_POST['yScale'] : '',
							$_POST['sala']
						);
	executeSQL($conn, $query, $params);
	
	$query = 'UPDATE TABSALDETALHE SET
					POSXSITE = ?,
					POSYSITE = ?
					WHERE CODSALA = ? AND INDICE = ?';
	
	$obj = json_decode($_POST['obj']);
	
	beginTransaction($conn);
	
	foreach ($obj as $cadeira) {
		$cadeira = get_object_vars($cadeira);
		$params = array($cadeira['x'], $cadeira['y'], $_POST['sala'], $cadeira['id']);
		
		executeSQL($conn, $query, $params);
	}
	
	$erros = sqlErrors();
	
	if (empty($erros)) {
		commitTransaction($conn);
		echo 'Dados salvos com sucesso!';
	} else {
		rollbackTransaction($conn);
		echo sqlErrors('messsage');
	}
}

}
?>