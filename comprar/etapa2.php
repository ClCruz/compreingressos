<?php
require_once('../settings/functions.php');
session_start();
$edicao = true;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>COMPREINGRESSOS.COM - Confer&ecirc;ncia de Itens</title>
		<meta name="author" content="C&C - Computação e Comunicação" />
		<link href="favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
		<!--[IF IE]>
		<link rel="stylesheet" href="../stylesheets/ajustesIE.css"/>
		<![ENDIF]-->
		<link rel="stylesheet" href="../stylesheets/smoothness/jquery-ui-1.8.4.custom.css"/>
		
		<script type="text/javascript" src="../javascripts/jquery.js"></script>
		<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.cookie.js"></script>
		
		<script type="text/javascript" src="../javascripts/contagemRegressiva.js?until=<?php echo tempoRestante(); ?>"></script>
		<script type="text/javascript" src="../javascripts/carrinho.js"></script>
		<script type="text/javascript" src="../javascripts/dadosEntrega.js"></script>
	</head>
	<body>
		<div id="background_holder">
			<div id="respiro">
				<div id="content_container">
					<?php require "header.php"; ?>
					<div id="crumbs">
						<a href="http://www.compreingressos.com">home</a> /
						<a href="#carrinho">carrinho</a> /
						<a href="#conferencia" class="selected">conferir itens</a>
					</div>
					<?php include "banners.php"; ?>
					<div id="center">
						<div id="center_left">
							<h1>Conferir itens</h1>
							<p class="help_text">Confira o(s) assento(s) escolhido(s), o pre&ccedil;o, a forma de
							entrega e clique em avan&ccedil;ar para continuar com o processo de compra.<br><br>
							Formas de entrega:<br>
							1. Retirar no local<br>
							A opção Retirar Ingresso no local funciona da seguinte maneira: No dia do espetáculo os 
							ingressos estarão disponíveis na bilheteria do Teatro em um guichê (bilheteria). Para retirar 
							o(s) ingresso(s) é necessário a apresentação de um documento de identificação (RG e/ou CPF) da 
							pessoa que realizou a compra.</p><br>
							<?php include "seloCertificado.php"; ?>
						</div>
						<div id="center_right">
							<div id="passos">
								<ul>
									<li><span class="numero">1. </span>Escolha de assentos</li>
									<li class="passo_ativo"><span class="numero">2. </span>Conferir Itens</li>
									<li><span class="numero">3. </span>Identifica&ccedil;&atilde;o</li>
									<li><span class="numero">4. </span>Confirma&ccedil;&atilde;o</li>
									<li><span class="numero">5. </span>Pagamento</li>
								</ul>
							</div>
							<div id="header_ticket">
								<a href="etapa1.php?<?php echo $_COOKIE['lastEvent']; ?>">
									<div class="botoes_ticket" id="botao_voltar">voltar</div>
								</a>
								<?php if (isset($_SESSION['operador'])) { ?>
								<a href="pagamento_cancelado.php?manualmente&operador" class="botao_cancelar">
									<div class="botoes_ticket">cancelar</div>
								</a>
								<?php } ?>
								<a href="etapa3.php?redirect=<?php echo urlencode('etapa4.php?eventoDS=' . $_GET['eventoDS']); ?>" id="botao_avancar" class="botao_avancar">
									<div class="botoes_ticket">avan&ccedil;ar</div>
								</a>
							</div>
							<?php require "resumoPedido.php"; ?>
							<div id="footer_ticket">
								<a href="etapa1.php?<?php echo $_COOKIE['lastEvent']; ?>">
									<div class="botoes_ticket" id="botao_voltar">voltar</div>
								</a>
								<?php if (isset($_SESSION['operador'])) { ?>
								<a href="pagamento_cancelado.php?manualmente&operador" class="botao_cancelar">
									<div class="botoes_ticket">cancelar</div>
								</a>
								<?php } ?>
								<a href="etapa3.php?redirect=<?php echo urlencode('etapa4.php?eventoDS=' . $_GET['eventoDS']); ?>" id="botao_avancar" class="botao_avancar">
									<div class="botoes_ticket">avan&ccedil;ar</div>
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