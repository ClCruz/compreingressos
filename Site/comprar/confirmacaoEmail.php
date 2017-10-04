<?php
	session_start();

	require_once('../settings/settings.php');
	require_once('../settings/functions.php');

	if (!isset($_SESSION['user'])) {
		header("Location: login.php?redirect=" . urlencode(getCurrentUrl()));
		die();
	}

	if ($_GET['action'] == 'confirmar') {

		$mainConnection = mainConnection();

		$query = 'SELECT 1 FROM MW_CONFIRMACAO_EMAIL WHERE ID_CLIENTE = ? AND CD_CONFIRMACAO = ?';
		$params = array($_SESSION['user'], trim($_POST['codigo']));
		
		$rs = executeSQL($mainConnection, $query, $params, true);

		if ($rs[0]) {
			$query = 'DELETE FROM MW_CONFIRMACAO_EMAIL WHERE ID_CLIENTE = ? AND CD_CONFIRMACAO = ?';
			executeSQL($mainConnection, $query, $params, true);

			unset($_SESSION['confirmar_email']);

			echo 'redirect.php?redirect=' . $_GET['redirect'];
		}

		die();

	} else if ($_GET['action'] == 'reenviar') {

		sendConfirmationMail($_SESSION['user'], preg_match('/assinatura/', $_GET['redirect']));

		echo json_encode(array('text' => 'Confirmação de e-mail enviada', 'detail' => 'Por favor, confirme o recebimento no e-mail cadastrado.'));

		die();
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow">
	<link href="../images/favicon.ico" rel="shortcut icon"/>
	<link href='https://fonts.googleapis.com/css?family=Paprika|Source+Sans+Pro:200,400,400italic,200italic,300,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="../stylesheets/cicompra.css"/>
	<link rel="stylesheet" href="../stylesheets/ajustes2.css"/>
	<link rel="stylesheet" type="text/css" href="../stylesheets/nova_home.css">
	<link rel="stylesheet" type="text/css" href="../stylesheets/icons/flaticon1/flaticon.css">
	<link rel="stylesheet" type="text/css" href="../stylesheets/icons/socicon/styles.css">

	<script src="../javascripts/jquery.2.0.0.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.placeholder.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.selectbox-0.2.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.mask.min.js" type="text/javascript"></script>
	<script src="../javascripts/cicompra.js" type="text/javascript"></script>

	<script src="../javascripts/confirmacaoEmail.js" type="text/javascript"></script>

	<script src="../javascripts/jquery.utils2.js" type="text/javascript"></script>
	<script src="../javascripts/common.js" type="text/javascript"></script>
	<script src="../javascripts/faro.js" type="text/javascript"></script>
    <script src="../javascripts/faro.js" type="text/javascript"></script>
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
						<img src="../images/ico_black_passo2.png">
					</div>
					<div class="descricao">
						<p class="nome">Confirmação de e-mail</p>
						<p class="descricao">
							Informe o código recebido no e-mail cadastrado<br/>ou solicite o reenvio do e-mail de confirmação.
						</p>
						<div class="sessao">
							<p class="tempo" id="tempoRestante"></p>
							<p class="mensagem"></p>
						</div>
					</div>
				</div>

				<span id="identificacao">
					<form id="confirmacaoForm" method="post" action="confirmacaoEmail.php">
						<div class="identificacao">
							<p class="frase"><b>Já recebi</b><br/>o código</p>
							<p class="site">de confirmação</p>
							<input type="text" name="codigo" placeholder="digite o código recebido" id="codigo" value="<?php echo $_GET['codigo']; ?>">
							<div class="erro_help">
								<p class="erro">código inválido</p>
								<p class="help"></p>
							</div>
							<input type="button" class="submit logar" id="confirmar" />
						</div>
						<div class="identificacao">
							<p class="frase"><b>Não recebi</b><br/>o código</p>
							<p class="site">de confirmação</p>
							<input type="button" class="submit reenviar" id="reenviar" />
						</div>
					</form>
				</span>

			</div>
		</div>

		<div id="texts">
			<div class="centraliza">
				<p>Informe o código recebido no e-mail cadastrado ou solicite o reenvio do e-mail de confirmação.</p>
			</div>
		</div>

		<?php include "footer.php"; ?>

		<?php //include "selos.php"; ?>
	</div>
</body>
</html>