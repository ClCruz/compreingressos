<?php
$pedido_id = $parametros['OrderData']['OrderId'];
$braspag_id = $result->AuthorizeTransactionResult->OrderData->BraspagOrderId;
$braspag_transaction_id = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->BraspagTransactionId;
$transaction_id = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->AcquirerTransactionId;
$transaction_auth = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->AuthorizationCode;
$meio_pagamento = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->PaymentMethod;

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
			 PV.CD_BIN_CARTAO,
			 R.ID_SESSION,
			 R.CD_BINITAU,
			 PV.ID_USUARIO_CALLCENTER,
			 PV.IN_RETIRA_ENTREGA
			 FROM MW_CLIENTE C
			 INNER JOIN MW_PEDIDO_VENDA PV ON PV.ID_CLIENTE = C.ID_CLIENTE
			 INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
			 INNER JOIN MW_RESERVA R ON R.ID_RESERVA = IPV.ID_RESERVA
			 WHERE PV.ID_PEDIDO_VENDA = ?';
$params = array($pedido_id);
$dados = executeSQL($mainConnection, $query, $params, true);

$query = 'SELECT DISTINCT A.CODAPRESENTACAO, B.ID_BASE, B.DS_NOME_BASE_SQL
			 FROM
			 MW_BASE B
			 INNER JOIN MW_EVENTO E ON E.ID_BASE = B.ID_BASE
			 INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
			 INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_APRESENTACAO = A.ID_APRESENTACAO
			 INNER JOIN MW_PEDIDO_VENDA P ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
			 WHERE P.ID_CLIENTE = ? AND P.ID_PEDIDO_VENDA = ? AND P.IN_SITUACAO = \'P\'';
$params = array($dados['ID_CLIENTE'], $pedido_id);
$result = executeSQL($mainConnection, $query, $params);

$noErrors = true;
$retornoProcedure = '';

// Definir se cliente busca ingresso
if ($dados["ID_USUARIO_CALLCENTER"]) {
	//receber ingresso
	if ($dados["IN_RETIRA_ENTREGA"] != 'R')
		$caixa = 252;
	//buscar ingresso
	else
		$caixa = 254;				
} else {
	//receber ingresso
	if ($dados["IN_RETIRA_ENTREGA"] != 'R')
		$caixa = 253;
	//buscar ingresso
	else
		$caixa = 255;
}

$query = 'UPDATE MW_PEDIDO_VENDA SET
            ID_TRANSACTION_BRASPAG = ?
			WHERE ID_PEDIDO_VENDA = ?
			AND ID_CLIENTE = ?';
executeSQL($mainConnection, $query, array($braspag_transaction_id, $pedido_id, $dados['ID_CLIENTE']));

//beginTransaction($mainConnection);

while ($rs = fetchResult($result) and $noErrors) {
	$query = 'EXEC '.strtoupper($rs['DS_NOME_BASE_SQL']).'..SP_VEN_INS001_WEB ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?';
	$params = array($dados['ID_SESSION'], $rs['ID_BASE'], $meio_pagamento, $rs['CODAPRESENTACAO'],
						 $dados['DS_DDD_TELEFONE'], $dados['DS_TELEFONE'], ($dados['DS_NOME'].' '.$dados['DS_SOBRENOME']),
						 $dados['CD_CPF'], $dados['CD_RG'], $pedido_id, $braspag_id,
						 $transaction_auth, $transaction_id, $dados['CD_BIN_CARTAO'],
						 $caixa);
	$retornoProcedure = executeSQL($mainConnection, $query, $params, true);
	$noErrors = ($retornoProcedure[0] and $noErrors);
}

$sqlErrors = sqlErrors();
if ($noErrors and empty($sqlErrors)) {
	executeSQL($mainConnection, 'DELETE MW_RESERVA WHERE ID_SESSION = ?', array($dados['ID_SESSION']));
	//commitTransaction($mainConnection);

	$json = json_encode(array('retorno' => 'OK - Pedido = ' . $pedido_id));
	include('logiPagareChamada.php');
	
	ob_clean();
} else {
	include('errorMail.php');
}

include('successMail.php');