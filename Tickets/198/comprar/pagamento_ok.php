<?php
session_start();
require_once('../settings/functions.php');

$mainConnection = mainConnection();

$json = json_encode(array('descricao' => '1. chamada do pagamento_ok - codigo_pedido=' . $_POST['codigo_pedido'],'Post='=>$_POST ));
include('logiPagareChamada.php');

$campanha = get_campanha_etapa(basename(__FILE__, '.php'));

$query = 'SELECT DS_NOME FROM MW_CLIENTE WHERE ID_CLIENTE = ?';
$param = array($_SESSION['user']);
$rs = executeSQL($mainConnection, $query, $param, true);
$nome = $rs['DS_NOME'];

setcookie('pedido', '', -1);
setcookie('id_braspag', '', -1);
setcookie('entrega', '', -1);
setcookie('binItau', '', -1);

$query = 'SELECT VL_TOTAL_PEDIDO_VENDA FROM MW_PEDIDO_VENDA WHERE ID_PEDIDO_VENDA = ?';
$params = array($_GET['pedido']);
$rs = executeSQL($mainConnection, $query, $params, true);
$valorPagamento = $rs['VL_TOTAL_PEDIDO_VENDA'];

$json = json_encode(array('descricao' => '3. fim da chamada do pagamento_ok - codigo_pedido=' . $_POST['codigo_pedido'],'Post='=>$_POST ));
include('logiPagareChamada.php');
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
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

		<?php echo $campanha['script']; ?>
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
								<h1>Obrigado, <?php echo utf8_encode($nome); ?></h1>
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

		<!-- Google Code for Compra de Ingresso Conversion Page -->
		<script type="text/javascript">
		/* <![CDATA[ */
		var google_conversion_id = 1038667940;
		var google_conversion_language = "en";
		var google_conversion_format = "3";
		var google_conversion_color = "ffffff";
		var google_conversion_label = "IwGiCLKwrQMQpKGj7wM";
		var google_conversion_value = <?php echo $valorPagamento; ?>;
		/* ]]> */
		</script>
		<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
		</script>
		<noscript>
		<div style="display:inline;">
		<img height="1" width="1" style="border-style:none;" alt="" src="https://www.googleadservices.com/pagead/conversion/1038667940/?value=<?php echo $valorPagamento; ?>&amp;label=IwGiCLKwrQMQpKGj7wM&amp;guid=ON&amp;script=0"/>
		</div>
		</noscript>
	</body>
</html>