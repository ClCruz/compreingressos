						<form id="identificacaoForm" name="identificacao" method="post" action="autenticacao.php">
							<?php if (isset($_GET['tag'])) { ?>
							<input type="hidden" name="tag" value="<?php echo $_GET['tag']; ?>" />
							<?php } ?>
							<div id="identificacao">
								<img class="icone_id" src="../images/icon_sou.jpg" alt="Sou cliente COMPREINGRESSOS.COM" title="Sou cliente COMPREINGRESSOS.COM"/>
								<div id="id_left">
									<h1>Sou cliente compreingressos.com</h1>
									<p class="help_text">Autentique-se usando seu e-mail e senha!</p>
									<h2>E-mail</h2>
									<input name="email" type="text" id="login" size="30" maxlength="100"/>
									<p class="err_msg">Insira seu e-mail</p>
									<h2>Senha</h2>
									<input name="senha" type="password" id="senha" size="15" maxlength="30"/>
									<p class="err_msg">Insira sua senha (no m&iacute;nimo 6 caract&eacute;res)</p>
									<a id="logar" href="etapa4.php">
										<div class="botoes_ticket">autentique-se</div>
									</a>
									<p class="err_msg">Combinação de E-mail/senha inválida<br>Por favor tente novamente.</p>
									<a id="esqueci" href="#esqueci">esqueci a senha</a>
									<div id="esqueciForm">
										<h2>Insira seu e-mail</h2>
										<input type="text" id="recupera_por_email" size="30" maxlength="100"/>
										<p class="err_msg">Insira um e-mail v&aacute;lido!</p>
										<a id="enviar_senha" href="esqueciSenha.php">
											<div class="botoes_ticket">enviar senha</div>
										</a>
										<p class="aviso">Uma nova senha foi enviada para o seu e-mail!</p>
									</div>
								</div>
								<img class="icone_id" src="../images/icon_naosou.jpg" alt="Sou cliente COMPREINGRESSOS.COM" title="Sou cliente COMPREINGRESSOS.COM"/>
								<div id="id_right">
									<h1>N&atilde;o sou cliente compreingressos.com</h1>
									<p class="help_text">Clique no link abaixo e preencha seu cadastro.</p>
									<a class="bt_cadastro" href="#cadastro">
										<div class="botoes_ticket">cadastre-se</div>
									</a>
								</div>
							</div>
						</form>