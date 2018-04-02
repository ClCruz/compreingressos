<?php
require_once('../settings/functions.php');

function consultarSplitPagarme($pedido, $where, $payment_method) {
	$mainConnection = mainConnection();

	$query = "select distinct e.CodPeca, e.id_base
			  from mw_pedido_venda pv
			  inner join mw_item_pedido_venda ipv on ipv.id_pedido_venda = pv.id_pedido_venda
			  inner join mw_apresentacao a on a.id_apresentacao = ipv.id_apresentacao
			  inner join mw_evento e on e.id_evento = a.id_evento
			  where pv.id_pedido_venda = ?";
	$param = array($pedido);
	$stmt = executeSQL($mainConnection, $query, $param, true);

	$query = "SELECT r.recipient_id
	,rs.nr_percentual_split
	,rs.liable
	,rs.charge_processing_fee
	,rs.percentage_credit_web
	,rs.percentage_debit_web
	,rs.percentage_boleto_web
	,rs.percentage_credit_box_office
	,rs.percentage_debit_box_office
	FROM tabPeca tb
	INNER JOIN CI_MIDDLEWAY..mw_evento e ON tb.CodPeca=e.CodPeca
	INNER JOIN CI_MIDDLEWAY..mw_produtor p ON p.id_produtor = tb.id_produtor and p.in_ativo=1
	INNER JOIN CI_MIDDLEWAY..mw_regra_split rs ON rs.id_produtor = p.id_produtor and rs.id_evento=e.id_evento
	INNER JOIN CI_MIDDLEWAY..mw_recebedor r ON rs.id_recebedor = r.id_recebedor and r.in_ativo=1
	WHERE tb.CodPeca = ? and rs.in_ativo = 1";

	$conn = getConnection($stmt["id_base"]);
	$param = array($stmt["CodPeca"]);
	$result = executeSQL($conn, $query, $param);

	if(!hasRows($result))
		return null;

	$split = array();
	while($rs = fetchResult($result)) {
		$perToUse = 0;
		switch ($where) {
			case "web":
				switch ($payment_method) {
					case "credit":
					case "credit_card":
							$perToUse = $rs["percentage_credit_web"];
						break;
					case "boleto":
						$perToUse = $rs["percentage_boleto_web"];
						break;
					case "debit":
					case "debit_card":
						$perToUse = $rs["percentage_debit_web"];
						break;							
				}
				break;
			case "bilheteria":
				switch ($payment_method) {
					case "credit":
					case "credit_card":
							$perToUse = $rs["percentage_credit_box_office"];
						break;
					case "debit":
					case "debit_card":
						$perToUse = $rs["percentage_debit_box_office"];
						break;							
				}
				break;
		}
		$split[] = array(
			"recipient_id" => $rs["recipient_id"],
	    	"percentage" => $perToUse,
	    	"liable" => $rs["liable"],
	    	"charge_processing_fee" => $rs["charge_processing_fee"]);
	}

	return $split;
}
?>