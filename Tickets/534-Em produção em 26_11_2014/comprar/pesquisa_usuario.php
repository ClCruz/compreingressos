<?php
session_start();

if (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) {
	require_once('../settings/functions.php');
	
	if (isset($_SESSION['user']) and is_numeric($_SESSION['user'])) {
		$mainConnection = mainConnection();
		
		$query = 'SELECT ID_CLIENTE, DS_NOME, DS_SOBRENOME, DS_DDD_TELEFONE, DS_TELEFONE, CD_CPF
					 FROM MW_CLIENTE
					 WHERE ID_CLIENTE = ?';
		$rs = executeSQL($mainConnection, $query, array($_SESSION['user']), true);
		$userSelected = true;
	} else {
		$userSelected = false;
	}
} else header("Location: loginOperador.php?redirect=pesquisa_operador.php");

$campanha = get_campanha_etapa(basename(__FILE__, '.php'));
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

	<script type="text/javascript" src="../javascripts/identificacao_cadastro_operador.js"></script>
	
	<script>
	$(function() {
		$('#buscar').off('click').click(function(event) {
			event.preventDefault();
			
			var form = $('#identificacaoForm'),
				 valido = true;

			form.find(':input').each(function() {
				if ($(this).val().length < 3 && $(this).val() != '') valido = false;
			});
			
			if (!valido) {
				$('#resultadoBusca').slideUp('fast', function() {
					$(this).html('<p>Os campos preenchidos devem ter, pelo menos, 3 caractéres para efetuar a busca.</p>');
				}).slideDown('fast');
				return false;
			}
			
			$.ajax({
				url: form.attr('action') + '?redirect=' + encodeURI('minha_conta.php?assinatura=1') + $.serializeUrlVars(),
				data: form.serialize(),
				type: form.attr('method'),
				success: function(data) {
					$('#resultadoBusca').slideUp('fast', function() {
						$(this).html(data);
					}).slideDown('fast');
				}
			});
		});

		$('#limpar').click();
		
		<?php if ($userSelected) { ?>
		$('#nomeBusca').val('<?php echo utf8_encode($rs['DS_NOME']); ?>');
		$('#sobrenomeBusca').val('<?php echo utf8_encode($rs['DS_SOBRENOME']); ?>');
		$('#telefoneBusca').val('<?php echo $rs['DS_TELEFONE']; ?>');
		$('#cpfBusca').val('<?php echo $rs['CD_CPF']; ?>');
		
		$('#buscar').click();
		<?php } ?>
	})
	</script>

	<?php echo $campanha['script']; ?>

	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-16656615-1']);
	  _gaq.push(['_setDomainName', 'compreingressos.com']);
	  _gaq.push(['_setAllowLinker', true]);
	  _gaq.push(['_trackPageview']);

	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>
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
						<img src="../images/ico_black_passo3.png">
					</div>
					<div class="descricao">
						<p class="nome">Identificação</p>
					</div>
				</div>

				<?php require "div_identificacao_operador.php"; ?>
				<div id="resultadoBusca" class="identificacao"></div>
				<?php require "div_cadastro.php"; ?>

			</div>
		</div>

		<div id="texts">
			<div class="centraliza">
				<p></p>
			</div>
		</div>

		<?php include "footer.php"; ?>

		<?php include "selos.php"; ?>

		<div id="overlay">
			<?php require 'termosUso.php'; ?>
		</div>
	</div>
</body>
</html>