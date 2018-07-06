<?php
include_once("../multisite/tellmethesite.php");

function multiSite_getLogo() {
    $ret = "";
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "../images/menu_logo.png";
        break;
        case "ingressoslitoral":
            $ret = "../images/multi_litoralingressos/logo_header.png";
        break;
    }
    
    return $ret;
}
function multiSite_getLogoFullURI() {
    $ret = "";
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = multiSite_getURI("URI_SSL", "images/menu_logo.png");
        break;
        case "ingressoslitoral":
            $ret = multiSite_getURI("URI_SSL", "images/multi_litoralingressos/logo_header.png");
        break;
    }
    
    return $ret;
}
function multiSite_getGoogleAnalytics() {
    $ret = "";
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "UA-16656615-1";
        break;
        case "ingressoslitoral":
            $ret = "UA-16656615-1";
        break;
    }
    
    return $ret;
}
function mulsiSite_getFavico() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "<?php echo mulsiSite_getFavico()?>";
        break;
        case "ingressoslitoral":
            $ret = "../images/multi_litoralingressos/favicon.ico";
        break;
    }
    return $ret;
}
function multiSite_getDefaultMiniatura() {
    $ret = "";

    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "images/default_espetaculo.jpg";
        break;
        case "ingressoslitoral":
            $ret = "images/multi_litoralingressos/default_miniatura.png";
        break;
    }
    
    return $ret;
}
?>