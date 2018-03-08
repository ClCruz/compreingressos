<?php

class PagarMe_Recipient extends PagarMe_Model {

	const ENDPOINT_RECIPIENTS = '/recipients';

	public static function findAllByRecipientId($recipientId)
	{
		$request = new PagarMe_Request(
            self::ENDPOINT_RECIPIENTS . '/' . $recipientId . '/balance/operations', 'GET'
        );

        $response = $request->run();
        $class = get_called_class();
        return new $class($response);
	}

	public static function findSaldoByRecipientId($recipientId)
	{
		$request = new PagarMe_Request(
            self::ENDPOINT_RECIPIENTS . '/' . $recipientId . '/balance', 'GET'
        );

        $response = $request->run();
        $class = get_called_class();
        return new $class($response);
	}

}
