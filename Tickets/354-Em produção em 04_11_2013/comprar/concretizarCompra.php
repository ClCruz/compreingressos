<?php
$pedido_id = $parametros['OrderData']['OrderId'];
$braspag_id = $result->AuthorizeTransactionResult->OrderData->BraspagOrderId;
$braspag_transaction_id = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->BraspagTransactionId;
$transaction_id = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->AcquirerTransactionId;
$transaction_auth = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->AuthorizationCode;
$meio_pagamento = $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->PaymentMethod;

$query = 'SELECT ID_MEIO_PAGAMENTO
			 FROM MW_MEIO_PAGAMENTO MP
			 WHERE CD_MEIO_PAGAMENTO = ?';
$params = array($meio_pagamento);
$id_meio_pagamento = executeSQL($mainConnection, $query, $params, true);
$id_meio_pagamento = $id_meio_pagamento['ID_MEIO_PAGAMENTO'];

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
            ID_TRANSACTION_BRASPAG = ?,
            ID_MEIO_PAGAMENTO = ?
			WHERE ID_PEDIDO_VENDA = ?
			AND ID_CLIENTE = ?';
executeSQL($mainConnection, $query, array($braspag_transaction_id, $id_meio_pagamento, $pedido_id, $dados['ID_CLIENTE']));

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

	$json = json_encode(array('retorno' => '6. OK - Pedido = ' . $pedido_id));
	include('logiPagareChamada.php');
	
	ob_clean();

	$query = "SELECT R.ID_RESERVA, R.ID_APRESENTACAO, R.ID_APRESENTACAO_BILHETE, R.DS_LOCALIZACAO AS DS_CADEIRA,
					R.DS_SETOR, E.ID_EVENTO, E.DS_EVENTO, ISNULL(LE.DS_LOCAL_EVENTO, B.DS_NOME_TEATRO) DS_NOME_TEATRO,
					CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103) DT_APRESENTACAO, A.HR_APRESENTACAO,
					AB.VL_LIQUIDO_INGRESSO, AB.DS_TIPO_BILHETE, E.ID_BASE, A.CodApresentacao, R.CodVenda
				FROM MW_ITEM_PEDIDO_VENDA R
				INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO AND A.IN_ATIVO = '1'
				INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = '1'
				INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE
				INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE AND AB.IN_ATIVO = '1'
				LEFT JOIN MW_LOCAL_EVENTO LE ON E.ID_LOCAL_EVENTO = LE.ID_LOCAL_EVENTO
				WHERE R.ID_PEDIDO_VENDA = ?
				ORDER BY E.DS_EVENTO, R.ID_APRESENTACAO, R.DS_LOCALIZACAO";
	$params = array($pedido_id);
	$result = executeSQL($mainConnection, $query, $params);

	$queryServicos = "SELECT TOP 1 TC.IN_TAXA_POR_PEDIDO
					FROM MW_TAXA_CONVENIENCIA TC
					INNER JOIN MW_PEDIDO_VENDA PV ON PV.DT_PEDIDO_VENDA >= TC.DT_INICIO_VIGENCIA
					INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
					INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = IPV.ID_APRESENTACAO AND A.ID_EVENTO = TC.ID_EVENTO
					WHERE PV.ID_PEDIDO_VENDA = ?
					ORDER BY TC.DT_INICIO_VIGENCIA DESC";
	$rsServicos = executeSQL($mainConnection, $queryServicos, array($pedido_id), true);

	$itensPedido = array();
	$i = -1;
	while ($itens = fetchResult($result)) {
	    $i++;

	    if ($i == 0) {
	        if ($rsServicos['IN_TAXA_POR_PEDIDO'] == 'S') {
	            $valorConveniencia = obterValorServico($itens['ID_APRESENTACAO_BILHETE'], true, $pedido_id);

	            $itensPedido[$i]['descricao_item'] = 'Serviço';
	            $itensPedido[$i]['valor_item'] = $valorConveniencia;

	            $valorConveniencia = 0;
	            $i++;
	        } else {
	            $valorConveniencia = obterValorServico($itens['ID_APRESENTACAO_BILHETE'], false, $pedido_id);
	        }
	    } else {
	        $valorConveniencia = obterValorServico($itens['ID_APRESENTACAO_BILHETE'], false, $pedido_id);
	    }

	    $itensPedido[$i]['descricao_item']['evento'] = utf8_encode($itens['DS_EVENTO']);
	    $itensPedido[$i]['descricao_item']['data'] = $itens['DT_APRESENTACAO'];
	    $itensPedido[$i]['descricao_item']['hora'] = $itens['HR_APRESENTACAO'];
	    $itensPedido[$i]['descricao_item']['teatro'] = utf8_encode($itens['DS_NOME_TEATRO']);
	    $itensPedido[$i]['descricao_item']['setor'] = utf8_encode($itens['DS_SETOR']);
	    $itensPedido[$i]['descricao_item']['cadeira'] = utf8_encode($itens['DS_CADEIRA']);
	    $itensPedido[$i]['descricao_item']['bilhete'] = utf8_encode($itens['DS_TIPO_BILHETE']);

	    $itensPedido[$i]['valor_item'] = ($itens['VL_LIQUIDO_INGRESSO'] + $valorConveniencia);
	    $itensPedido[$i]['id_base'] = $itens['ID_BASE'];
	    $itensPedido[$i]['CodApresentacao'] = $itens['CodApresentacao'];
	    $itensPedido[$i]['CodVenda'] = $itens['CodVenda'];
	}
} else {
	include('errorMail.php');
}

$json = json_encode(array('descricao' => '7. envio do email de sucesso - pedido ' . $pedido_id));
include('logiPagareChamada.php');

include('successMail.php');