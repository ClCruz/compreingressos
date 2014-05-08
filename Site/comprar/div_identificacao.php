<span id="identificacao">
	<form id="identificacaoForm" name="identificacao" method="post" action="autenticacao.php">
		<?php if (isset($_GET['tag'])) { ?>
		<input type="hidden" name="tag" value="<?php echo $_GET['tag']; ?>" />
		<input type="hidden" name="from" value="cadastro" />
		<?php } ?>
		<div class="identificacao">
			<p class="frase"><b>Já sou</b> cliente</p>
			<p class="site">compreingressos.com</p>
			<input type="text" name="email" placeholder="digite seu e-mail" id="login" maxlength="100">
			<div class="erro_help">
				<p class="erro">insira seu e-mail</p>
				<p class="help">e-mail cadastrado</p>
			</div>
			<input type="password" name="senha" placeholder="digite sua senha" id="senha" maxlength="30">
			<div class="erro_help">
				<p class="erro"></p>
				<p class="help"></p>
			</div>
			<input type="button" class="submit avancar passo4" id="logar" href="etapa4.php">
			<div class="erro_help">
				<p class="erro" style="width:200px">Combinação de E-mail/senha inválida<br>Por favor tente novamente.</p>
				<p class="help"></p>
			</div>
			<a id="esqueci" href="#esqueci" class="esqueci_senha">esqueci minha senha</a>
			<div id="esqueciForm" class="container_esqueci_senha">
				<input type="text" name="email_esqueci_senha" placeholder="digite seu e-mail cadastrado" id="recupera_por_email" maxlength="100">
				<div class="erro_help">
					<p class="erro">e-mail inválido</p>
					<p class="help"></p>
				</div>
				<input type="button" class="submit trocar_senha" id="enviar_senha" href="esqueciSenha.php">
				<div class="resultado">
					um email com instruções para recuperar<br>
					sua senha foi enviado para:<br>
					<span>fulano@host.com.br</span><br>
					se não encontrar o e-mail verifique sua caixa de spam
				</div>
			</div>
		</div>
		<div class="identificacao">
			<p class="frase"><b>Não sou</b> cliente</p>
			<p class="site">compreingressos.com</p>
			<a href="" class="botao cadastrar bt_cadastro">cadastrar</a>
		</div>
	</form>
</span>