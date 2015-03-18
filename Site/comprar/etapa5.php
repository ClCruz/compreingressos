<?php
ob_start();
require_once('../settings/functions.php');
require_once('../settings/settings.php');

if ($is_manutencao === true) {
	header("Location: manutencao.php");
	die();
}

require('acessoLogado.php');
require('verificarBilhetes.php');
require('verificarServicosPedido.php');
require('verificarLimitePorCPF.php');
require('verificarEntrega.php');
require('verificarAssinatura.php');

if (isset($_COOKIE['entrega'])) {
    $action = "verificatempo";
    $etapa = 4;
    $idestado = $_COOKIE['entrega'];
    require('calculaFrete.php');
}

$json = json_encode(array('descricao' => ($_POST ? '2.' : '1.') .' etapa5 - ' . ($_POST ? 'envio de dados' : 'formulario cartao')));
include('logiPagareChamada.php');

$campanha = get_campanha_etapa(basename(__FILE__, '.php'));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow">
	<link href="../images/favicon.ico" rel="shortcut icon"/>
	<link href='https://fonts.googleapis.com/css?family=Paprika|Source+Sans+Pro:200,400,400italic,200italic,300,900' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="../stylesheets/cicompra.css"/>
    <?php require("desktopMobileVersion.php"); ?>
	<link rel="stylesheet" href="../stylesheets/ajustes2.css"/>

	<script src="../javascripts/jquery.2.0.0.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.placeholder.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.selectbox-0.2.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.mask.min.js" type="text/javascript"></script>
	<script src="../javascripts/cicompra.js" type="text/javascript"></script>

	<script src="../javascripts/jquery.cookie.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.utils2.js" type="text/javascript"></script>
	<script src="../javascripts/common.js" type="text/javascript"></script>

	<script type="text/javascript" src="../javascripts/contagemRegressiva.js?until=<?php echo tempoRestante(); ?>"></script>
	<script type="text/javascript" src="../javascripts/formCartao.js"></script>

	<script>
	$(function(){
		$('.botao_pagamento').click(function(e){
			e.preventDefault();
			$('#dadosPagamento').submit();
		});
	});
	</script>

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

	<title>COMPREINGRESSOS.COM - Gestão e Venda de Ingressos</title>
</head>
<body>
	<div id="pai">
		<?php require "header.php"; ?>
		<div id="content">
			<div class="alert">
				<div class="centraliza">
					<img src="../images/ico_erro_notificacao.png">
					<div class="container_erros"></div>
					<a>fechar</a>
				</div>
			</div>

			<form id="dadosPagamento" action="formCartao.php" method="post">
				<div class="centraliza">
					<div class="descricao_pag">
						<div class="img">
							<img src="../images/ico_black_passo5.png">
						</div>
						<div class="descricao">
							<p class="nome">5. Pagamento</p>
							<p class="descricao">
								passo <b>5 de 5</b> escolha a bandeira de sua preferência
							</p>
							<div class="sessao">
								<p class="tempo" id="tempoRestante"></p>
								<p class="mensagem">
									Após essse prazo seu pedido será cancelado<br>
									automaticamente e os lugares liberados
								</p>
							</div>
						</div>
						<a href="etapa5.php?eventoDS=<?php echo $_GET['eventoDS']; ?><?php echo $campanha['tag_avancar']; ?>" class="botao avancar passo6 botao_pagamento <?php echo $_COOKIE['total_exibicao'] == 0 ? 'finalizar' : '' ?>">pagamento</a>
					
						<?php require('formCartao.php'); ?>

					</div>
					
					<div class="container_botoes_etapas">
					<a href="etapa4.php?eventoDS=<?php echo $_GET['eventoDS']; ?><?php echo $campanha['tag_voltar']; ?>" class="botao voltar passo4">confirmação</a>
					<a href="etapa5.php?eventoDS=<?php echo $_GET['eventoDS']; ?><?php echo $campanha['tag_avancar']; ?>" class="botao avancar passo6 botao_pagamento <?php echo $_COOKIE['total_exibicao'] == 0 ? 'finalizar' : '' ?>">pagamento</a>
					</div>
					<div class="img_cod_cartao"><img src=""><p></p></div>

					<div class="compra_captcha">
					<?php
					require_once('../settings/recaptchalib.php');
					echo recaptcha_get_html($recaptcha['public_key'], null, true);
					?>
					</div>
						
				</div>
			
			</form>

		</div>

		<div id="texts">
			<div class="centraliza">
				<p>Escolha o cartão de crédito de sua preferência, preencha os dados e clique em Pagar para finalizar o seu pedido.</p>
			</div>
		</div>

		<?php include "footer.php"; ?>

		<?php include "selos.php"; ?>
	</div>
</body>
</html>