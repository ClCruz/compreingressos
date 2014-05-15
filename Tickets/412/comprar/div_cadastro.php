<?php session_start(); ?>
<script>var RecaptchaOptions = {theme: 'white'};</script>
<div id="dados_conta">
	<form id="form_cadastro" name="form_cadastro" method="POST" action="cadastro.php">
		<?php if (isset($_GET['tag'])) { ?>
		<input type="hidden" name="tag" value="<?php echo $_GET['tag']; ?>" />
		<?php } ?>
		<div class="coluna">

			<?php if (!(isset($_SESSION['user']) and is_numeric($_SESSION['user']))) { ?>
			<p class="frase">3.1 Dados pessoais</p>
			<?php } else { ?>
			<p class="frase">Seus dados</p>
			<?php } ?>

			<div class="input_area nome">
				<div class="icone"></div>
				<div class="inputs">
					<p class="titulo">Qual o seu nome?</p>
					<input type="text" name="nome" id="nome" maxlength="50" placeholder="nome" pattern=".{1,50}" value="<?php echo utf8_encode($rs['DS_NOME']); ?>">
					<div class="erro_help">
						<p class="erro">informe seu nome</p>
						<p class="help"></p>
					</div>
					<input type="text" name="sobrenome" id="sobrenome" maxlength="50" placeholder="sobrenome" pattern=".{1,50}" value="<?php echo utf8_encode($rs['DS_SOBRENOME']); ?>">
					<div class="erro_help">
						<p class="erro">informe seu sobrenome</p>
						<p class="help"></p>
					</div>
				</div>
			</div>

			<div class="input_area sexo">
				<div class="icone"></div>
				<div class="inputs">
					<p class="titulo">Sexo</p>
					<input id="radio_masculino" type="radio" name="sexo" class="radio" value="M" <?php echo ($rs['IN_SEXO'] == 'M') ? 'checked ' : ''; ?>>
					<label class="radio" for="radio_masculino">masculino</label>
					<input id="radio_feminino" type="radio" name="sexo" class="radio" value="F" <?php echo ($rs['IN_SEXO'] == 'F') ? 'checked ' : ''; ?>>
					<label class="radio" for="radio_feminino">feminino</label>
				</div>
				<div class="erro_help">
					<p class="erro">informe seu sexo</p>
					<p class="help"></p>
				</div>
			</div>

			<div class="input_area nascimento">
				<div class="icone"></div>
				<div class="inputs">
					<p class="titulo">Data de nascimento</p>
					<div class="dia">
						<?php echo comboDia('nascimento_dia', $rs['DT_NASCIMENTO'][0], true); ?>
					</div>
					<div class="mes">
						<?php echo comboMeses('nascimento_mes', $rs['DT_NASCIMENTO'][1], false, true); ?>
					</div>
					<div class="ano">
						<?php echo comboAnos('nascimento_ano', $rs['DT_NASCIMENTO'][2], date('Y')-100, date('Y'), true); ?>
					</div>
					<div class="erro_help">
						<p class="erro">informe a data de nascimento</p>
						<p class="help"></p>
					</div>
				</div>
			</div>

			<div class="input_area telefones">
				<div class="icone"></div>
				<div class="inputs">
					<p class="titulo">Telefones de contato</p>
					<input type="text" name="fixo" id="fixo" placeholder="telefone fixo" maxlength="14" autocomplete="off" pattern=".{14}" value="<?php echo utf8_encode($rs['DS_DDD_TELEFONE'].$rs['DS_TELEFONE']); ?>">
					<div class="erro_help">
						<p class="erro">insira o telefone fixo</p>
						<p class="help">(ddd + nº)</p>
					</div>
					<input type="text" name="celular" id="celular" placeholder="telefone celular" maxlength="14" autocomplete="off" value="<?php echo utf8_encode($rs['DS_DDD_CELULAR'].$rs['DS_CELULAR']); ?>">
					<div class="erro_help">
						<p class="erro"></p>
						<p class="help">opcional</p>
					</div>
				</div>
			</div>

			<div class="input_area identificacao">
				<div class="icone"></div>
				<div class="inputs">
					<p class="titulo">Identificação</p>

					<input id="checkbox_estrangeiro" type="checkbox" name="checkbox_estrangeiro" class="checkbox" value="true" <?php echo $rs['ID_DOC_ESTRANGEIRO'] ? 'checked' : ''; ?>>
					<label class="checkbox" for="checkbox_estrangeiro">
						Não sou brasileiro e não tenho CPF<br/>
						I am not a Brazilian and I don't have a CPF<br/>
						Yo no soy de Brasil y no tengo la CPF
					</label><br/><br/>

					<span>
					<?php echo comboTipoDocumento('tipo_documento', $rs['ID_DOC_ESTRANGEIRO']); ?>
					</span>
					<div class="erro_help">
						<p class="erro">
							select the document type<br/>
							seleccione el tipo de documento
						</p>
						<p class="help"></p>
					</div>

					<input type="text" name="rg" id="rg" placeholder="R.G./Document/Documento" maxlength="22" pattern=".{1,22}" value="<?php echo utf8_encode($rs['CD_RG']); ?>"/>
					<div class="erro_help">
						<p class="erro">
							Type your document<br/>
							Escriba su documento
						</p>
						<p class="help"></p>
					</div>
					<input type="text" name="cpf" id="cpf" placeholder="C.P.F" maxlength="14" autocomplete="off" maxlength="11" pattern=".{14}" value="<?php echo utf8_encode($rs['CD_CPF']); ?>">
					<div class="erro_help">
						<p class="erro">informe seu CPF</p>
						<p class="help"></p>
					</div>
				</div>
			</div>

			<div class="input_area endereco">
				<div class="icone"></div>
				<div class="inputs">
					<p class="titulo">Endereço</p>
					<input type="text" name="cep" id="cep" placeholder="CEP" maxlength="9" autocomplete="off" pattern=".{9}" value="<?php echo utf8_encode($rs['CD_CEP']); ?>">
					<div class="erro_help">
						<p class="erro">informe seu CEP</p>
						<p class="help"><a href="http://www.buscacep.correios.com.br/" target="_blank">não sabe seu CEP?</a></p>
					</div>
					<span>
					<?php echo comboEstado('estado', $rs['ID_ESTADO'], true); ?>
					</span>
					<div class="erro_help">
						<p class="erro">selecione o estado</p>
						<p class="help"></p>
					</div>
					<input type="text" name="cidade" id="cidade" placeholder="cidade" maxlength="50" pattern=".{1,50}" value="<?php echo utf8_encode($rs['DS_CIDADE']); ?>">
					<div class="erro_help">
						<p class="erro">informe sua cidade</p>
						<p class="help"></p>
					</div>
					<input type="text" name="bairro" id="bairro" placeholder="bairro" maxlength="50" pattern=".{1,50}" value="<?php echo utf8_encode($rs['DS_BAIRRO']); ?>">
					<div class="erro_help">
						<p class="erro">informe seu bairro</p>
						<p class="help"></p>
					</div>
					<input type="text" name="endereco" id="endereco" placeholder="rua, avenida, praça..." maxlength="150" pattern=".{1,150}" value="<?php echo utf8_encode($rs['DS_ENDERECO']); ?>">
					<div class="erro_help">
						<p class="erro">informe seu logradouro</p>
						<p class="help"></p>
					</div>
					<input type="text" name="complemento" id="complemento" placeholder="complemento" maxlength="50" value="<?php echo utf8_encode($rs['DS_COMPL_ENDERECO']); ?>">
					<div class="erro_help">
						<p class="erro"></p>
						<p class="help"></p>
					</div>
				</div>
			</div>
		</div>
		<div class="coluna">

			<?php if (!(isset($_SESSION['user']) and is_numeric($_SESSION['user']))) { ?>
			<p class="frase">3.2 Dados da conta</p>
			<?php } else { ?>
			<p class="frase">Dados de acesso</p>
			<?php } ?>

			<?php if (!(isset($_SESSION['user']) and is_numeric($_SESSION['user'])) or preg_match('/etapa/', basename($_SERVER['SCRIPT_FILENAME']))) { ?>
			<div class="input_area login">
				<div class="icone"></div>
				<div class="inputs">
					<p class="titulo">Login</p>
					<input type="text" name="email1" id="email1" pattern=".{1,200}" placeholder="digite seu e-mail">
					<div class="erro_help">
						<p class="erro">informe seu e-mail</p>
						<p class="help"></p>
					</div>
					<input type="text" name="email2" id="email2" pattern=".{1,200}" placeholder="confirme seu e-mail">
					<div class="erro_help">
						<p class="erro">confirmação de e-mail não confere</p>
						<p class="help"></p>
					</div>
					<?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
					<input type="password" name="senha1" id="senha1" pattern=".{1,200}" placeholder="digite sua senha">
					<div class="erro_help">
						<p class="help senha">mínimo 6 caracteres com letras e números</p>
					</div>
					<input type="password" name="senha2" id="senha2" pattern=".{1,200}" placeholder="confirme sua senha">
					<div class="erro_help">
						<p class="erro">confirmação de senha não confere</p>
						<p class="help"></p>
					</div>
					<?php } ?>
				</div>
			</div>
			<?php } else { ?>
			<div class="input_area login">
				<div class="icone"></div>
				<div class="inputs">
					<p class="titulo">Login</p>
					<input type="text" name="email" id="email" value="<?php echo utf8_encode($rs['CD_EMAIL_LOGIN']); ?>" <?php echo (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) ? 'disabled' : ''; ?>/>
					<div class="erro_help">
						<p class="erro"></p>
						<p class="help"></p>
					</div>
				</div>
			</div>
			<?php } ?>

			<?php if (isset($_SESSION['user']) and is_numeric($_SESSION['user'])) { ?>
			<p class="frase">Guia de Espetáculos</p>
			<?php } ?>
			<div class="input_area guia_sms">
				<input id="checkbox_guia" type="checkbox" name="extra_info" class="checkbox" value="S" <?php echo ($rs['IN_RECEBE_INFO'] == 'S' or (!(isset($_SESSION['user']) and is_numeric($_SESSION['user'])))) ? 'checked' : ''; ?>>
				<label class="checkbox" for="checkbox_guia">quero receber o guia de espetáculos, com atrações específicas para a minha localidade</label>
				<input id="checkbox_sms" type="checkbox" name="extra_sms" class="checkbox" value="S" <?php echo ($rs['IN_RECEBE_SMS'] == 'S') ? 'checked' : ''; ?>>
				<label class="checkbox" for="checkbox_sms" id="label_sms">autorizo o envio de mensagens SMS</label>
				
				<?php if (!(isset($_SESSION['user']) and is_numeric($_SESSION['user'])) or preg_match('/etapa/', basename($_SERVER['SCRIPT_FILENAME']))) { ?>
				<input id="checkbox_politica" type="checkbox" name="concordo" class="checkbox" value="S">
				<label class="checkbox" for="checkbox_politica" id="label_politica">
					concordo com os <a href="" target="_blank" class="termos_de_uso">termos de uso</a> e a 
					<a href="" target="_blank" class="politica_de_privacidade">política de privacidade</a>
				</label>
				<?php } ?>
			</div>
			<?php
				if (!(isset($_SESSION['user']) and is_numeric($_SESSION['user'])) and !(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) {
					require_once('../settings/settings.php');
					require_once('../settings/recaptchalib.php');
					echo recaptcha_get_html($recaptcha['public_key'], null, true);
				}
			?>
			
			<input type="button" class="submit salvar_dados" style="margin-top: 36px">
			<div class="erro_help">
				<p class="erro"></p>
				<p class="help senha hidden">Seus dados foram atualizados com sucesso!</p>
			</div>
		</div>
	</form>
</div>