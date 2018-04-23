<?php
require_once('../settings/functions.php');

$gw_pagarme = array("apikey" => "", "postbackURI"=> "");
$gw_tipagos = array("idLoja" => "", "keyLoja"=> "", "codProduto"=> "", "url_ws"=> "");


//$type: pagarme, tipagos
function configureSplit($type) {
    global $gw_pagarme, $gw_tipagos;

    switch ($type) {
        case "pagarme":
            if ($_ENV['IS_TEST']) {
                $gw_pagarme["apikey"] = "ak_test_183DNskQiE3q7uBAA8UQjkSvENOEdY";
                $gw_pagarme["postbackURI"] = "http://homolog.compreingressos.com/comprar/pagarme_receiver.php";
            } else {
                $gw_pagarme["apikey"] = "ak_live_pcYp3eGXxpOBHqViOLfBQ61NQ4433y";
                $gw_pagarme["postbackURI"] = "http://compra.compreingressos.com/comprar/pagarme_receiver.php";
            }

            Pagarme::setApiKey($gw_pagarme["apikey"]);
            $postback_url = $gw_pagarme["postbackURI"];
            return $gw_pagarme;
    break;
        case "tipagos":
            if ($_ENV['IS_TEST']) {
                $gw_tipagos["url_ws"] = "https://www.ti-pagos.com/bridgeservices/";
                $gw_tipagos["idLoja"] = "7309"; 
                $gw_tipagos["keyLoja"] = "49994822278418282883";
                $gw_tipagos["codProduto"] = "47";
            
            } else {
                $gw_tipagos["url_ws"] = "https://www.ti-pagos.com/bridgeservices/";
                $gw_tipagos["idLoja"] = "7922"; 
                $gw_tipagos["keyLoja"] = "88281288497982783035";
                $gw_tipagos["codProduto"] = "55";
            }
            return $gw_tipagos;
        break;
    }
    return null;
}
?>