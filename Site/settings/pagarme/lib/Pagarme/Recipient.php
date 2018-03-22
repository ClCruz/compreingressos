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

        $start_date_timestamp = "";
        $end_date_timestamp = "";

        if ($start_date!="") {
            $start_date_timestamp =  (string)(strtotime($start_date)*1000);
        }
        if ($end_date!="") {
            $end_date_timestamp = (string)(strtotime($end_date)*1000);
        }

        //error_log("start_date_timestamp " . $start_date_timestamp);
        //error_log("end_date_timestamp " . $end_date_timestamp);

		$request = new PagarMe_Request(
            '/balance/operations', 'GET'
        );
        $params = array("recipient_id"=> $recipientId
        ,"status" => $status
        ,"count"=> 1000
        ,"start_date" => $start_date_timestamp
        ,"end_date" => $end_date_timestamp
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

}
