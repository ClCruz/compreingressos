<?php
include_once("../multisite/tellmethesite.php");
    
function multiSite_getFacebook() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "http://www.facebook.com/compreingressos";
        break;
        case "ingressoslitoral":
            $ret = "";
        break;
    }
    return $ret;
}
function multiSite_getTwitter() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "http://twitter.com/compreingressos";
        break;
        case "ingressoslitoral":
            $ret = "";
        break;
    }
    return $ret;
}
function multiSite_getBlog() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "http://blog.compreingressos.com/";
        break;
        case "ingressoslitoral":
            $ret = "";
        break;
    }
    return $ret;
}
function multiSite_getInstagram() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "https://www.instagram.com/compreingressos";
        break;
        case "ingressoslitoral":
            $ret = "";
        break;
    }
    return $ret;
}
function multiSite_getYoutube() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "https://www.youtube.com/compreingressos";
        break;
        case "ingressoslitoral":
            $ret = "";
        break;
    }
    return $ret;
}
function multiSite_getGooglePlus() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "https://plus.google.com/b/107039038797259256027/107039038797259256027";
        break;
        case "ingressoslitoral":
            $ret = "";
        break;
    }
    return $ret;
}

function multiSite_getName() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "COMPREINGRESSOS.COM";
        break;
        case "ingressoslitoral":
            $ret = "LITORALINGRESSOS.COM";
        break;
    }
    return $ret;
}
function multiSite_getNameWithoutDotCom() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "compreingressos";
        break;
        case "ingressoslitoral":
            $ret = "litoralingressos";
        break;
    }
    return $ret;
}
function multiSite_getEmail($type) {
    $ret = "";
    switch ($type) {
        case "lembrete":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "lembrete@compreingressos.com";
                break;
                case "ingressoslitoral":
                    $ret = "contato@litoralingressos.com";
                break;
            }
        break;
        case "marketing":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "marketing@compreingressos.com";
                break;
                case "ingressoslitoral":
                    $ret = "contato@litoralingressos.com";
                break;
            }
        break;
        case "info":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "info@compreingressos.com";
                break;
                case "ingressoslitoral":
                    $ret = "contato@litoralingressos.com";
                break;
            }
        break;
        case "sac":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "sac@compreingressos.com";
                break;
                case "ingressoslitoral":
                    $ret = "contato@litoralingressos.com";
                break;
            }
        break;
        case "pedido":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "pedido@compreingressos.com";
                break;
                case "ingressoslitoral":
                    $ret = "contato@litoralingressos.com";
                break;
            }
        break;
        case "compreingressos@gmail":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "compreingressos@gmail.com";
                break;
                case "ingressoslitoral":
                    $ret = "contato@litoralingressos.com";
                break;
            }
        break;
        case "compreingressos@siscompre":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "compreingressos@siscompre.com";
                break;
                case "ingressoslitoral":
                    $ret = "contato@litoralingressos.com";
                break;
            }
        break;
        case "compreingressospedidos@hotmail":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "compreingressospedidos@hotmail.com";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
        case "assinantea@siscompre":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "assinantea@siscompre.com";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
    }
    return $ret;
}
function multiSite_getEmailPassword($type) {
    $ret = "";
    switch ($type) {
        case "lembrete":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
        case "marketing":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
        case "info":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
        case "sac":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
        case "pedido":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
        case "compreingressos@gmail":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "743081@clc";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
        case "compreingressos@siscompre":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
        case "compreingressospedidos@hotmail":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "Clcruz121415";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
        case "assinantea@siscompre":
            switch (getCurrentSite()) {
                case "compreingressos":
                    $ret = "ci2016aa@";
                break;
                case "ingressoslitoral":
                    $ret = "";
                break;
            }
        break;
    }
    return $ret;
}
function multiSite_getPhone() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "11 2122 4070";
        break;
        case "ingressoslitoral":
            $ret = "";
        break;
    }
    return $ret;
}
function multiSite_CNPJ() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "CNPJ 72.853.328/0001-61";
        break;
        case "ingressoslitoral":
            $ret = "056.111.85/0001-94";
        break;
    }
    return $ret;
}
function multiSite_getTitle() {
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "COMPREINGRESSOS.COM - Gestão e Venda de Ingressos";
        break;
        case "ingressoslitoral":
            $ret = "LITORALINGRESSOS.COM - Venda de Ingressos";
        break;
    }
    return $ret;
}
function multiSite_getURIReeimprimir($concat = "") {
    $ret = multiSite_getDomainCompra();
    $ret = "https://" . $ret;
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "https://compra.compreingressos.com/comprar/reimprimirEmail.php?pedido=";
        break;
        case "ingressoslitoral":
            $ret = "https://compra.litoralingressos.com/comprar/reimprimirEmail.php?pedido=";
        break;
    }
    $ret .= $concat;
    return $ret;
}
function multiSite_getURICompra($concat = "") {
    $ret = multiSite_getDomainCompra();
    $ret = "https://" . $ret;

    $ret = $ret . $concat;
    return $ret;
}
function multiSite_getURIAdmin($concat = "") {
    $ret = multiSite_getDomainCompra();
    $ret = "https://" . $ret;

    $ret = $ret . "admin/" . $concat;
    return $ret;
}
function multiSite_seloCertificado() {
    $ret = "";
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "https://seal.verisign.com/getseal?host_name=compra.compreingressos.com&size=S&use_flash=NO&use_transparent=getsealjs_b.js&lang=pt";
        break;
        case "ingressoslitoral":
            $ret = "https://seal.verisign.com/getseal?host_name=compra.litoralingressos.com&size=S&use_flash=NO&use_transparent=getsealjs_b.js&lang=pt";
        break;
    }
    return $ret;    
}
function multiSite_getDomainCompra() {
    $ret = "";
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "compra.compreingressos.com/";
        break;
        case "ingressoslitoral":
            $ret = "compra.litoralingressos.com/";
        break;
    }
    return $ret;
}
function multiSite_getTomTicket() {
    $ret = "";
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "https://compreingressos.tomticket.com/kb/";
        break;
        case "ingressoslitoral":
            $ret = "";
        break;
    }
    return $ret;
}
function multiSite_getSearch($concat = "") {
    $ret = multiSite_getURI("URI_SSL");
    $ret .= "/espetaculos";
    $ret = $ret . $concat;
    return $ret;
}
function multiSite_getURI($type, $concat = "") {
    $ret = "";
    switch (getCurrentSite()) {
        case "compreingressos":
            $ret = "www.compreingressos.com/";
        break;
        case "ingressoslitoral":
            $ret = "www.litoralingressos.com/";
        break;
    }

    switch ($type) {
        case "URI":
            $ret = "http://" . $ret;
        break;
        case "URI_SSL":
            $ret = "https://" . $ret;
        break;
    }   
    $ret = $ret . $concat;
    return $ret;
}
?>