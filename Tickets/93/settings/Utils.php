<?php
/**
 * Conjunto de funções de uso geral.
 * @author Edicarlos Barbosa <edicarlosbarbosa@gmail.com>
 * @since 23-08-2011 14:00
 * @version 1.0.0
 * @license GNU GENERAL PUBLIC LICENSE
 */

function tratarData($data) {
    $data = explode("/", $data);
    return $data[2] . $data[1] . $data[0];
}

function formatarValor($value){
    return (is_null($value)) ? '' : $value;
}

function formatarValorNumerico($value){
    return (is_null($value)) ? '' : number_format($value, 2, ',', '.');
}

?>
