<?php
require 'acessoLogado.php';

if (isset($_SESSION['user']) and is_numeric($_SESSION['user'])) {
	require_once('../settings/functions.php');
	
	$mainConnection = mainConnection();
	$query = 'SELECT DS_NOME, DS_SOBRENOME, CONVERT(VARCHAR(10), DT_NASCIMENTO, 103) DT_NASCIMENTO, DS_TELEFONE, DS_CELULAR, DS_DDD_TELEFONE, DS_DDD_CELULAR, CD_CPF, CD_RG, ID_ESTADO, DS_CIDADE, DS_BAIRRO, DS_ENDERECO, DS_COMPL_ENDERECO, CD_CEP, CD_EMAIL_LOGIN, IN_RECEBE_INFO, IN_RECEBE_SMS, IN_SEXO FROM MW_CLIENTE WHERE ID_CLIENTE = ?';
	$params = array($_SESSION['user']);
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	$rs['DT_NASCIMENTO'] = explode('/', $rs['DT_NASCIMENTO']);
	$rs['CD_CEP'] = array(substr($rs['CD_CEP'], 0, 5), substr($rs['CD_CEP'], -3));
	
	$query = 'SELECT ID_PEDIDO_VENDA,
				 CASE IN_RETIRA_ENTREGA
				 	WHEN \'R\' THEN \'Retirada no Local\'
					WHEN \'E\' THEN \'Para o endereço...\'
					ELSE \' - \'
				 END IN_RETIRA_ENTREGA,
				 CONVERT(VARCHAR(10), DT_PEDIDO_VENDA, 103) DT_PEDIDO_VENDA, VL_TOTAL_PEDIDO_VENDA,
				 IN_SITUACAO
				 FROM MW_PEDIDO_VENDA
				 WHERE ID_CLIENTE = ? AND IN_SITUACAO <> \'P\'';
	$params = array($_SESSION['user']);
	$result = executeSQL($mainConnection, $query, $params);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>COMPREINGRESSOS.COM - Minha Conta</title>
		<meta name="author" content="C&C - Computação e Comunicação" />
		<link href="favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="../stylesheets/smoothness/jquery-ui-1.8.4.custom.css"/>
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
		
		<script type="text/javascript" src="../javascripts/jquery.js"></script>
		<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.cookie.js"></script>
		<script type="text/javascript" src="../javascripts/identificacao_cadastro.js"></script>
		<script type="text/javascript" src="../javascripts/minhaConta.js"></script>
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
							<span class="#cadastro">
								<h1>Dados Pessoais</h1>
								<p class="help_text">Navegue pelos menus para acessar seus dados.</p>
								<p class="help_text">As informa&ccedil;&otilde;es ao lado foram preenchidas no ato do 
									seu cadastro, para alter&aacute;-las basta escrever nos campos e clicar em salvar.</p>
							</span>
							
							<span class="#pedidos">
								<h1>Pedidos</h1>
								<p class="help_text">Navegue pelos menus para acessar seus dados.</p>
								<p class="help_text">Confira na lista ao lado todos os pedidos listados na sua conta.</p>
							</span>
							
							<?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
							<span class="#identificacao">
								<h1>Senha</h1>
								<p class="help_text">Navegue pelos menus para acessar seus dados.</p>
								<p class="help_text">As informa&ccedil;&otilde;es ao lado foram preenchidas no ato do 
								seu cadastro, para alter&aacute;-las basta escrever nos campos e clicar em salvar.</p>
							</span>
							<?php } ?>
							<?php include "seloCertificado.php"; ?>
						</div>
						<div id="center_right">
							<div id="header_ticket">
								<a href="etapa2.php" class="botao_cancelar" style="margin:0">
									<div class="botoes_ticket">minha compra</div>
								</a>
							</div>
							<div class="titulo with_border_bottom">
								<h1>Ol&aacute; <?php echo utf8_encode($rs['DS_NOME']); ?></h1>
							</div>
							<div id="abas_minha_conta">
								<a href="#cadastro">
									<div class="aba_minha_conta aba_down">dados pessoais</div>
								</a>
								<?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
								<a href="#identificacao">
									<div class="aba_minha_conta">senha</div>
								</a>
								<?php } ?>
								<a href="#pedidos">
									<div class="aba_minha_conta">pedidos</div>
								</a>
								<?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
								<a href="logout.php">
									<div class="aba_minha_conta">sair</div>
								</a>
								<?php } ?>
							</div>
							<?php require 'div_cadastro.php'; ?>
							<div id="pedidos">
								<table>
									<tr>
										<th>Pedido</th>
										<th>Forma de Entrega</th>
										<th>Data do Pedido</th>
										<th>Total do Pedido</th>
										<th>Status</th>	
									</tr>
									<?php
									while ($rs = fetchResult($result)) {
									?>
									<tr>
										<td><a href="detalhes_pedido.php?pedido=<?php echo $rs['ID_PEDIDO_VENDA']; ?>"><?php echo $rs['ID_PEDIDO_VENDA']; ?></a></td>
										<td><?php echo $rs['IN_RETIRA_ENTREGA']; ?></td>
										<td><?php echo $rs['DT_PEDIDO_VENDA']; ?></td>
										<td>R$ <?php echo $rs['VL_TOTAL_PEDIDO_VENDA']; ?></td>
										<td><?php echo comboSituacao('situacao', $rs['IN_SITUACAO'], false); ?></td>
									</tr>
									<?php
									}
									?>
								</table>
							</div>
							<span id="detalhes_pedido"></span>
							
							<?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
							<div id="identificacao">
								<form id="trocaSenha" method="post" action="cadastro.php">
									<h2>Senha atual</h2>
									<input name="senha" type="password" id="senha" size="30" maxlength="30"/>
									<p class="err_msg">Insira sua senha atual</p>
									<h2>Nova senha</h2>
									<input name="senha1" type="password" id="senha1" size="30" maxlength="30"/>
									<p class="err_msg">Insira uma nova senha</p>
									<h2>Confirme sua nova senha</h2>
									<input name="senha2" type="password" id="senha2" size="30" maxlength="30"/>
									<p class="err_msg">Confirme sua senha</p>
								</form>
							</div>
							<?php } ?>
							<div id="footer_ticket">
								<a href="etapa2.php" class="botao_cancelar" style="margin:0 15px 0 0;">
									<div class="botoes_ticket">minha compra</div>
								</a>
								<a href="#minha_conta_pedidos">
									<div class="botoes_ticket" id="botao_voltar">voltar</div>
								</a>
								<a href="#minha_conta_senha" id="cadastreme">
									<div class="botoes_ticket" id="botao_pagamento">salvar altera&ccedil;&otilde;es</div>
								</a>
							</div>
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
