<?php
session_start();
if (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) {
	require_once('../settings/functions.php');
	require_once('../settings/settings.php');
	
} else header("Location: loginOperador.php?redirect=etapa0.php");
//echo session_id();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>COMPREINGRESSOS.COM - Escolha de Local e Evento</title>
		<meta name="author" content="C&C - Computação e Comunicação" />
		<link href="favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/annotations.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
		<link rel="stylesheet" href="../stylesheets/smoothness/jquery-ui-1.8.4.custom.css"/>
		
		<script type="text/javascript" src="../javascripts/jquery.js"></script>
		<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>
		<script>
		$(function() {
			$.busyCursor();
			
			$('#teatro').change(function() {
				$('loadingIcon').fadeIn('fast');
				
				$.ajax({
					url: 'listaEventos.php',
					type: 'get',
					data: 'teatro=' + $('#teatro').val(),
					success: function(data) {
						$('#eventos').slideUp('fast', function() {
							$(this).html(data);
						}).slideDown('fast');
					},
					complete: function() {
						$('loadingIcon').fadeOut('slow');
					}
				});
			});
		});
		</script>
	</head>
	<body>
		<div id="background_holder">
			<div id="respiro">
				<div id="content_container">
					<?php require "header.php"; ?>
					<div id="crumbs">
						<a href="http://www.compreingressos.com">home</a> /
						<a href="#espetaculo">atra&ccedil;&otilde;es</a> /
						<a href="#espetaculo"><?php echo utf8_encode($rs['DS_EVENTO']); ?></a> /
						<a href="#assentos" class="selected">escolha de assentos</a>
					</div>
					<?php include "banners.php"; ?>
					<div id="center">
						<div id="center_left">
							<h1>Escolha de assentos</h1>
							<p class="help_text">Escolha até <?php echo $maxIngressos; ?> lugares desejados e clique em avan&ccedil;ar para continuar 
							o processo de compra de ingressos.</p>
							<?php include "seloCertificado.php"; ?>
						</div>
						<div id="center_right">
							<div id="passos">
								<ul>
									<li><span class="numero">1. </span>Escolha de assentos</li>
									<li><span class="numero">2. </span>Conferir Itens</li>
									<li><span class="numero">3. </span>Identifica&ccedil;&atilde;o</li>
									<li><span class="numero">4. </span>Confirma&ccedil;&atilde;o</li>
									<li><span class="numero">5. </span>Pagamento</li>
								</ul>
							</div>
							
							<div id="header_ticket">
								<a href="logout.php?redirect=loginOperador.php" id="botao_voltar">
									<div class="botoes_ticket">logoff</div>
								</a>
							</div>
							
							<div class="titulo">
								<h1>Escolha de Local</h1>
							</div>
							
							<p>Selecione o local desejado: <?php echo comboTeatro('teatro'); ?></p>
							<div id="eventos"></div>
							
							<div id="footer_ticket">
								<a href="logout.php?redirect=loginOperador.php" id="botao_voltar">
									<div class="botoes_ticket">logoff</div>
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