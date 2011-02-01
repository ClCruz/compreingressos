<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 24, true)) {
	
$pagina = basename(__FILE__);

if (isset($_GET['action'])) {
	
	require('actions/'.$pagina);
	
} else {
	
	$result = executeSQL($mainConnection, 'SELECT ID_CARTAO_PATROCINADO, CODPECA, CONVERT(VARCHAR(10), DT_INICIO, 103) DT_INICIO, CONVERT(VARCHAR(10), DT_FIM, 103) DT_FIM FROM MW_EVENTO_PATROCINADO WHERE ID_BASE = ? AND CODPECA = ?', array($_GET['teatro'], $_GET['codpeca']));
	
	$conn = getConnection($_GET['teatro']);
	
?>

<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>'
	
	$('#app table').delegate('a', 'click', function(event) {
		event.preventDefault();
		
		var $this = $(this),
			 href = $this.attr('href'),
			 id = 'idCartaoPatrocinado=' + $.getUrlVar('idCartaoPatrocinado', href) + '&teatro=' + $.getUrlVar('teatro', href) + '&codpeca=' + $.getUrlVar('codpeca', href),
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
						
						tr.find('td:not(.button):eq(0)').html($('#idCartaoPatrocinado option:selected').text());
						tr.find('td:not(.button):eq(1)').html($('#dtInicio').val());
						tr.find('td:not(.button):eq(2)').html($('#dtFim').val());
						
						$this.text('Editar').attr('href', pagina + '?action=edit&' + id);
						tr.find('td.button a:last').attr('href', pagina + '?action=delete&' + id);
						tr.removeAttr('id');
					} else {
						$.dialog({text: data});
					}
				}
			});
		} else if (href.indexOf('?action=edit') != -1) {
			if(!hasNewLine()) return false;
			
			var values = new Array();
			
			tr.attr('id', 'newLine');
			
			$.each(tr.find('td:not(.button)'), function() {
				values.push($(this).text());
			});
			
			tr.find('td:not(.button):eq(0)').html('<?php echo comboCartaoPatrocinado('idCartaoPatrocinado'); ?>');
			$('#idCartaoPatrocinado option').filter(function(){return $(this).text() == values[0]}).attr('selected', 'selected');
			tr.find('td:not(.button):eq(1)').html('<input name="dtInicio" type="text" class="datePicker inputStyle" id="dtInicio" maxlength="10" value="' + values[1] + '" readonly />');
			tr.find('td:not(.button):eq(2)').html('<input name="dtFim" type="text" class="datePicker inputStyle" id="dtFim" maxlength="10" value="' + values[2] + '" readonly />');
			
			$this.text('Salvar').attr('href', pagina + '?action=update&' + id);
			
			setDatePickers();
			
			$('#dtInicio').change(function() {
				$("#dtFim").datepicker("option", "minDate", $(this).val());
			});
		} else if (href == '#delete') {
			tr.remove();
		} else if (href.indexOf('?action=delete') != -1) {
			$.confirmDialog({
				text: 'Tem certeza que deseja apagar este registro?',
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
									'<?php echo comboCartaoPatrocinado('idCartaoPatrocinado'); ?>' +
								'</td>' +
								'<td>'+
									'<input name="dtInicio" type="text" class="datePicker inputStyle" id="dtInicio" maxlength="10" readonly />' +
								'</td>' +
								'<td>'+
									'<input name="dtFim" type="text" class="datePicker inputStyle" id="dtFim" maxlength="10" readonly />' +
								'</td>' +
								'<td class="button"><a href="' + pagina + '?action=add">Salvar</a></td>' +
								'<td class="button"><a href="#delete">Apagar</a></td>' +
							'</tr>';
		$(newLine).appendTo('#app table tbody');
		setDatePickers();
	});
	
	function validateFields() {
		var campos = $(':input'),
			 valido = true;
			 
		$.each(campos, function() {
			var $this = $(this);
			
			if ($this.val() == '' || $this.val() == '-1') {
				$this.parent().addClass('ui-state-error');
				valido = false;
			} else {
				$this.parent().removeClass('ui-state-error');
			}
		});
		return valido;
	}
	
	$('#teatro').change(function() {
		document.location = '?p=' + pagina.replace('.php', '') + '&teatro=' + $(this).val();
	});
	
	$('#codpeca').change(function() {
		document.location = '?p=' + pagina.replace('.php', '') + '&teatro=' + $('#teatro').val() + '&codpeca=' + $(this).val();
	});
});
</script>
<h2>Cart&otilde;es Patrocinados</h2>
<form id="dados" name="dados" method="post">
	<p style="width:400px;"><?php echo comboTeatro('teatro', $_GET['teatro']) . comboTabPeca('codpeca', $conn, $_GET['codpeca']); ?></p>
	<table class="ui-widget ui-widget-content">
		<thead>
			<tr class="ui-widget-header ">
				<th>Cart&atilde;o Patrocinado</th>
				<th>Data de In&iacute;cio</th>
				<th>Data de Fim</th>
				<th colspan="2">A&ccedil;&otilde;es</th>
			</tr>
		</thead>
		<tbody>
			<?php
				while($rs = fetchResult($result)) {
					$idCartaoPatrocinado = $rs['ID_CARTAO_PATROCINADO'];
					$dtInicio = $rs['DT_INICIO'];
					$dtFim = $rs['DT_FIM'];
			?>
			<tr>
				<td><?php echo comboCartaoPatrocinado('idCartaoPatrocinado', $idCartaoPatrocinado, false); ?></td>
				<td><?php echo $dtInicio; ?></td>
				<td><?php echo $dtFim; ?></td>
				<td class="button"><a href="<?php echo $pagina; ?>?action=edit&idCartaoPatrocinado=<?php echo $idCartaoPatrocinado; ?>&teatro=<?php echo $_GET['teatro']; ?>&codpeca=<?php echo $_GET['codpeca']; ?>">Editar</a></td>
				<td class="button"><a href="<?php echo $pagina; ?>?action=delete&idCartaoPatrocinado=<?php echo $idCartaoPatrocinado; ?>&teatro=<?php echo $_GET['teatro']; ?>&codpeca=<?php echo $_GET['codpeca']; ?>">Apagar</a></td>
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