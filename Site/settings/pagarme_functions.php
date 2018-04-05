<?php
require_once('../settings/functions.php');

require '../settings/pagarme/Pagarme.php';

if ($_ENV['IS_TEST']) {
	//ticketpay : ak_test_183DNskQiE3q7uBAA8UQjkSvENOEdY
	//compreingressos: ak_test_rh88QdkXXKpFQhTkVlCm63zrw3kQgJ
	Pagarme::setApiKey("ak_test_183DNskQiE3q7uBAA8UQjkSvENOEdY");
	$postback_url = 'http://homolog.compreingressos.com/comprar/pagarme_receiver.php';
} else {
	//ticketpay: ak_live_pcYp3eGXxpOBHqViOLfBQ61NQ4433y
	//compreingressos: ak_live_5aYKGG3AyIb8cvv7Tq44q7ZasJzPl8
	Pagarme::setApiKey("ak_live_pcYp3eGXxpOBHqViOLfBQ61NQ4433y");
	$postback_url = 'https://compra.compreingressos.com/comprar/pagarme_receiver.php';
}

function pagarPedidoPagarme($id_pedido, $dados_extra) {
	global $postback_url;
	global $transaction;
	global $response;

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

	$amount = number_format($rs['VL_TOTAL_PEDIDO_VENDA'] * 100, 0, '', '');

	$transaction_data = array(
		"metadata" => array("id_pedido_venda" => $id_pedido),

		"amount" => $amount,

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
	$payment_method = "";
	// credit card
	if ($rs['CD_MEIO_PAGAMENTO'] == 910) {
		$payment_method = "credit_card";
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
		$payment_method = "boleto";
		$transaction_data = array_merge($transaction_data, array(
			"payment_method" => "boleto"
		));
	}
	// erro
	else return false;
	$split = consultarSplitPagarme($id_pedido, "web", $payment_method, $amount);

	if (is_array($split)) {
		$transaction_data = array_merge($transaction_data, array(
			"split_rules" => $split
		));
	}
	try {
		$transaction = new PagarMe_Transaction($transaction_data);
		$transaction->charge();
		$response = array('success' => true, 'transaction' => $transaction);
	} catch (Exception $e) {
		executeSQL(mainConnection(), "insert into tbLogAux ( dt_log, descricao) values (getdate(), ?)", array(session_id(). " - " . " - " . "SPLIT: " . print_r($split, true) ));

		error_log("Erro no pagar.me: " . $e->getMessage());
		$response = array('success' => false, 'error' => tratarErroPagarme($e, $id_pedido));
	}

	$query = 'INSERT INTO MW_PEDIDO_PAGSEGURO (ID_PEDIDO_VENDA, DT_STATUS, CD_STATUS, OBJ_PAGSEGURO) VALUES (?, GETDATE(), ?, ?)';
	$params = array($id_pedido, $transaction->status, base64_encode(serialize($transaction)));
	executeSQL($mainConnection, $query, $params);
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

function salvarRecebedorPagarme($data) {	

	$mainConnection = mainConnection();
 
	$recipient = new PagarMe_Recipient(array(
		"anticipatable_volume_percentage" => 100, 
		"automatic_anticipation_enabled" => false, 
		"transfer_enabled" => false,
		"transfer_interval" => "monthly",
		"transfer_day" => array_key_exists("transfer_day", $data) && isset($data["transfer_day"]) && $data["transfer_day"]!="" && !empty($data["transfer_day"]) ? $data["transfer_day"] : 0,
	    "bank_account" => array(
	    	"bank_code" => $data["banco"],
	        "agencia" => $data["agencia"],
	        "agencia_dv" => array_key_exists("dv_agencia", $data) && isset($data["dv_agencia"]) && $data["dv_agencia"]!="" && !empty($data["dv_agencia"]) ? $data["dv_agencia"] : null,
	        "conta" => $data["conta_bancaria"],
	        "type" => $data["tipo"] == "CC" ? "conta_corrente" : "conta_poupanca",
	        "conta_dv" => $data["dv_conta_bancaria"],
	        "document_number" => $data["cpf_cnpj"],
	        "legal_name" => $data["razao_social"]
	    )
	));

    return $recipient->create();
}

function atualizarRecebedorPagarme($data, $id) {
	$recipient = PagarMe_Recipient::findById($id);

	$recipient->setAnticipatableVolumePercentage(100);

	$recipient->setTransferDay(array_key_exists("transfer_day", $data) && isset($data["transfer_day"]) && $data["transfer_day"]!="" && !empty($data["transfer_day"]) ? $data["transfer_day"] : 0);

    $bank_account = new Pagarme_Bank_Account(array(
	    	"bank_code" => $data["banco"],
	        "agencia" => $data["agencia"],
	        "agencia_dv" => array_key_exists("dv_agencia", $data) && isset($data["dv_agencia"]) && $data["dv_agencia"]!="" && !empty($data["dv_agencia"]) ? $data["dv_agencia"] : null,
	        "conta" => $data["conta_bancaria"],
	        "type" => $data["tipo"] == "CC" ? "conta_corrente" : "conta_poupanca",
	        "conta_dv" => $data["dv_conta_bancaria"],
	        "document_number" => $data["cpf_cnpj"],
	        "legal_name" => $data["razao_social"]
	    )
	);
    $bank_account->create();

    $recipient->setBankAccountId($bank_account->getId());

    $recipient->save();
}

function consultarSplitPagarme($pedido, $where, $payment_method, $amount) {
	$mainConnection = mainConnection();

	$query = "select distinct e.CodPeca, e.id_base
			  from mw_pedido_venda pv
			  inner join mw_item_pedido_venda ipv on ipv.id_pedido_venda = pv.id_pedido_venda
			  inner join mw_apresentacao a on a.id_apresentacao = ipv.id_apresentacao
			  inner join mw_evento e on e.id_evento = a.id_evento
			  where pv.id_pedido_venda = ?";
	$param = array($pedido);
	$stmt = executeSQL($mainConnection, $query, $param, true);

	$query = "SELECT DISTINCT r.recipient_id
	,rs.nr_percentual_split
	,rs.liable
	,rs.charge_processing_fee
	,rs.percentage_credit_web
	,rs.percentage_debit_web
	,rs.percentage_boleto_web
	,rs.percentage_credit_box_office
	,rs.percentage_debit_box_office
	,(CASE r.cd_cpf_cnpj WHEN '11665394000113' THEN 1 ELSE 0 END) IsTicketPay
	FROM tabPeca tb
	INNER JOIN CI_MIDDLEWAY..mw_evento e ON tb.CodPeca=e.CodPeca
	INNER JOIN CI_MIDDLEWAY..mw_produtor p ON p.id_produtor = tb.id_produtor and p.in_ativo=1
	INNER JOIN CI_MIDDLEWAY..mw_regra_split rs ON rs.id_produtor = p.id_produtor and rs.id_evento=e.id_evento
	INNER JOIN CI_MIDDLEWAY..mw_recebedor r ON rs.id_recebedor = r.id_recebedor and r.in_ativo=1
	WHERE tb.CodPeca = ? and rs.in_ativo = 1
	ORDER BY (CASE r.cd_cpf_cnpj WHEN '11665394000113' THEN 1 ELSE 0 END)";

	$conn = getConnection($stmt["id_base"]);
	$param = array($stmt["CodPeca"]);
	$result = executeSQL($conn, $query, $param);

	if(!hasRows($result))
		return null;


	$count = hasRows($result, true);
	$i = 0;
	$amountUsed = 0;
	$amount = $amount/100;

	$split = array();
	while($rs = fetchResult($result)) {
		$i = $i+1;
		$perToUse = 0;
		$amountToUse = 0;

		switch ($where) {
			case "web":
				switch ($payment_method) {
					case "credit":
					case "credit_card":
							$perToUse = $rs["percentage_credit_web"];
						break;
					case "boleto":
						$perToUse = $rs["percentage_boleto_web"];
						break;
					case "debit":
					case "debit_card":
						$perToUse = $rs["percentage_debit_web"];
						break;							
				}
				break;
			case "bilheteria":
				switch ($payment_method) {
					case "credit":
					case "credit_card":
							$perToUse = $rs["percentage_credit_box_office"];
						break;
					case "debit":
					case "debit_card":
						$perToUse = $rs["percentage_debit_box_office"];
						break;							
				}
				break;
		}

		if ($count==$i) {
			$amoutToUse = round($amount-$amountUsed, 2);
		}
		else {
			$amoutToUse = round($amount*($perToUse/100), 2);
		}

		$amountUsed = $amountUsed + $amoutToUse;

		//error_log("perToUse: " . $perToUse);
		//error_log("amoutToUse: " . $amoutToUse);

		$split[] = array(
			"recipient_id" => $rs["recipient_id"],
			// "percentage" => $perToUse,
			"amount" => $amoutToUse*100,
	    	"liable" => $rs["liable"],
	    	"charge_processing_fee" => $rs["charge_processing_fee"]);
	}
	//error_log("Split: " . print_r($split, true));
	return $split;
}

function getDatePagarMe($value) {
	$ret = "";
	if ($value!="") {
		$ret =  (string)(strtotime($value)*1000);
	}
	return $ret;
}

function getAmountPagarMe($value) {
	$ret = $value*100;
	return $ret;
}

function consultarExtratoRecebedorPagarme($recipient_id, $status, $start_date, $end_date, $count, $evento) {
	$start_date_modified = "";
	$end_date_modified = "";

	if ($start_date!="")
	{
		$start_dateSplit = explode("/", $start_date);
		$start_date_modified = $start_dateSplit[2] . "-" . $start_dateSplit[1] . "-" . $start_dateSplit[0];
	}

	if ($end_date!="")
	{
		$end_dateSplit = explode("/", $end_date);
		$end_date_modified = $end_dateSplit[2] . "-" . $end_dateSplit[1] . "-" . $end_dateSplit[0];
	}

	$balance_operations = PagarMe_Recipient::getOperationHistory($recipient_id, $status, $count, getDatePagarMe($start_date_modified), getDatePagarMe($end_date_modified));
	$json = array();
	foreach ($balance_operations as $value) {
		$query = "SELECT DISTINCT e.id_evento, e.ds_evento
		FROM mw_pedido_venda pv 
		INNER JOIN mw_item_pedido_venda ipv ON pv.id_pedido_venda=ipv.id_pedido_venda
		INNER JOIN mw_apresentacao ipva ON ipv.id_apresentacao=ipva.id_apresentacao
		INNER JOIN mw_evento e ON ipva.id_evento=e.id_evento
		WHERE pv.id_pedido_ipagare='Pagar.me' AND cd_numero_autorizacao=?";
	
		$param = array($value["movement_object"]["transaction_id"]);
		$result = executeSQL(mainConnection(), $query, $param,true);

		$split = array();
		$id_evento = $result["id_evento"];
		$ds_evento = $result["ds_evento"];

		if ($result["id_evento"] == 0 || $result["ds_evento"] == null) {
			$query = "SELECT DISTINCT e.id_evento, e.ds_evento
			FROM mw_pedido_venda_gateway pvg
			INNER JOIN mw_evento e ON e.CodPeca=pvg.CodPeca AND e.id_base=pvg.id_base
			WHERE TransacaoGateway=?";
		
			$param = array($value["movement_object"]["transaction_id"]);
			$result2 = executeSQL(mainConnection(), $query, $param,true);

			$id_evento = $result2["id_evento"];
			$ds_evento = $result2["ds_evento"] == null ? "Bilheteria" : $result2["ds_evento"];
		}
		
		$ds_evento = $ds_evento == null || $ds_evento == "" ? "Bilheteria" : $ds_evento;
		$id_evento = $ds_evento == "Bilheteria" ? "0" : (string)$id_evento;
		$letMePass = false;

		// error_log("evento: " . $evento);
		// error_log("id_evento: " . $id_evento);
		// error_log("ds_evento: " . $ds_evento);
		
		if ($evento == "-1") {
			$letMePass = true;
		}
		else {
			if ($evento == ((string)$id_evento)) {
				$letMePass = true;
			}
		}
		if ($letMePass) {
			$json[] = array("amount"=> $value["amount"]
				,"fee" => $value["fee"]
				,"transaction_id" => $value["movement_object"]["transaction_id"]
				,"payment_date" => $value["movement_object"]["payment_date"]
				,"type" => $value["movement_object"]["type"]
				,"payment_method" => $value["movement_object"]["payment_method"]
				,"date_created" => $value["date_created"]
				,"id_evento" => $id_evento
				,"ds_evento" => $ds_evento
			);		
		}
	}
	//error_log("json.. ".print_r($json,true));
	// error_log("result is....");
	// error_log($balance_operations->__toJSON(true));
	//return $balance_operations->__toJSON(true);
	return $json;
}

function consultarSaldoRecebedorPagarme($recipient_id) {
	$balance_operations = PagarMe_Recipient::findSaldoByRecipientId($recipient_id);
	return $balance_operations;
}

function consultarTaxaSaque() {
	$response = PagarMe_Calls::getCompany();

	$ret = array("credito_em_conta"=> $response["pricing"]["transfers"]["credito_em_conta"]
	,"ted" => $response["pricing"]["transfers"]["ted"]
	,"doc" => $response["pricing"]["transfers"]["doc"]);

//	error_log(print_r($ret, true));

	return $ret;
}

function consultarTransferencias($recipient_id) {
	$response = PagarMe_Calls::listTransfers($recipient_id);

	$json = array();
	
	foreach ($response as $value) {
		$json[] = array("amount"=> $value["amount"]
		,"type" => $value["type"]
		,"status" => $value["status"]
		,"fee" => $value["fee"]
		,"funding_date" => $value["funding_date"]
		,"funding_estimated_date" => $value["funding_estimated_date"]
		,"date_created" => $value["date_created"]
		);
	}

	return $json;
}
function consultarAntecipaveis($recipient_id) {
	$response = PagarMe_Calls::listAnticipations($recipient_id);
	$json = array();
	
	foreach ($response as $value) {
		$json[] = array("amount"=> $value["amount"]
		,"anticipation_fee" => $value["anticipation_fee"]
		,"date_created" => $value["date_created"]
		,"fee" => $value["fee"]
		,"payment_date" => $value["payment_date"]
		,"status" => $value["status"]
		,"timeframe" => $value["timeframe"]
		,"type" => $value["type"]
		);
	}

	return $json;
}

function efetuarSaquePagarme($recipient_id, $amount) {
	try {
		$amount = getAmountPagarMe($amount);
		// error_log("amount".$amount);
		// error_log("recipient_id".$recipient_id);
		$request = new PagarMe_Request("/transfers", "POST");
		$request->setParameters(array("amount" => $amount, "recipient_id" => $recipient_id));
		$response = $request->run();
		// error_log("Saque..".print_r($response, true));
		return array("status" => "success", "msg" => "A Transação de Saque foi efetuada com sucesso!", "response"=> $response);
	} catch (Exception $e) {
		return array("status" => "error", "msg" => $e->getMessage());
	}
}

function getTransaction($transaction_id) {
	try {
		$ret = PagarMe_Recipient::getTransaction($transaction_id);
		//error_log($ret);
		return $ret;
	} catch (Exception $e) {
		return array("status" => "error", "msg" => $e->getMessage());
	}
}

function verificaMinimoMaximoAntecipacao($recipient_id,  $payment_date, $timeframe) {
	try {
		$dateSplit = explode("/", $payment_date);
		$date_modified = getDatePagarMe($dateSplit[2] . "-" . $dateSplit[1] . "-" . $dateSplit[0]);
		//error_log("recipient_id ". $recipient_id );
		//error_log("date_modified ". $date_modified );
		//error_log("timeframe ". $timeframe );
		$ret = PagarMe_Recipient::getLimits($recipient_id, $date_modified, $timeframe);
		return $ret;
	} catch (Exception $e) {
		return array("status" => "error", "msg" => $e->getMessage());
	}
}

function verificarAntecipacao($recipient_id, $amount, $payment_date, $timeframe) {
	try {
		$dateSplit = explode("/", $payment_date);
		$date_modified = getDatePagarMe($dateSplit[2] . "-" . $dateSplit[1] . "-" . $dateSplit[0]);
		$stringAux = "recipient_id = ".$recipient_id;
		$stringAux = $stringAux . " amount = ".getAmountPagarMe($amount);
		$stringAux = $stringAux . " payment_date = ".$date_modified;
		$stringAux = $stringAux . " timeframe = ".$timeframe;
		
		$ret = PagarMe_Recipient::getResumo($recipient_id, getAmountPagarMe($amount), $date_modified, $timeframe);

//		error_log( print_r( $ret, true ) );
		return $ret->__toJSON(true);
	} catch (Exception $e) {
		return array("status" => "error", "msg" => $e->getMessage());
	}
}

function efetuarAntecipacaoPagarme($recipient_id, $amount, $payment_date, $timeframe) {
	try {
		$dateSplit = explode("/", $payment_date);
		$date_modified = getDatePagarMe($dateSplit[2] . "-" . $dateSplit[1] . "-" . $dateSplit[0]);

		$request = new PagarMe_Request("/recipients/$recipient_id/bulk_anticipations", "POST");
		$request->setParameters(array(
			"payment_date" => $date_modified,
			"timeframe" => $timeframe,
			"requested_amount" => getAmountPagarMe($amount),
			"build" => false
		));
		$response = $request->run();
		return array("status" => "success", "msg" => "A Transação de Antecipação foi efetuada com sucesso!");
	} catch (Exception $e) {
		return array("status" => "error", "msg" => $e->getMessage());
	}
}
?>