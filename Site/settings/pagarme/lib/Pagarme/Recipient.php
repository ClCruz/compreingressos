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
    

    public static function getOperationHistory($recipientId, $status, $count, $start_date, $end_date)
	{
        //status: waiting_funds
        //status: available
        //status: transferred

        if ($status == "") {
            $status = "waiting_funds";
        }

        //error_log("start_date_timestamp " . $start_date_timestamp);
        //error_log("end_date_timestamp " . $end_date_timestamp);

		$request = new PagarMe_Request(
            '/balance/operations', 'GET'
        );
        $params = array("recipient_id"=> $recipientId
        ,"status" => $status
        ,"count"=> 1000
        ,"start_date" => $start_date
        ,"end_date" => $end_date
        );

        $response = $request->runWithParameter($params);
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
    
    public static function getLimits($recipientId, $payment_date, $timeframe)
	{
		$request = new PagarMe_Request(
            self::ENDPOINT_RECIPIENTS . '/' . $recipientId . '/bulk_anticipations/limits', 'GET'
        );

        $params = array("payment_date"=> $payment_date
        ,"timeframe" => $timeframe
        );

        $response = $request->runWithParameter($params);

        $class = get_called_class();
        return new $class($response);
    }
    public static function getResumo($recipientId, $amount, $payment_date, $timeframe)
	{
		$request = new PagarMe_Request(
            self::ENDPOINT_RECIPIENTS . '/' . $recipientId . '/bulk_anticipations', 'POST'
        );
     
        $params = array("payment_date"=> $payment_date
        ,"timeframe" => $timeframe
        ,"requested_amount" => $amount
        ,"build" => true
        );
        error_log("1.");
        $response = $request->runWithParameter($params);
        error_log("2.");
        $class = get_called_class();
        error_log("3.");
        $id = $response->getId();
        error_log("4.");
        error_log("5." . $id);
        $request2 = new PagarMe_Request(
            self::ENDPOINT_RECIPIENTS . '/' . $recipientId . '/bulk_anticipations/' . $id, 'DELETE'
        );
        error_log("6.");
        $params2 = array("build" => true);
        error_log("7.");
        $response2 = $request2->runWithParameter($params2);
        error_log("8.");

        return new $class($response);
	}

}
