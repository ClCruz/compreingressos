<?php

require_once('../settings/functions.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

/**
* @author Ítalo Patrício
* Data de Criação: 15/11/2017  
* Descrição: Integração com o gateway de pagamento da Global.
*/
//https://www.globalpaymentsinc.com/-/media/global-payments/files/brazil/manual-e-commerce-pt-16.pdf
/*
trataPeticion 
 
Este método será utilizado nas seguintes condições: 
 
- Solicitações de Autorização 
- Solicitações de Captura da transação 
- Solicitações de Cancelamento 
- Resposta de Autorização 
- Resposta de Captura da transação 
- Resposta de Cancelamento 
- Consulta DCC (conversor de moedas) 

*/

/*
Chave  de acesso: qwertyasdf0123456789
*/



function getConfigGlobal($id) {
	$mainConnection = mainConnection();
	
	$rs_global = executeSQL($mainConnection, "SELECT
												ID_GATEWAY_PAGAMENTO,
												DS_URL,
												CD_GATEWAY_PAGAMENTO,
												DS_URL_CONSULTA,
												CD_KEY_GATEWAY_PAGAMENTO,
												DS_URL_RETORNO
											FROM MW_GATEWAY_PAGAMENTO
											WHERE (ID_GATEWAY = 9 AND IN_ATIVO = 1 AND ? IS NULL)
											OR ID_GATEWAY_PAGAMENTO = ?", array($id, $id), true);

return array(
	'id' => $rs_global['ID_GATEWAY_PAGAMENTO'],
	
	'transaction_url' => $rs_global['DS_URL'],
	'merchantCode' => $rs_global['CD_GATEWAY_PAGAMENTO'],
	'merchantKey' => $rs_global['CD_KEY_GATEWAY_PAGAMENTO'],
	'returnUrl' => $rs_global['DS_URL_RETORNO']
);
}		


// $chaveAcesso = 'qwertyasdf0123456789';



class OptionAction{
	const AUTORIZAR = 'autorizar';
	const CAPTURAR = 'capturar';
	const CONSULTAR = 'consultar';
	const CANCELAR = 'cancelar'; 
}

class trataPeticion{
	
		/**
		 * Type: Numérico
		 * Obrigatório  Valor total da transação de Compra. Exemplos:
		 *	- Para uma transação de R$100,00 a loja deve enviar este campo
		 *	 com 10000.
		 *	- Para uma transação de R$0,01 a loja deve enviar este campo
		 *	com 1.
	 	 *	- Para uma transação de R$0,10 a loja deve enviar este campo
		 *	com 10.
		 */	
		public $DS_MERCHANT_AMOUNT; 
		/**
		 * Type: Alfanumérico
		 * Obrigatório Número do Pedido. Os 4 primeiros dígitos devem
		 * ser numéricos. Este campo não pode ser repetido
		 */
		public $DS_MERCHANT_ORDER;  
		/**
		 * Type: Numérico
		 * Obrigatório Número do estabelecimento (MID) definido pela
		 * Global Payments.
		 */
		public $DS_MERCHANT_MERCHANTCODE; 
		/**
		 * Type: Numérico
		 * Obrigatório  Número de Terminal que será definido pela Global
		 * Payments.
		 */
		public $DS_MERCHANT_TERMINAL; 
		/** 
		 * Type: Numérico
		 * Obrigatório
		 * Moeda da transação deverá ser 986 para o Brasil
		*/
		public $DS_MERCHANT_CURRENCY = 986; 
		/**
		 * Obrigatório Número do cartão
		 */
		public $DS_MERCHANT_PAN; 
		/**
		 * Obrigatório  Data de Vencimento do cartão. A formatação
	 	 * deverá ser AAMM onde AA igual aos 2 últimos dígitos do ano e
		 * MM os dois dígitos do Mês.
		 */
		public $DS_MERCHANT_EXPIRYDATE;
		/**
		 * Obrigatório CVV - Código de Segurança que está no cartão.
		 */
		public $DS_MERCHANT_CVV2; 
		/**
		 * Obrigatório  Indica o tipo de transação:
		 *	A – Autorização (sem 3D Secure)
		 *	0 – Autorização (com autenticação 3D Secure)
		 *	1 – Pré-Autorização
		 */
		public $DS_MERCHANT_TRANSACTIONTYPE;
		/**
		 * Obrigatório Forma de Pagamento – Crédito ou Débito
		 *	01 – Crédito
		 *	02 – Débito
		 */
		public $DS_MERCHANT_ACCOUNTTYPE; 
		/**
		 * Obrigatório
		 */		
		public $DS_MERCHANT_PLANTYPE; 
		/**
		 * Opcional
		 */
		public $DS_MERCHANT_PLANINSTALLMENTSNUMBER;
		/**
		 * Opcional
		 */
		public $DS_MERCHANT_PRODUCTDESCRIPTION; 
		/**
		 * Opcional
		 */	
		public $DS_MERCHANT_TITULAR; 
		/**
		 * Opcional
		 */
		public $DS_MERCHANT_MERCHANTDATA; 
		/**
		 * Opcional
		 */	
		public $DS_MERCHANT_CLIENTIP; 
		/**
		 * Obrigatório
		 */
		public $DS_MERCHANT_MERCHANTSIGNATURE; 
		/**
		 * Opcional
		 */
		public $DS_MERCHANT_VCIND; 
		/**
		 * Opcional
		 */
		public $DS_MERCHANT_RECURRINGPAYMENT; 
		/**
		 * Obrigatório em transações autenticadas 3D Secure.
		 */
		public $DS_MERCHANT_ACCEPTHEADER;  
		/**
		 * Obrigatório em transações autenticadas 3D Secure.
		 */
		public $DS_MERCHANT_USERAGENT; 
		
}



class PeticionResponse {

}

/*
	function sendRequest($params, $function = 'trataPeticion'){
		$url_ws="https://sis-t.redsys.es:25443/sis/services/SerClsWSEntrada";

		//  print_r($params);
	 	//die();

 
		 
		// $url_ws  ="https://sis-t.redsys.es:25443/apl02/services/SerClsWSConsulta?wsdl";

		try{
			$client = new SoapClient($url_ws,array(
				'trace'=>true, 
				'exceptions'=>true
			   ));
			
		    // $result = $client->__soapCall($function,array('<![CDATA['.str_replace('<?xml version="1.0">','',$params).' ] ]>'));
			$result = new stdClass();
			$result->trataPeticionResponse = $client->trataPeticion('<![CDATA['.str_replace('<?xml version="1.0"?>','',$params).']]>');
			//print_r($client->__getFunctions());	
			print_r($result);
			die();
		}
		catch(SoapFault $ex){
			print_r($ex);
			die();
		}
		catch(Exception $ex){
			print_r($ex);
			die();
		}

			
		if(isset($result->trataPeticionReturn) && $result->trataPeticionReturn < 0){
			echo 'Response Fail: ';
			print_r($result);
			
			return false;
		}
		else{
			echo 'Response Success: ';
			print_r($result->trataPeticionReturn);
			echo '<br>';
			return true;
		}

	}
*/

	
	
	function getEnvelopeSoap($content){
		
		$soap_request = '
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservice.sis.sermepa.es">
			<soapenv:Header/>
			<soapenv:Body>
			<web:trataPeticion>
				<web:datoEntrada>
				<![CDATA['.trim(str_replace('<?xml version="1.0"?>','',$content)).']]>
				</web:datoEntrada>
			</web:trataPeticion>
			</soapenv:Body>
		</soapenv:Envelope>';

		return $soap_request;
	}
	function getEnvelopeSoap3DES($content){
		
		$soap_request = '
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservice.sis.sermepa.es">
			<soapenv:Header/>
			<soapenv:Body>
			<web:trataPeticion3DES>
				<web:datoEntrada>
				<![CDATA['.trim(str_replace('<?xml version="1.0"?>','',$content)).']]>
				</web:datoEntrada>
			</web:trataPeticion3DES>
			</soapenv:Body>
		</soapenv:Envelope>';

		return $soap_request;
	}
	
	function sendCurl($soap_request,$header, $config){
		$soapUrl= $config['transaction_url']; 
		// $soapUrl="https://sis-t.redsys.es:25443/sis/services/SerClsWSEntrada";

		$soap_do = curl_init();
		curl_setopt($soap_do, CURLOPT_URL, $soapUrl );
		curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($soap_do, CURLOPT_POST,           true );
		curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $soap_request);
		curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
		curl_setopt($soap_do, CURLOPT_HEADER, false);
		
		
		$result = curl_exec($soap_do);
		if( $result === false) {
			$err = 'Curl error: ' . curl_error($soap_do);
			curl_close($soap_do);
			
			$json = json_encode(array('descricao' => 'sendCurl ERROR ', $err, 'url'=>$soapUrl, 'soap_request'=>$soap_request, 'header'=>$header));
			include('../comprar/logiPagareChamada.php');
			//print $err;

		} else {
			curl_close($soap_do);
		}
		return $result;  
	}

	/**
	 * return xml
	 */
	function getReturnSOAP($resultContent){
		$html = html_entity_decode($resultContent);  				
		$xml = new SimpleXMLElement($html);
		
		return $xml;
	}
	
	/**
	 * return xml
	 */
	function obj2xml($object){  				
		$xml = new SimpleXMLElement($object);
		return $xml;
	}
	
	function xmlToarray($param){
		//Convert param to array
		$paramXML = new SimpleXMLElement(str_replace('<?xml version="1.0"?>','',$param));
		$paramArr = xml2array($paramXML->asXML());
		
		return $paramArr;
	}
	
	function str2xml($params){
        $paramsXML = new SimpleXMLElement(str_replace('<?xml version="1.0"?>','',$params));
        return $paramsXML;
    }

	function sendRequestCurl($params,$opAction, $on3DES = false, $config = null){
		
		//echo "sendRequestCurl:Begin<br>";
		// $soapUrl="https://sis-t.redsys.es:25443/sis/services/SerClsWSEntrada";
		
		// $soapUrl="https://sis-t.redsys.es:25443/sis/services/SerClsWSEntrada/wsdl/SerClsWSEntrada.wsdl";
		
		try{
			//echo "sendRequestCurl:Current<br>";
			
			$paramsXML = str2xml($params);
			
			//Convert param to array
			$paramsArr = xmlToarray($params);
			//Percorre array multidimensional e trasforma num unico array dimensional resultante
			array_walk_recursive($paramsArr, function($v,$k)use(&$resultB){
				$resultB[$k]=$v;
			});
			
			$json = json_encode(array('descricao' => 'Passo global_payments_sended_'.$resultB['DS_MERCHANT_ORDER'].' - sendRequestCurl', trim(str_replace('<?xml version="1.0" ?>','',$paramsXML->asXML()))));
			include('../comprar/logiPagareChamada.php');
			//DEBUG SENDED
			// file_put_contents('global_payments_sended_'.$resultB['DS_MERCHANT_ORDER'].'.xml',trim(str_replace('<?xml version="1.0" ? >','',$paramsXML->asXML())));

			// if($on3DES)
			// 	$soap_request = getEnvelopeSoap3DES($paramsXML->asXML());
			// else
				$soap_request = getEnvelopeSoap($paramsXML->asXML());
		 

		    $header = array(
			  "Content-type: application/soap+xml",
			  "SOAPAction: \" \"",
			  "Content-length: ".strlen($soap_request)
			 // "User-Agent: ".$_SERVER['HTTP_USER_AGENT']
			);		   		   

			$result = sendCurl($soap_request, $header, $config);

			if(!$result){
				$msg = "retorno sendCurl false";
				$json = json_encode(array('descricao' => 'pedido_'.$resultB['DS_MERCHANT_ORDER'].' - sendRequestCurl', $msg ));
				include('../comprar/logiPagareChamada.php');

				return array('success'=>false);
			}
			//Convert result to xml	
			$xml = getReturnSOAP($result);
			
			$json = json_encode(array('descricao' => 'Passo global_payments_response_'.$resultB['DS_MERCHANT_ORDER'].' - sendRequestCurl', trim(str_replace('<?xml version="1.0" ?>','',$xml->asXML()))));
			include('../comprar/logiPagareChamada.php');
			//DEBUG RESPONSE
			// file_put_contents('global_payments_response_'.$resultB['DS_MERCHANT_ORDER'].'.xml', $xml->asXML());
			
			//   $xml = simplexml_load_string($html, "SimpleXMLElement", LIBXML_NOCDATA);
			$array = xml2array($xml->asXML());
				  
			//Percorre array multidimensional e trasforma num unico array dimensional resultante
			array_walk_recursive($array, function($v,$k)use(&$resultA){
					$resultA[$k]=$v;
			});

			// echo "sendRequestCurl:End<br>";
			return tratarReturnPeticion($resultA, $resultB, $opAction);
		
			die();
		}
		catch(Exception $ex){
			//print_r($ex);
		// echo "sendRequestCurl:Exception<br>";
			$json = json_encode(array('descricao' => 'pedido_'.$resultB['DS_MERCHANT_ORDER'].' - sendRequestCurl  EXCEPTION ', $ex ));
			include('../comprar/logiPagareChamada.php');

			return array('success'=>false);
			// die();
		}
		// echo "sendRequestCurl:End Function<br>";
		
	}
	

	/**
	 * Retorno da requisição SOAP
	 * param $resultA 
	 * Parametro da requisição SOAP
	 * param $resultB 
	 */
	function tratarReturnPeticion($resultA, $resultB,$opAction){
	
		if($opAction == OptionAction::AUTORIZAR){
			if($resultA['CODIGO'] !== '0'){
				$msg = "Falha no Pedido {$resultB['DS_MERCHANT_ORDER']} erro de código {$resultA['CODIGO']} ". tratarErro($resultA['CODIGO']);
				return array('success'=>false, 'error'=> $msg);
			}else{
				$msg = "Pedido {$resultB['DS_MERCHANT_ORDER']} realizado com sucesso! ";
				return  array('success'=>true, 'transaction'=> $resultA);
			}
	
		}
		else if($opAction == OptionAction::CAPTURAR){
			if($resultA['CODIGO'] !== '0'){
				$msg = "Falha no Pedido {$resultB['DS_MERCHANT_ORDER']} erro de código {$resultA['CODIGO']} ". tratarErro($resultA['CODIGO']);
				return array('success'=>false, 'error'=> $msg);
			}else{
				$msg = "Pedido {$resultB['DS_MERCHANT_ORDER']} capturado com sucesso! ";
				return array('success'=>true, 'transaction'=> $resultA);				
			}
		}
		else if($opAction == OptionAction::CANCELAR	){
			if($resultA['CODIGO'] !== '0'){
				$msg = "Falha no Pedido {$resultB['DS_MERCHANT_ORDER']} erro de código {$resultA['CODIGO']} ". tratarErro($resultA['CODIGO']);
				return array('success'=>false, 'error'=> $msg, 'result'=>$resultA);
			}else{
				$msg = "Pedido {$resultB['DS_MERCHANT_ORDER']} cancelado com sucesso! ";
				return array('success'=>true, 'transaction'=> $resultA);
			}
		}
		
	}
	
	function tratarErro($sisId){
		// CÓDIGO  ERRO SIS DESCRIÇÃO 
		$ERRO_SIS = array(
		"SIS0001"=>"Erro genérico"
		,"SIS0002"=>""
		,"SIS0007"=>"Erro ao desmontar o XML de entrada"
		,"SIS0008"=>"Erro falta Ds_Merchant_MerchantCode"
		,"SIS0009"=>"Erro de formato no Ds_Merchant_MerchantCode"
		,"SIS0010"=>"Erro falta Ds_Merchant_Terminal"
		,"SIS0011"=>"Erro de formato no Ds_Merchant_Terminal"
		,"SIS0014"=>"Erro de formato no Ds_Merchant_Order"
		,"SIS0015"=>"Erro falta Ds_Merchant_Currency"
		,"SIS0016"=>"Erro de formato no Ds_Merchant_Currency"
		,"SIS0018"=>"Erro falta Ds_Merchant_Amount"
		,"SIS0019"=>"Erro de formato no Ds_Merchant_Amount"
		,"SIS0020"=>"Erro falta Ds_Merchant_MerchantSignature"
		,"SIS0021"=>"Erro a Ds_Merchant_MerchantSignature vem vazia"
		,"SIS0022"=>"Erro de formato no Ds_Merchant_TransactionType"
		,"SIS0023"=>"Erro Ds_Merchant_TransactionType desconhecido"
		,"SIS0025"=>"Erro de formato do Ds_Merchant_ConsumerLanguage"
		,"SIS0026"=>"Loja / terminal não existente"
		,"SIS0027"=>"Tipo de moeda não habilitada para este terminal"
		,"SIS0028"=>"Loja / terminal está desativado"
		,"SIS0030"=>"Operação não é válida"
		,"SIS0031"=>"Método de pagamento não reconhecido"
		,"SIS0034"=>"Erro ao acessar a base de dados"
		,"SIS0035"=>"Erro interno de sistema. Não foi possível recuperar dados da sessão."
		,"SIS0038"=>"Erro interno Java."
		,"SIS0040"=>"A loja não possui nenhum método de pagamento habilitado"
		,"SIS0041"=>"Erro no cálculo da HASH dos dados da loja"
		,"SIS0042"=>"A assinatura enviada não está correta"
		,"SIS0046"=>"O BIN do cartão não está ativado"
		,"SIS0051"=>"Erro número de pedido repetido"
		,"SIS0054"=>"Transação não localizada. Não foi possível realizar o cancelamento"
		,"SIS0055"=>"Existe mais de um pagamento com o mesmo número de pedido"
		,"SIS0056"=>"Cancelamento não autorizado para esta operação"
		,"SIS0057"=>"O valor a ser cancelado supera o permitido"
		,"SIS0058"=>"Inconsistência de dados na validação da confirmação da transação"
		,"SIS0059"=>"Operação não é válida para realizar a confirmação da transação"
		,"SIS0060"=>"Já existe uma confirmação associada à esta pré-autorização"
		,"SIS0061"=>"Operação não autorizada para confirmar a pré-autorização"
		,"SIS0062"=>"O valor a capturar supera o permitido"
		,"SIS0063"=>"Número do cartão não disponível"
		,"SIS0064"=>"O número do cartão não pode ter mais de 19 posições"
		,"SIS0065"=>"O número do cartão não é numérico"
		,"SIS0066"=>"Mês de expiração não disponível"
		,"SIS0067"=>"O mês de expiração não é numérico"
		,"SIS0068"=>"O mês da expiração não é válido"
		,"SIS0069"=>"Ano de expiração não disponível"
		,"SIS0070"=>"O ano de expiração não é numérico"
		,"SIS0071"=>"Cartão expirado"
		,"SIS0072"=>"Operação não é possível de ser anulada"
		,"SIS0073"=>"Erro no cancelamento"
		,"SIS0074"=>"Erro falta Ds_Merchant_Order"
		,"SIS0075"=>"Erro o Ds_Merchant_Order tem menos de 4 posições ou mais de 12"
		,"SIS0076"=>"Erro o Ds_Merchant_Order não possui as 4 primeiras posições preenchidas com números."
		,"SIS0077"=>"Erro o Ds_Merchant_Order não está formatado corretamente."
		,"SIS0078"=>"Método de pagamento não disponível"
		,"SIS0079"=>"Erro ao realizar o pagamento com cartão"
		,"SIS0081"=>"Nova sessão, os dados armazenados foram perdidos"
		,"SIS0089"=>"O valor de Ds_Merchant_ExpiryDate não ocupa 4 posições"
		,"SIS0092"=>"O valor de Ds_Merchant_ExpiryDate é nulo"
		,"SIS0093"=>"Cartão não reconhecido"
		,"SIS0112"=>"Erro tipo de transação especificado em Ds_Merchant_Transaction_Type não é permitido"
		,"SIS0114"=>"Está realizando a chamada por GET, é necessário realizá-la por POST."
		,"SIS0132"=>"A data da captura não pode superar mais de 7 dias a partir da pré-autorização"
		,"SIS0142"=>"Tempo excedido para o pagamento"
		,"SIS0216"=>"Erro Ds_Merchant_CVV2 tem mais de 3 ou 4 posições"
		,"SIS0217"=>"Erro de formato em Ds_Merchant_CVV2"
		,"SIS0221"=>"CVV2 é obrigatório"
		,"SIS0222"=>"Já existe um cancelamento associado à pré-autorização"
		,"SIS0223"=>"Cancelamento da Pré-autorização não autorizada"
		,"SIS0225"=>"Não existe transação para realizar o cancelamento"
		,"SIS0226"=>"Inconsistência de dados na validação de cancelamento da transação"
		,"SIS0227"=>"Valor do campo Ds_Merchan_TransactionDate não é válido"
		,"SIS0252"=>"A loja não permite o envio do cartão"
		,"SIS0253"=>"Verifique se o seu cartão é válido"
		,"SIS0261"=>"Operação cancelada, pois, infringe o controle de restrições na entrada ao sistema"
		,"SIS0274"=>"Operação desconhecida ou não permitida na entrada ao sistema"
		,"SIS0416"=>"Valor não permitido para cancelamento"
		,"SIS0417"=>"Cancelamento não permitido por exceder o prazo limite"
		,"SIS0418"=>"Não existe plano de vendas vigente para esta operação"
		,"SIS0419"=>"O valor do campo DS_MERCHANT_ACCOUNTTYPE (CRE/DEB) é incompatível a configuração do cartão "
		,"SIS0420"=>"A loja não possui formas de pagamento habilitadas para este tipo de operação"
		,"SIS0428"=>"Transação de débito não autenticada"
		,-1 => "Erro não encontrado!");
		

	   return $ERRO_SIS[$sisId];		

	}

	function arrayFromTag($value){
		echo "Key $key  has value $value <br>\n\r";
		return $value;
	}

	function processarTransacao(trataPeticion $objSend, $chaveAcesso, $opAction, $on3DES = false, $config = null){
		//$objSend->DS_MERCHANT_MERCHANTCODE = $config['MERCHANTCODE'];
		//$objSend->DS_MERCHANT_TERMINAL = '001';

		
		$json = json_encode(array('descricao' => 'pedido_'.$objSend->DS_MERCHANT_ORDER.' - processarTransacao  CONFIGURACAO INFO', $config ));
		include('../comprar/logiPagareChamada.php');

		$xmlResult = processarDados($objSend, $chaveAcesso, $opAction);

		if(!$xmlResult){
			$msg = 'Falha no processamento dos dados!';
			$json = json_encode(array('descricao' => 'pedido_'.$objSend->DS_MERCHANT_ORDER.' - processarTransacao ', $msg ));
			include('../comprar/logiPagareChamada.php');
			return array('success'=>false);
		}
		else{
			$json = json_encode(array('descricao' => 'pedido_'.$objSend->DS_MERCHANT_ORDER.' - processarTransacao  xmlResult', 'xmlResult'=> $xmlResult, 'objSend' => $objSend ));
			include('../comprar/logiPagareChamada.php');
			return sendRequestCurl($xmlResult,$opAction, $on3DES,$config);
					// return sendRequest($xmlResult);
				}
	}



	function getInfoPedido($id_pedido, $dados_extra){
		$config = getConfigGlobal();
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


		// INICIO DO BLOCO DA PREPARAÇÂO DA REQUISIÇÃO
		$trataPeticion = new trataPeticion();
		// INFORMAÇÃO DA LOJA
		$trataPeticion->DS_MERCHANT_MERCHANTCODE = $config['merchantCode'];
		// $trataPeticion->DS_MERCHANT_MERCHANTCODE = rand(1,1000000);
		
		$trataPeticion->DS_MERCHANT_TERMINAL = '001';
		//FIM INFORMAÇÃO DA LOJA
		
		//Informação do Pedido
		$trataPeticion->DS_MERCHANT_AMOUNT = ($rs['VL_TOTAL_PEDIDO_VENDA'] * 100);
		$trataPeticion->DS_MERCHANT_ORDER = $id_pedido.rand(1,1000000);
		//Fim Informação do Pedido    
	
		$trataPeticion->DS_MERCHANT_PAN = $dados_extra['numCartao'] ;
		$trataPeticion->DS_MERCHANT_CVV2 = $dados_extra['codSeguranca'];
		$trataPeticion->DS_MERCHANT_EXPIRYDATE = substr($dados_extra['validadeAno'],2).$dados_extra['validadeMes'];


		return $trataPeticion;
	}



	/**
	 * DS_MERCHANT_TRANSACTIONTYPE: Obrigatório – Indica o tipo de transação: 
	 * 			2 – Confirmação da Pré-Autorização 
	 * 			3 - Cancelamento  
	 * 			9 – Cancelamento da Pré-Autorização 
	 * 
	 */

    function autorizarCompraCredito(trataPeticion $objSend, $chaveAcesso, $config){
		
		// Tipo da transação
		$objSend->DS_MERCHANT_TRANSACTIONTYPE = 'A';
		// 01-Crédito ou 02-Débito
		$objSend->DS_MERCHANT_ACCOUNTTYPE = '01';
		return processarTransacao($objSend,$chaveAcesso,OptionAction::AUTORIZAR,false,$config);
	}
	
	function autorizarCompraDebito(trataPeticion $objSend, $chaveAcesso, $config){
	
		// Tipo da transação
		$objSend->DS_MERCHANT_TRANSACTIONTYPE = '0';
		// 01-Crédito ou 02-Débito
		$objSend->DS_MERCHANT_ACCOUNTTYPE = '02';
		$objSend->DS_MERCHANT_PLANTYPE = '01';
		//$objSend->DS_MERCHANT_PLANINSTALLMENTSNUMBER = '01';
		
		$objSend->DS_MERCHANT_ACCEPTHEADER = trim($_SERVER['HTTP_ACCEPT']);
		$objSend->DS_MERCHANT_USERAGENT = trim($_SERVER['HTTP_USER_AGENT']);

		//Retorno da primeira requisição débito
		$resultProcess = processarTransacao($objSend,$chaveAcesso,OptionAction::AUTORIZAR, true,$config);

		
		if($resultProcess['success']){
			$json = json_encode(array('descricao' => 'pedido_'.$objSend->DS_MERCHANT_ORDER.' - autorizarCompraDebito (sucesso) processarTransacao ','resultProcess' => $resultProcess ));
			include('../comprar/logiPagareChamada.php');
			
			redirectClientToBank($resultProcess['transaction'],$objSend,$config);
			//return true;
		}
		else{
			$json = json_encode(array('descricao' => 'pedido_'.$objSend->DS_MERCHANT_ORDER.' - autorizarCompraDebito  (falha) processarTransacao','resultProcess' => $resultProcess ));
			include('../comprar/logiPagareChamada.php');
			return array('success'=>false);
		}

	}

	function capturarCompra(trataPeticion $objSend,$chaveAcesso){
		$objSend->DS_MERCHANT_TRANSACTIONTYPE = '2'; 
		return processarTransacao($objSend,$chaveAcesso,OptionAction::CAPTURAR);
	}
	
	function cancelarCompra(trataPeticion $objSend,$chaveAcesso){
		$objSend->DS_MERCHANT_TRANSACTIONTYPE = '3'; 
		return processarTransacao($objSend,$chaveAcesso,OptionAction::CANCELAR);		
	}


	/** 
	 * Params:
	 *  trataPeticion $objSend
	 *  OptionAction $opAction 
	 *  
	 * Como utilizar: Para utilizar basta passar o objeto de transação preenchido com os dados
	 *  iniciais referente a compra, pois os dados obrigatórios referentes a transação
	 *  serão inseridos durante o processamento dos dados.
	 * 
	 * Return:
	 * xmlResult - Em Caso de sucesso 
	 * False - Em caso de falha
	 */
	function processarDados(trataPeticion $objSend, $chaveAcesso,  $opAction){
		if($objSend === null || $chaveAcesso === null || $chaveAcesso == '' ){
			$msg = "processarDados Erro 001!";
			$json = json_encode(array('descricao' => 'pedido_'.$resultB['DS_MERCHANT_ORDER'].' - processarDados ', $msg ));
			include('../comprar/logiPagareChamada.php');
			return false;
		}
		else if(!validarDados($objSend,$opAction)){
			$msg = "processarDados Erro 002!";
			$json = json_encode(array('descricao' => 'pedido_'.$resultB['DS_MERCHANT_ORDER'].' - processarDados ', $msg ));
			include('../comprar/logiPagareChamada.php');
			return false;
		}

		$CHAVE_HASH = gerarChaveAssinatura($objSend,$chaveAcesso, $opAction);
		$objSend->DS_MERCHANT_MERCHANTSIGNATURE = $CHAVE_HASH;


		$xmlResult = array2xml($objSend,'DATOSENTRADA');

		return $xmlResult;
	}

	/** 
	 * Params:
	 *  trataPeticion $objSend
	 *  OptionAction $opAction
	 * 
	 * Return:
	 *  true - Em caso de validação ser aprovada
	 *  false - Em caso da validação falhar por falta de alguma informação
	 */
	function validarDados(trataPeticion $objSend, $opAction){
		$resultValidation = true;	
		if(empty($objSend->DS_MERCHANT_AMOUNT)
			or $objSend->DS_MERCHANT_AMOUNT === null or
			empty($objSend->DS_MERCHANT_ORDER)
			or $objSend->DS_MERCHANT_ORDER === null or 
			empty($objSend->DS_MERCHANT_MERCHANTCODE) 
			or $objSend->DS_MERCHANT_MERCHANTCODE === null or
			empty($objSend->DS_MERCHANT_CURRENCY)
			or $objSend->DS_MERCHANT_CURRENCY === null or
			$objSend->DS_MERCHANT_TRANSACTIONTYPE == ''
			or $objSend->DS_MERCHANT_TRANSACTIONTYPE === null
		){
				$msg = "validarDados Erro 001!";
				//print_r($objSend);
				$json = json_encode(array('descricao' => 'pedido_'.$objSend->DS_MERCHANT_ORDER.' - validarDados ', $msg,'object' => $objSend ));
				include('../comprar/logiPagareChamada.php');
				
				$resultValidation = false;
			} 	
			
			if($opAction == OptionAction::AUTORIZAR ){
				if(empty($objSend->DS_MERCHANT_PAN)
				or $objSend->DS_MERCHANT_PAN === null
				or empty($objSend->DS_MERCHANT_CVV2)
				or $objSend->DS_MERCHANT_CVV2 === null
				){
					$msg = "validarDados Erro 002!";
					$json = json_encode(array('descricao' => 'pedido_'.$resultB->DS_MERCHANT_ORDER.' - validarDados ', $msg,'object' => $objSend ));
					include('../comprar/logiPagareChamada.php');

					$resultValidation = false;
			}
		}

		return $resultValidation; 
	}

	/**
	 * Params:
	 * trataPeticion $objSend
	 * string $CHAVE
	 * OptionAction $opAction
	 * Description: Gera assinatura conforme requisitado no manual da global.
	 *  
	 */
	function gerarChaveAssinatura(trataPeticion $objSend, $CHAVE, $opAction){
		$chave_str = '';
		$chave_str .= $objSend->DS_MERCHANT_AMOUNT;
		$chave_str .= $objSend->DS_MERCHANT_ORDER;
		$chave_str .= $objSend->DS_MERCHANT_MERCHANTCODE;
		$chave_str .= $objSend->DS_MERCHANT_CURRENCY;
		if($opAction == OptionAction::AUTORIZAR ){ // if opção igual autorizar
			$chave_str .= $objSend->DS_MERCHANT_PAN; 
			$chave_str .= $objSend->DS_MERCHANT_CVV2; 
		}
		$chave_str .= $objSend->DS_MERCHANT_TRANSACTIONTYPE;
		$chave_str .= $CHAVE;	

		return hash('sha256',$chave_str);
	}
	function gerarChaveAssinaturaDebito($objSend, $CHAVE){
		$chave_str = '';
		$chave_str .= $objSend->DS_MERCHANT_ORDER;
		$chave_str .= $objSend->DS_MERCHANT_MERCHANTCODE;
		$chave_str .= $objSend->DS_MERCHANT_TRANSACTIONTYPE;
		$chave_str .= $objSend->DS_MERCHANT_MD;

		$chave_str .= $CHAVE;	

		return hash('sha256',$chave_str);
	}

	function array2xml($data, $root = null){
		$xml = new SimpleXMLElement($root ? '<' . $root . '/>' : '<root/>');
		array_walk_recursive($data, function($value, $key)use($xml){
			if($value !== null)
			$xml->addChild($key, $value);
		});
		return $xml->asXML();
	}

	function array2strxml($data){
		$xmlStr = '';

		foreach($data as $key => $value){
			$xmlStr .= "<$key>$value</$key>";
		}

		return $xmlStr;
	}

	function xml2array($contents, $get_attributes = 1, $priority = 'tag')
    {
        if (!$contents) return array();
        if (!function_exists('xml_parser_create')) {
            // print "'xml_parser_create()' function not found!";
            return array();
        }
        // Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); // http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents) , $xml_values);
        xml_parser_free($parser);
        if (!$xml_values) return; //Hmm...
        // Initializations
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        $current = & $xml_array; //Refference
        // Go through the tags.
        $repeated_tag_index = array(); //Multiple tags with same name will be turned into an array
        foreach($xml_values as $data) {
            unset($attributes, $value); //Remove existing values, or there will be trouble
            // This command will extract these variables into the foreach scope
            // tag(string), type(string), level(int), attributes(array).
            extract($data); //We could use the array by itself, but this cooler.
            $result = array();
            $attributes_data = array();
            if (isset($value)) {
                if ($priority == 'tag') $result = $value;
                else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
            }
            // Set the attributes too.
            if (isset($attributes) and $get_attributes) {
                foreach($attributes as $attr => $val) {                                   
                                    if ( $attr == 'ResStatus' ) {
                                        $current[$attr][] = $val;
                                    }
                    if ($priority == 'tag') $attributes_data[$attr] = $val;
                    else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }
            // See tag status and do the needed.
                        //echo"<br/> Type:".$type;
            if ($type == "open") { //The starting of the tag '<tag>'
                $parent[$level - 1] = & $current;
                if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                    $current[$tag] = $result;
                    if ($attributes_data) $current[$tag . '_attr'] = $attributes_data;
                                        //print_r($current[$tag . '_attr']);
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
                }
                else { //There was another element with the same tag name
                    if (isset($current[$tag][0])) { //If there is a 0th element it is already an array
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                    else { //This section will make the value an array if multiple tags with the same name appear together
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        ); //This will combine the existing item and the new item together to make an array
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            }
            elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
                // See if the key is already taken.
                if (!isset($current[$tag])) { //New Key
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data) $current[$tag . '_attr'] = $attributes_data;
                }
                else { //If taken, put all things inside a list(array)
                    if (isset($current[$tag][0]) and is_array($current[$tag])) { //If it is already an array...
                        // ...push the new element into that array.
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                    else { //If it is not an array...
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        ); //...Make it an array using using the existing value and the new value
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
							if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                            if ($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            }
            elseif ($type == 'close') { //End of tag '</tag>'
                $current = & $parent[$level - 1];
            }
        }
        return ($xml_array);
	}
	
	function getReturnTransactionDebito(){
		$config = getConfigGlobal();
		$chaveAcesso = $config['merchantKey'];

		//Objeto para envio de solicitação de aprovação à GLOBAL PAYMENTS
		$objs = new stdClass;
		
		$objs->DS_MERCHANT_ORDER = $_REQUEST['DS_MERCHANT_ORDER'];
		$objs->DS_MERCHANT_MERCHANTCODE = $_REQUEST['DS_MERCHANT_MERCHANTCODE'];
		$objs->DS_MERCHANT_TERMINAL =  $_REQUEST['DS_MERCHANT_TERMINAL'];
		$objs->DS_MERCHANT_TRANSACTIONTYPE = $_REQUEST['DS_MERCHANT_TRANSACTIONTYPE'];
		$objs->DS_MERCHANT_PARESPONSE = $_REQUEST['PaRes'];
		$objs->DS_MERCHANT_MD = $_REQUEST['MD'];
		$objs->DS_MERCHANT_MERCHANTSIGNATURE  = gerarChaveAssinaturaDebito($objs,$chaveAcesso);

		$paramsXML = array2xml($objs,'DATOSENTRADA');


		//Convert param to array
		$paramsArr = xmlToarray($paramsXML);
		//Percorre array multidimensional e trasforma num unico array dimensional resultante
		array_walk_recursive($paramsArr, function($v,$k)use(&$resultB){
			$resultB[$k]=$v;
		});
		
		$soap_request = getEnvelopeSoap($paramsXML);
		   
		$header = array(
			"Content-type: application/soap+xml",
			"SOAPAction: \" \"",
			"Content-length: ".strlen($soap_request)
		// "User-Agent: ".$_SERVER['HTTP_USER_AGENT']
		);		   		   

		//DEBUG RESPONSE
		file_put_contents('global_payments_sended_bank_'.$resultB['DS_MERCHANT_ORDER'].'.xml', $paramsXML);

		$result = sendCurl($soap_request, $header, $config);

		
		//Convert result to xml	
		$xml = getReturnSOAP($result);
		
		//DEBUG RESPONSE
		file_put_contents('global_payments_response_bank_'.$resultB['DS_MERCHANT_ORDER'].'.xml', $xml->asXML());

		$array = xml2array($xml->asXML());
				
		//Percorre array multidimensional e trasforma num unico array dimensional resultante
		array_walk_recursive($array, function($v,$k)use(&$resultA){
				$resultA[$k]=$v;
		});
		
		//echo "Result Pagamento débito GLOBAL PAYMENTS<br>";
		// print_r($result);
		return tratarReturnPeticion($resultA, $resultB, OptionAction::AUTORIZAR);
	}
	
	// Retorno da segunda requisição débito
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'retBank'){
		
		if(isset($_REQUEST['DS_MERCHANT_ORDER'])){
			getReturnTransactionDebito();
		
		}else{
			echo "Sessão não encontrada!";
		}

		die();
	}

	function redirectClientToBank($objSend, trataPeticion $objParamSend,$config){
		
		$html ='<html> 
		<head> 
		<title>ii</title> 
		</head> 
		<body OnLoad="OnLoadEvent();" > 
		<form name="downloadForm" action="'.$objSend['Ds_AcsUrl'].'" method="POST"> 
		<noscript> 
		<br> 
		<br> 
		<center> 
		<h1>Processing your 3-D Secure Transaction</h1> 
		<h2> 
		JavaScript is currently disabled or is not supported 
		by your browser.<br></h2> 
		<h3>Please click Submit to continue 
		the processing of your 3-D Secure 
		transaction.</h3> 
		<input type="submit" value="Submit"> 
		</center> 
		</noscript> 
		<input type="hidden" name="PaReq" value="'.$objSend['Ds_PaRequest'].'">
		<input type="hidden" name="TermUrl" value="http://localhost:98/settings/global_functions.php?action=retBank&DS_MERCHANT_ORDER='.$objParamSend->DS_MERCHANT_ORDER.'&DS_MERCHANT_MERCHANTCODE='.$objParamSend->DS_MERCHANT_MERCHANTCODE.'&DS_MERCHANT_TERMINAL='.$objParamSend->DS_MERCHANT_TERMINAL.'&DS_MERCHANT_TRANSACTIONTYPE='.$objParamSend->DS_MERCHANT_TRANSACTIONTYPE.'"> 
		<input type="hidden" name="MD" value="'.$objSend['Ds_MD'].'"> 
		</form> 
		<SCRIPT LANGUAGE="Javascript" > 
		<!-- 
		function OnLoadEvent() 
		{ 
		document.downloadForm.submit();
		} 
		//--> 
		</SCRIPT> 
		</body> 
	    </html> 
		';
		echo 'wd = window.open ("", "_blank"); 
			  wd.document.body.innerHTML = '.$html.'
		';
		// echo $html;

	}
	
	
	// echo "Function<br>";
	// sendRequest();
	
	// $data = processarDados();
	
	// $xml_data = array2xml($data,'DATOSENTRADA');
	
	// echo '<pre>';
	// print_r($data);
	
	// header('Content-type: text/xml');
	// print $xml_data;
	

	// if(!isset($_SESSION['ORDER'])){ // BEGIN SESSION ORDER
		// $_SESSION['ORDER'] = rand(1,10000000);

	// // INICIO DO BLOCO DA PREPARAÇÂO DA REQUISIÇÃO
	// $trataPeticion = new trataPeticion();
	// // INFORMAÇÃO DA LOJA
	// $trataPeticion->DS_MERCHANT_MERCHANTCODE = '012005510536001';
	// $trataPeticion->DS_MERCHANT_TERMINAL = '001';
	// //FIM INFORMAÇÃO DA LOJA
	
	// //Informação do Pedido
	// $trataPeticion->DS_MERCHANT_AMOUNT = (100.00 * 100);
	// $trataPeticion->DS_MERCHANT_ORDER = rand(1,10000000);
	// //Fim Informação do Pedido
	
	//*******************AUTORIZAÇÃO DE COMPRA ***********************/
		
	// $trataPeticion->DS_MERCHANT_CURRENCY = 986; //Informação definida por padrão
	//Cartões válidos
	// Pagamento à vista:
	// $trataPeticion->DS_MERCHANT_PAN = '4548810000000003' ;
	// $trataPeticion->DS_MERCHANT_CVV2 = '123';
	// $trataPeticion->DS_MERCHANT_EXPIRYDATE = '4912';
	
	// // Pagamento parcelado:
	// $trataPeticion->DS_MERCHANT_PAN = '4761120000000148' ;
	// $trataPeticion->DS_MERCHANT_CVV2 = '111';
	// $trataPeticion->DS_MERCHANT_EXPIRYDATE = '1712';

// Transação negada 
// $trataPeticion->DS_MERCHANT_PAN = '1111111111111117' ;
// $trataPeticion->DS_MERCHANT_CVV2 = '123';
// $trataPeticion->DS_MERCHANT_EXPIRYDATE = '2012';


//********* Cartão de Crédito ******/
// $result = autorizarCompraCredito($trataPeticion,$chaveAcesso);
// unset($trataPeticion);
// if(!$result){
// 	echo 'Falha ao enviar solicitação';
// }

//*********Fim Cartão de Crédito ******/

//********* Cartão de Débito ******/
// $result = autorizarCompraDebito($trataPeticion,$chaveAcesso);
// if(!$result){
// 		echo 'Falha ao enviar solicitação';
// 		print_r($trataPeticion);
// }

//*********Fim Cartão de Débito ******/


//*******************FIM AUTORIZAÇÃO DE COMPRA ***********************/

//*******************CAPTURA DE COMPRA ***********************/
// $trataPeticion->DS_MERCHANT_ORDER = '1132184';
// $result = capturarCompra($trataPeticion,$chaveAcesso);
// if(!$result){
	// 	echo 'Falha ao enviar solicitação';
	// }else{
		// 	print_r($result);
		// }
		
		//*******************FIM CAPTURA DE COMPRA ***********************/
		
		//*******************CANCELAMENTO DE COMPRA ***********************/
		// $trataPeticion->DS_MERCHANT_ORDER = '1132184';
		// $result = cancelarCompra($trataPeticion,$chaveAcesso);
		// if(!$result){
			// 	echo 'Falha ao enviar solicitação';
			// }else{
				// 	print_r($result);
				// }
				//*******************FIM CANCELAMENTO DE COMPRA ***********************/
				
			// } // FIM IF SESSION ORDER
			
			
			//Teste Cartão Débito
			
			
			
			//Teste de valores
			
			// $valor1 = 100.00;
			// $valor2 = 0.01;
			// $valor3 = 0.10;
			
			// echo OptionAction::AUTORIZAR;

// echo "valor 1= ".($valor1 * 100)
// 	."<br>"
// 	."valor 2= ".($valor2 * 100)
// 	."<br>"
// 	."valor 3= ".($valor3 * 100)
// 	."<br>"
// 	;


