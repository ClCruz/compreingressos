<?php
require_once('../settings/functions.php');
session_start();
$edicao = true;

$campanha = get_campanha_etapa(basename(__FILE__, '.php'));

require('verificarServicosPedido.php');
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

	<script type="text/javascript" src="../javascripts/contagemRegressiva.js?until=<?php echo tempoRestante(); ?>"></script>
	<script type="text/javascript" src="../javascripts/carrinho.js"></script>
	<script type="text/javascript" src="../javascripts/dadosEntrega.js"></script>
	<?php echo $scriptServicosPorPedido; ?>
	
	<?php echo $campanha['script']; ?>

    <?php if (!isset($_SESSION['msg_tipo_ingresso'])) { $_SESSION['msg_tipo_ingresso'] = true; ?>
    <script type="text/javascript">
      $(function(){
      	$.confirmDialog({
	        text: 'Confirme o tipo de ingresso selecionado antes de avançar.',
	        detail: '',
	        uiOptions: {
	          buttons: {
	            'Ok, entendi': ['', function(){
	              fecharOverlay();
	            }]
	          }
	        }
	      });
      });
    </script>
    <?php } ?>

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
						<img src="../images/ico_black_passo2.png">
					</div>
					<div class="descricao">
						<p class="nome">2. Tipo de ingresso</p>
						<p class="descricao">
							passo <b>2 de 5</b> escolha descontos e vantagens
						</p>
						<div class="sessao">
							<p class="tempo" id="tempoRestante"></p>
							<p class="mensagem">
								Após essse prazo seu pedido será cancelado<br>
								automaticamente e os lugares liberados
							</p>
						</div>
					</div>
					<a href="etapa3.php?redirect=<?php echo urlencode('etapa4.php?eventoDS=' . $_GET['eventoDS'] . $campanha['tag_avancar']); ?><?php echo $campanha['tag_avancar']; ?>" class="botao avancar passo3 botao_avancar">outros pedidos</a>
				</div>
				
				<?php require "resumoPedido.php"; ?>

				<div class="container_botoes_etapas">
					<a href="etapa1.php?<?php echo $_COOKIE['lastEvent']; ?><?php echo $campanha['tag_voltar']; ?>" class="botao voltar passo1">outros pedidos</a>
					<a href="etapa3.php?redirect=<?php echo urlencode('etapa4.php?eventoDS=' . $_GET['eventoDS'] . $campanha['tag_avancar']); ?><?php echo $campanha['tag_avancar']; ?>" class="botao avancar passo3 botao_avancar">outros pedidos</a>
				</div>
			</div>
		</div>

		<div id="texts">
			<div class="centraliza">
				<span id="cme"></span>
				
				<p>Confira o(s) assento(s) escolhido(s), o preço, a forma de entrega e clique em avançar para continuar com o processo de compra.</p>

				<p>Formas de entrega:</p>
				<p>1. E-ticket</p>
				<p>No dia da atração escolhida, os ingressos estarão disponíveis na bilheteria, balcão ou guichê determinados pelo local onde se realizará o evento.</p>
				<p>- Seus ingressos só poderão ser retirados no dia da apresentação sendo obrigatório apresentar o cartão utilizado na compra e um documento de identificação pessoal.</p>
				<p>- No caso de meia entrada ou de promoções é obrigatório a apresentação do documento que comprove o benefício no momento da retirada dos ingressos e na entrada no local.</p>
			</div>
		</div>

		<?php include "footer.php"; ?>

		<?php include "selos.php"; ?>
	</div>
</body>
</html>