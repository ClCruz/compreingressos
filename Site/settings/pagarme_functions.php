<?php
require_once('../settings/functions.php');

require '../settings/pagarme/Pagarme.php';

if ($_ENV['IS_TEST']) {
	Pagarme::setApiKey("ak_test_rh88QdkXXKpFQhTkVlCm63zrw3kQgJ");
	$postback_url = 'http://homolog.compreingressos.com/comprar/pagarme_receiver.php';
} else {
	Pagarme::setApiKey("ak_live_5aYKGG3AyIb8cvv7Tq44q7ZasJzPl8");
	$postback_url = 'https://compra.compreingressos.com/comprar/pagarme_receiver.php';
}

function pagarPedidoPagarme($id_pedido, $dados_extra) {
	global $postback_url;

	$mainConnection = mainConnection();

	$query = "SELECT
				P.ID_PEDIDO_VENDA,
				C.CD_EMAIL_LOGIN,
				ISNULL(P.VL_FRETE, 0) AS VL_FRETE,
				P.VL_TOTAL_PEDIDO_VENDA,
				P.ID_IP,
				C.ID_CLIENTE,
				C.CD_CPF,
				C.CD_RG,
				C.DS_NOME,
				C.DS_SOBRENOME,
				CONVERT(VARCHAR(10),DT_NASCIMENTO, 110) AS DT_NASCIMENTO,
				ISNULL(C.IN_SEXO, 'M') AS IN_SEXO,
				C.DS_ENDERECO,
				C.NR_ENDERECO,
				C.DS_COMPL_ENDERECO,
				C.DS_BAIRRO,
				C.DS_CIDADE,
				E.SG_ESTADO,
				C.CD_CEP,
				C.DS_DDD_TELEFONE,
				C.DS_TELEFONE,
				C.DS_DDD_CELULAR,
				C.DS_CELULAR,
				P.NR_PARCELAS_PGTO,
				P.CD_BIN_CARTAO,
				MP.CD_MEIO_PAGAMENTO,

				P.IN_RETIRA_ENTREGA,
				P.DS_CUIDADOS_DE,
				P.NM_CLIENTE_VOUCHER,
				P.DS_EMAIL_VOUCHER,
				P.DS_ENDERECO_ENTREGA,
				P.NR_ENDERECO_ENTREGA,
				P.DS_COMPL_ENDERECO_ENTREGA,
				P.DS_BAIRRO_ENTREGA,
				P.DS_CIDADE_ENTREGA,
				E2.SG_ESTADO AS SG_ESTADO_ENTREGA,
				P.CD_CEP_ENTREGA,

				C.ID_DOC_ESTRANGEIRO,
				P.NM_TITULAR_CARTAO
			FROM MW_PEDIDO_VENDA P
			INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = P.ID_CLIENTE
			INNER JOIN MW_ESTADO E ON E.ID_ESTADO = C.ID_ESTADO
			LEFT JOIN MW_ESTADO E2 ON E2.ID_ESTADO = P.ID_ESTADO
			LEFT JOIN MW_MEIO_PAGAMENTO MP ON MP.ID_MEIO_PAGAMENTO = P.ID_MEIO_PAGAMENTO
			WHERE P.ID_PEDIDO_VENDA = ?";

	$rs = executeSQL($mainConnection, $query, array($id_pedido), true);

	foreach($rs as $key => $val) {
		$rs[$key] = utf8_encode($val);
	}

	$transaction_data = array(
		"metadata" => array("id_pedido_venda" => $id_pedido),

		"amount" => number_format($rs['VL_TOTAL_PEDIDO_VENDA'] * 100, 0, '', ''),

		"customer" => array(
			"name" => $rs['DS_NOME'].' '.$rs['DS_SOBRENOME'],
			"document_number" => $rs['CD_CPF'],
			"email" => trim($rs['CD_EMAIL_LOGIN']),
			"sex" => $rs['IN_SEXO'],
			"born_at" => $rs['DT_NASCIMENTO'],
			"address" => array(
				"street" => $rs['DS_ENDERECO'],
				"neighborhood" => $rs['DS_BAIRRO'],
				"zipcode" => $rs['CD_CEP'],
				"street_number" => $rs['NR_ENDERECO'],
				"complementary" => $rs['DS_COMPL_ENDERECO'],
				"city" => $rs['DS_CIDADE'],
				"state" => $rs['SG_ESTADO']
			),
			"phone" => array(
				"ddd" => $rs['DS_DDD_TELEFONE'],
				"number" => $rs['DS_TELEFONE']
			)
		),

		"postback_url" => $postback_url
	);

	// credit card
	if ($rs['CD_MEIO_PAGAMENTO'] == 910) {
		$transaction_data = array_merge($transaction_data, array(
			"card_hash" => $dados_extra["card_hash"],
			"installments" => $rs['NR_PARCELAS_PGTO'],
			"payment_method" => "credit_card",
			"soft_descriptor" => NULL,
			"capture" => true,
			"async" => false
		));
	}
	// boleto
	elseif ($rs['CD_MEIO_PAGAMENTO'] == 911) {
		$transaction_data = array_merge($transaction_data, array(
			"payment_method" => "boleto"
		));
	}
	// erro
	else return false;

	try {

		$transaction = new PagarMe_Transaction($transaction_data);
		$transaction->charge();

		$response = array('success' => true, 'transaction' => $transaction);

		$query = 'INSERT INTO MW_PEDIDO_PAGSEGURO (ID_PEDIDO_VENDA, DT_STATUS, CD_STATUS, OBJ_PAGSEGURO) VALUES (?, GETDATE(), ?, ?)';
		$params = array($id_pedido, $transaction->status, base64_encode(serialize($transaction)));
		executeSQL($mainConnection, $query, $params);

	} catch (Exception $e) {
		$response = array('success' => false, 'error' => tratarErroPagarme($e, $id_pedido));
	}

	return $response;
}

