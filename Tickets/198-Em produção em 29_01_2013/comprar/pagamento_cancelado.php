<?php
session_start();

require_once('../settings/functions.php');
require_once('../settings/settings.php');

if (isset($_COOKIE['ipagareError'])) {
	foreach ($_COOKIE['ipagareError'] as $key => $val) {
		setcookie('ipagareError['.$key.']', '', -1);
	}
} else if (isset($_GET['manualmente']) or isset($_GET['tempoExpirado'])) {
	$mainConnection = mainConnection();
	$query = 'SELECT DISTINCT E.ID_BASE
				 FROM
				 MW_EVENTO E
				 INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
				 INNER JOIN MW_RESERVA R ON R.ID_APRESENTACAO = A.ID_APRESENTACAO
				 WHERE R.ID_SESSION = ?';
	$params = array(session_id());
	$result = executeSQL($mainConnection, $query, $params);
	
	$conn = array();
	
	$noErrors = true;
	
	while ($rs = fetchResult($result)) {
		$conn[$rs['ID_BASE']] = getConnection($rs['ID_BASE']);
		beginTransaction($conn[$rs['ID_BASE']]);
		
		$query = 'DELETE FROM TABLUGSALA WHERE ID_SESSION = ? AND STACADEIRA = \'T\'';
		$params = array(session_id());
		executeSQL($conn[$rs['ID_BASE']], $query, $params);
		
		$sqlErrors = sqlErrors();
		$noErrors = (empty($sqlErrors) and $noErrors);
	}
	
	beginTransaction($mainConnection);
	
	if (isset($_COOKIE['pedido']) and is_numeric($_COOKIE['pedido'])) {
		$query = 'UPDATE MW_PEDIDO_VENDA SET
					 IN_SITUACAO = ?
					 WHERE ID_PEDIDO_VENDA = ? AND ID_CLIENTE = ?';
		$params = array('C', $_COOKIE['pedido'], $_SESSION['user']);
		executeSQL($mainConnection, $query, $params);
		
		$sqlErrors = sqlErrors();
		$noErrors = (empty($sqlErrors) and $noErrors);
	}
	
	$query = 'DELETE FROM MW_RESERVA
				 WHERE ID_SESSION = ?';
	$params = array(session_id());
	executeSQL($mainConnection, $query, $params);
	
	$sqlErrors = sqlErrors();
	$noErrors = (empty($sqlErrors) and $noErrors);
	
	$sqlErrors = sqlErrors();
	if ($noErrors and empty($sqlErrors)) {
		setcookie('pedido', '', -1);
		setcookie('id_braspag', '', -1);
		commitTransaction($mainConnection);
		foreach ($conn as $connection) {
			commitTransaction($connection);
		}
	} else {
		rollbackTransaction($mainConnection);
		foreach ($conn as $connection) {
			rollbackTransaction($connection);
		}
	}
	
	setcookie('binItau', '', -1);
	
	if (isset($_SESSION['operador'])) {
		unset($_SESSION['user']);
		setcookie('pedido', '', -1);
		setcookie('id_braspag', '', -1);
		header("Location: etapa0.php");
	}
}

