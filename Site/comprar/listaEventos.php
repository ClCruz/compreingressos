<?php
session_start();
if (isset($_SESSION['operador']) and is_numeric($_SESSION['operador']) and isset($_GET['teatro'])) {
	require_once('../settings/functions.php');
	
	$mainConnection = mainConnection();
	$query = 'WITH RESULTADO AS
				(SELECT
					  E.ID_EVENTO, E.DS_EVENTO, A.ID_APRESENTACAO, A.DT_APRESENTACAO, A.DS_PISO
					  FROM MW_EVENTO E
					  INNER JOIN MW_APRESENTACAO A 
					  ON A.ID_EVENTO = E.ID_EVENTO 
					  AND A.IN_ATIVO = 1 
					  WHERE E.IN_ATIVO = 1 AND E.ID_BASE = ?
				  AND CONVERT(CHAR(8), A.DT_APRESENTACAO,112) IN (SELECT CONVERT(CHAR(8), min(A1.DT_APRESENTACAO),112)
						  FROM 
						  MW_APRESENTACAO A1 
						  WHERE A1.ID_EVENTO = A.ID_EVENTO
						  AND A1.IN_ATIVO = \'1\'
							AND CONVERT(CHAR(8), A1.DT_APRESENTACAO,112) >= CONVERT(CHAR(8), GETDATE(),112))
				) 
				SELECT ID_EVENTO, DS_EVENTO, ID_APRESENTACAO, DT_APRESENTACAO 
				FROM RESULTADO R
				WHERE R.DS_PISO IN (SELECT MIN(DS_PISO) FROM RESULTADO R2 WHERE R2.ID_EVENTO = R.ID_EVENTO GROUP BY R2.ID_EVENTO)
				ORDER BY DS_EVENTO, DT_APRESENTACAO, DS_PISO';
	$params = array($_GET['teatro']);
	$result = executeSQL($mainConnection, $query, $params);
	
	if (hasRows($result)) {
	?>
	<div class="titulo">
		<h1>Escolha de Evento</h1>
	</div>
	<p>Selecione o evento desejado:</p>
	<ul>
	<?php while ($rs = fetchResult($result)) { ?>
		<li><a href="etapa1.php?apresentacao=<?php echo $rs['ID_APRESENTACAO']; ?>&eventoDS=<?php echo utf8_encode($rs['DS_EVENTO']); ?>" target="_top"><?php echo utf8_encode($rs['DS_EVENTO']); ?></a></li>
	<?php } ?>
	</ul>
	<?php
	} else {
	?>
	<div class="titulo">
		<h1>Nenhum evento dispon&iacute;vel para esse local.</h1>
	</div>
	<?php
	}
}
?>