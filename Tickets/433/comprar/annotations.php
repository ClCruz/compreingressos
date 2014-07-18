<?php
if (isset($_GET['teatro']) and isset($_GET['codapresentacao'])) {
	require_once('../settings/functions.php');
	session_start();
	
	$conn = getConnection($_GET['teatro']);
	$query .= 'SELECT S.INDICE, S.NOMOBJETO, S.CLASSEOBJ, S.CODSETOR, SE.NOMSETOR, S.POSXSITE, S.POSYSITE,
					CASE ISNULL(L.STACADEIRA, \'D\')
						WHEN \'D\' THEN \'O\'
						ELSE \'C\'
					END STATUS,
					L.ID_SESSION,
					S.IMGVISAOLUGAR
					FROM TABSALDETALHE S
					INNER JOIN TABSETOR SE ON SE.CODSALA = S.CODSALA AND SE.CODSETOR = S.CODSETOR
					INNER JOIN TABAPRESENTACAO A ON A.CODSALA = S.CODSALA
					INNER JOIN TABPECA P ON P.CODPECA = A.CODPECA
					LEFT JOIN TABLUGSALA L ON L.INDICE = S.INDICE AND L.CODAPRESENTACAO = A.CODAPRESENTACAO
					WHERE A.CODAPRESENTACAO = ? AND S.TIPOBJETO = \'C\' AND P.STAPECA = \'A\' AND CONVERT(varchar(8), P.DATFINPECA, 112) >= CONVERT(varchar(8), GETDATE(), 112) AND P.IN_VENDE_SITE = 1';
	$params = array($_GET['codapresentacao']);
	$result = executeSQL($conn, $query, $params);
	
	$cadeiras = '[';
	
	while ($rs = fetchResult($result)) {
		
		$rs['STATUS'] = (session_id() == $rs['ID_SESSION']) ? 'S' : $rs['STATUS'];
		
		$cadeiras .= "{" . 
						"id:'" . $rs['INDICE'] . "'" .
						",name:'" . $rs['NOMOBJETO'] . "'" .
						",classeObj:'" . $rs['CLASSEOBJ'] . "'" .
						",setor:'" . utf8_encode($rs['NOMSETOR']) . "'" .
						",codSetor:'" . $rs['CODSETOR'] . "'" .
						",x:" . $rs['POSXSITE'] .
						",y:" . $rs['POSYSITE'] .
						// O = openned / C = closed / S = standby = selected by current user
						",status:'" . $rs['STATUS'] . "'" .
						($rs['IMGVISAOLUGAR'] ? ",img:'" . $rs['IMGVISAOLUGAR'] . "'" : '') .
						"},";
	}
	
	header("Content-type: text/txt");
	echo substr($cadeiras, 0, -1) . ']';
}
?>