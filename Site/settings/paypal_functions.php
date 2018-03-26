<?php
require_once('../settings/functions.php');

require_once('../settings/settings.php');

	function getObjFromString($json) {
        return json_decode($json, true);
    }
    function getObjToSave($id_pedido_venda, $json_data, $json_payment) {
        $paypal_data = getObjFromString($json_data);
        $paypal_payment = getObjFromString($json_payment);

        $toReturn = array(
            "id_pedido_venda"=>$id_pedido_venda,
            "paymentToken"=>$paypal_data["paymentToken"],
            "orderID"=>$paypal_data["orderID"],
            "payerID"=>$paypal_data["payerID"],
            "paymentID"=>$paypal_data["paymentID"],
            "dataJSON"=>utf8_encode($json_data),
            "paymentJSON"=>utf8_encode($json_payment),
            "state"=>$paypal_payment["state"],
            "cart"=>$paypal_payment["cart"],
            "amount"=>$paypal_payment["transactions"][0]["amount"]["total"] ,            
        );
		
		return $toReturn;
    }

    function paypal_saveTo($obj) {
		$mainConnection = mainConnection();
        try {
            $query = "INSERT INTO [mw_gateway_paypal]
                    ([id_pedido_venda]
                    ,[dt_criacao]
                    ,[paymentToken]
                    ,[orderID]
                    ,[payerID]
                    ,[paymentID]
                    ,[dataJSON]
                    ,[paymentJSON]
                    ,[state]
                    ,[cart])
                VALUES
                    (?
                    ,GETDATE()
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?)";

            $params = array($obj["id_pedido_venda"]
            ,$obj["paymentToken"]
            ,$obj["orderID"]
            ,$obj["payerID"]
            ,$obj["paymentID"]
            ,$obj["dataJSON"]
            ,$obj["paymentJSON"]
            ,$obj["state"]
            ,$obj["cart"]);

            // error_log("query. " . $query);
            // error_log("params " . print_r($params, true));

            $result = executeSQL($mainConnection, $query, $params);

            // $sqlErrors = sqlErrors();
            // if ($errors and empty($sqlErrors)) {
 
            // } else {
            //     error_log("erro: ".print_r($sqlErrors, true));
            // }
        } catch (SoapFault $e) {
            error_log("paypal_functions .2 - error in paypal_saveTo: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("paypal_functions .3 - error in paypal_saveTo: " . $e->getMessage());
        }
    }

?>