function getStatusPagarme($id) {
	$status = array(
		'processing' => array(
			'name' => 'processando',
			'description' => 'transação sendo processada'
		),
		'authorized' => array(
			'name' => 'autorizado',
			'description' => 'transação autorizada. Cliente possui saldo na conta e este valor foi reservado para futura captura, que deve acontecer em no máximo 5 dias. Caso a transação não seja capturada, a autorização é cancelada automaticamente'
		),
		'paid' => array(
			'name' => 'pago',
			'description' => 'transação paga (autorizada e capturada)'
		),
		'refunded' => array(
			'name' => 'estornado',
			'description' => 'transação estornada'
		),
		'waiting_payment' => array(
			'name' => 'aguardando pagamento',
			'description' => 'transação aguardando pagamento (status para transações criadas com boleto bancário)'
		),
		'pending_refund' => array(
			'name' => 'aguardando estorno',
			'description' => 'transação paga com boleto aguardando para ser estornada'
		),
		'refused' => array(
			'name' => 'recusado',
			'description' => 'transação não autorizada'
		),
		'chargedback' => array(
			'name' => 'contestado',
			'description' => 'transação sofreu chargeback'
		)
	);

	return $status[$id];
}

function getNotificationPagarme($notificationCode) {

	try {
		$response = PagarMe_Transaction::findById($notificationCode);

		$response = array('success' => true, 'transaction' => $response);

	} catch (Exception $e) {
		$response = array('success' => false, 'error' => tratarErroPagarme($e));
	}

	return $response;
}

function estonarPedidoPagarme($id_pedido, $bank_data = array()) {

	$mainConnection = mainConnection();

	$query = "SELECT OBJ_PAGSEGURO FROM MW_PEDIDO_PAGSEGURO WHERE ID_PEDIDO_VENDA = ? ORDER BY DT_STATUS DESC";
    $params = array($id_pedido);
    $rs = executeSQL($mainConnection, $query, $params, true);

    $transaction = unserialize(base64_decode($rs['OBJ_PAGSEGURO']));

	try {

		if (empty($bank_data))
        	$transaction->refund();
        else
        	$transaction->refund($bank_data);

		$response = array('success' => true, 'transaction' => $transaction);

		$query = 'INSERT INTO MW_PEDIDO_PAGSEGURO (ID_PEDIDO_VENDA, DT_STATUS, CD_STATUS, OBJ_PAGSEGURO) VALUES (?, GETDATE(), ?, ?)';
		$params = array($id_pedido, $transaction->status, base64_encode(serialize($transaction)));
		executeSQL($mainConnection, $query, $params);

    } catch (Exception $e) {

        $response = array('success' => false, 'error' => tratarErroPagarme($e, $id_pedido));

    }

    return $response;
}

function tratarErroPagarme($error_obj, $id_pedido) {

	$nova_msg = $error_obj->getMessage();

	if ($id_pedido) {
		$mainConnection = mainConnection();

		executeSQL($mainConnection, "insert into mw_log_ipagare values (getdate(), ?, ?)",
	        array(NULL, json_encode(array('descricao' => 'erro pagarme pedido ' . $id_pedido, 'error' => $nova_msg)))
	    );
	}

	return $nova_msg;
}