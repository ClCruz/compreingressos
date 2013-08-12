<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>COMPREINGRESSOS.COM - Escolha de Assentos</title>
	<meta name="author" content="C&C - Computação e Comunicação" />
	<link href="favicon.ico" rel="shortcut icon"/>
	<link rel="stylesheet" href="../stylesheets/ci.css"/>
	<link rel="stylesheet" href="../stylesheets/annotations.css"/>
	<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
	<link rel="stylesheet" href="../stylesheets/jquery.tooltip.css"/>
	<link rel="stylesheet" href="../stylesheets/smoothness/jquery-ui-1.10.3.custom.css"/>

	<script type="text/javascript" src="../javascripts/jquery.js"></script>
	<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
	<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>
	<script type="text/javascript" src="../javascripts/jquery.annotate.js"></script>
	<script type="text/javascript" src="../javascripts/jquery.tooltip.min.js"></script>
	<script type="text/javascript" src="../javascripts/plateia.js?<?php echo $vars; ?>"></script>
	<!-- SCRIPT TAG -->
	<script type="text/JavaScript">
	    var idcampanha = <?php echo ($idcampanha != "") ? $idcampanha : 0; ?>;
	    if(idcampanha != 0){
		var ADM_rnd_<?php echo $idcampanha; ?> = Math.round(Math.random() * 9999);
		var ADM_post_<?php echo $idcampanha; ?> = new Image();
		ADM_post_<?php echo $idcampanha; ?>.src = 'https://ia.nspmotion.com/ptag/?pt=<?php echo $idcampanha; ?>&r='+ADM_rnd_<?php echo $idcampanha; ?>;
	    }
	</script>
	<!-- END SCRIPT TAG -->
	<?php echo $campanha['script']; ?>
    </head>
    <body>
	<div id="background_holder">
	    <div id="respiro">
		<div id="content_container">
<?php require "header.php"; ?>
		    <div id="crumbs">
			<a href="http://www.compreingressos.com">home</a> /
			<a href="#espetaculo">atra&ccedil;&otilde;es</a> /
			<a href="#espetaculo"><?php echo utf8_encode($rs['DS_EVENTO']); ?></a> /
			<a href="#assentos" class="selected">escolha de assentos</a>
		    </div>
<?php include "banners.php"; ?>
		    <div id="center">
			<div id="center_left">
			    <h1>Escolha de assentos</h1>
			    <p class="help_text">Escolha at&eacute; <?php echo $maxIngressos; ?> lugares desejados e clique em avan&ccedil;ar para continuar
							o processo de compra de ingressos.</p>
			    <h3>Outras apresenta&ccedil;&otilde;es</h3>
			    <iframe src="timeTable.php?evento=<?php echo $rs['ID_EVENTO']; ?>" style="width:inherit; width:100%; height:400px;" frameborder="0"></iframe>
				<?php include "seloCertificado.php"; ?>
			</div>
			<div id="center_right" class="scroll">
			    <div id="passos">
				<ul>
				    <li class="passo_ativo"><span class="numero">1. </span>Escolha de assentos</li>
				    <li><span class="numero">2. </span>Conferir Itens</li>
				    <li><span class="numero">3. </span>Identifica&ccedil;&atilde;o</li>
				    <li><span class="numero">4. </span>Confirma&ccedil;&atilde;o</li>
				    <li><span class="numero">5. </span>Pagamento</li>
				</ul>
			    </div>
			    <div id="header_ticket"></div>
			    <div class="titulo">
				<h1 class="uppercase">Site em Manutenção</h1>
			    </div>
    			    <div id="footer_ticket"></div>
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
