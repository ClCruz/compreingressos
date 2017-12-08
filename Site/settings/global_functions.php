<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

	function sendRequest($params, $function = 'trataPeticion'){

		 print_r($params);
	 	//die();

 
		 
		// $url_ws  ="https://sis-t.redsys.es:25443/apl02/services/SerClsWSConsulta?wsdl";
		$url_ws="https://sis-t.redsys.es:25443/sis/services/SerClsWSEntrada";

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

	function sendRequestCurl($params,$opAction){
		$soapUrl="https://sis-t.redsys.es:25443/sis/services/SerClsWSEntrada";

		// $soapUrl="https://sis-t.redsys.es:25443/sis/services/SerClsWSEntrada/wsdl/SerClsWSEntrada.wsdl";

		
		try{
			//Convert param to array
			$paramsXML = new SimpleXMLElement(str_replace('<?xml version="1.0"?>','',$params));
			$paramsArr = xml2array($paramsXML->asXML());
			//Percorre array multidimensional e trasforma num unico array dimensional resultante
			array_walk_recursive($paramsArr, function($v,$k)use(&$resultB){
				$resultB[$k]=$v;
			});
		
			$paramsXML->DS_MERCHANT_USERAGENT = $_SERVER['HTTP_USER_AGENT'];

			file_put_contents('global_payments_sended',str_replace('<?xml version="1.0"?>','',$paramsXML->asXML()));



			$soap_request = '
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservice.sis.sermepa.es">
			<soapenv:Header/>
			<soapenv:Body>
			   <web:trataPeticion>
				  <web:datoEntrada>
				  <![CDATA['.str_replace('<?xml version="1.0"?>','',$paramsXML->asXML()).']]>
				  </web:datoEntrada>
			   </web:trataPeticion>
			</soapenv:Body>
		 </soapenv:Envelope>';
		 
		 
		 
		 $header = array(
			  "Content-type: application/soap+xml",
			  "SOAPAction: \" \"",
			  "Content-length: ".strlen($soap_request)
			 // "User-Agent: ".$_SERVER['HTTP_USER_AGENT']
			);

			// $_SERVER['HTTP_USER_AGENT']
		   
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
			  print $err;
			} else {
			  curl_close($soap_do);
			  
			  
			  
			  //Convert result to xml
			  $std = new stdClass();
			  $html = html_entity_decode($result);  				
			  $xml = new SimpleXMLElement($html);
			  
			  file_put_contents('global_payments_response', $html);

			//   $xml = simplexml_load_string($html, "SimpleXMLElement", LIBXML_NOCDATA);
			  $array = xml2array($xml->asXML());
				  
			//Percorre array multidimensional e trasforma num unico array dimensional resultante
			array_walk_recursive($array, function($v,$k)use(&$resultA){
					$resultA[$k]=$v;
			});



			if($opAction == OptionAction::AUTORIZAR){
				if($resultA['CODIGO'] !== '0'){
					echo "Falha no Pedido {$resultB['DS_MERCHANT_ORDER']} erro de código {$resultA['CODIGO']} ". tratarErro($resultA['CODIGO']);
				}else{
					echo "Pedido {$resultB['DS_MERCHANT_ORDER']} realizado com sucesso! ";
				}

			}
			else if($opAction == OptionAction::CAPTURAR){
				if($resultA['CODIGO'] !== '0'){
					echo "Falha no Pedido {$resultB['DS_MERCHANT_ORDER']} erro de código {$resultA['CODIGO']} ". tratarErro($resultA['CODIGO']);
				}else{
					echo "Pedido {$resultB['DS_MERCHANT_ORDER']} capturado com sucesso! ";
				}
				return $resultA;
			}
			else if($opAction == OptionAction::CANCELAR	){
				if($resultA['CODIGO'] !== '0'){
					echo "Falha no Pedido {$resultB['DS_MERCHANT_ORDER']} erro de código {$resultA['CODIGO']} ". tratarErro($resultA['CODIGO']);
				}else{
					echo "Pedido {$resultB['DS_MERCHANT_ORDER']} cancelado com sucesso! ";
				}
				return $resultA;
			}
			//   print_r($array);
			//   print_r($array_result);

			  //print 'Operation completed without any errors';
			 // echo $soap_request;
			}


	
		die();
		}
		catch(Exception $ex){
			print_r($ex);
			die();
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
		,"SIS0428"=>"Transação de débito não autenticada");
		

	   return $ERRO_SIS[$sisId];		

	}

	function arrayFromTag($value){
		echo "Key $key  has value $value <br>\n\r";
		return $value;
	}


	function walk_recursive_to_tag( $array, $tag) {
		$res = array();
		
		foreach ($array as $k => $v) {
			if(count($res) > 0){
				echo 'parei';
				break;
			}
			if($k == $tag){
				echo "key $k \n\r";
				// print_r($v);
				 $res = $v;
			}
			else if (!is_array($v)) {
				walk_recursive_to_tag($v, $tag);				
			}

		}

		return $res;
	}


	function processarTransacao(trataPeticion $objSend, $chaveAcesso, $opAction){
		$xmlResult = processarDados($objSend, $chaveAcesso, $opAction);
		
				if(!$xmlResult){
					echo 'Falha no processamento dos dados!';
					return false;
				}
				else{
					return sendRequestCurl($xmlResult,$opAction);
					// return sendRequest($xmlResult);
				}
	}

	/**
	 * DS_MERCHANT_TRANSACTIONTYPE: Obrigatório – Indica o tipo de transação: 
	 * 			2 – Confirmação da Pré-Autorização 
	 * 			3 - Cancelamento  
	 * 			9 – Cancelamento da Pré-Autorização 
	 * 
	 */

    function autorizarCompraCredito(trataPeticion $objSend, $chaveAcesso){
		// Tipo da transação
		$objSend->DS_MERCHANT_TRANSACTIONTYPE = 'A';
		// 01-Crédito ou 02-Débito
		$objSend->DS_MERCHANT_ACCOUNTTYPE = '01';
		return processarTransacao($objSend,$chaveAcesso,OptionAction::AUTORIZAR);
	}
	
	function autorizarCompraDebito(trataPeticion $objSend, $chaveAcesso){
		// Tipo da transação
		$objSend->DS_MERCHANT_TRANSACTIONTYPE = '0';
		// 01-Crédito ou 02-Débito
		$objSend->DS_MERCHANT_ACCOUNTTYPE = '02';
		$objSend->DS_MERCHANT_PLANTYPE = '01';
		$objSend->DS_MERCHANT_PLANINSTALLMENTSNUMBER = '01';
		
		
		return processarTransacao($objSend,$chaveAcesso,OptionAction::AUTORIZAR);
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
			echo "processarDados Erro 001!";
			return false;
		}
		else if(!validarDados($objSend,$opAction)){
			echo "processarDados Erro 002!";
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
				echo "validarDados Erro 001!";
				print_r($objSend);
				die();
				$resultValidation = false;
			} 	
			
			if($opAction == OptionAction::AUTORIZAR ){
				if(empty($objSend->DS_MERCHANT_PAN)
				or $objSend->DS_MERCHANT_PAN === null
				or empty($objSend->DS_MERCHANT_CVV2)
				or $objSend->DS_MERCHANT_CVV2 === null
				){
					echo "validarDados Erro 002!";
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
	
	
	
	
	// echo "Function<br>";
	// sendRequest();
	
	// $data = processarDados();
	
	// $xml_data = array2xml($data,'DATOSENTRADA');
	
	// echo '<pre>';
	// print_r($data);
	
	// header('Content-type: text/xml');
	// print $xml_data;
	
	// INICIO DO BLOCO DA PREPARAÇÂO DA REQUISIÇÃO
	$trataPeticion = new trataPeticion();
	// INFORMAÇÃO DA LOJA
	$chaveAcesso = 'qwertyasdf0123456789';
	$trataPeticion->DS_MERCHANT_MERCHANTCODE = '012005510536001';
	$trataPeticion->DS_MERCHANT_TERMINAL = '001';
	//FIM INFORMAÇÃO DA LOJA
	
	//Informação do Pedido
	$trataPeticion->DS_MERCHANT_AMOUNT = (100.00 * 100);
	$trataPeticion->DS_MERCHANT_ORDER = rand(1,10000000);
	//Fim Informação do Pedido
	
	//*******************AUTORIZAÇÃO DE COMPRA ***********************/
		
	// $trataPeticion->DS_MERCHANT_CURRENCY = 986; //Informação definida por padrão
	//Cartões válidos
	// Pagamento à vista:
	$trataPeticion->DS_MERCHANT_PAN = '4548812049400004' ;
	$trataPeticion->DS_MERCHANT_CVV2 = '123';
	$trataPeticion->DS_MERCHANT_EXPIRYDATE = '2012';
	
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
// if(!$result){
	// 	echo 'Falha ao enviar solicitação';
	// }

//*********Fim Cartão de Crédito ******/

//********* Cartão de Débito ******/
$result = autorizarCompraDebito($trataPeticion,$chaveAcesso);
if(!$result){
		echo 'Falha ao enviar solicitação';
		print_r($trataPeticion);
}

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


