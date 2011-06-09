<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>COMPREINGRESSOS.COM - Borderô Web</title>
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
		var email_pattern = /\b[\w\.-]+@[\w\.-]+\.\w{2,4}\b/i;
		
		$.busyCursor();
		$('p.aviso, p.err_msg').hide();

		$('#logar').click(function(event) {
		    event.preventDefault();

		    var form = $('form'),
			$this = $(this);

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
				$this.findNextMsg().slideDown('fast');
			    }
			},
			complete: function() {
			    $('#loadingIcon').fadeOut('slow');
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
			<a href="http://www.compreingressos.com/">home</a> / <a href="#minha_conta" class="selected">borderô web</a>
		    </div>
		    <?php include "banners.php"; ?>
		    <div id="center">
			<div id="center_left">
			    <h1>Borderô Web</h1>
			    <p class="help_text">Identifique-se com seu login e senha de operador.</p>
			    <?php include "seloCertificado.php"; ?>
			</div>
			<div id="center_right">
			    <form method="post" action="../admin/autenticacao.php">
				<div id="identificacao">
				    <img class="icone_id" src="../images/icon_sou.jpg" alt="Sou cliente COMPREINGRESSOS.COM" title="Sou cliente COMPREINGRESSOS.COM">
				    <div id="id_left">
					<h1>SGV - Sistema de Gestão e Venda de Ingressos</h1>
					<p class="help_text">Autentique-se usando seu login e senha!</p>
					<h2>Login</h2>
					<input name="usuario" size="30" maxlength="30" type="text">
					<p style="display: none;" class="err_msg">Insira seu login</p>
					<h2>Senha</h2>
					<input name="senha" size="20" maxlength="30" type="password">
					<a id="logar" href="#">
					    <div class="botoes_ticket">autentique-se</div>
					</a>
					<p style="display: none;" class="err_msg">Combinação de login/senha inválida<br>Por favor tente novamente.</p>
					<p class="menorzinho">Melhor Utilização no navegador GOOGLE CHROME.</p>
				    </div>
				    <div id="id_right" style="margin:60px 0 0 80px;text-align:center">
					<img src="../images/logo_grupocicom.jpg" />
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