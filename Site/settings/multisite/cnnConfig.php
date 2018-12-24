<?php
require_once("tellmethesite.php");
$isDev = true;

function multiSite_getCurrentSQLServer() {
    global $isDev;
    
    $ret = array("host" => null, "user" => null, "pass" => null, "port"=> "1433");

    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = array("host" => "192.168.81.17"
                        ,"user" => "web"
                        ,"pass" => "!ci@web@2018!"
                        , "port"=> "1433");
        break;
        case "ingressoslitoral":
            if ($isDev) {
                $ret = array("host" => "191.252.102.210"
                ,"user" => "dev"
                ,"pass" => "!il@dev#$"
                , "port"=> "1533");
            }
            else {
                $ret = array("host" => "172.17.0.2"
                            ,"user" => "dev"
                            ,"pass" => "!il@dev#$"
                            , "port"=> "1433");
            }
        break;
    }
    
    return $ret;
}
function multiSite_getCurrentMysql() {
    $ret = array("host" => null, "user" => null, "pass" => null, "database" => null);
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = array("host" => "192.168.81.22"
                        ,"user" => "php"
                        ,"pass" => "SNq3mhh5Tyb59J"
                        ,"database" => "compreingressos_production"
                        , "port"=> "4003");
        break;
        case "ingressoslitoral":
            $ret = array("host" => "172.17.0.4"
                        ,"user" => "php"
                        ,"pass" => "SNq3mhh5Tyb59J"
                        ,"database" => "compreingressos_production"
                        , "port"=> "3306");
        break;
    }
    
    return $ret;
}

?>