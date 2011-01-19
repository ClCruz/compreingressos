<?php
require_once('../settings/functions.php');
session_start();
$mainConnection = mainConnection();

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {// se for ajax
	if (is_array($_POST['code'])) {
		
		$query = 'SELECT E.ID_BASE, CONVERT(VARCHAR(8), A.DT_APRESENTACAO, 112) FROM MW_EVENTO E INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO WHERE ID_APRESENTACAO = ?';
		$query2 = 'SELECT 1
					  FROM TABEVENTOPATROCINADO EP
					  INNER JOIN TABCARTOESVALIDOS CV ON CV.CODEVENTOPATROCINADO = EP.CODEVENTOPATROCINADO
					  WHERE EP.CODEVENTOPATROCINADO = ?
							  AND ? BETWEEN CONVERT(VARCHAR(8), EP.DATINICIO, 112) AND CONVERT(VARCHAR(8), DATTERMINO, 112)
							  AND ? BETWEEN CV.NUMINICIAL AND CV.NUMFINAL';
		$valido = true;
		$erro = '';
		
		for ($i = 0; $i < count($_POST['apresentacao']); $i++) {
			if ($apresentacaoAtual != $_POST['apresentacao'][$i]) {
				$rs = executeSQL($mainConnection, $query, array($_POST['apresentacao'][$i]), true);
				
				$conn = getConnection($rs[0]);
				
				$rs = executeSQL($conn, $query2, array($_POST['code'][$i], $rs[1], $_POST['bin1'].$_POST['bin2']), true);
				
				$valido = ($valido and $rs[0]);
				
				if (!$valido) $erro .= $_POST['apresentacao'][$i] . ',';
			}
			$apresentacaoAtual = $_POST['apresentacao'][$i];
		}
		
		echo ($erro != '') ? substr($erro, 0, -1) : 'true';
	}
	
} else {
	
	$rs = executeSQL($mainConnection, 'SELECT CD_CPF FROM MW_CLIENTE WHERE ID_CLIENTE = ?', array($_SESSION['user']), true);
	$cpf = $rs[0];
	
	//lista codapresentacao e id_base a partir da reserva
	$query = 'SELECT A.CODAPRESENTACAO, E.ID_BASE
				 FROM MW_EVENTO E
				 INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
				 INNER JOIN MW_RESERVA R ON R.ID_APRESENTACAO = A.ID_APRESENTACAO
				 WHERE R.ID_SESSION = ?
				 GROUP BY A.CODAPRESENTACAO, E.ID_BASE';
	
	//confere se a apresentacao tem bin e se o bin informado é valido
	$query2 = 'SELECT P.QT_BIN_POR_CPF, ISNULL(V.CODEVENTOPATROCINADO, 0) AS RANGE_VALIDA, COUNT(AB.ID_APRESENTACAO_BILHETE) AS COMPRANDO
				  FROM TABAPRESENTACAO A
				  INNER JOIN TABPECA P ON P.CODPECA = A.CODPECA
				  INNER JOIN TABEVENTOPATROCINADO EP ON A.CODPECA = EP.CODPECA
				  LEFT JOIN TABCARTOESVALIDOS V ON V.CODEVENTOPATROCINADO = EP.CODEVENTOPATROCINADO
						 AND ? BETWEEN V.NUMINICIAL AND V.NUMFINAL
				  INNER JOIN CI_MIDDLEWAY..MW_EVENTO E ON E.CODPECA = P.CODPECA
				  INNER JOIN CI_MIDDLEWAY..MW_BASE B ON B.ID_BASE = E.ID_BASE AND B.DS_NOME_BASE_SQL = DB_NAME()
				  INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A2 ON A2.CODAPRESENTACAO = A.CODAPRESENTACAO
				  LEFT JOIN CI_MIDDLEWAY..MW_RESERVA R ON R.ID_APRESENTACAO = A2.ID_APRESENTACAO
				  LEFT JOIN CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE AND AB.CODTIPBILHETE = EP.CODTIPBILHETE
				  WHERE A.CODAPRESENTACAO = ?
						  AND CONVERT(CHAR(8), A.DATAPRESENTACAO, 112) BETWEEN CONVERT(CHAR(8), EP.DATINICIO, 112)
						  AND CONVERT(CHAR(8), EP.DATTERMINO, 112)
						  AND P.IN_BIN_ITAU = 1
				  GROUP BY P.QT_BIN_POR_CPF, ISNULL(V.CODEVENTOPATROCINADO, 0)';
	
	//quantos ingressos o cliente comprou com o bin
	$query3 = 'SELECT SUM(CASE H.CODTIPLANCAMENTO WHEN 1 THEN 1 ELSE -1 END) AS TOTAL
				  FROM TABCLIENTE C
				  INNER JOIN TABHISCLIENTE H ON C.CODIGO = H.CODIGO
				  INNER JOIN TABCOMPROVANTE CR ON CR.CODCLIENTE = H.CODIGO AND CR.CODAPRESENTACAO = H.CODAPRESENTACAO
				  INNER JOIN TABINGRESSO I ON I.CODVENDA = CR.CODVENDA AND I.INDICE = H.INDICE
				  WHERE C.CPF = ? AND H.CODAPRESENTACAO = ? AND I.BINCARTAO = ?';
				 
	$result = executeSQL($mainConnection, $query, array(session_id()));
	$erro = '';
	
	while ($rs = fetchResult($result)) {
		$conn = getConnection($rs['ID_BASE']);
		$codapresentacao = $rs['CODAPRESENTACAO'];
		
		$rs = executeSQL($conn, $query2, array($_COOKIE['binItau'], $codapresentacao), true);
		
		if ($rs['QT_BIN_POR_CPF'] > 0) {
			$limite = $rs['QT_BIN_POR_CPF'];
			$comprando = $rs['COMPRANDO'];
			
			if ($rs['RANGE_VALIDA']) {
				$rs = executeSQL($conn, $query3, array($cpf, $codapresentacao, $_COOKIE['binItau']), true);
				if ($rs['TOTAL'] >= $limite) {
					$erro = 'Você atingiu o limite de '.$limite.' ingresso(s) promocional(is) para esse BIN em um ou mais eventos.<br><br>Favor revisar o pedido.';
				} else if ($limite < $rs['TOTAL'] + $comprando) {
					$erro = 'Você tem apenas '.(($rs['TOTAL'] + $comprando) - $limite).' ingresso(s) promocional(is) disponível(is) para esse BIN em um ou mais eventos.<br><br>Favor revisar o pedido.';
				}
			} else if ($comprando > 0) {
				$erro = 'O BIN informado não é válido para um ou mais eventos.';
			}
		}
	}
	
	if ($erro != '') {
		if (basename($_SERVER['SCRIPT_FILENAME']) == 'etapa5.php') {
			header("Location: etapa4.php");
		} else {
			$scriptValidarBin = '<script type="text/javascript">
											$(function(){
												$.dialog({title:"Aviso...", text:"'.$erro.'"});
											});
										</script>';
		}
	}

}
?>