$campanha = get_campanha_etapa('etapa5');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>COMPREINGRESSOS.COM - Finaliza&ccedil;&atilde;o</title>
		<meta name="author" content="C&C - Computação e Comunicação" />
		<link href="favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
		
		<script type="text/javascript" src="../javascripts/jquery.js"></script>
		<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
	</head>
	<body>
		<div id="background_holder">
			<div id="respiro">
				<div id="content_container">
					<?php require "header.php"; ?>
					<div id="crumbs">
						<a href="http://www.compreingressos.com">home</a> / <a href="#carrinho" class="selected">carrinho</a>
					</div>
					<?php include "banners.php"; ?>
					<div id="center">
						<div id="center_left">
							<h1>Pedido cancelado</h1>
							<?php include "seloCertificado.php"; ?>
						</div>
						<div id="ticket_net">
							<div class="titulo">
								<?php
								if (isset($_COOKIE['ipagareError'])) {
								?>
									<h3>Seu pedido foi negado!</h3>
									<p class="msg_ipagarert">Transação não autorizada. Verifique os dados do cartão.</p>
									<p>Por favor, clique no botão abaixo para tentar novamente ou cancele esse pedido.</p>
							</div>
							<div id="footer_ticket">
								<?php if ($_COOKIE['ipagareError']['codigo_erro'] != '201') { ?>
							    <a href="etapa5.php?falha<?php echo $campanha['tag_voltar']; ?>">
									<div class="botoes_ticket" id="botao_voltar">tentar novamente</div>
								</a>
								<a href="pagamento_cancelado.php?manualmente">
									<div class="botoes_ticket" id="botao_avancar">cancelar</div>
								</a>
								<?php } else { ?>
								    <?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
									    <a href="http://www.compreingressos.com/">
										    <div class="botoes_ticket" id="botao_avancar">home</div>
									    </a>
								    <?php } else { ?>
									    <a href="etapa0.php">
										    <div class="botoes_ticket" id="botao_avancar">nova venda</div>
									    </a>
								    <?php } ?>
								<?php }?>
							</div>
								<?php
								} else if (isset($_GET['captcha'])) {
									?>
									<h3>Seu pedido foi negado!</h3>
									<p>O código informado não corresponde à imagem/áudio.</p>
									<p>Por favor clique no botão abaixo para tentar novamente ou cancele esse pedido.</p>
							</div>
							<div id="footer_ticket">
								<?php if ($_COOKIE['ipagareError']['codigo_erro'] != '201') { ?>
							    <a href="etapa5.php?falha<?php echo $campanha['tag_voltar']; ?>">
									<div class="botoes_ticket" id="botao_voltar">tentar novamente</div>
								</a>
								<a href="pagamento_cancelado.php?manualmente">
									<div class="botoes_ticket" id="botao_avancar">cancelar</div>
								</a>
								<?php } else { ?>
								    <?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
									    <a href="http://www.compreingressos.com/">
										    <div class="botoes_ticket" id="botao_avancar">home</div>
									    </a>
								    <?php } else { ?>
									    <a href="etapa0.php">
										    <div class="botoes_ticket" id="botao_avancar">nova venda</div>
									    </a>
								    <?php } ?>
								<?php }?>
							</div>
									<?php
								} else {
								    ?><h3>Seu pedido foi cancelado!</h3><?php
									if (isset($_GET['manualmente'])) {
								?>
								<p>Voc&ecirc; pode recome&ccedil;ar clicando no bot&atilde;o "iniciar" ou continuar navegando no nosso portal clicando no bot&atilde;o "home".</p>
							</div>
							<div id="footer_ticket">
							<?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
								<a href="http://www.compreingressos.com/">
									<div class="botoes_ticket" id="botao_voltar">home</div>
								</a>
								<a href="etapa1.php?<?php echo $_COOKIE['lastEvent']; ?>">
									<div class="botoes_ticket" id="botao_avancar">iniciar</div>
								</a>
							<?php } else { ?>
								<a href="etapa0.php">
									<div class="botoes_ticket" id="botao_avancar">nova venda</div>
								</a>
							<?php } ?>
							</div>
								<?php
									} else {
								?>
								<p>Voc&ecirc; excedeu o tempo limite de <?php echo $compraExpireTime; ?> minutos para completar a opera&ccedil;&atilde;o.</p>
								<p>Por favor inicie e fa&ccedil;a seu pedido novamente.</p>
							</div>
							<div id="footer_ticket">
								<a href="etapa1.php?<?php echo $_COOKIE['lastEvent']; ?>">
									<div class="botoes_ticket" id="botao_avancar">iniciar</div>
								</a>
							</div>
								<?php
									}
								}
								?>
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