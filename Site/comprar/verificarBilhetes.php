<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

$query = "SELECT COUNT(1) INGRESSOS_NULOS FROM MW_RESERVA WHERE ID_SESSION = ? AND ID_APRESENTACAO_BILHETE IS NULL";
$rs = executeSQL($mainConnection, $query, array(session_id()), true);

$msgBilheteInvalido = 'Não é possível concluir o pedido.<br><br>Favor alterar o pedido para continuar.';

if ($rs['INGRESSOS_NULOS'] > 0) {
	if (basename($_SERVER['SCRIPT_FILENAME']) == 'etapa5.php') {
		header("Location: etapa4.php");
	} else {
		$scriptBilheteInvalido = '<script type="text/javascript">
											$(function(){
												$.dialog({title:"Aviso...", text:"'.$msgBilheteInvalido.'", uiOptions:{width:500}});
											});
										</script>';
	}
}

//mensagem desnecessária, o erro genérico de pedido inválido já é exibido
$scriptBilheteInvalido = '';