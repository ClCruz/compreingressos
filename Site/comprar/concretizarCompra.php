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

$tentativas = 3; // para executar a procedure
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

do {

	executeSQL($mainConnection, 'INSERT INTO tab_log_gabriel (data, passo, parametros) VALUES (GETDATE(), ?, ?)', array('ANTES prc_vender_pedido', json_encode($params)));

	$retornoProcedure = executeSQL($mainConnection, $query, $params, true);
	$noErrors = $retornoProcedure[0];

	executeSQL($mainConnection, 'INSERT INTO tab_log_gabriel (data, passo, parametros) VALUES (GETDATE(), ?, ?)', array('DEPOIS prc_vender_pedido', json_encode($retornoProcedure)));

	$tentativas--;

} while (!$retornoProcedure[0] AND $tentativas > 0);
$sqlErrors = sqlErrors();


if ($noErrors and empty($sqlErrors)) {
	
	$session_id = (in_array($meio_pagamento, array('892', '893', '900', '901', '902', '911')) ? $pedido_id : session_id());

	executeSQL($mainConnection, 'UPDATE MW_PROMOCAO SET ID_SESSION = NULL, ID_PEDIDO_VENDA = ? WHERE ID_SESSION = ?', array($pedido_id, $session_id));
	executeSQL($mainConnection, 'DELETE MW_RESERVA WHERE ID_SESSION = ?', array($session_id));
	//commitTransaction($mainConnection);

	$json = json_encode(array('retorno' => '6. OK - Pedido = ' . $pedido_id));
	include('logiPagareChamada.php');

	$json = json_encode(array('descricao' => '7. envio do email de sucesso - pedido ' . $pedido_id));
	include('logiPagareChamada.php');

	sendSuccessMail($pedido_id);

} else {

	$rs = executeSQL($mainConnection, "SELECT ID_GATEWAY FROM MW_MEIO_PAGAMENTO WHERE ID_MEIO_PAGAMENTO = ?", array($id_meio_pagamento), true);

	// estornar pedido (apenas o dinheiro, nao estornar os registros no banco)
	switch ($rs['ID_GATEWAY']) {

		// Fastcash
		case 2:
			$response = "estorno operacional fastcash";
			break;

		// PagSeguro
		case 3:
			require_once('../settings/pagseguro_functions.php');
            $query = "SELECT OBJ_PAGSEGURO FROM MW_PEDIDO_PAGSEGURO WHERE ID_PEDIDO_VENDA = ? ORDER BY DT_STATUS DESC";
            $params = array($pedido_id);
            $rs = executeSQL($mainConnection, $query, $params, true);
            $transaction =  unserialize(base64_decode($rs['OBJ_PAGSEGURO']));
            $response = estonarPedidoPagseguro($transaction->getCode());
			break;

		// CompreIngressos
		case 4:
			$response = "estorno operacional compreingressos";
			break;

		// Braspag
		case 5:
			$pedido = executeSQL($mainConnection,
								'SELECT
									CONVERT(VARCHAR(23), DT_PEDIDO_VENDA, 126) DATA,
                            		ID_TRANSACTION_BRASPAG BRASPAG_ID
                                FROM MW_PEDIDO_VENDA
                                WHERE ID_PEDIDO_VENDA = ?', array($pedido_id), true);

			$parametros = array();
			$parametros['RequestId'] = $ri;
            $parametros['Version'] = '1.0';
            $parametros['TransactionDataCollection']['TransactionDataRequest']['BraspagTransactionId'] = $pedido['BRASPAG_ID'];
            $parametros['TransactionDataCollection']['TransactionDataRequest']['Amount'] = 0;

            $is_cancelamento = date('d', strtotime($pedido['DATA'])) == date('d');

            $options = array(
                'local_cert' => file_get_contents('../settings/cert.pem'),
                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE
            );

            $result_gateway_pagamento = executeSQL($mainConnection, 'SELECT ID_GATEWAY_PAGAMENTO, DS_GATEWAY_PAGAMENTO, CD_GATEWAY_PAGAMENTO, DS_URL FROM MW_GATEWAY_PAGAMENTO');

            $conta = array();

            while ($rs_gateway_pagamento = fetchResult($result_gateway_pagamento)) {

                $url_braspag = $rs_gateway_pagamento['DS_URL'];
                $parametros['MerchantId'] = $rs_gateway_pagamento['CD_GATEWAY_PAGAMENTO'];
                $conta[$rs_gateway_pagamento['ID_GATEWAY_PAGAMENTO']]['descricao'] = $rs_gateway_pagamento['DS_GATEWAY_PAGAMENTO'];

                try {
                    $client = @new SoapClient($url_braspag, $options);

                    if ($is_cancelamento) {
                        $result = $client->VoidCreditCardTransaction(array('request' => $parametros));
                        $response = $result->VoidCreditCardTransactionResult;

                    } else {
                        $result = $client->RefundCreditCardTransaction(array('request' => $parametros));
                        $response = $result->RefundCreditCardTransactionResult;

                    }
                } catch (SoapFault $e) {
                    $response = $e->getMessage();
                }
            }
			break;

		// Pagar.me
		case 6:
			require_once('../settings/pagarme_functions.php');
            $response = estonarPedidoPagarme($pedido_id);
			break;

		// TiPagos
		case 7:
			require_once('../settings/tipagos_functions.php');
			$response = estonarPedidoTiPagos($pedido_id);
			break;

		// cielo
		case 8:
			require_once('../settings/cielo_functions.php');
			$response = estonarPedidoCielo($braspag_transaction_id, $pedido_id);
			break;
	}

	$json = json_encode(array('retorno' => '6. Erro - Restorno estorno automático pedido = ' . $pedido_id, 'response' => $response));
	include('logiPagareChamada.php');



	include('errorMail.php');



	die("Ocorreu um erro inesperado.<br>Por favor, tente novamente.<br>Se o erro persistir favor entrar em contato com a nossa central de atendimento e reportar o erro 528.");
}