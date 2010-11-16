<?php
session_start();
require_once('../settings/functions.php');

$mainConnection = mainConnection();

$json = json_encode(array('descricao' => '1. chamada do pagamento_ok - codigo_pedido=' . $_POST['codigo_pedido'],'Post='=>$_POST ));
include('logiPagareChamada.php');


if (isset($_POST['codigo_pedido'])) {
	$json = json_encode(array('descricao' => '2. entrada no pagamento_ok - retorno do ipagare'));
	include('logiPagareChamada.php');
	
	require('processarRetornoiPagare.php');
	
	if ($validado) {
		$query = 'SELECT 1 FROM MW_PEDIDO WHERE ID_PEDIDO = ? AND IN_SITUACAO = \'F\'';
		$params = array($_POST['codigo_pedido']);
		$result = executeSQL($mainConnection, $query, $params);
		
		if (!hasRows($result)) {
			$query = 'SELECT DS_NOME, DS_SOBRENOME, DS_DDD_TELEFONE, DS_TELEFONE,
						 DS_NOME, DS_SOBRENOME, CD_CPF, CD_RG FROM MW_CLIENTE WHERE ID_CLIENTE = ?';
			$params = array($_SESSION['user']);
			$dados = executeSQL($mainConnection, $query, $params, true);
			
			$query = 'SELECT DISTINCT A.CODAPRESENTACAO, B.ID_BASE, B.DS_NOME_BASE_SQL
						 FROM
						 MW_BASE B
						 INNER JOIN MW_EVENTO E ON E.ID_BASE = B.ID_BASE
						 INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
						 INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_APRESENTACAO = A.ID_APRESENTACAO
						 INNER JOIN MW_PEDIDO_VENDA P ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
						 WHERE P.ID_CLIENTE = ? AND P.ID_PEDIDO_VENDA = ? AND P.IN_SITUACAO = \'P\'';
			$params = array($_SESSION['user'], $_POST['codigo_pedido']);
			$result = executeSQL($mainConnection, $query, $params);
			
			$noErrors = true;
			$retornoProcedure = '';
			$sqlErrors = array();
			
			// Definir se cliente busca ingresso
			if(isset($_SESSION["operador"])){
				//buscar ingresso
				if(isset($_COOKIE["entrega"]) && $_COOKIE["entrega"] == -1)
					$caixa = 254;
				//receber ingresso
				else if(isset($_COOKIE["entrega"]) && $_COOKIE["entrega"] != -1)
					$caixa = 252;
				//buscar ingresso
				else
					$caixa = 254;				
			}else{
				//buscar ingresso
				if(isset($_COOKIE["entrega"]) && $_COOKIE["entrega"] == -1)
					$caixa = 255;
				//receber ingresso
				else if(isset($_COOKIE["entrega"]) && $_COOKIE["entrega"] != -1)
					$caixa = 253;
				//buscar ingresso
				else
					$caixa = 255;				
			}
			
			//beginTransaction($mainConnection);
			
			while ($rs = fetchResult($result) and $noErrors) {
				$query = 'EXEC '.strtoupper($rs['DS_NOME_BASE_SQL']).'..SP_VEN_INS001_WEB ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?';
				$params = array(session_id(), $rs['ID_BASE'], $_POST['codigo_pagamento'], $rs['CODAPRESENTACAO'],
									 $dados['DS_DDD_TELEFONE'], $dados['DS_TELEFONE'], ($dados['DS_NOME'].' '.$dados['DS_SOBRENOME']),
									 $dados['CD_CPF'], $dados['CD_RG'], $_POST['codigo_pedido'], $_POST['uid_pedido'],
									 $_POST['numero_autorizacao'], $_POST['numero_transacao'], $_POST['numero_cartao'], $caixa);
				$retornoProcedure = executeSQL($mainConnection, $query, $params, true);
				$noErrors = ($retornoProcedure[0] and $noErrors);
				$sqlErrors = sqlErrors();
				
				$json = json_encode(array('sp_ven_ins001_web' => $query,'params=' => $params, 'retorno_procedure' => $retornoProcedure));
				include('logiPagareChamada.php');

			}
			
			$sqlErrors = sqlErrors();
			if ($noErrors and empty($sqlErrors)) {
				executeSQL($mainConnection, 'DELETE MW_RESERVA WHERE ID_SESSION = ?', array(session_id()));
				//commitTransaction($mainConnection);
			} else {
				include('errorMail.php');
				//rollbackTransaction($mainConnection);
			}
		}
		?>
			<script>
			top.window.location = 'pagamento_ok.php?pedido=<?php echo $_POST['codigo_pedido']; ?>';
			</script>
		<?php
		include('logiPagare.php');
		exit();
	} else {
		echo 'Dados Inválidos<br><br>Não tente alterar os dados enviados pelo iPagare.';
	}
} else {
	$query = 'SELECT DS_NOME FROM MW_CLIENTE WHERE ID_CLIENTE = ?';
	$param = array($_SESSION['user']);
	$rs = executeSQL($mainConnection, $query, $param, true);
	$nome = $rs['DS_NOME'];
	
	setcookie('pedido', '', -1);
	setcookie('entrega', '', -1);
}

