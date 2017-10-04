<?php
require_once('../settings/functions.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow">
	<link href="../images/favicon.ico" rel="shortcut icon"/>
	<link href='https://fonts.googleapis.com/css?family=Paprika|Source+Sans+Pro:200,400,400italic,200italic,300,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="../stylesheets/cicompra.css"/>
    <?php require("desktopMobileVersion.php"); ?>
	<link rel="stylesheet" href="../stylesheets/ajustes2.css"/>

    <link rel="stylesheet" type="text/css" href="../stylesheets/nova_home.css">
    <link rel="stylesheet" type="text/css" href="../stylesheets/icons/flaticon1/flaticon.css">
    <link rel="stylesheet" type="text/css" href="../stylesheets/icons/socicon/styles.css">

	<script src="../javascripts/jquery.2.0.0.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.placeholder.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.selectbox-0.2.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.mask.min.js" type="text/javascript"></script>
	<script src="../javascripts/cicompra.js" type="text/javascript"></script>

	<script src="../javascripts/jquery.cookie.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.utils2.js" type="text/javascript"></script>
	<script src="../javascripts/common.js" type="text/javascript"></script>
    <script src="../javascripts/faro.js" type="text/javascript"></script>

    <script>
		$(function() {
			$('p.erro').hide();
			
			$('#logar').click(function(event) {
				event.preventDefault();
				var $this = $(this),
					 form = $('#identificacaoForm'),
					 senha = $('#senhaOld'),
					 senha_txt = senha.val(),
					 valido = true;
				
				if (senha_txt.length < 6) {
					senha.findNextMsg().slideDown('fast');
					valido = false;
				} else senha.findNextMsg().slideUp('slow');
				
				if (valido) {
					$.ajax({
						url: form.attr('action') + '?' + $.serializeUrlVars(),
						data: form.serialize(),
						type: form.attr('method'),
						success: function(data) {
							if (data.substr(0, 4) == 'redi') {
								$this.findNextMsg().slideUp('slow');
								document.location = data;
							} else {
								$.dialog({text:data});
							}
						}
					});
				}
			});
		});
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
						<img src="">
					</div>
					<div class="descricao">
						<p class="nome">Troca de senha</p>
						<p class="descricao">
							Procure utilizar letras, n&uacute;meros e caracteres especiais para criar sua nova senha.<br/>
							A senha deve ter no mínimo 6 caracteres.
						</p>
						<div class="sessao">
							<p class="tempo" id="tempoRestante"></p>
							<p class="mensagem"></p>
						</div>
					</div>
				</div>

				<form id="identificacaoForm" name="identificacao" method="post" action="autenticacaoOperador.php">
					<div class="identificacao">
						<input name="senhaOld" type="password" id="senhaOld" size="15" maxlength="30" placeholder="senha atual" />
						<div class="erro_help">
							<p class="erro">insira a senha atual</p>
							<p class="help"></p>
						</div>
						<br/>

						<input name="senha1" type="password" id="senha1" size="15" maxlength="30" placeholder="nova senha" />
						<div class="erro_help">
							<p class="erro">insira a nova senha</p>
							<p class="help"></p>
						</div>
						<br/>

						<input name="senha2" type="password" id="senha2" size="15" maxlength="30" placeholder="confirmação de senha" />
						<div class="erro_help">
							<p class="erro">insira a confirmação da nova senha</p>
							<p class="help"></p>
						</div>
						<input type="button" class="submit avancar passo4" id="logar">
					</div>
				</form>

			</div>
		</div>

		<div id="texts">
			<div class="centraliza"></div>
		</div>

		<?php include "footer.php"; ?>

		<?php //include "selos.php"; ?>
	</div>
</body>
</html>