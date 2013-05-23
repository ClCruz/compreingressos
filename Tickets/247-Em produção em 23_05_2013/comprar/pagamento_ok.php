<?php
session_start();
require_once('../settings/functions.php');

require('acessoLogado.php');
require_once('../settings/settings.php');
require_once('../settings/MCAPI.class.php');

$mainConnection = mainConnection();

$json = json_encode(array('descricao' => '1. chamada do pagamento_ok - codigo_pedido=' . $_GET['pedido'], 'Post='=>$_GET ));
include('logiPagareChamada.php');

$campanha = get_campanha_etapa(basename(__FILE__, '.php'));

$query = 'SELECT
			C.DS_NOME,
			PV.VL_TOTAL_PEDIDO_VENDA,
			PV.VL_TOTAL_TAXA_CONVENIENCIA,
			PV.VL_FRETE,
			ISNULL(PV.DS_CIDADE_ENTREGA, C.DS_CIDADE) DS_CIDADE,
			ISNULL(E1.DS_ESTADO, E2.DS_ESTADO) DS_ESTADO
			FROM MW_PEDIDO_VENDA PV
			INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = PV.ID_CLIENTE
			LEFT JOIN MW_ESTADO E1 ON E1.ID_ESTADO = PV.ID_ESTADO
			LEFT JOIN MW_ESTADO E2 ON E2.ID_ESTADO = C.ID_ESTADO
			WHERE PV.ID_PEDIDO_VENDA = ? AND PV.ID_CLIENTE = ?';
$params = array($_GET['pedido'], $_SESSION['user']);
$rs = executeSQL($mainConnection, $query, $params, true);

$valorPagamento = $rs['VL_TOTAL_PEDIDO_VENDA'];
$valorServico = $rs['VL_TOTAL_TAXA_CONVENIENCIA'];
$valorFrete = $rs['VL_FRETE'];
$cidade = utf8_encode($rs['DS_CIDADE']);
$estado = utf8_encode($rs['DS_ESTADO']);
$nome = $rs['DS_NOME'];

$json = json_encode(array('descricao' => '3. fim da chamada do pagamento_ok - codigo_pedido=' . $_GET['pedido'], 'Post='=>$_GET ));
include('logiPagareChamada.php');

$scriptTransactionAnalytics = "
_gaq.push(['_addTrans',
	'" . $_GET['pedido'] . "',
	'Compreingressos.com',
	'" . $valorPagamento . "',
	'" . $valorServico . "',
	'" . $valorFrete . "',
	'" . $cidade . "',
	'" . $estado . "',
	'BRA'
]);";

$dados_pedido = array(
	'id' => $_GET['pedido'],
	'email_id' => $_COOKIE['mc_eid'],
	'total' => $valorPagamento,
	'shipping' => $valorFrete,
	'tax' => $valorServico,
	'store_id' => 1,
	'store_name' => 'Compreingressos.com',
	'campaign_id' => $_COOKIE['mc_cid']
);

$query = "SELECT COUNT(1) QUANTIDADE, R.ID_APRESENTACAO_BILHETE,
				E.ID_EVENTO, E.DS_EVENTO, ISNULL(LE.DS_LOCAL_EVENTO, B.DS_NOME_TEATRO) DS_NOME_TEATRO,
				AB.VL_LIQUIDO_INGRESSO, AB.DS_TIPO_BILHETE
			FROM MW_ITEM_PEDIDO_VENDA R
			INNER JOIN MW_PEDIDO_VENDA PV ON PV.ID_PEDIDO_VENDA = R.ID_PEDIDO_VENDA
			INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO AND A.IN_ATIVO = '1'
			INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = '1'
			INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE
			INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE AND AB.IN_ATIVO = '1'
			LEFT JOIN MW_LOCAL_EVENTO LE ON E.ID_LOCAL_EVENTO = LE.ID_LOCAL_EVENTO
			WHERE R.ID_PEDIDO_VENDA = ? AND PV.ID_CLIENTE = ?
			GROUP BY R.ID_APRESENTACAO_BILHETE,
				E.ID_EVENTO, E.DS_EVENTO, ISNULL(LE.DS_LOCAL_EVENTO, B.DS_NOME_TEATRO),
				AB.VL_LIQUIDO_INGRESSO, AB.DS_TIPO_BILHETE";
$params = array($_GET['pedido'], $_SESSION['user']);
$result = executeSQL($mainConnection, $query, $params);

