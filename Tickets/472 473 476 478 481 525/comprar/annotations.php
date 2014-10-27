<?php
if (isset($_GET['teatro']) and isset($_GET['codapresentacao'])) {
	require_once('../settings/functions.php');
	session_start();
	
	$conn = getConnection($_GET['teatro']);
	$query .= "WITH RESULTADO AS (
					SELECT PR.ID_CADEIRA FROM CI_MIDDLEWAY..MW_PACOTE_RESERVA PR
					INNER JOIN CI_MIDDLEWAY..MW_PACOTE_APRESENTACAO PA ON PA.ID_PACOTE = PR.ID_PACOTE
					INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.ID_APRESENTACAO = PA.ID_APRESENTACAO
					INNER JOIN CI_MIDDLEWAY..MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
					WHERE E.ID_BASE = ? AND A.CODAPRESENTACAO = ? AND PR.IN_STATUS_RESERVA IN ('A', 'S')

					UNION ALL

					SELECT PR.ID_CADEIRA FROM CI_MIDDLEWAY..MW_PACOTE_RESERVA PR
					INNER JOIN CI_MIDDLEWAY..MW_PACOTE P ON P.ID_PACOTE = PR.ID_PACOTE
					INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A2 ON A2.ID_APRESENTACAO = P.ID_APRESENTACAO
					INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.ID_EVENTO = A2.ID_EVENTO AND A.DT_APRESENTACAO = A2.DT_APRESENTACAO AND A.HR_APRESENTACAO = A2.HR_APRESENTACAO AND A2.IN_ATIVO = 1
					INNER JOIN CI_MIDDLEWAY..MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = 1
					WHERE E.ID_BASE = ? AND A.CODAPRESENTACAO = ? AND PR.IN_STATUS_RESERVA IN ('A', 'S')
				)

				SELECT DISTINCT S.INDICE, S.NOMOBJETO, S.CLASSEOBJ, S.CODSETOR, SE.NOMSETOR, S.POSXSITE, S.POSYSITE,
				CASE WHEN (L.STACADEIRA IS NULL AND R.ID_CADEIRA IS NULL) THEN 'O'
					ELSE 'C'
				END STATUS,
				L.ID_SESSION,
				S.IMGVISAOLUGAR
				FROM TABSALDETALHE S
				INNER JOIN TABSETOR SE ON SE.CODSALA = S.CODSALA AND SE.CODSETOR = S.CODSETOR
				INNER JOIN TABAPRESENTACAO A ON A.CODSALA = S.CODSALA
				INNER JOIN TABPECA P ON P.CODPECA = A.CODPECA
				LEFT JOIN TABLUGSALA L ON L.INDICE = S.INDICE AND L.CODAPRESENTACAO = A.CODAPRESENTACAO
				LEFT JOIN RESULTADO R ON R.ID_CADEIRA = S.INDICE
				WHERE A.CODAPRESENTACAO = ? AND S.TIPOBJETO = 'C' AND P.STAPECA = 'A' 
				AND CONVERT(varchar(8), P.DATFINPECA, 112) >= CONVERT(varchar(8), GETDATE(), 112) AND P.IN_VENDE_SITE = 1";
	$params = array($_GET['teatro'], $_GET['codapresentacao'], $_GET['teatro'], $_GET['codapresentacao'], $_GET['codapresentacao']);
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