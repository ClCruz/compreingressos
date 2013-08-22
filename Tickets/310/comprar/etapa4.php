<?php
$edicao = false;
session_start();

require_once('../settings/functions.php');

require('acessoLogado.php');
require('verificarBilhetes.php');
require('verificarServicosPedido.php');
require('verificarLimitePorCPF.php');
require('verificarEntrega.php');

if (isset($_COOKIE['entrega'])) {
    $action = "verificatempo";
    $etapa = 4;
    $idestado = $_COOKIE['entrega'];
    require('calculaFrete.php');
}

$campanha = get_campanha_etapa(basename(__FILE__, '.php'));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>COMPREINGRESSOS.COM - Finaliza&ccedil;&atilde;o</title>
		<meta name="author" content="C&C - Computação e Comunicação" />
		<link href="favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="../stylesheets/smoothness/jquery-ui-1.10.3.custom.css"/>
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
		<!--[IF IE]>
		<link rel="stylesheet" href="../stylesheets/ajustesIE.css"/>
		<![ENDIF]-->
		
		<script type="text/javascript" src="../javascripts/jquery.js"></script>
		<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.cookie.js"></script>
		
		<script type="text/javascript" src="../javascripts/contagemRegressiva.js?until=<?php echo tempoRestante(); ?>"></script>
		<script type="text/javascript" src="../javascripts/carrinho.js"></script>
		<script type="text/javascript" src="../javascripts/dadosEntrega.js"></script>
		<?php echo $scriptBilheteInvalido.$scriptTempoLimiteFrete.$scriptLlimitePorCPF.$scriptValidarBin.$scriptEntrega.$scriptServicosPorPedido; ?>

		<?php echo $campanha['script']; ?>

		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-16656615-1']);
		  _gaq.push(['_setDomainName', 'compreingressos.com']);
		  _gaq.push(['_setAllowLinker', true]);
		  _gaq.push(['_trackPageview']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>
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
							<h1>Confirma&ccedil;&atilde;o</h1>
							<p class="help_text">Confira atentamente os dados do seu pedido e as condições e endereço de entrega
                                                        se for o caso.<br /><br>
                                                        <b>Clique em avançar para efetuar o pagamento.</b><br><br></p>
							<?php include "seloCertificado.php"; ?>
						</div>
						<div id="center_right">
							<div id="passos">
								<ul>
									<li><span class="numero">1. </span>Escolha de assentos</li>
									<li><span class="numero">2. </span>Conferir Itens</li>
									<li><span class="numero">3. </span>Identifica&ccedil;&atilde;o</li>
									<li class="passo_ativo"><span class="numero">4. </span>Confirma&ccedil;&atilde;o</li>
									<li><span class="numero">5. </span>Pagamento</li>
								</ul>
							</div>
							<div id="header_ticket">
								<a href="etapa2.php?eventoDS=<?php echo $_GET['eventoDS']; ?><?php echo $campanha['tag_voltar']; ?>">
									<div class="botoes_ticket" id="botao_voltar">alterar pedido</div>
								</a>
								<?php if (isset($_SESSION['operador'])) { ?>
								<a href="pagamento_cancelado.php?manualmente&operador" class="botao_cancelar">
									<div class="botoes_ticket">cancelar</div>
								</a>
								<?php } ?>
								<a href="etapa5.php?eventoDS=<?php echo $_GET['eventoDS']; ?><?php echo $campanha['tag_avancar']; ?>" id="botao_pagamento" class="botao_pagamento">
									<div class="botoes_ticket">avan&ccedil;ar</div>
								</a>
							</div>
							<?php require 'resumoPedido.php';?>
							<div id="footer_ticket">
								<a href="etapa2.php?eventoDS=<?php echo $_GET['eventoDS']; ?><?php echo $campanha['tag_voltar']; ?>">
									<div class="botoes_ticket" id="botao_voltar">alterar pedido</div>
								</a>
								<?php if (isset($_SESSION['operador'])) { ?>
								<a href="pagamento_cancelado.php?manualmente&operador" class="botao_cancelar">
									<div class="botoes_ticket">cancelar</div>
								</a>
								<?php } ?>
								<a href="etapa5.php?eventoDS=<?php echo $_GET['eventoDS']; ?><?php echo $campanha['tag_avancar']; ?>" id="botao_pagamento" class="botao_pagamento">
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