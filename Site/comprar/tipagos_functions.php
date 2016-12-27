<?php
require_once('../settings/functions.php');

if ($_ENV['IS_TEST']) {
	$url_ws  = "https://www.ti-pagos.com/bridgeservices/";
} else {
	$url_ws  = "";
}

function pagarPedidoTiPagos($id_pedido, $dados_extra) {
	global $url_ws;

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

			$nCartao = str_replace("-", "", $dados_extra['numCartao']);
			$dadosCartao = $dados_extra['nomeCartao'].";".$nCartao.";".$dados_extra['validadeAno'].$dados_extra['validadeMes'];

			$valorTotal = str_replace(',', '', $rs['VL_TOTAL_PEDIDO_VENDA']);
			$valorTotal = str_replace('.', '', $rs['VL_TOTAL_PEDIDO_VENDA']);

			if($dados_extra['parcelas'] > 1){
				$formaPagamento = "2";
			} else {
				$formaPagamento = "1";
			}


			$dados = array("header" => array("idLoja"=>"7309", 
									 "keyLoja"=>"49994822278418282883",
									 "codProduto"=>"47"),
				   "tipoCapturaCliente"=>"3",
				   "dadosCliente"=>$dadosCartao,
				   "codSeguranca"=>$dados_extra['codSeguranca'],
				   "valor"=>$valorTotal,
				   "formaPagamento"=>$formaPagamento,
				   "qtdeParcelas"=>$dados_extra['parcelas'],
				   "transacaoCapturada"=>true,
				   "descricaoPedido"=>"Teste", "nsuTransacao"=>"GABD5648642");

			$post_data = json_encode($dados);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json')); 
			curl_setopt($ch, CURLOPT_URL, $url_ws."comprar");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$server_output = curl_exec($ch);
			curl_close($ch);

			$resp = json_decode($server_output, true);

			if($resp['retorno']['rc'] == '0'){
				$response = array('success' => true, 'transaction' => $resp);

				$query = 'INSERT INTO MW_PEDIDO_PAGSEGURO (ID_PEDIDO_VENDA, DT_STATUS, CD_STATUS, OBJ_PAGSEGURO) VALUES (?, GETDATE(), ?, ?)';
				$params = array($id_pedido, $response['transaction']['retorno']['rc'], base64_encode(serialize($response['transaction'])));
				executeSQL($mainConnection, $query, $params);
			} else {
				$response = array('success' => false, 'error' => getStatusTiPagos($resp['retorno']['rc']));
			}

		return $response;
}

function getStatusTiPagos($id) {
	$status = array(
		'1' =>  array(
			'description' => 'Erro na Aplicação.'
		),
		'2' => array(
			'description' => 'Id de conexão inválido.'
		),
		'7' => array(
			'description' => 'Tipo de captura do cliente não suportado.'
		),
		'8' => array(
			'description' => 'Transação não autorizada.'
		),
		'9' => array(
			'description' => 'Erro na validação da Loja / Terminal.'
		),
		'10' => array(
			'description' => 'Loja / Canal não encontrado.'
		),
		'11' => array(
			'description' => 'A Loja não está ativa.'
		),
		'12' => array(
			'description' => 'Key da loja inválida.'
		),
		'13' => array(
			'description' => 'Terminal não encontrado'
		),
		'14' => array(
			'description' => 'Terminal não está ativo.'
		),
		'16' => array(
			'description' => 'A loja não está habilitada para a bandeira / tipo de compra recebidos.'
		),
		'17' => array(
			'description' => 'Não foram encontrados adquirentes cadastrados para a loja.'
		),
		'18' => array(
			'description' => 'O adquirente cadastrado para a transação não é suportado por esta versão.'
		),
		'19' => array(
			'description' => 'Transação não localizada para o NSU informado.'
		),
		'20' => array(
			'description' => 'Erro ao enviar transação para o adquirente.'
		),
		'23' => array(
			'description' => 'Tipo de parcelamento inválido.'
		),
		'30' => array(
			'description' => 'Valor de parcelamento inferior ao mínimo permitido.'
		),
		'34' => array(
			'description' => 'Dados do cliente informados inválidos.'
		),
		'42' => array(
			'description' => 'Transação negada.'
		),
		'55' => array(
			'description' => 'Bandeira ou cartão recebido não suportado.'
		),
		'56' => array(
			'description' => 'Não foi possível buscar o plano para o tipo de compra enviado.'
		),
		'57' => array(
			'description' => 'Tipo de compra enviado não parametrizado para a loja.'
		),
		'58' => array(
			'description' => 'Repasse já efetuado para a transação enviada. Não será possível concluir a operação.'
		),
		'59' => array(
			'description' => 'Dados do cliente insuficientes para o tipo de adquirente associado a loja.'
		),
		'62' => array(
			'description' => 'Bandeira não disponível para o plano / produto.'
		),
		'65' => array(
			'description' => 'Tipo de captura do cliente não suportado.'
		),
		'66' => array(
			'description' => 'Data de expiração do cartão não informada.'
		),
		'67' => array(
			'description' => 'Data de expiração do cartão com formato inválido.'
		),
		'68' => array(
			'description' => 'CVV2/CVC2 não informado.'
		),
		'69' => array(
			'description' => 'CVV2/CVC2 com formato inválido.'
		),
		'73' => array(
			'description' => 'Canal sem plano definido.'
		)
	);

	return $status[$id];
}

function estonarPedidoTiPagos($id_pedido, $bank_data = array()) {
	global $url_ws;

	$mainConnection = mainConnection();

	$query = "SELECT OBJ_PAGSEGURO FROM MW_PEDIDO_PAGSEGURO WHERE ID_PEDIDO_VENDA = ? ORDER BY DT_STATUS DESC";
    $params = array($id_pedido);
    $rs = executeSQL($mainConnection, $query, $params, true);

    $transaction = unserialize(base64_decode($rs['OBJ_PAGSEGURO']));


    $dados = array("header" => array("idLoja"=>"7309", 
									 "keyLoja"=>"49994822278418282883",
									 "codProduto"=>"47"),
				   "nsuTipagos"=>$transaction['nsuTipagos'],
				   "valor"=>100);

    $post_data = json_encode($dados);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json')); 
	curl_setopt($ch, CURLOPT_URL, $url_ws."cancelarcompra");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$server_output = curl_exec($ch);
	curl_close($ch);

	$resp = json_decode($server_output, true);

	if($resp['retorno']['rc'] == '0') {
		$response = array('success' => true, 'transaction' => $transaction);

		$query = 'INSERT INTO MW_PEDIDO_PAGSEGURO (ID_PEDIDO_VENDA, DT_STATUS, CD_STATUS, OBJ_PAGSEGURO) VALUES (?, GETDATE(), ?, ?)';
		$params = array($id_pedido, $transaction->status, base64_encode(serialize($transaction)));
		executeSQL($mainConnection, $query, $params);

    } else {

        $response = array('success' => false, 'error' => getStatusTiPagos($resp['retorno']['rc']));

    }

    return $response;
}
function tratarErroTiPagos($error_obj) {
	$nova_msg = $error_obj->getMessage();

	return $nova_msg;
}