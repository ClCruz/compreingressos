<?php
$pedido_id = $parametros['OrderData']['OrderId'];
$braspag_id = $result->AuthorizeTransactionResult->OrderData->BraspagOrderId;
$braspag_transaction_id = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->BraspagTransactionId;
$transaction_id = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->AcquirerTransactionId;
$transaction_auth = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->AuthorizationCode;

// se for usuario do pdv ou se for um pedido de valor 0 e ingresso promocional
if((isset($_SESSION['usuario_pdv']) and $_SESSION['usuario_pdv'] == 1)
	or ($PaymentDataCollection['Amount'] == 0 and $is_promocional)){
    $meio_pagamento = $_POST['codCartao'];
}else{
    $meio_pagamento = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->PaymentMethod;
}

executeSQL($mainConnection, "insert into mw_log_ipagare values (getdate(), ?, ?)",
    array($_SESSION['user'], json_encode(array('descricao' => '5.3. concretizando retorno do pedido=' . $pedido_id, 'post' => $result)))
);

$query = 'SELECT ID_MEIO_PAGAMENTO
			 FROM MW_MEIO_PAGAMENTO MP
			 WHERE CD_MEIO_PAGAMENTO = ?';
$params = array($meio_pagamento);
$id_meio_pagamento = executeSQL($mainConnection, $query, $params, true);
$id_meio_pagamento = $id_meio_pagamento['ID_MEIO_PAGAMENTO'];


$noErrors = true;
$retornoProcedure = '';

$query = "UPDATE MW_PEDIDO_VENDA SET
            ID_TRANSACTION_BRASPAG = ?,
			ID_PEDIDO_IPAGARE = ?,
			CD_NUMERO_AUTORIZACAO = ?,
			CD_NUMERO_TRANSACAO = ?,
            ID_MEIO_PAGAMENTO = ?
			WHERE ID_PEDIDO_VENDA = ? and in_situacao = 'P'";
executeSQL($mainConnection, $query, array($braspag_transaction_id, $braspag_id, $transaction_auth, $transaction_id, $id_meio_pagamento, $pedido_id));

//beginTransaction($mainConnection);

if (isset($_SESSION['usuario_pdv']) and $_SESSION['usuario_pdv'] == 1) {
	$query = 'EXEC prc_vender_pedido ?, ?';
	$params = array($pedido_id, 249);
} else {
	$query = 'EXEC prc_vender_pedido ?';
	$params = array($pedido_id);
}

executeSQL($mainConnection, 'INSERT INTO tab_log_gabriel (data, passo, parametros) VALUES (GETDATE(), ?, ?)', array('ANTES prc_vender_pedido', json_encode($params)));

$retornoProcedure = executeSQL($mainConnection, $query, $params, true);
$noErrors = ($retornoProcedure[0] and $noErrors);

executeSQL($mainConnection, 'INSERT INTO tab_log_gabriel (data, passo, parametros) VALUES (GETDATE(), ?, ?)', array('DEPOIS prc_vender_pedido', json_encode($retornoProcedure)));

$sqlErrors = sqlErrors();
if ($noErrors and empty($sqlErrors)) {
	$session_id = (in_array($meio_pagamento, array('892', '893')) ? $pedido_id : session_id());

	executeSQL($mainConnection, 'UPDATE MW_PROMOCAO SET ID_SESSION = NULL, ID_PEDIDO_VENDA = ? WHERE ID_SESSION = ?', array($pedido_id, $session_id));
	executeSQL($mainConnection, 'DELETE MW_RESERVA WHERE ID_SESSION = ?', array($session_id));
	//commitTransaction($mainConnection);

	$json = json_encode(array('retorno' => '6. OK - Pedido = ' . $pedido_id));
	include('logiPagareChamada.php');

	$json = json_encode(array('descricao' => '7. envio do email de sucesso - pedido ' . $pedido_id));
	include('logiPagareChamada.php');

	sendSuccessMail($pedido_id);
} else {
	include('errorMail.php');
}