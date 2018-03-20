<?php
session_start();

if ($_GET['action'] == 'logout') {
	foreach ($_COOKIE as $key => $val) {
		setcookie($key, "", time() - 3600);
	}
	
	session_unset();
	session_destroy();
} else if ($_GET['action'] == 'trocarSenha' && $_SESSION['senha'] != true) {
	require_once('acessoLogado.php');
}

require_once('../settings/settings.php');
require_once('../settings/functions.php');

require_once('header_new.php');
?>
    <div id='content'>
    	<div id='app'>
			<script>
			$(function() {
				$.busyCursor();
				
				$('#enviar').button().click(function(event) {
					event.preventDefault();
					
					var form = $('form');
					
					$("#loadingIcon").fadeIn('fast');
					
					$.ajax({
						url: form.attr('action') + '?' + $.serializeUrlVars(),
						data: form.serialize(),
						type: form.attr('method'),
						success: function(data) {
							if (data.substr(0, 4) == 'redi') {
								document.location = data;
							} else {
								$("#idmessagerror").html(data);
								$("#iderroralert").show();
							}
						},
						complete: function() {
							$('#loadingIcon').fadeOut('slow');
						}
					});
				});
			})
			</script>
			<?php if ($_GET['action'] == 'trocarSenha') { ?>
			<h2>Trocar Senha</h2>
			<form action="autenticacao.php" method="post">
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td align="right">Senha atual:</td>
						<td><input type="password" name="senhaOld" /></td>
					</tr>
					<tr>
						<td align="right">Nova senha:</td>
						<td><input type="password" name="senha1" /></td>
					</tr>
					<tr>
						<td align="right">Confirme a senha:</td>
						<td><input type="password" name="senha2" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input type="submit" id="enviar" value="Enviar" /></td>
					</tr>
				</table>
			</form>
			<?php } else { ?>
			
<div>
	<div class="flex-center flex-column">
	
		<div class="view overlay">
			<img src="../images/logo.png" style="width:100%; max-width:350px;" class="mx-auto d-block mb-4" alt="">
			<a href="#">
				<div class="mask rgba-white-slight"></div>
			</a>
		</div>
		
		<div class="view overlay">
		<h3>
			<p class="text-center"><font color="#FFFFFF"  face="verdana" size="5">Seja bem-vindo!</font></strong></p>
			<p class="text-center mt-2"><font color="#FFFFFF" face="verdana" size="3">Faça <b>login</b> para ter acesso a todas as <br>funcionalidades do Portal de Administração.</font></p>
		</h3>
		</div>

		<div id="iderroralert" class="alert alert-warning alert-dismissible fade show" style="display: none" role="alert">
		  <button  type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="javascript:$('#iderroralert').hide()">
			<span aria-hidden="true">&times;</span>
		  </button>
		  <span id="idmessagerror"></span>
		</div>
	
		<!--Card-->
		<div class="card mt-4">
			<!--Card content-->
			<div class="card-body">
				<!--Title-->
				<h4 class="card-title">
				<i class="fa fa-laptop" aria-hidden="true"></i>
				Portal de Administração</h4>
				<!--Text-->
				<form action="autenticacao.php" method="post">
					<!-- Material input email -->
					<div class="md-form">
						<i class="fa fa-user prefix grey-text"></i>
						<input type="text" id="usuario" placeholder="Usuário" name="usuario" class="form-control">
					</div>

					<!-- Material input password -->
					<div class="md-form">
						<i class="fa fa-lock prefix grey-text"></i>
						<input type="password" id="senha"placeholder="Senha" name="senha" class="form-control">
					</div>

					<div class="text-center mt-4">
						<button class="btn btn-danger" id="enviar" type="submit">Entrar</button>
					</div>
				</form>
			</div>

		</div>
		<!--/.Card-->
		
	</div>
</div>
			
			<?php } ?>
		</div>
    </div>
<?php
require_once('footer_new.php');
?>