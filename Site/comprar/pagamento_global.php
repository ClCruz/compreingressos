<?php

session_start();
require_once('../settings/functions.php');
require_once('../settings/settings.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// print_r($_SESSION['global_pay']);
//DESCOMENTAR APÓS PRIMEIRO TESTE POIS JA FOI ADICIONADO O ENCONDE NA OUTRA PONTA
// base64_decode(unserialize($_SESSION['global_pay']));
$response =  unserialize(base64_decode($_SESSION['global_pay']));

if ($is_manutencao === true) {
	header("Location: manutencao.php");
	die();
}


require('acessoLogado.php');

require_once('../settings/global_functions.php');

//etapa validada caso seja a requisição de retorno antes do envio para a site do banco 
if(!isset($_SESSION['global_pay'])){
	header("Location: ".$homeSite);
	die();
}else if(!isset($_GET['action'])){
	// print_r(unserialize($response['objSend']));
	// die();
	redirectClientToBank($response['transaction'],unserialize($response['objSend']),$response['config'],$response['id_pedido'],$response['codCartao']);
	die();
}


$mainConnection = mainConnection();

$campanha = get_campanha_etapa(basename(__FILE__, '.php'));


$json = json_encode(array('descricao' => 'pagamento global - dados recebidos', 'post' => $_POST, 'get' => $_GET));
include('logiPagareChamada.php');

$query = "SELECT PP.CD_STATUS
            FROM MW_PEDIDO_VENDA P
            INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = P.ID_CLIENTE
            INNER JOIN MW_PEDIDO_PAGSEGURO PP ON PP.ID_PEDIDO_VENDA = P.ID_PEDIDO_VENDA
            WHERE P.ID_PEDIDO_VENDA = ? AND P.ID_CLIENTE = ?
            ORDER BY PP.DT_STATUS DESC";
$params = array($_GET['id_pedido'], $_SESSION['user']);
$rs = executeSQL($mainConnection, $query, $params, true);

// se nao encontrar nenhum registro pode ser usuario tentando acessar
// um pedido de outro usuario ou meio de pagamento que nao bate com o selecionado
// e se não houver ação de retorno do banco e não existir as informações do pedido será dado como uma transação inválida
if (empty($rs) && !isset($_GET['action']) && $_GET['action'] != 'retBank' && !isset($_GET['DS_MERCHANT_ORDER']) ) {
	header("Location: ".$homeSite);
    die();
} else {
	//caso a etapa de requisição venha do banco realizo a finalização da venda
	
	$query = "SELECT M.CD_MEIO_PAGAMENTO, P.IN_SITUACAO
				FROM MW_PEDIDO_VENDA P
				INNER JOIN MW_MEIO_PAGAMENTO M ON M.ID_MEIO_PAGAMENTO = P.ID_MEIO_PAGAMENTO
				WHERE ID_PEDIDO_VENDA = ?";
	$params = array($_GET['ID_PEDIDO']);
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	// se o pedido ja esta finalizado enviar para o pedido na minha_conta
	if ($rs['IN_SITUACAO'] == 'F') {
		header("Location: minha_conta.php?pedido={$_GET['ID_PEDIDO']}");
		die();
	}
	
	$response = getReturnTransactionDebito();

if ($response['success'] 
&& !is_null($response['transaction']['Ds_AuthorisationCode'])
 && !empty($response['transaction']['Ds_AuthorisationCode']) ) {
		
		$parametros['OrderData']['OrderId'] = $_GET['ID_PEDIDO'];

		$result = new stdClass();
		
		$result->AuthorizeTransactionResult->OrderData->BraspagOrderId = 'Global';
		$result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->BraspagTransactionId = $_GET['ID_PEDIDO'];
		$result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->AcquirerTransactionId = $response['transaction']['Ds_AuthorisationCode'];
		$result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->AuthorizationCode = $response['transaction']['Ds_Order'];
		$result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->PaymentMethod = $_GET['codCartao'];
		
		executeSQL($mainConnection, "insert into mw_log_ipagare values (getdate(), ?, ?)",
		array($_SESSION['user'], json_encode(array('descricao' => 'concretizar compra debito do pedido=' . $parametros['OrderData']['OrderId'], 'stdClass'=>$result, 'response' => $response, 'POST'=>$_POST)))
	);
	require('concretizarCompra.php');
	
	// se necessario, replica os dados de assinatura e imprime url de redirecionamento
	require('concretizarAssinatura.php');
	
	$return = ob_get_clean();
	
	header("Location: pagamento_ok.php?pedido={$_GET['ID_PEDIDO']}");
	die();
	
}
else{
	//*******************CANCELAMENTO DE COMPRA ***********************/

		$query = "SELECT QT_HR_ANTECED FROM MW_MEIO_PAGAMENTO WHERE CD_MEIO_PAGAMENTO = ?";
		$params = array($rs['CD_MEIO_PAGAMENTO']);
		$rsExt = executeSQL($mainConnection, $query, $params, true);
		$horas_antes_apresentacao_pagamento = is_null($rsExt['QT_HR_ANTECED']) ?  1 : $rsExt['QT_HR_ANTECED'];

		extenderTempo($horas_antes_apresentacao_pagamento * 10);

				
		$query = "UPDATE MW_RESERVA SET ID_SESSION = ? WHERE ID_SESSION = ?";
		$params = array(session_id(), $_GET['ID_PEDIDO']);
		executeSQL($mainConnection, $query, $params);

		// print_r($params);
		header("Location: etapa5.php");
		die();
		// 	$result = cancelarCompra($response['objSend'],$response['config']);
		// 	if(!$result['success']){
		// 		$msg = 'Falha ao enviar solicitação de cancelamento';
								
		// 		$json = json_encode(array('descricao' => 'pagamento global - dados recebidos (FALHA) cancelamento ', 'error' => $msg));
		// 		include('logiPagareChamada.php');
		// 	}else{
		// 		$msg = 'Solicitação de cancelamento enviada com sucesso';
		// 		$json = json_encode(array('descricao' => 'pagamento global - dados recebidos (SUCESSO) cancelamento ', 'msg' => $msg));
		// 		include('logiPagareChamada.php');	
		// 	}
		// //*******************FIM CANCELAMENTO DE COMPRA ***********************/

		// header("Location: pagamento_cancelado.php?manualmente=1&global=1");
        // die();
	}


}