<html>
<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
</head>
<body>
<div id="iframe_ticket_net">
<?php
if (isset($_GET['evento']) and is_numeric($_GET['evento'])) {
	require_once('../settings/functions.php');
	require_once('../settings/settings.php');

	$mainConnection = mainConnection();

	// Verifica a base de dados de origem do evento para poder verificar se ela ainda está habilitada para venda na web
	$query = 'SELECT DS_NOME_BASE_SQL, CODPECA
				 FROM MW_EVENTO E
				 INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE
				 WHERE E.ID_EVENTO = ? AND E.IN_ATIVO = \'1\'';

	$params = array($_GET['evento']);
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	$nomeBase = $rs['DS_NOME_BASE_SQL'];
	
	if (!empty($rs)) {
		// Verifica se o evento está ativo e se pode vender pela web
		$query = 'SELECT (ISNULL(QT_HR_ANTECED, 24) * -1) AS QT_HR_ANTECED
					 FROM ' . $nomeBase . '..TABPECA
					 WHERE CODPECA = ? AND STAPECA = \'A\' AND CONVERT(CHAR(8), DATFINPECA,112) >= CONVERT(CHAR(8), GETDATE(),112)';
		
		$params = array($rs['CODPECA']);
		$rs = executeSQL($mainConnection, $query, $params, true);
		
		if (!empty($rs)) {
			/*$query = 'SELECT E.DS_EVENTO, MIN(A.ID_APRESENTACAO) ID_APRESENTACAO, CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103) DT_APRESENTACAO, A.HR_APRESENTACAO, A.DT_APRESENTACAO DT_APRESENTACAO_ORDER,
						 DATEDIFF(HH, DATEADD(HH, ?, CONVERT(DATETIME, CONVERT(VARCHAR, A.DT_APRESENTACAO, 112) + \' \' + LEFT(HR_APRESENTACAO,2) + \':\' + RIGHT(HR_APRESENTACAO,2) + \':00\')) ,GETDATE() ) AS TELEFONE
						 FROM MW_EVENTO E
						 INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO AND A.IN_ATIVO = \'1\'
						 WHERE E.ID_EVENTO = ? AND E.IN_ATIVO = \'1\' AND CONVERT(CHAR(8), A.DT_APRESENTACAO,112) >= CONVERT(CHAR(8), GETDATE(),112)
						 GROUP BY E.DS_EVENTO, CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103), A.HR_APRESENTACAO, A.DT_APRESENTACAO
						 ORDER BY DT_APRESENTACAO_ORDER'*/;
						 
			$query = 'SELECT E.DS_EVENTO, A.ID_APRESENTACAO, CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103) DT_APRESENTACAO, A.HR_APRESENTACAO, A.DT_APRESENTACAO DT_APRESENTACAO_ORDER,
						 DATEDIFF(HH, DATEADD(HH, ?, CONVERT(DATETIME, CONVERT(VARCHAR, A.DT_APRESENTACAO, 112) + \' \' + LEFT(HR_APRESENTACAO,2) + \':\' + RIGHT(HR_APRESENTACAO,2) + \':00\')) ,GETDATE() ) AS TELEFONE
						 FROM MW_EVENTO E
						 INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO AND A.IN_ATIVO = \'1\'
						 WHERE E.ID_EVENTO = ? AND E.IN_ATIVO = \'1\' AND CONVERT(CHAR(8), A.DT_APRESENTACAO,112) >= CONVERT(CHAR(8), GETDATE(),112)
						 AND A.ID_APRESENTACAO IN (SELECT A1.ID_APRESENTACAO
														  FROM 
														  MW_APRESENTACAO A1 
														  WHERE A1.ID_EVENTO = A.ID_EVENTO
														  AND A1.IN_ATIVO = \'1\'
														  AND CONVERT(CHAR(8), A1.DT_APRESENTACAO,112) >= CONVERT(CHAR(8), GETDATE(),112)
														  AND DS_PISO = (SELECT MIN(A2.DS_PISO) FROM MW_APRESENTACAO A2 WHERE A2.IN_ATIVO = \'1\' AND A2.ID_EVENTO = A1.ID_EVENTO AND A2.DT_APRESENTACAO = A1.DT_APRESENTACAO))
						 ORDER BY DT_APRESENTACAO_ORDER';
			
			$params = array($rs['QT_HR_ANTECED'], $_GET['evento']);
			$result = executeSQL($mainConnection, $query, $params);
			
			if (hasRows($result)) {
				setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");
				
				$pageURL = 'http';
				if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
				$pageURL .= "://";
				if ($_SERVER["SERVER_PORT"] != "80") {
					$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
				} else {
					$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				}
				$url = str_replace(basename($pageURL), '', $pageURL);
?>
				<table>
<?php
					while ($rs = fetchResult($result)) {
						$hora = explode('h', $rs['HR_APRESENTACAO']);
						$data = explode('/', $rs['DT_APRESENTACAO']);
						$tempo = mktime($hora[0], $hora[1], 0, $data[1], $data[0], $data[2]);
?>
						<tr class="apresentacao_ticket">
							<td><?php echo utf8_encode(strtoupper(strftime("%a", $tempo))); ?></td>
							<td>|</td>
							<td><?php echo date('d/m', $tempo); ?></td>
							<td>|</td>
							<td><?php echo $rs['HR_APRESENTACAO']; ?></td>
							<td>|</td>
							<td>
								<?php if ($rs['TELEFONE'] >= 0) { ?>
									SOMENTE BILHETERIA
								<?php } else { ?>
								<a href="<?php echo $url; ?>etapa1.php?apresentacao=<?php echo $rs['ID_APRESENTACAO']; ?>&eventoDS=<?php echo utf8_encode($rs['DS_EVENTO']); ?>" target="_top">
									<div class="botao_compre"></div>
								<?php } ?>
								</a>
							</td>
						</tr>
<?php
					}
?>
				</table>
<?php
			}
		}
	} else {
		echo '';//'<p>Evento não localizado ou fora do prazo de exibição.</p>';
	}
}
?>
</div>
</body>
</html>