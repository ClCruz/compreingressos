<?php
final class Util {

	/**
	 * Formata um valor para ter no máximo dois dígitos após a vírgula.
	 */
	public function formatAmount($amount){
		return round($amount, 2);
	}

	/*
	 * Formata o valor do pedido no formato exigido pelo I-PAGARE.
	 * Por exemplo, um pedido de
	 * - valor total R$ 499,99 deve ser enviado como 49999
	 * - valor total R$ 499,00 deve ser enviado como 49900
	 * - quantidade igual a 1 deve ser enviado como 100
	 * - quantidade igual a 1,5 deve ser enviado como 150
	 */
	public function formataParaIpagare($amount){
		$amountInt = (int)$amount;
		if($amountInt == $amount){
			//se o número for inteiro, então tira os zeros do final.
			$amount = (int)$amount;
			//transforma em string
			$amountStr = $amount;
			$amountStr = str_ireplace(",", "", $amountStr);
			$amountStr = str_ireplace(".", "", $amountStr);
			//adiciona os zeros dos centavos.
			$amountStr = $amountStr  . '00';
		}else{
			$amount = self::formatAmount($amount);
			$amountStr = $amount;

			$pos = strpos($amountStr, '.');
			$decimais = substr($amountStr, $pos+1);
			$tamDecimais = strlen($decimais);

			while($tamDecimais < 2){
				$amountStr = $amountStr . '0';

				$pos = strpos($amountStr, '.');
				$decimais = substr($amountStr, $pos+1);
				$tamDecimais = strlen($decimais);
			}

			$amountStr = str_ireplace(",", "", $amountStr);
			$amountStr = str_ireplace(".", "", $amountStr);
		}
			
		return $amountStr;
	}

	public function isEmpty ($str){
		if ($str == null){
			return true;
		}
		if(trim($str) == ''){
			return true;
		}
		return false;
	}

	public function convertStringToDate($dataStatus, $horaStatus){
		$hora = "00";
		$minuto = "00";
		$segundo = "00";

		if($horaStatus != null && $horaStatus){
			$hora = substr($horaStatus,0,2);
			$minuto = substr($horaStatus,2,2);
			$segundo = substr($horaStatus,4,2);
		}

		$dia = substr($dataStatus,0,2);
		$mes = substr($dataStatus,2,2);
		$ano = substr($dataStatus,4);

		$data = strftime("%d/%m/%Y %X", mktime($hora,$minuto,$segundo,$mes,$dia,$ano));

		return $data;
	}

	public function convertStringToDouble($valorStr) {
		$valorStr = str_ireplace(".", "", $valorStr);
		$valor = $valorStr /100;
		return $valor;
	}

	public function postHttp($url,$data){
		$params = '';
		foreach ($data as $name => $value) {
			$params = $params . $name . '=' . $value . '&';
		}

		$params = array('http' => array(
                  'method' => 'POST',
                  'content' => $params
		));


		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if (!$fp) {
			throw new Exception("Problem with $url, $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		if ($response === false) {
			throw new Exception("Problem reading data from $url, $php_errormsg");
		}
		return $response;
	}


}
?>