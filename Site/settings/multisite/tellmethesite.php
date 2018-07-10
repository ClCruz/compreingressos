<?php
// $whatIsTheSite = "compreingressos";
$whatIsTheSite = "ingressoslitoral";
// $type = "bringressos;"

function getCurrentSite() {
    global $whatIsTheSite;
    $ret = $whatIsTheSite;
    return $ret;
}
?>