<?php
session_start();
require_once('../settings/functions.php');
require_once('../settings/settings.php');

if ($is_manutencao === true) {
	header("Location: manutencao.php");
	die();
}

require('acessoLogado.php');

require_once('../settings/global_functions.php');

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
$params = array($_GET['DS_MERCHANT_ORDER'], $_SESSION['user']);
$rs = executeSQL($mainConnection, $query, $params, true);

// se nao encontrar nenhum registro pode ser usuario tentando acessar
// um pedido de outro usuario ou meio de pagamento que nao bate com o selecionado
if (empty($rs)) {
    header("Location: ".$homeSite);
    die();
} else {
	
	$query = "SELECT M.CD_MEIO_PAGAMENTO, P.IN_SITUACAO
				FROM MW_PEDIDO_VENDA P
				INNER JOIN MW_MEIO_PAGAMENTO M ON M.ID_MEIO_PAGAMENTO = P.ID_MEIO_PAGAMENTO
				WHERE ID_PEDIDO_VENDA = ?";
	$params = array($_GET['DS_MERCHANT_ORDER']);
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	// se o pedido ja esta finalizado enviar para o pedido na minha_conta
	if ($rs['IN_SITUACAO'] == 'F') {
		header("Location: minha_conta.php?pedido={$_GET['DS_MERCHANT_ORDER']}");
		die();
	}

	$response = getReturnTransactionDebito();

	if ($response['success']) {
		
		$result = new stdClass();

		$result->AuthorizeTransactionResult->OrderData->BraspagOrderId = 'Global';
		$result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->BraspagTransactionId = $_GET['DS_MERCHANT_ORDER'];
		$result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->AcquirerTransactionId = $_GET['DS_MERCHANT_ORDER'];
		$result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->AuthorizationCode = $response['transaction']['DS_MERCHANT_MERCHANTSIGNATURE'];
		$result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->PaymentMethod = $_POST['codCartao'];

		require('concretizarCompra.php');

		// se necessario, replica os dados de assinatura e imprime url de redirecionamento
		require('concretizarAssinatura.php');

		$return = ob_get_clean();
	
		header("Location: pagamento_ok.php?pedido={$_GET['DS_MERCHANT_ORDER']}");
		die();
		
	}
	else {
		$descricao_erro = $response['error'] ? $response['error'] : 'Transação não autorizada.';
	}



}