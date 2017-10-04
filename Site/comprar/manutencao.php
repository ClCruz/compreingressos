<?php
  require_once('../settings/settings.php');
  require_once('../settings/functions.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow">
	<link href="../images/favicon.ico" rel="shortcut icon"/>
	<link href='https://fonts.googleapis.com/css?family=Paprika|Source+Sans+Pro:200,400,400italic,200italic,300,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="../stylesheets/cicompra.css"/>
    <?php require("desktopMobileVersion.php"); ?>
	<link rel="stylesheet" href="../stylesheets/ajustes2.css"/>

    <link rel="stylesheet" type="text/css" href="../stylesheets/nova_home.css">
    <link rel="stylesheet" type="text/css" href="../stylesheets/icons/flaticon1/flaticon.css">
    <link rel="stylesheet" type="text/css" href="../stylesheets/icons/socicon/styles.css">

	<script src="../javascripts/jquery.2.0.0.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.placeholder.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.selectbox-0.2.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.mask.min.js" type="text/javascript"></script>
	<script src="../javascripts/cicompra.js" type="text/javascript"></script>

	<script src="../javascripts/jquery.cookie.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.utils2.js" type="text/javascript"></script>
	<script src="../javascripts/common.js" type="text/javascript"></script>
    <script src="../javascripts/faro.js" type="text/javascript"></script>
	<script type="text/javascript">
	$(function(){
		$(window).on('resize', function(){
			var window_size = $(this).width(),
				banner_size = $('#banner').width(),
				margin = ((banner_size - window_size) / 2) * -1;

			$('#banner').css({'margin-left': margin})
		}).trigger('resize');
	});
	</script>

	<title>COMPREINGRESSOS.COM - Gestão e Venda de Ingressos</title>
</head>
<body>
	<div id="pai">
		<?php require "header.php"; ?>

		<img id="banner" src="../images/manutencao.jpg">

		<div id="texts">
			<div class="centraliza">
				<span id="cme"></span>
				<p></p>
			</div>
		</div>

		<?php include "footer.php"; ?>

		<?php //include "selos.php"; ?>
	</div>
</body>
</html>