<?php
session_start();

if (isset($_SESSION['operador'])) {
	header("Location: etapa3_2.php");
} else if (isset($_SESSION['user'])) {
	if (isset($_GET['redirect'])) {
		header("Location: " . urldecode($_GET['redirect']));
	} else {
		if ($_COOKIE['entrega']) {
			header("Location: etapa3_entrega.php");
		} else {
			header("Location: etapa4.php");
		}
	}
}

require_once('../settings/functions.php');

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

	<script src="../javascripts/identificacao_cadastro.js" type="text/javascript"></script>
	<script src="../javascripts/contagemRegressiva.js?until=<?php echo tempoRestante(); ?>" type="text/javascript"></script>

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

			<div class="centraliza">
				<div class="descricao_pag">
					<div class="img">
						<img src="../images/ico_black_passo3.png">
					</div>
					<div class="descricao">
						<p class="nome">3. Identificação</p>
						<p class="descricao">
							passo <b>3 de 5</b> identifique-se ou cadastre-se
						</p>
						<div class="sessao">
							<p class="tempo" id="tempoRestante"></p>
							<p class="mensagem">
								Após essse prazo seu pedido será cancelado<br>
								automaticamente e os lugares liberados
							</p>
						</div>
					</div>
				</div>

				<?php require "div_identificacao.php"; ?>
				<?php require "div_cadastro.php"; ?>

			</div>
		</div>

		<div id="texts">
			<div class="centraliza">
				<p>Identifique-se com seu e-mail e senha de acesso ou cadastre-se e crie sua conta.</p>
			</div>
		</div>

		<?php include "footer.php"; ?>

		<?php include "selos.php"; ?>

		<div id="overlay">
			<?php require 'termosUso.php'; ?>
		</div>
	</div>
</body>
</html>