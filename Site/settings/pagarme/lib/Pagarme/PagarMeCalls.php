<?php

class PagarMe_Calls extends PagarMe_Model {
    public static function getCompany()
	{
		$request = new PagarMe_Request(
            '/company', 'GET'
        );
        $params = array();

        $response = $request->runWithParameter($params);
        $class = get_called_class();
        return new $class($response);
	}
}
?>