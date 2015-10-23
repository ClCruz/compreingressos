<?php
session_start();
require_once('../settings/functions.php');

require('acessoLogado.php');
require_once('../settings/settings.php');
require_once('../settings/MCAPI.class.php');

$mainConnection = mainConnection();

$json = json_encode(array('descricao' => '8. chamada do pagamento_ok - codigo_pedido=' . $_GET['pedido'], 'Post='=>$_GET ));
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

$json = json_encode(array('descricao' => '9. fim da chamada do pagamento_ok - codigo_pedido=' . $_GET['pedido'], 'Post='=>$_GET ));
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
	$evento_info = getEvento($rs['ID_EVENTO']);

	$id_item = $rs['ID_EVENTO'] . '_' . $rs['ID_APRESENTACAO_BILHETE'];
	$ds_item = utf8_encode($rs['DS_EVENTO'] . ' - ' . $evento_info['nome_teatro']);
	$tipo = utf8_encode($rs['DS_TIPO_BILHETE']);
	$valor = $rs['VL_LIQUIDO_INGRESSO'];
	$quantidade = $rs['QUANTIDADE'];
	$id_evento = $rs['ID_EVENTO'];

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

limparCookies();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow">
	<link href="../images/favicon.ico" rel="shortcut icon"/>
	<link href='https://fonts.googleapis.com/css?family=Paprika|Source+Sans+Pro:200,400,400italic,200italic,300,900' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="../stylesheets/cicompra.css"/>
    <?php require("desktopMobileVersion.php"); ?>
	<link rel="stylesheet" href="../stylesheets/ajustes2.css"/>

	<script src="../javascripts/jquery.2.0.0.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.placeholder.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.selectbox-0.2.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.mask.min.js" type="text/javascript"></script>
	<script src="../javascripts/cicompra.js" type="text/javascript"></script>

	<script src="../javascripts/jquery.cookie.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.utils2.js" type="text/javascript"></script>
	<script src="../javascripts/common.js" type="text/javascript"></script>
	
	<style type="text/css">
	#selos {
		margin-bottom: 0;
	}
	.imprima_agora.nova_venda {
	    float: right;
	    width: auto;
	}
	</style>

	<script type="text/javascript">
	function popup(url, width, height) {
		var left = (window.screen.width/2)-(width/2);
		var top = (window.screen.height/2)-(height/2);

		var win = window.open(url, "_blank", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+width+', height='+height);
		win.moveTo(left, top);
	}
	</script>

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

	<script type="text/javascript">
	var fb_param = {};
	fb_param.pixel_id = '6009548174001';
	fb_param.value = '<?php echo $valorPagamento; ?>';
	fb_param.currency = 'USD';
	(function(){
	  var fpw = document.createElement('script');
	  fpw.async = true;
	  fpw.src = '//connect.facebook.net/en_US/fp.js';
	  var ref = document.getElementsByTagName('script')[0];
	  ref.parentNode.insertBefore(fpw, ref);
	})();
	</script>
	<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6009548174001&amp;value=<?php echo $valorPagamento; ?>&amp;currency=USD" /></noscript>

	<!-- Facebook Conversion Code for Compra -->
	<script>(function() {
	  var _fbq = window._fbq || (window._fbq = []);
	  if (!_fbq.loaded) {
	    var fbds = document.createElement('script');
	    fbds.async = true;
	    fbds.src = '//connect.facebook.net/en_US/fbds.js';
	    var s = document.getElementsByTagName('script')[0];
	    s.parentNode.insertBefore(fbds, s);
	    _fbq.loaded = true;
	  }
	})();
	window._fbq = window._fbq || [];
	window._fbq.push(['track', '6025588813845', {'value':'<?php echo $valorPagamento; ?>','currency':'BRL'}]);
	</script>
	<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/tr?ev=6025588813845&amp;cd[value]=<?php echo $valorPagamento; ?>&amp;cd[currency]=BRL&amp;noscript=1" /></noscript>

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

			<div class="centraliza" id="pedido">
				<div class="descricao_pag">
					<div class="img">
						<img src="../images/ico_black_passo6.png">
					</div>
					<div class="descricao final">
						<p class="nome">Muito obrigado!</p>
						<p class="descricao">
							<b>Seus ingressos foram enviados para<br>
							seu e-mail,</b> verifique a caixa de spam<br>
							se não encontrar a mensagem.
						</p>
					</div>
					<div class="numero_pedido">
						<p class="numero">
							Seu pedido com o número<br>
							<a href="minha_conta.php?pedido=<?php echo $_GET['pedido']; ?>" <?php echo (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) ? 'target="_blank"' : ''; ?>><?php echo $_GET['pedido']; ?></a> foi realizado.
						</p>
						<p class="minha_conta">
							Você pode conferir essa compra e as<br>
							anteriores acessando a <a href="minha_conta.php" <?php echo (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) ? 'target="_blank"' : ''; ?>>minha conta</a>
						</p>
					</div>
				</div>
				<div class="imprima_agora"><a href="reimprimirEmail.php?pedido=<?php echo $_GET['pedido']; ?>"><div class="icone"></div>Imprima agora seus ingressos.</a></div>
				<?php if ((isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) { ?>
					<div class="imprima_agora nova_venda"><a href="etapa0.php">NOVA VENDA</a></div>
				<?php } ?>
				<div class="euvou">
            	<a href="javascript:popup('https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($homeSite . 'espetaculos/' . $id_evento); ?>', 600, 350);"><div class="icone"></div>Eu vou! Convide seus amigos no <img src="../images/ico_facebook_logo.png"></a></div>

            	<?php require 'detalhes_pedido.php';?>

			</div>
		</div>

		<div id="texts">
			<div class="centraliza">
				<p>Muito obrigado por escolher a COMPREINGRESSOS para a compra de seus ingressos.</p>

				<p>Fique por dentro das principais atrações em cartaz na sua cidade através do nosso Guia de Espetáculos enviado por email. Adicione o email marketing@compreingressos.com ao seu catálogo de endereços para receber nossos emails na sua caixa de entrada.</p>

				<p>Curta nossa página no <a href=“http://www.facebook.com/compreingressos”>Facebook</a> e acompanhe diariamente as últimas novidades da nossa programação.</p>

				<p>Bom espetáculo!</p>
			</div>
		</div>

		<?php include "footer.php"; ?>

		<?php include "selos.php"; ?>
	</div>

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