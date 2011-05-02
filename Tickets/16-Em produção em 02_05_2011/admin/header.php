<?php
require_once('../settings/functions.php');
session_start();
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    
    <title><?php echo $title; ?></title>
    
    <link rel='stylesheet' type='text/css' href='../stylesheets/reset.css' />
	 
    <link rel='stylesheet' type='text/css' href='../javascripts/fg-menu/fg.menu.css' />
    <link rel='stylesheet' type='text/css' href='../javascripts/fg-menu/theme/ui.all.css' />
        <link rel="stylesheet" type="text/css" href="../stylesheets/customred/jquery-ui-1.8.12.custom.css"/>
	<!--<link rel="stylesheet" type="text/css" href="../stylesheets/customred/jquery-ui-1.7.3.custom.css"/>-->
	 
    <link rel='stylesheet' type='text/css' href='../stylesheets/admin.css' />
    <link rel='stylesheet' type='text/css' href='../stylesheets/ajustes.css' /> 
	 
	<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="../stylesheets/ajustesIE.css"/>
	<![endif]-->
    
    <script type='text/javascript' src='../javascripts/jquery.js'></script>
    <script type='text/javascript' src='../javascripts/jquery-ui-1.8.12.custom.min.js'></script>
    <script type='text/javascript' src='../javascripts/fg-menu/fg.menu.js'></script>
    <script type='text/javascript' src='../javascripts/jquery.ui.datepicker-pt-BR.js'></script>
    <script type='text/javascript' src='../javascripts/jquery.utils.js'></script>
	 
	 <script>
		 $(function(){
			// BUTTONS
			$('.fg-button').hover(
				function(){ $(this).removeClass('ui-state-default').addClass('ui-state-focus'); },
				function(){ $(this).removeClass('ui-state-focus').addClass('ui-state-default'); }
			);
			
			// MAIN MENU
			$('#flyOut').menu({
				content: $('#flyOut').next().html(), // grab content from this page
				showSpeed: 400,
				width: 200,
				linkToFront: true,
				flyOut: true
			});
			
			$('#flyOut').next().find('a').unbind('click').click(function(event) {
				event.preventDefault();
				
				var $this = $(this);
				
				if ($this.attr('href') != '#') {
					$.ajax({
						url: $.getUrlVar('p', $this.attr('href')) + '.php',
						success: function(data) {
							$('#app').fadeTo('fast', 0, function() {
								$(this).html(data).fadeTo('fast', 1);
							});
						}
					});
				}
			});
			
		 });
	 </script>
</head>

<body>
<div id='holder'>
	<div id='header'>
    	<p style='font-weight:bold' id="clock"><?php echo date('d/m/Y - H:i (T)'); ?></p>
		<?php
		if (isset($_SESSION['admin'])) {
			$mainConnection = mainConnection();
			$query = 'SELECT DS_NOME FROM MW_USUARIO WHERE ID_USUARIO = ?';
			$params = array($_SESSION['admin']);
			$rs = executeSQL($mainConnection, $query, $params, true);
		?>
        <p>Bem vindo, <?php echo $rs['DS_NOME']; ?>!<br />
		[<a href='./login.php?action=logout'>Sair</a>]</p>
		
    	<?php
		}
		getSiteLogo();
		?>
    </div>
    
    <div id='mainMenu' class="ui-state-default">
    	<?php
		if (isset($_SESSION['admin'])) {
			require_once('mainMenu.php');
		}
		?>
    </div>