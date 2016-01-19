<?php
// print_order(620201, true);die();
$mainConnection = mainConnection();
echo_header();

if (isset($_GET["cpf"])) {	

	$query = "SELECT ID_CLIENTE, DS_NOME, DS_SOBRENOME FROM MW_CLIENTE WHERE CD_CPF = ?";
    $params = array($_GET['cpf']);
    $rs = executeSQL($mainConnection, $query, $params, true);
    $id_cliente = $rs['ID_CLIENTE'];
    $nome_cliente = $rs['DS_NOME'] ." ". $rs['DS_SOBRENOME'];

    $query ="SELECT DISTINCT PV.ID_PEDIDO_VENDA,                
                PV.DT_PEDIDO_VENDA, 
                PV.VL_TOTAL_PEDIDO_VENDA
            FROM MW_PEDIDO_VENDA PV
            INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
            INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = IPV.ID_APRESENTACAO AND A.IN_ATIVO = 1
            INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = 1
            WHERE ID_CLIENTE = 27226 AND IN_SITUACAO = 'F'
            AND A.DT_APRESENTACAO >= GETDATE()
            ORDER BY 1 DESC";
    $params = array($id_cliente);
    $result = executeSQL($mainConnection, $query, $params);    

    if (!hasRows($result)) {
    	display_error("Não existem ingressos para o CPF informado.", utf8_decode("Atenção"));
    	die();
    }

    $pedido_options = array(999 => 'Voltar');

	while ($rs = fetchResult($result)) {
		$pedido_options[$rs['ID_PEDIDO_VENDA']] = $rs['ID_PEDIDO_VENDA'] ." - ". $rs['DT_PEDIDO_VENDA']->format('d/m/y') . " - " . number_format($rs['VL_TOTAL_PEDIDO_VENDA'], 2, ',', '');
	}

    echo "<WRITE_AT LINE=5 COLUMN=0> $nome_cliente</WRITE_AT>";
    echo "<WRITE_AT LINE=7 COLUMN=0> Selecione o Pedido:</WRITE_AT>";
    
    echo_select('pedido', $pedido_options, 5);

    echo "<POST>";

} else if(isset($_GET["pedido"])) {	

	$query ="SELECT
				E.DS_EVENTO,
				A.DT_APRESENTACAO,
				A.HR_APRESENTACAO,
				A.DS_PISO,
				R.ID_APRESENTACAO_BILHETE,
				COUNT(R.ID_RESERVA) AS QTD_INGRESSOS,
				AB.DS_TIPO_BILHETE
			FROM MW_ITEM_PEDIDO_VENDA R
			INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
			INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
			INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
			WHERE ID_PEDIDO_VENDA = ?
			GROUP BY
				E.DS_EVENTO,
				A.DT_APRESENTACAO,
				A.HR_APRESENTACAO,
				A.DS_PISO,
				R.ID_APRESENTACAO_BILHETE,
				AB.DS_TIPO_BILHETE";

	$result = executeSQL($mainConnection, $query, array($_GET['pedido']));

	$total_ingressos = 0;
	$total_servico = 0;

	$confirmacao_options = array();

	while ($rs = fetchResult($result)) {

		if ($last_title != $rs['DS_EVENTO'].$rs['DT_APRESENTACAO']->format('d/m/Y').$rs['HR_APRESENTACAO'].$rs['DS_PISO']) {

			if (count($confirmacao_options) > 2) $confirmacao_options[] = ' ';

			$confirmacao_options[] = utf8_encode($rs['DS_EVENTO']);
			$confirmacao_options[] = $rs['DT_APRESENTACAO']->format('d/m/Y').' '.$rs['HR_APRESENTACAO'];
			$confirmacao_options[] = utf8_encode($rs['DS_PISO']);
			
			$last_title = $rs['DS_EVENTO'].$rs['DT_APRESENTACAO']->format('d/m/Y').$rs['HR_APRESENTACAO'].$rs['DS_PISO'];

			$confirmacao_options[] = ' ';
		}

		$confirmacao_options[] = utf8_encode(str_pad(substr($rs['DS_TIPO_BILHETE'], 0, 24), 24, ' ', STR_PAD_RIGHT).' x'.str_pad($rs['QTD_INGRESSOS'], 2, ' ', STR_PAD_LEFT));
	}

	$confirmacao_options[] = ' ';
	$confirmacao_options[999] = 'Voltar';
	$confirmacao_options[888] = 'Confirmar';

	echo_select('confirmacao', $confirmacao_options, 0);

	echo "<GET TYPE=HIDDEN NAME=id_pedido VALUE=".$_GET['pedido'].">";
	echo "<GET TYPE=HIDDEN NAME=ignore_history VALUE=1>";

	echo "<POST>";

} else if (isset($_GET["confirmacao"])) {

	include('../settings/Log.class.php');

	$log = new Log($_SESSION['admin']);
    $log->__set('funcionalidade', 'Reimpressão POS');
    $log->__set('parametros', array($_GET['id_pedido']));
    $log->__set('log', "Pedido ?");
    $log->save($mainConnection);
	
	print_order($_GET['id_pedido'], true);

	echo "<GET TYPE=HIDDEN NAME=reset VALUE=1>";

	echo "<POST>";

} else {

	echo "<WRITE_AT LINE=7 COLUMN=0> Informe o CPF:</WRITE_AT>";

	echo "<GET TYPE=CPF NAME=cpf COL=1 LIN=10>";

	echo "<POST>";

}