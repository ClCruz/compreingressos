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

require_once('header.php');
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
								$.dialog({title: 'Aviso...', text: data});
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
			<h2>Login</h2>
			<form action="autenticacao.php" method="post">
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td align="right">Usu&aacute;rio:</td>
						<td><input type="text" name="usuario" /></td>
					</tr>
					<tr>
						<td align="right">Senha:</td>
						<td><input type="password" name="senha" /></td>
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
			<?php } ?>
		</div>
    </div>
<?php
require_once('footer.php');
?>