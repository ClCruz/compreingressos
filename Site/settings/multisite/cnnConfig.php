<?php
require_once("tellmethesite.php");

function multiSite_getCurrentSQLServer() {
    
    $ret = array("host" => null, "user" => null, "pass" => null);

    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = array("host" => "sqlserver.compreingressos.com"
                        ,"user" => "dev"
                        ,"pass" => "!ci@dev@2018!");
        break;
        case "ingressoslitoral":
            $ret = array("host" => "172.17.0.2"
                        ,"user" => "dev"
                        ,"pass" => "!il@dev#$");
        break;
    }
    
    return $ret;
}
function multiSite_getCurrentMysql() {
    $ret = array("host" => null, "user" => null, "pass" => null, "database" => null);
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = array("host" => "192.168.81.15"
                        ,"user" => "php"
                        ,"pass" => "SNq3mhh5Tyb59J"
                        ,"database" => "compreingressos_production");
        break;
        case "ingressoslitoral":
            $ret = array("host" => "172.17.0.3"
                        ,"user" => "php"
                        ,"pass" => "SNq3mhh5Tyb59J"
                        ,"database" => "compreingressos_production");
        break;
    }
    
    return $ret;
}

?>