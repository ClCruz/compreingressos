<?php
session_start();
if (isset($_SESSION['operador']) and is_numeric($_SESSION['operador']) and isset($_GET['teatro'])) {
	require_once('../settings/functions.php');
	
	$mainConnection = mainConnection();
	$query = 'SELECT
					  E.DS_EVENTO, A.ID_APRESENTACAO, A.DT_APRESENTACAO
					  FROM MW_EVENTO E
					  INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO AND A.IN_ATIVO = 1 AND CONVERT(INT, A.DT_APRESENTACAO) >= CONVERT(INT, GETDATE())
					  WHERE E.IN_ATIVO = 1 AND E.ID_BASE = ?
				  AND A.ID_APRESENTACAO IN (SELECT A1.ID_APRESENTACAO
						  FROM 
						  MW_APRESENTACAO A1 
						  WHERE A1.ID_EVENTO = A.ID_EVENTO
						  AND A1.IN_ATIVO = \'1\'
						  AND DS_PISO = (SELECT MIN(A2.DS_PISO) FROM MW_APRESENTACAO A2 WHERE A2.ID_EVENTO = A1.ID_EVENTO))
				  AND CONVERT(INT, A.DT_APRESENTACAO) IN (SELECT CONVERT(INT, A1.DT_APRESENTACAO)
						  FROM 
						  MW_APRESENTACAO A1 
						  WHERE A1.ID_EVENTO = A.ID_EVENTO
						  AND A1.IN_ATIVO = \'1\'
						  AND CONVERT(INT, A1.DT_APRESENTACAO) = (SELECT CONVERT(INT, MIN(A2.DT_APRESENTACAO)) FROM MW_APRESENTACAO A2 WHERE A2.ID_EVENTO = A1.ID_EVENTO AND A2.IN_ATIVO = \'1\' AND CONVERT(INT, A2.DT_APRESENTACAO) >= CONVERT(INT, GETDATE())))
				  ORDER BY DS_EVENTO';
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