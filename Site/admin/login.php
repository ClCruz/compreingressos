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
			
<div style="height: 100vh">
	<div class="flex-center flex-column">
	
		<div class="view overlay">
			<img src="../images/menu_logo2.png" class="mx-auto d-block z-depth-3 mt-3 mb-5" alt="">
			<a href="#">
				<div class="mask rgba-white-slight"></div>
			</a>
		</div>

	
		<!--Card-->
		<div class="card">
			<!--Card content-->
			<div class="card-body">
				<!--Title-->
				<h4 class="card-title">Acesso - Administrativo</h4>
				<!--Text-->
				<form action="autenticacao.php" method="post">

					<!-- Material input email -->
					<div class="md-form">
						<i class="fa fa-envelope prefix grey-text"></i>
						<input type="text" id="usuario" placeholder="Usuário" name="usuario" class="form-control">
					</div>

					<!-- Material input password -->
					<div class="md-form">
						<i class="fa fa-lock prefix grey-text"></i>
						<input type="password" id="senha"placeholder="Senha" name="senha" class="form-control">
					</div>

					<div class="text-center mt-4">
						<button class="btn btn-primary" id="Enviar" type="submit">Enviar</button>
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
//require_once('footer.php');
?>