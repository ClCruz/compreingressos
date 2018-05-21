<?php

$multisite_names = array(
    "URI" => "http://www.compreingressos.com/"
    ,"URI_SSL" => "https://www.compreingressos.com/" );

function multiSite_getInfo($type) {
    global $multisite_names;
    $ret = "";

    switch ($type) {
        case "URI":
            $ret = $multisite_names["URI"];
        break;
        case "URI_SSL":
            $ret = $multisite_names["URI_SSL"];
        break;
    }
    return $ret;
}
?>