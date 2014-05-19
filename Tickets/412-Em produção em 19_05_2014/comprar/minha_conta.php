<?php
require 'acessoLogado.php';

if (isset($_SESSION['user']) and is_numeric($_SESSION['user'])) {
	require_once('../settings/functions.php');
	
	$mainConnection = mainConnection();
	$query = 'SELECT DS_NOME, DS_SOBRENOME, CONVERT(VARCHAR(10), DT_NASCIMENTO, 103) DT_NASCIMENTO, DS_TELEFONE, DS_CELULAR, DS_DDD_TELEFONE, DS_DDD_CELULAR, CD_CPF, CD_RG, ID_ESTADO, DS_CIDADE, DS_BAIRRO, DS_ENDERECO, DS_COMPL_ENDERECO, CD_CEP, CD_EMAIL_LOGIN, IN_RECEBE_INFO, IN_RECEBE_SMS, IN_SEXO, ID_DOC_ESTRANGEIRO FROM MW_CLIENTE WHERE ID_CLIENTE = ?';
	$params = array($_SESSION['user']);
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	$rs['DT_NASCIMENTO'] = explode('/', $rs['DT_NASCIMENTO']);
	
	$query = "SELECT ID_PEDIDO_VENDA,
	CASE IN_RETIRA_ENTREGA
	WHEN 'R' THEN 'retirada no Local'
	WHEN 'E' THEN 'no endereço'
	ELSE ' - '
	END IN_RETIRA_ENTREGA,
	CONVERT(VARCHAR(10), DT_PEDIDO_VENDA, 103) DT_PEDIDO_VENDA, VL_TOTAL_PEDIDO_VENDA,
	IN_SITUACAO
	FROM MW_PEDIDO_VENDA
	WHERE ID_CLIENTE = ? AND IN_SITUACAO <> 'P'
	ORDER BY 1 DESC";
	$params = array($_SESSION['user']);
	$result = executeSQL($mainConnection, $query, $params);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow">
	<link href="../images/favicon.ico" rel="shortcut icon"/>
	<link href='https://fonts.googleapis.com/css?family=Paprika|Source+Sans+Pro:200,400,400italic,200italic,300,900' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="../stylesheets/cicompra.css"/>
	<link rel="stylesheet" href="../stylesheets/ajustes2.css"/>

	<script src="../javascripts/jquery.2.0.0.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.placeholder.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.selectbox-0.2.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.mask.min.js" type="text/javascript"></script>
	<script src="../javascripts/cicompra.js" type="text/javascript"></script>

	<script src="../javascripts/jquery.cookie.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.utils2.js" type="text/javascript"></script>
	<script src="../javascripts/common.js" type="text/javascript"></script>

	<script src="../javascripts/minhaConta.js" type="text/javascript"></script>
	<script src="../javascripts/identificacao_cadastro.js" type="text/javascript"></script>
	<script src="../javascripts/dadosEntrega.js" type="text/javascript"></script>
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
						<img src="../images/ico_enderecos.png">
					</div>
					<div class="descricao">
						<p class="nome">
							Minha conta
							<a href="logout.php">logout</a>
						</p>
						<p class="descricao">
							Olá <b><?php echo utf8_encode($rs['DS_NOME']); ?>,</b> veja seus dados da conta, histórico de pedidos, troque
							a sua senha ou altere suas configurações do guia de espetáculos
						</p>
						<div class="menu_conta">
							<a href="#meus_pedidos" class="botao meus_pedidos ativo">meus pedidos</a>
							<a href="#dados_conta" class="botao dados_conta">dados da conta</a>
							<?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
							<a href="#trocar_senha" class="botao trocar_senha">troca de senha</a>
							<?php } ?>
							<a href="#enderecos" class="botao enderecos ativo">endereços</a>
						</div>
					</div>
				</div>

				<?php require 'div_cadastro.php'; ?>

				<table id="meus_pedidos">
					<thead>
						<tr>
							<td width="170">Pedido</td>
							<td width="200">Forma de Entrega</td>
							<td width="160">Data do Pedido</td>
							<td width="160">Total do Pedido</td>
							<td width="200">Status</td>
						</tr>
					</thead>
					<tbody>
						<?php
						while ($rs = fetchResult($result)) {
							?>
							<tr>
								<td class="npedido"><a href="detalhes_pedido.php?pedido=<?php echo $rs['ID_PEDIDO_VENDA']; ?>"><?php echo $rs['ID_PEDIDO_VENDA']; ?></a></td>
								<td><?php echo $rs['IN_RETIRA_ENTREGA']; ?></td>
								<td><?php echo $rs['DT_PEDIDO_VENDA']; ?></td>
								<td>R$ <?php echo number_format($rs['VL_TOTAL_PEDIDO_VENDA'], 2, ',', ''); ?></td>
								<td><?php echo comboSituacao('situacao', $rs['IN_SITUACAO'], false); ?></td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<span id="detalhes_pedido"></span>

				<?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
				<form id="trocar_senha" method="post" action="cadastro.php">
					<div class="coluna">
						<div class="input_area login troca_de_senha">
							<div class="icone"></div>
							<div class="inputs">
								<p class="titulo">Login</p>
								<input type="password" name="senha" id="senha" placeholder="digite sua senha atual">
								<div class="erro_help">
									<p class="erro">senha atual não confere</p>
									<p class="help"></p>
								</div>

								<input type="password" name="senha1" id="senha1" placeholder="digite sua nova senha">
								<div class="erro_help">
									<p class="erro"></p>
									<p class="help senha">mínimo 6 caracteres com letras e números</p>
								</div>

								<input type="password" name="senha2" id="senha2" placeholder="confirme sua nova senha">
								<div class="erro_help">
									<p class="erro">as senhas devem ser idênticas</p>
									<p class="help"></p>
								</div>

								<input type="button" class="submit salvar_dados">
								<div class="erro_help">
									<p class="help senha hidden">sua senha foi alterada com sucesso</p>
								</div>
							</div>
						</div>
					</div>
				</form>
				<?php } ?>

				<span id="enderecos" class="minha_conta">
				<?php require "dadosEntrega.php"; ?>
				</span>
			</div>
		</div>

		<div id="texts">
			<div class="centraliza">
				<p></p>
			</div>
		</div>

		<?php include "footer.php"; ?>

		<?php include "selos.php"; ?>
	</div>
</body>
</html>