$json = json_encode(array('descricao' => '3. fim da chamada do pagamento_ok - codigo_pedido=' . $_POST['codigo_pedido'],'Post='=>$_POST ));
include('logiPagareChamada.php');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>COMPREINGRESSOS.COM - Finaliza&ccedil;&atilde;o</title>
		<meta name="author" content="C&C - Computação e Comunicação" />
		<link href="favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/annotations.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css">
		
		<script type="text/javascript" src="../javascripts/jquery.js"></script>
		<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>
	</head>
	<body>
		<div id="background_holder">
			<div id="respiro">
				<div id="content_container">
					<?php require "header.php"; ?>
					<div id="crumbs">
						<a href="http://www.compreingressos.com">home</a> /
						<a href="#carrinho" class="selected">carrinho</a>
					</div>
					<?php include "banners.php"; ?>
					<div id="center">
						<div id="center_left">
							<h1>Pagamento</h1>
							<p class="help_text">Pagamento finalizado.</p>
							<?php include "seloCertificado.php"; ?>
						</div>
						<div id="center_right">
							<div id="passos">
								<ul>
									<li><span class="numero">1. </span>Escolha de assentos</li>
									<li><span class="numero">2. </span>Conferir Itens</li>
									<li><span class="numero">3. </span>Identifica&ccedil;&atilde;o</li>
									<li><span class="numero">4. </span>Finaliza&ccedil;&atilde;o</li>
									<li class="passo_ativo"><span class="numero">5. </span>Pagamento</li>
								</ul>
							</div>
							<div class="titulo with_border_bottom">
								<h1>Obrigado, <?php echo $nome; ?></h1>
							</div>
							<div class="titulo">
								<h1>Seu pagamento foi conclu&iacute;do com sucesso!</h1>
								<p>O n&uacute;mero do seu pedido &eacute; <a href="minha_conta.php?pedido=<?php echo $_GET['pedido']; ?>" <?php echo (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) ? 'target="_blank"' : ''; ?>><?php echo $_GET['pedido']; ?></a></p>
								<p>Para visualiz&aacute;-lo basta clicar no link acima ou acessar o menu <a href="minha_conta.php" <?php echo (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) ? 'target="_blank"' : ''; ?>>minha conta</a></p>
							</div>
							<div id="footer_ticket">
							<?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
								<a href="http://www.compreingressos.com/">
									<div class="botoes_ticket" id="botao_avancar">home</div>
								</a>
							<?php } else { ?>
								<a href="etapa0.php">
									<div class="botoes_ticket" id="botao_avancar">nova venda</div>
								</a>
							<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- fim respiro -->
		</div>
		<!-- fim background -->
		<?php include "footer.php"; ?>
	</body>
</html>