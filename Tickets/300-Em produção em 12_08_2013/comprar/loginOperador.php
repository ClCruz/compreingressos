<?php
require_once('../settings/functions.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>COMPREINGRESSOS.COM - Operador</title>
		<meta name="author" content="C&C - Computação e Comunicação" />
		<link href="favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
		<link rel="stylesheet" href="../stylesheets/smoothness/jquery-ui-1.10.3.custom.css"/>
		
		<script type="text/javascript" src="../javascripts/jquery.js"></script>
		<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.cookie.js"></script>
		<script>
		$(function() {
			$('p.aviso, p.err_msg').hide();
			$.busyCursor();
			
			$('#logar').click(function(event) {
				event.preventDefault();
				var $this = $(this),
					 form = $('#identificacaoForm'),
					 senha = $('#senha'),
					 senha_txt = senha.val(),
					 valido = true;
				
				if (senha_txt.length < 6) {
					senha.findNextMsg().slideDown('fast');
					valido = false;
				} else senha.findNextMsg().slideUp('slow');
				
				if (valido) {
					$("#loadingIcon").fadeIn('fast');
					
					$.ajax({
						url: form.attr('action') + '?' + $.serializeUrlVars(),
						data: form.serialize(),
						type: form.attr('method'),
						success: function(data) {
							if (data.substr(0, 4) == 'redi') {
								$this.findNextMsg().slideUp('slow');
								document.location = data;
							} else {
								$this.findNextMsg().slideUp('fast', function() {
									$(this).text(data).slideDown('fast')
								});
							}
						},
						complete: function() {
							$('#loadingIcon').fadeOut('slow');
						}
					});
				}
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
						<a href="http://www.compreingressos.com">home</a> / <a href="#minha_conta" class="selected">minha conta</a>
					</div>
					<?php include "banners.php"; ?>
					<div id="center">
						<div id="center_left">
							<h1>Operador</h1>
							<p class="help_text">Identifique-se com seu login e senha de acesso.</p>
						</div>
						<div id="center_right">
							<form id="identificacaoForm" name="identificacao" method="post" action="autenticacaoOperador.php">
								<div id="identificacao">
									<img class="icone_id" src="../images/icon_sou.jpg" alt="Sou cliente COMPREINGRESSOS.COM" title="Sou cliente COMPREINGRESSOS.COM"/>
									<div id="id_left">
										<h1>Sou operador compreingressos.com</h1>
										<p class="help_text">Autentique-se usando seu login e senha!</p>
										<h2>Login</h2>
										<input name="login" type="text" id="login" size="30" maxlength="100"/>
										<p class="err_msg">Insira seu login</p>
										<h2>Senha</h2>
										<input name="senha" type="password" id="senha" size="15" maxlength="30"/>
										<p class="err_msg">Insira sua senha (no m&iacute;nimo 6 caract&eacute;res)</p>
										<a id="logar" href="#">
											<div class="botoes_ticket">autentique-se</div>
										</a>
										<p class="err_msg">Combinação de login/senha inválida<br>Por favor tente novamente.</p>
									</div>
								</div>
							</form>
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
