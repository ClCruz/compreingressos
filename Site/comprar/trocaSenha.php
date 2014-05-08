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
		<script>
		$(function() {
			$('#logar').click(function(event) {
				event.preventDefault();
				
				var form = $('#identificacaoForm');
				
				$.ajax({
					url: form.attr('action') + '?' + $.serializeUrlVars(),
					data: form.serialize(),
					type: form.attr('method'),
					success: function(data) {
						if (data.substr(0, 4) == 'redi') {
							document.location = data;
						} else {
							$.dialog({title: 'Aviso...', text: data});
						}
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
						<a href="http://www.compreingressos.com">home</a> / <a href="#minha_conta" class="selected">minha conta</a>
					</div>
					<?php include "banners.php"; ?>
					<div id="center">
						<div id="center_left">
							<h1>Operador</h1>
							<p class="help_text">A troca de senha deve ser efetuada antes de prosseguir.</p>
						</div>
						<div id="center_right">
							<form id="identificacaoForm" name="identificacao" method="post" action="autenticacaoOperador.php">
								<div id="identificacao">
									<img class="icone_id" src="../images/icon_naosou.jpg" alt="Sou cliente COMPREINGRESSOS.COM" title="Sou cliente COMPREINGRESSOS.COM"/>
									<div id="id_left">
										<h1>Troca de senha</h1>
										<p class="help_text">Procure utilizar letras, n&uacute;meros e caracteres especiais para criar sua nova senha.</p>
										<p class="help_text">A senha deve ter no mínimo 6 caracteres.</p>
										<h2>Senha atual</h2>
										<input name="senhaOld" type="password" id="senhaOld" size="15" maxlength="30"/>
										<h2>Nova senha</h2>
										<input name="senha1" type="password" id="senha1" size="15" maxlength="30"/>
										<h2>Confirma&ccedil;&atilde;o da senha</h2>
										<input name="senha2" type="password" id="senha2" size="15" maxlength="30"/>
										<a id="logar" href="#">
											<div class="botoes_ticket">continuar</div>
										</a>
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
