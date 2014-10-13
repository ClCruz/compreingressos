<?php
require_once('../settings/functions.php');
include('../settings/Log.class.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 280, true)) {
	
$pagina = basename(__FILE__);

if (isset($_GET['action'])) {
	
	require('actions/'.$pagina);
	
} else {
	
	$result = executeSQL($mainConnection, "SELECT R.ID_BASE, R.ID_USUARIO 
										   FROM MW_RESPONS_TEATRO R
										   INNER JOIN MW_USUARIO U ON R.ID_USUARIO = U.ID_USUARIO
										   INNER JOIN MW_BASE B ON R.ID_BASE = B.ID_BASE
										   WHERE R.ID_USUARIO = ? OR ? IS NULL
										   ORDER BY DS_NOME, DS_NOME_TEATRO", array($_GET['codUsuario'], $_GET['codUsuario']));
	
?>

<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>'
	
	$('#app table').delegate('a', 'click', function(event) {
		event.preventDefault();
		
		var $this = $(this),
			 href = $this.attr('href'),
			 id = 'codUsuario=' + $.getUrlVar('codUsuario', href) + '&idBase=' + $.getUrlVar('idBase', href),
			 tr = $this.closest('tr');
		
		if (href.indexOf('?action=add') != -1 || href.indexOf('?action=update') != -1) {
			if (!validateFields()) return false;
			
			$.ajax({
				url: href,
				type: 'post',
				data: $('#dados').serialize(),
				success: function(data) {
					if (data.substr(0, 4) == 'true') {
						var id = $.serializeUrlVars(data);
						
						tr.find('td:not(.button):eq(0)').html($('#codUsuario option:selected').text());
						tr.find('td:not(.button):eq(1)').html($('#idBase option:selected').text());
						
						$this.remove();
						tr.find('td.button a:last').attr('href', pagina + '?action=delete&' + id);
						tr.removeAttr('id');
					} else {
						$.dialog({text: data});
					}
				}
			});
		} else if (href == '#delete') {
			tr.remove();
		} else if (href.indexOf('?action=delete') != -1) {
			$.confirmDialog({
				text: 'O Responsável pelo Teatro e suas respectivas permissões de visualização dos eventos serão excluidos.<br><br>Tem certeza que deseja apagar este registro?',
				uiOptions: {
					buttons: {
						'Sim': function() {
							$(this).dialog('close');
							$.get(href, function(data) {
								if (data.replace(/^\s*/, "").replace(/\s*$/, "") == 'true') {
									tr.remove();
								} else {
									$.dialog({text: data});
								}
							});
						}
					}
				}
			});
		}
	});
	
	$('#new').button().click(function(event) {
		event.preventDefault();
		
		if(!hasNewLine()) return false;
		
		var newLine = '<tr id="newLine">' +
								'<td>' +
									'<?php echo comboAdmins('codUsuario', $_GET['codUsuario']); ?>' +
								'</td>' +
								'<td>'+
									'<?php echo comboLocal('idBase'); ?>' +
								'</td>' +
								'<td class="button"><a href="' + pagina + '?action=add">Salvar</a></td>' +
								'<td class="button"><a href="#delete">Apagar</a></td>' +
							'</tr>';
		$(newLine).appendTo('#app table tbody');
		removeOptions();
	});

	function removeOptions() {
		<?php if (isset($_GET['codUsuario']) and $_GET['codUsuario']) { ?>
		$('#codUsuario option:not(:selected)').remove();
		<?php } ?>
	}
	
	function validateFields() {
		var codUsuario = $('#codUsuario'),
			 idBase = $('#idBase'),
			 valido = true;
		if (codUsuario.val() == '') {
			codUsuario.parent().addClass('ui-state-error');
			valido = false;
		} else {
			codUsuario.parent().removeClass('ui-state-error');
		}
		if (idBase.val() == '') {
			idBase.parent().addClass('ui-state-error');
			valido = false;
		} else {
			idBase.parent().removeClass('ui-state-error');
		}
		
		return valido;
	}
});
</script>
<h2>Usuário Responsável pelo Teatro</h2>
<form id="dados" name="dados" method="post">
	<table class="ui-widget ui-widget-content">
		<thead>
			<tr class="ui-widget-header ">
				<th>Usuário</th>
				<th>Local</th>
				<th colspan="2">A&ccedil;&otilde;es</th>
			</tr>
		</thead>
		<tbody>
			<?php
				while($rs = fetchResult($result)) {
					$codUsuario = $rs['ID_USUARIO'];
					$idBase = $rs['ID_BASE'];
			?>
			<tr>
				<td><?php echo comboAdmins('codUsuario', $codUsuario, false); ?></td>
				<td><?php echo comboLocal('idBase', $idBase, false); ?></td>
				<td>&nbsp;</td>
				<td class="button"><a href="<?php echo $pagina; ?>?action=delete&codUsuario=<?php echo $codUsuario; ?>&idBase=<?php echo $idBase; ?>">Apagar</a></td>
			</tr>
			<?php
				}
			?>
		</tbody>
	</table>
	<a id="new" href="#new">Novo</a>
</form>
<?php
}

}
?>