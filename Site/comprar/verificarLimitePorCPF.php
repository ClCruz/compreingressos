<?php
require_once('../settings/functions.php');
if (isset($_SESSION['user'])) {
	$mainConnection = mainConnection();
	
	$rs = executeSQL($mainConnection, 'SELECT CD_CPF FROM MW_CLIENTE WHERE ID_CLIENTE = ?', array($_SESSION['user']), true);
	$cpf = $rs[0];
	
	$query = 'SELECT E.ID_BASE, E.DS_EVENTO, A.CODAPRESENTACAO, SUM(1) TOTAL
				 FROM MW_RESERVA R
				 INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
				 INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
				 WHERE R.ID_SESSION = ?
				 GROUP BY E.ID_BASE, E.DS_EVENTO, A.CODAPRESENTACAO';
	$result = executeSQL($mainConnection, $query, array(session_id()));
	
	$start = 'Caro Sr(a)., o(s) seguinte(s) evento(s) permite(m) apenas a compra de um número limitado de ingressos.<br><br>';
	$start .= '<table class=\'ui-widget ui-widget-content\' style=\'width:100%; text-align:left\'><thead><th>Evento</th><th>Limite</th><th>Saldo Atual</th></thead><tbody>';
	$limitePorCPF = $start;
	
	while ($rs = fetchResult($result)) {
		
		$conn = getConnection($rs['ID_BASE']);
		$query = 'SELECT (
						 SELECT ISNULL(QT_INGRESSOS_POR_CPF, 0)
						 FROM TABAPRESENTACAO A
						 INNER JOIN TABPECA P ON P.CODPECA = A.CODPECA
						 WHERE A.CODAPRESENTACAO = ?
					 ) AS QT_INGRESSOS_POR_CPF, (
						 SELECT SUM(CASE H.CODTIPLANCAMENTO WHEN 1 THEN 1 ELSE -1 END)
						 FROM TABCLIENTE C
						 INNER JOIN TABHISCLIENTE H ON H.CODIGO = C.CODIGO AND H.CODAPRESENTACAO = ?
						 WHERE C.CPF = ?
					 ) AS QTDVENDIDO';
		$result2 = executeSQL($conn, $query, array($rs['CODAPRESENTACAO'], $rs['CODAPRESENTACAO'], $cpf));
		
		if (hasRows($result2)) {
			$evento = $rs['DS_EVENTO'];
			$comprando = $rs['TOTAL'];
			$rs = fetchResult($result2);
			if ($rs['QT_INGRESSOS_POR_CPF'] != 0 and $rs['QT_INGRESSOS_POR_CPF'] < $rs['QTDVENDIDO'] + $comprando) {
				$limitePorCPF .= '<tr><td>'.$evento.'</td><td>'.$rs['QT_INGRESSOS_POR_CPF'].'</td>';
				$limitePorCPF .= '<td>'.($rs['QT_INGRESSOS_POR_CPF'] - $rs['QTDVENDIDO']).'</td></tr>';
			}
		}
		
	}
	
	$finish = '</tbody></table><br>Por favor, remova o(s) ingresso(s) que esta(ão) em desacordo com a tabela acima.';
	$limitePorCPF .= $finish;
	
	if ($limitePorCPF != $start.$finish) {
		if (basename($_SERVER['SCRIPT_FILENAME']) == 'etapa5.php') {
			header("Location: etapa4.php");
		} else {
			$scriptLlimitePorCPF = '<script type="text/javascript">
												$(function(){
													$.dialog({title:"Aviso...", text:"'.$limitePorCPF.'", uiOptions:{width:500}});
												});
											</script>';
		}
	}
}
?>