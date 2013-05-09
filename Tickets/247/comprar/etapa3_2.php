<?php
session_start();

if (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) {
	require_once('../settings/functions.php');
	
	if (isset($_SESSION['user']) and is_numeric($_SESSION['user'])) {
		$mainConnection = mainConnection();
		
		$query = 'SELECT ID_CLIENTE, DS_NOME, DS_SOBRENOME, DS_DDD_TELEFONE, DS_TELEFONE, CD_CPF
					 FROM MW_CLIENTE
					 WHERE ID_CLIENTE = ?';
		$rs = executeSQL($mainConnection, $query, array($_SESSION['user']), true);
		$userSelected = true;
	} else {
		$userSelected = false;
	}
} else header("Location: loginOperador.php?redirect=etapa3_2.php");

$campanha = get_campanha_etapa(basename(__FILE__, '.php'));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>COMPREINGRESSOS.COM - Identifica&ccedil;&atilde;o</title>
		<meta name="author" content="C&C - Computação e Comunicação" />
		<link href="favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
		<link rel="stylesheet" href="../stylesheets/smoothness/jquery-ui-1.8.4.custom.css"/>
		
		<script type="text/javascript" src="../javascripts/jquery.js"></script>
		<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.cookie.js"></script>
		
		<script type="text/javascript" src="../javascripts/identificacao_cadastro_operador.js"></script>
		<script type="text/javascript" src="../javascripts/contagemRegressiva.js?until=<?php echo tempoRestante(); ?>"></script>
		
		<script>
		$(function() {
			$('#limpar').click();
			
			<?php if ($userSelected) { ?>
			$('#nomeBusca').val('<?php echo utf8_encode($rs['DS_NOME']); ?>');
			$('#sobrenomeBusca').val('<?php echo utf8_encode($rs['DS_SOBRENOME']); ?>');
			$('#telefoneBusca').val('<?php echo $rs['DS_TELEFONE']; ?>');
			$('#cpfBusca').val('<?php echo $rs['CD_CPF']; ?>');
			
			$('#buscar').click();
			<?php } ?>
		})
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
	</head>
	<body>
		<div id="background_holder">
			<div id="respiro">
				<div id="content_container">
					<?php require "header.php"; ?>
					<div id="crumbs">
						<a href="http://www.compreingressos.com">home</a> /
						<a href="#carrinho">carrinho</a> /
						<a href="#identificacao" class="selected">identifica&ccedil;&atilde;o</a>
					</div>
					<?php include "banners.php"; ?>
					<div id="center">
						<div id="center_left">
							<h1>Identifica&ccedil;&atilde;o</h1>
							<p class="help_text">Identifique o cliente com a busca e clique no resultado desejado para prosseguir.</p>
							<?php include "seloCertificado.php"; ?>
						</div>
						<div id="center_right">
							<div id="passos">
								<ul>
									<li><span class="numero">1. </span>Escolha de assentos</li>
									<li><span class="numero">2. </span>Conferir Itens</li>
									<li class="passo_ativo"><span class="numero">3. </span>Identifica&ccedil;&atilde;o</li>
									<li><span class="numero">4. </span>Confirma&ccedil;&atilde;o</li>
									<li><span class="numero">5. </span>Pagamento</li>
								</ul>
							</div>
							<div id="header_ticket">
								<a href="etapa2.php?eventoDS=<?php echo $_GET['eventoDS']; ?>">
									<div class="botoes_ticket" id="botao_voltar">alterar pedido</div>
								</a>
								<a href="pagamento_cancelado.php?manualmente&operador" class="botao_cancelar">
									<div class="botoes_ticket">cancelar</div>
								</a>
							</div><br><br><br>
							<?php require "div_identificacao_operador.php"; ?>
							<div id="resultadoBusca"></div>
							<?php require "div_cadastro_operador.php"; ?>
							<div id="footer_ticket">
								<a href="etapa2.php?eventoDS=<?php echo $_GET['eventoDS']; ?>">
									<div class="botoes_ticket" id="botao_voltar">alterar pedido</div>
								</a>
								<a href="pagamento_cancelado.php?manualmente&operador" class="botao_cancelar">
									<div class="botoes_ticket">cancelar</div>
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