while ($rs = fetchResult($result)) {
	$id_item = $rs['ID_EVENTO'] . '_' . $rs['ID_APRESENTACAO_BILHETE'];
	$ds_item = utf8_encode($rs['DS_EVENTO'] . ' - ' . $rs['DS_NOME_TEATRO']);
	$tipo = utf8_encode($rs['DS_TIPO_BILHETE']);
	$valor = $rs['VL_LIQUIDO_INGRESSO'];
	$quantidade = $rs['QUANTIDADE'];

	$scriptTransactionAnalytics .= "
	_gaq.push(['_addItem',
		'" . $_GET['pedido'] . "',
		'" . $id_item . "',
		'" . $ds_item . "',
		'" . $tipo . "',
		'" . $valor . "',
		'" . $quantidade . "'
	]);";

	$dados_pedido['items'][] = array(
		'product_id' => $id_item,
		'product_name' => $ds_item,
		'category_id' => $rs['ID_APRESENTACAO_BILHETE'],
		'category_name' => $tipo,
		'qty' => $quantidade,
		'cost' => $valor
	);
}

if ($_COOKIE['mc_eid'] and $_COOKIE['mc_cid']) {
	$mcap = new MCAPI($MailChimp['api_key']);
	$mcap->campaignEcommOrderAdd($dados_pedido);
}

setcookie('pedido', '', -1);
setcookie('id_braspag', '', -1);
setcookie('entrega', '', -1);
setcookie('binItau', '', -1);

setcookie('mc_eid', '', -1);
setcookie('mc_cid', '', -1);
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>COMPREINGRESSOS.COM - Finaliza&ccedil;&atilde;o</title>
		<meta name="author" content="C&C - Computação e Comunicação" />
		<link href="favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/annotations.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css">
		
		<script type="text/javascript" src="../javascripts/jquery.js"></script>
		<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>

		<?php echo $campanha['script']; ?>

		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-16656615-1']);
		  _gaq.push(['_setDomainName', 'compreingressos.com']);
		  _gaq.push(['_setAllowLinker', true]);
		  _gaq.push(['_trackPageview']);

		  <?php echo $scriptTransactionAnalytics; ?>
		  _gaq.push(['_trackTrans']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>
	</head>
	<body>
		<div id="background_holder">
			<div id="respiro">
				<div id="content_container">
					<?php require "header.php"; ?>
					<div id="crumbs">
						<a href="http://www.compreingressos.com">home</a> /
						<a href="#carrinho" class="selected">carrinho</a>
					</div>
					<?php include "banners.php"; ?>
					<div id="center">
						<div id="center_left">
							<h1>Pagamento</h1>
							<p class="help_text">Pagamento finalizado.</p>
							<?php include "seloCertificado.php"; ?>
						</div>
						<div id="center_right">
							<div id="passos">
								<ul>
									<li><span class="numero">1. </span>Escolha de assentos</li>
									<li><span class="numero">2. </span>Conferir Itens</li>
									<li><span class="numero">3. </span>Identifica&ccedil;&atilde;o</li>
									<li><span class="numero">4. </span>Finaliza&ccedil;&atilde;o</li>
									<li class="passo_ativo"><span class="numero">5. </span>Pagamento</li>
								</ul>
							</div>
							<div class="titulo with_border_bottom">
								<h1>Obrigado, <?php echo utf8_encode($nome); ?></h1>
							</div>
							<div class="titulo">
								<h1>Seu pagamento foi conclu&iacute;do com sucesso!</h1>
								<p>O n&uacute;mero do seu pedido &eacute; <a href="minha_conta.php?pedido=<?php echo $_GET['pedido']; ?>" <?php echo (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) ? 'target="_blank"' : ''; ?>><?php echo $_GET['pedido']; ?></a></p>
								<p>Para visualiz&aacute;-lo basta clicar no link acima ou acessar o menu <a href="minha_conta.php" <?php echo (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) ? 'target="_blank"' : ''; ?>>minha conta</a></p>
							</div>
							<div id="footer_ticket">
							<?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
								<a href="http://www.compreingressos.com/">
									<div class="botoes_ticket" id="botao_avancar">home</div>
								</a>
							<?php } else { ?>
								<a href="etapa0.php">
									<div class="botoes_ticket" id="botao_avancar">nova venda</div>
								</a>
							<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- fim respiro -->
		</div>
		<!-- fim background -->
		<?php include "footer.php"; ?>

		<!-- Google Code for Compra de Ingresso Conversion Page -->
		<script type="text/javascript">
		/* <![CDATA[ */
		var google_conversion_id = 1038667940;
		var google_conversion_language = "en";
		var google_conversion_format = "3";
		var google_conversion_color = "ffffff";
		var google_conversion_label = "IwGiCLKwrQMQpKGj7wM";
		var google_conversion_value = <?php echo $valorPagamento; ?>;
		/* ]]> */
		</script>
		<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
		</script>
		<noscript>
		<div style="display:inline;">
		<img height="1" width="1" style="border-style:none;" alt="" src="https://www.googleadservices.com/pagead/conversion/1038667940/?value=<?php echo $valorPagamento; ?>&amp;label=IwGiCLKwrQMQpKGj7wM&amp;guid=ON&amp;script=0"/>
		</div>
		</noscript>
	</body>
</html>