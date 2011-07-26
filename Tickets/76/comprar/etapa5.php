<?php
ob_start();
require_once('../settings/functions.php');
require_once('../settings/settings.php');

require('acessoLogado.php');
require('verificarLimitePorCPF.php');

if (isset($_COOKIE['entrega'])) {
    $action = "verificatempo";
    $etapa = 4;
    $idestado = $_COOKIE['entrega'];
    require('calculaFrete.php');
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>COMPREINGRESSOS.COM - Finaliza&ccedil;&atilde;o</title>
	<meta name="author" content="C&C - Computação e Comunicação" />
	<link href="favicon.ico" rel="shortcut icon"/>
	<link rel="stylesheet" href="../stylesheets/smoothness/jquery-ui-1.8.4.custom.css"/>
	<link rel="stylesheet" href="../stylesheets/ci.css"/>
	<link rel="stylesheet" href="../stylesheets/annotations.css"/>
	<link rel="stylesheet" href="../stylesheets/ajustes.css">

	<script type="text/javascript" src="../javascripts/jquery.js"></script>
	<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
	<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>

	<script type="text/javascript" src="../javascripts/contagemRegressiva.js?until=<?php echo tempoRestante(); ?>"></script>

	<script>
	    $(function(){
		$('.botao_pagamento').click(function(e){
		    e.preventDefault();
		    $('#dadosPagamento').submit();
		});
	    });
	</script>
    </head>
    <body>
	<div id="travaOverlay" title="Atenção..." class="ui-helper-hidden">
		    Esta operação pode demorar alguns segundos para ser finalizada.<br/><br/>
		    Por favor, não feche ou atualize seu navegador até que o processamento seja concluído.<br/><br/>
		    Assim que possível você será redirecionado(a) para a página final.
	</div>
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
			    <p class="help_text">Escolha o cart&atilde;o de cr&eacute;dito de sua preferência, preencha os dados e clique em Efetuar Pagamento
				para finalizar o seu pedido.<br><br></p>
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
				<a href="etapa4.php?eventoDS=<?php echo $_GET['eventoDS']; ?>">
				    <div class="botoes_ticket" id="botao_voltar">voltar</div>
				</a>
				<?php if (isset($_SESSION['operador'])) {
 ?>
    				<a href="pagamento_cancelado.php?manualmente&operador" class="botao_cancelar">
    				    <div class="botoes_ticket">cancelar</div>
    				</a>
				<?php } ?>
<?php if (!$_POST) { ?>
    				<a href="#" id="botao_pagamento" class="botao_pagamento">
    				    <div class="botoes_ticket">Efetuar Pagamento</div>
    				</a>
<?php } else { ?>
    				<a href="etapa5.php?eventoDS=<?php echo $_GET['eventoDS']; ?>" id="botao_avancar" class="botao_avancar">
    				    <div class="botoes_ticket">tentar novamente</div>
    				</a>
    				<a href="etapa2.php?eventoDS=<?php echo $_GET['eventoDS']; ?>" style="margin-right:15px;" id="botao_avancar" class="botao_avancar">
    				    <div class="botoes_ticket">alterar pedido</div>
    				</a>
<?php } ?>
			    </div>
			    <div id="forma_pagamento">
<?php require('formCartao.php'); ?>
			    </div>
			    <div id="footer_ticket">
				<a href="etapa4.php?eventoDS=<?php echo $_GET['eventoDS']; ?>">
				    <div class="botoes_ticket" id="botao_voltar">voltar</div>
				</a>
<?php if (isset($_SESSION['operador'])) { ?>
    				<a href="pagamento_cancelado.php?manualmente&operador" class="botao_cancelar">
    				    <div class="botoes_ticket">cancelar</div>
    				</a>
				<?php } ?>
<?php if (!$_POST) { ?>
    				<a href="#" id="botao_pagamento" class="botao_pagamento">
    				    <div class="botoes_ticket">Efetuar Pagamento</div>
    				</a>
<?php } else { ?>
    				<a href="etapa5.php?eventoDS=<?php echo $_GET['eventoDS']; ?>" id="botao_avancar" class="botao_avancar">
    				    <div class="botoes_ticket">tentar novamente</div>
    				</a>
    				<a href="etapa2.php?eventoDS=<?php echo $_GET['eventoDS']; ?>" style="margin-right:15px;" id="botao_avancar" class="botao_avancar">
    				    <div class="botoes_ticket">alterar pedido</div>
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