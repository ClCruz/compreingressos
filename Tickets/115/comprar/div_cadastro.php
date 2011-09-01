<?php session_start(); ?>
						<div id="cadastro">
							<form id="form_cadastro" name="form_cadastro" method="POST" action="cadastro.php">
								<?php if (isset($_GET['tag'])) { ?>
								<input type="hidden" name="tag" value="<?php echo $_GET['tag']; ?>" />
								<?php } ?>
								<div id="id_left_cadastro">
									<?php if (!(isset($_SESSION['user']) and is_numeric($_SESSION['user']))) { ?>
									<h1>Dados pessoais</h1>
									<p class="help_text">Os dados abaixo ser&atilde;o mantidos em sigilo e utilizados apenas pela compreingressos.com</p>
									<?php } ?>
									<?php if (isset($_SESSION['user']) and is_numeric($_SESSION['user'])) { ?>
									<h2>Login/e-mail:</h2>
									<input type="text" name="email" id="email" size="30" maxlength="100" value="<?php echo utf8_encode($rs['CD_EMAIL_LOGIN']); ?>" <?php echo (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) ? 'disabled' : ''; ?>/>
									<hr/>
									<?php } ?>
									<h2>Nome</h2>
									<input type="text" name="nome" id="nome" size="30" maxlength="50" value="<?php echo utf8_encode($rs['DS_NOME']); ?>"/>
									<p class="err_msg">Insira o nome</p>
									<h2>Sobrenome</h2>
									<input type="text" name="sobrenome" id="sobrenome" size="30" maxlength="50" value="<?php echo utf8_encode($rs['DS_SOBRENOME']); ?>"/>
									<p class="err_msg">Insira o sobrenome</p>
									<h2>Sexo</h2>
									<p class="help_text"><input name="sexo" type="radio" value="F" <?php echo ($rs['IN_SEXO'] == 'F') ? 'checked ' : ''; ?>> feminino &nbsp;
									<input name="sexo" type="radio" value="M" <?php echo ($rs['IN_SEXO'] == 'M') ? 'checked ' : ''; ?>> masculino</p>
									<p class="err_msg">Insira o sexo</p>
									<h2>Data de nascimento (DD/MM/AAAA)</h2>
									<span><input type="text" name="nascimento_dia" class="number" id="nascimento_dia" size="2" maxlength="2" value="<?php echo utf8_encode($rs['DT_NASCIMENTO'][0]); ?>"/> / <input type="text" name="nascimento_mes" class="number" id="nascimento_mes" size="2" maxlength="2" value="<?php echo utf8_encode($rs['DT_NASCIMENTO'][1]); ?>"/> / <input type="text" name="nascimento_ano" class="number" id="nascimento_ano" size="4" maxlength="4" value="<?php echo utf8_encode($rs['DT_NASCIMENTO'][2]); ?>"/></span>
									<p class="err_msg">Insira a data de nascimento</p>
									<h2>Telefone</h2>
									<span>(<input type="text" name="ddd1" class="number" id="ddd1" size="2" maxlength="2" value="<?php echo utf8_encode($rs['DS_DDD_TELEFONE']); ?>"/>)
									<input type="text" name="telefone" id="telefone" size="15" maxlength="15" value="<?php echo utf8_encode($rs['DS_TELEFONE']); ?>"/></span>
									<p class="err_msg">Insira o telefone</p>
									<h2>Celular</h2>
									<span>(<input type="text" name="ddd2" class="number" id="ddd2" size="2" maxlength="2" value="<?php echo utf8_encode($rs['DS_DDD_CELULAR']); ?>"/>)
									<input type="text" name="celular" id="celular" size="15" maxlength="15" value="<?php echo utf8_encode($rs['DS_CELULAR']); ?>"/></span>
									<p class="err_msg">Insira o celular</p>
									<h2>R.G.</h2>
									<input type="text" name="rg" id="rg" size="30" maxlength="11" value="<?php echo utf8_encode($rs['CD_RG']); ?>"/>
									<p class="err_msg">Insira o R.G</p>
									<h2>C.P.F. (somente números)</h2>
									<input type="text" name="cpf" class="number" id="cpf" size="30" maxlength="11" value="<?php echo utf8_encode($rs['CD_CPF']); ?>"/>
									<p class="err_msg">Insira o C.P.F</p>
									<h2>Estado</h2>
									<?php echo comboEstado('estado', $rs['ID_ESTADO']); ?>
									<p class="err_msg">Insira o estado</p>
									<h2>Cidade</h2>
									<input type="text" name="cidade" id="cidade" size="30" maxlength="50" value="<?php echo utf8_encode($rs['DS_CIDADE']); ?>"/>
									<p class="err_msg">Insira a cidade</p>
									<h2>Bairro</h2>
									<input type="text" name="bairro" id="bairro" size="30" maxlength="50" value="<?php echo utf8_encode($rs['DS_BAIRRO']); ?>"/>
									<p class="err_msg">Insira o bairro</p>
									<h2>Endere&ccedil;o (rua/av./pra&ccedil; e n&uacute;mero)</h2>
									<input type="text" name="endereco" id="endereco" size="30" maxlength="150" value="<?php echo utf8_encode($rs['DS_ENDERECO']); ?>"/>
									<p class="err_msg">Insira o endere&ccedil;o</p>
									<h2>Complemento</h2>
									<input name="complemento" id="complemento" size="30" maxlength="50" value="<?php echo utf8_encode($rs['DS_COMPL_ENDERECO']); ?>"/>
									<h2>CEP</h2>
									<span><input type="text" name="cep1" class="number" id="cep1" size="5" maxlength="5" value="<?php echo utf8_encode($rs['CD_CEP'][0]); ?>"/>-<input type="text" name="cep2" class="number" id="cep2" size="3" maxlength="3" value="<?php echo utf8_encode($rs['CD_CEP'][1]); ?>"/></span>
									<p class="err_msg">Insira o CEP</p>
								</div>
								<div id="id_right_cadastro">
								<?php if (!(isset($_SESSION['user']) and is_numeric($_SESSION['user']))) { ?>
									<h1>Dados da conta</h1>
									<p class="help_text">Utilize os dados abaixo para identificar-se em nosso sistema.</p>
									<h2>E-mail</h2>
									<input type="text" name="email1" id="email1" size="30" maxlength="100"/>
									<p class="err_msg">Insira o e-mail</p>
									<h2>Confirme o e-mail</h2>
									<input type="text" name="email2" id="email2" size="30" maxlength="100"/>
									<p class="err_msg">Confirme o e-mail</p>
									<h2>Senha (no m&iacute;nimo 6 caract&eacute;res)</h2>
									<input type="password" name="senha1" id="senha1" size="30" maxlength="15"/>
									<p class="err_msg">Insira a senha</p>
									<h2>Confirme a senha</h2>
									<input type="password" name="senha2" id="senha2" size="30" maxlength="15"/>
									<p class="err_msg">Confirme a senha</p>
								<?php } ?>	
									<p class="help_text"><input name="extra_info" type="checkbox" id="extra_info" value="S" <?php echo ($rs['IN_RECEBE_INFO'] == 'S') ? 'checked' : ''; ?>> quero receber informativos sobre promo&ccedil;&otilde;es e participar de sorteios de ingressos</p>
									<p class="help_text"><input name="extra_sms" type="checkbox" id="extra_sms" value="S" <?php echo ($rs['IN_RECEBE_SMS'] == 'S') ? 'checked' : ''; ?>> concordo em receber mensagens SMS promocionais e sobre minhas compras</p>
								<?php if (!(isset($_SESSION['user']) and is_numeric($_SESSION['user']))) { ?>
									<p class="help_text"><input name="concordo" type="checkbox" id="concordo" value="S"> concordo com os <a href="termosUso.php" title="Termos de Uso" target="_blank" class="contrato">termos de uso</a> e com a <a href="declaracaoPrivacidade.php" title="Pol&iacute;tica de Privacidade" target="_blank" class="contrato">pol&iacute;tica de privacidade</a></p>
									<p class="err_msg">Voc&ecirc; deve concordar com nossos termos de uso e nossa pol&iacute;tica de privacidade</p>
									<a href="cadastro.php" id="cadastreme">
										<div class="botoes_ticket">cadastre-me</div>
									</a>
									<a class="bt_cadastro" href="#identificacao">
										<div class="botoes_ticket" id="botao_voltar" style="float:right">voltar</div>
									</a>
								<?php } ?>
								</div>
							</form>
						</div>