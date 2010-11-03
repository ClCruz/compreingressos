<?php
require_once('../settings/functions.php');

if (isset($_POST['codigo_pedido'])) {
	$mainConnection = mainConnection();
	
	$json = json_encode(array('descricao' => 'entrada no campainhaiPagare - retorno do ipagare'));
	include('logiPagareChamada.php');
	
	require('processarRetornoiPagare.php');
	
	if ($validado) {
		$query = 'SELECT 1 FROM MW_PEDIDO WHERE ID_PEDIDO = ? AND IN_SITUACAO = \'F\'';
		$params = array($_POST['codigo_pedido']);
		$result = executeSQL($mainConnection, $query, $params);
		
		if (!hasRows($result)) {
			$query = 'SELECT TOP 1
						 C.ID_CLIENTE,
						 C.DS_NOME,
						 C.DS_SOBRENOME,
						 C.DS_DDD_TELEFONE,
						 C.DS_TELEFONE,
						 C.DS_NOME,
						 C.DS_SOBRENOME,
						 C.CD_CPF,
						 C.CD_RG,
						 R.ID_SESSION
						 FROM MW_CLIENTE C
						 INNER JOIN MW_PEDIDO_VENDA PV ON PV.ID_CLIENTE = C.ID_CLIENTE
						 INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
						 INNER JOIN MW_RESERVA R ON R.ID_RESERVA = IPV.ID_RESERVA
						 WHERE PV.ID_PEDIDO_VENDA = ?';
			$params = array($_POST['codigo_pedido']);
			$dados = executeSQL($mainConnection, $query, $params, true);
			
			$query = 'SELECT DISTINCT A.CODAPRESENTACAO, B.ID_BASE, B.DS_NOME_BASE_SQL
						 FROM
						 MW_BASE B
						 INNER JOIN MW_EVENTO E ON E.ID_BASE = B.ID_BASE
						 INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
						 INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_APRESENTACAO = A.ID_APRESENTACAO
						 INNER JOIN MW_PEDIDO_VENDA P ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
						 WHERE P.ID_CLIENTE = ? AND P.ID_PEDIDO_VENDA = ? AND P.IN_SITUACAO = \'P\'';
			$params = array($dados['ID_CLIENTE'], $_POST['codigo_pedido']);
			$result = executeSQL($mainConnection, $query, $params);
			
			$noErrors = true;
			$retornoProcedure = '';
			
			beginTransaction($mainConnection);
			
			while ($rs = fetchResult($result) and $noErrors) {
				$query = 'EXEC '.strtoupper($rs['DS_NOME_BASE_SQL']).'..SP_VEN_INS001_WEB ?,?,?,?,?,?,?,?,?,?,?,?,?,?';
				$params = array($dados['ID_SESSION'], $rs['ID_BASE'], $_POST['codigo_pagamento'], $rs['CODAPRESENTACAO'],
									 $dados['DS_DDD_TELEFONE'], $dados['DS_TELEFONE'], ($dados['DS_NOME'].' '.$dados['DS_SOBRENOME']),
									 $dados['CD_CPF'], $dados['CD_RG'], $_POST['codigo_pedido'], $_POST['uid_pedido'],
									 $_POST['numero_autorizacao'], $_POST['numero_transacao'], $_POST['numero_cartao']);
				$retornoProcedure = executeSQL($mainConnection, $query, $params, true);
				$noErrors = ($retornoProcedure[0] and $noErrors);
			}
			
			$sqlErrors = sqlErrors();
			if ($noErrors and empty($sqlErrors)) {
				executeSQL($mainConnection, 'DELETE MW_RESERVA WHERE ID_SESSION = ?', array($dados['ID_SESSION']));
				commitTransaction($mainConnection);
				echo 'OK';
			} else {
				include('errorMail.php');
				rollbackTransaction($mainConnection);
				echo 'NOT OK';
			}
		}
		include('logiPagare.php');
		die();
	}
}
?>