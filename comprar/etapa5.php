<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');
require 'acessoLogado.php';
require 'processarDadosCompra.php';
$json = json_encode(array('descricao' => 'entrada no etapa5 - chamada ao ipagare'));
require 'logiPagareChamada.php';
ob_end_clean();//correção para faixa branca no topo
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
		
		<script type="text/javascript" src="../javascripts/contagemRegressiva.js?until=<?php echo tempoRestante(); ?>"></script>
	</head>
	<body>
		<div id="background_holder">
			<div id="respiro">
				<div id="content_container">
					<?php require "header.php"; ?>
					<div id="crumbs">
						<a href="http://www.compreingressos.com">home</a> /
						<a href="#carrinho">carrinho</a> /
						<a href="#pagamento" class="selected">pagamento</a>
					</div>
					<?php include "banners.php"; ?>
					<div id="center">
						<div id="center_left">
							<h1>Pagamento</h1>
							<p class="help_text">Escolha o cart&atilde;o de cr&eacute;dito e preencha seus dados para
							efetuar o pagamento.</p>
							<?php include "seloCertificado.php"; ?>
						</div>
						<div id="center_right">
							<div id="passos">
								<ul>
									<li><span class="numero">1. </span>Escolha de assentos</li>
									<li><span class="numero">2. </span>Conferir Itens</li>
									<li><span class="numero">3. </span>Identifica&ccedil;&atilde;o</li>
									<li><span class="numero">4. </span>Confirma&ccedil;&atilde;o</li>
									<li class="passo_ativo"><span class="numero">5. </span>Pagamento</li>
								</ul>
							</div>
							<div id="header_ticket">
								<a href="etapa4.php">
									<div class="botoes_ticket" id="botao_voltar">voltar</div>
								</a>
								<a href="pagamento_cancelado.php?manualmente<?php echo (isset($_SESSION['operador'])) ? '&operador' : '';?>">
									<div class="botoes_ticket" id="botao_avancar">cancelar</div>
								</a>
							</div>
							<div id="forma_pagamento">
								<?php
									echo $iPagare;
									//echo '<pre>';
									//print_r($parametros);
									//echo '</pre>';
								?>
							</div>
							<div id="footer_ticket">
								<a href="etapa4.php">
									<div class="botoes_ticket" id="botao_voltar">voltar</div>
								</a>
								<a href="pagamento_cancelado.php?manualmente<?php echo (isset($_SESSION['operador'])) ? '&operador' : '';?>">
									<div class="botoes_ticket" id="botao_avancar">cancelar</div>
								</a>
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