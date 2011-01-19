<?php
session_start();
require_once('../settings/functions.php');

if (isset($_POST['codigo_pedido'])) {
	$mainConnection = mainConnection();

	if (!is_numeric($_POST['codigo_pedido'])){
		$json = json_encode(array('campainha' => '9. ok. Pedido feito direto no iPagare= ' . $_POST['codigo_pedido']));
		include('logiPagareChamada.php');
		
		ob_clean();
		echo 'OK';
		die();	
	}

	$json = json_encode(array('campainha' => '1. campainhaiPagare - retorno do ipagare'));
	include('logiPagareChamada.php');
	
	require('processarRetornoiPagare.php');
	
	if ($validado) {
		$json = json_encode(array('campainha' => '1.1 campainhaiPagare - validou'));
		include('logiPagareChamada.php');

		$query = 'SELECT 1 FROM MW_PEDIDO_VENDA WHERE ID_PEDIDO_VENDA = ? AND IN_SITUACAO = \'F\'';
		$params = array($_POST['codigo_pedido']);
		$result = executeSQL($mainConnection, $query, $params);
		
		if (!hasRows($result)) {
			$query = 'SELECT TOP 1
						 C.ID_CLIENTE,
						 C.DS_NOME,
						 C.DS_SOBRENOME,
						 C.DS_DDD_TELEFONE,
						 C.DS_TELEFONE,
						 C.DS_NOME,
						 C.DS_SOBRENOME,
						 C.CD_CPF,
						 C.CD_RG,
						 R.ID_SESSION,
						 R.CD_BINITAU
						 FROM MW_CLIENTE C
						 INNER JOIN MW_PEDIDO_VENDA PV ON PV.ID_CLIENTE = C.ID_CLIENTE
						 INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
						 INNER JOIN MW_RESERVA R ON R.ID_RESERVA = IPV.ID_RESERVA
						 WHERE PV.ID_PEDIDO_VENDA = ?';
			$params = array($_POST['codigo_pedido']);
			$dados = executeSQL($mainConnection, $query, $params, true);
			
			$query = 'SELECT DISTINCT A.CODAPRESENTACAO, B.ID_BASE, B.DS_NOME_BASE_SQL
						 FROM
						 MW_BASE B
						 INNER JOIN MW_EVENTO E ON E.ID_BASE = B.ID_BASE
						 INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
						 INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_APRESENTACAO = A.ID_APRESENTACAO
						 INNER JOIN MW_PEDIDO_VENDA P ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
						 WHERE P.ID_CLIENTE = ? AND P.ID_PEDIDO_VENDA = ? AND P.IN_SITUACAO = \'P\'';
			$params = array($dados['ID_CLIENTE'], $_POST['codigo_pedido']);
			$result = executeSQL($mainConnection, $query, $params);

			$noErrors = true;
			$retornoProcedure = '';
			
			// Definir se cliente busca ingresso
			if (isset($_SESSION["operador"])){
				//buscar ingresso
				if (isset($_COOKIE["entrega"]) && $_COOKIE["entrega"] == -1)
					$caixa = 254;
				//receber ingresso
				else if (isset($_COOKIE["entrega"]) && $_COOKIE["entrega"] != -1)
					$caixa = 252;
				//buscar ingresso
				else
					$caixa = 254;				
			} else {
				//buscar ingresso
				if (isset($_COOKIE["entrega"]) && $_COOKIE["entrega"] == -1)
					$caixa = 255;
				//receber ingresso
				else if (isset($_COOKIE["entrega"]) && $_COOKIE["entrega"] != -1)
					$caixa = 253;
				//buscar ingresso
				else
					$caixa = 255;				
			}

			
			//beginTransaction($mainConnection);
			
			while ($rs = fetchResult($result) and $noErrors) {
				$query = 'EXEC '.strtoupper($rs['DS_NOME_BASE_SQL']).'..SP_VEN_INS001_WEB ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?';
				$params = array($dados['ID_SESSION'], $rs['ID_BASE'], $_POST['codigo_pagamento'], $rs['CODAPRESENTACAO'],
									 $dados['DS_DDD_TELEFONE'], $dados['DS_TELEFONE'], ($dados['DS_NOME'].' '.$dados['DS_SOBRENOME']),
									 $dados['CD_CPF'], $dados['CD_RG'], $_POST['codigo_pedido'], $_POST['uid_pedido'],
									 $_POST['numero_autorizacao'], $_POST['numero_transacao'], $_POST['numero_cartao'],
									 $caixa);
				$retornoProcedure = executeSQL($mainConnection, $query, $params, true);
				$noErrors = ($retornoProcedure[0] and $noErrors);
			}
			
			$sqlErrors = sqlErrors();
			if ($noErrors and empty($sqlErrors)) {
				executeSQL($mainConnection, 'DELETE MW_RESERVA WHERE ID_SESSION = ?', array($dados['ID_SESSION']));
				//commitTransaction($mainConnection);

				$json = json_encode(array('campainha' => '1.6 campainhaiPagare - retorno OK - Pedido = ' . $_POST['codigo_pedido']));
				include('logiPagareChamada.php');
				
				ob_clean();

				echo 'OK';
			} else {
				include('errorMail.php');
				//rollbackTransaction($mainConnection);

				$json = json_encode(array('campainha' => '1.7 campainhaiPagare - retorno NOT OK - Pedido = ' . $_POST['codigo_pedido']));
				include('logiPagareChamada.php');
				
				echo 'NOT OK';
			}
		}
		else {
			$json = json_encode(array('campainha' => '1.9 campainhaiPagare - ok - sem necessidade de atualizar. Pedido = ' . $_POST['codigo_pedido']));
			include('logiPagareChamada.php');

			ob_clean();
			
			echo 'OK';
		}
		include('logiPagare.php');
		die();
	}
	else {
		$json = json_encode(array('campainha' => '2. Erro na validaчуo no campainhaiPagare - retorno do ipagare'));
		include('logiPagareChamada.php');

		echo 'NOT OK';
	}
}
else {
	$json = json_encode(array('campainha' => '3 Tentativa via URL. Pedido = ' . $_POST['codigo_pedido']));
	include('logiPagareChamada.php');
	ob_clean();
	echo 'OK';
}
?>