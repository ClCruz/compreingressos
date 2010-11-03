<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 6, true)) {
	
$pagina = basename(__FILE__);

if (isset($_GET['action'])) {
	
	require('actions/'.$pagina);
	
} else {
	
	$result = executeSQL($mainConnection, 'SELECT E.DS_EVENTO, CONVERT(VARCHAR(10), T.DT_INICIO_VIGENCIA, 103) DT_INICIO_VIGENCIA, T.VL_TAXA_CONVENIENCIA, CASE WHEN CONVERT(CHAR(8), T.DT_INICIO_VIGENCIA, 112) >= CONVERT(CHAR(8), GETDATE(), 112) THEN 1 ELSE 0 END EDICAO FROM MW_TAXA_CONVENIENCIA T INNER JOIN MW_EVENTO E ON E.ID_EVENTO = T.ID_EVENTO WHERE E.ID_BASE = ?', array($_GET['teatro']));
	
	$resultTeatros = executeSQL($mainConnection, 'SELECT ID_BASE, DS_NOME_TEATRO FROM MW_BASE WHERE IN_ATIVO = \'1\'');
	
?>

<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>'
	
	$('#app table').delegate('a', 'click', function(event) {
		event.preventDefault();
		
		var $this = $(this),
			 href = $this.attr('href'),
			 id = 'idEvento=' + $.getUrlVar('idEvento', href) + '&data=' + $.getUrlVar('data', href),
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
						
						tr.find('td:not(.button):eq(0)').html($('#idEvento option:selected').text());
						tr.find('td:not(.button):eq(1)').html($('#data').val());
						tr.find('td:not(.button):eq(2)').html('R$ ' + $('#valor').val());
						
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
			
			tr.find('td:not(.button):eq(0)').html('<?php echo comboEvento('idEvento', $_GET['teatro']); ?>');
			$('#idEvento').find('option[text=' + values[0] + ']').attr('selected', 'selected');
			tr.find('td:not(.button):eq(1)').html('<input name="data" type="text" class="datePicker inputStyle" id="data" maxlength="10" value="' + values[1] + '" readonly>');
			tr.find('td:not(.button):eq(2)').html('R$ <input name="valor" type="text" class="number inputStyle" id="valor" maxlength="6" value="' + values[2].substr(3, values[2].length) + '" >');
			
			$this.text('Salvar').attr('href', pagina + '?action=update&' + id);
			
			setDatePickers();
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
									'<?php echo comboEvento('idEvento', $_GET['teatro']); ?>' +
								'</td>' +
								'<td><input name="data" type="text" class="datePicker inputStyle" id="data" maxlength="10" readonly></td>' +
								'<td>R$ <input name="valor" type="text" class="number inputStyle" id="valor" maxlength="6" ></td>' +
								'<td class="button"><a href="' + pagina + '?action=add">Salvar</a></td>' +
								'<td class="button"><a href="#delete">Apagar</a></td>' +
							'</tr>';
		$(newLine).appendTo('#app table tbody');
		setDatePickers();
		$('.datePicker').datepicker('option', 'minDate', 0);
	});
	
	$('#teatro').change(function() {
		document.location = '?p=' + pagina.replace('.php', '') + '&teatro=' + $(this).val();
	});
	
	function validateFields() {
		var idEvento = $('#idEvento'),
			 data = $('#data'),
			 valor = $('#valor'),
			 valido = true;
		if (idEvento.val() == '') {
			idEvento.parent().addClass('ui-state-error');
			valido = false;
		} else {
			idEvento.parent().removeClass('ui-state-error');
		}
		if (data.val() == '') {
			data.parent().addClass('ui-state-error');
			valido = false;
		} else {
			data.parent().removeClass('ui-state-error');
		}
		if (valor.val() <= 0) {
			valor.parent().addClass('ui-state-error');
			valido = false;
		} else {
			valor.parent().removeClass('ui-state-error');
		}
		
		return valido;
	}
});
</script>
<h2>Valor do Servi&ccedil;o</h2>
<form id="dados" name="dados" method="post">
	<p style="width:200px;"><?php echo comboTeatro('teatro', $_GET['teatro']); ?></p>
	<table class="ui-widget ui-widget-content">
		<thead>
			<tr class="ui-widget-header ">
				<th>Evento</th>
				<th>Data de In&iacute;cio de Vig&ecirc;ncia</th>
				<th>Valor</th>
				<th colspan="2">A&ccedil;&otilde;es</th>
			</tr>
		</thead>
		<tbody>
			<?php
				while($rs = fetchResult($result)) {
					$idEvento = utf8_encode($rs['DS_EVENTO']);
					$data = $rs['DT_INICIO_VIGENCIA'];
					$valor = $rs['VL_TAXA_CONVENIENCIA'];
			?>
			<tr>
				<td><?php echo $idEvento; ?></td>
				<td><?php echo $data; ?></td>
				<td>R$ <?php echo str_replace('.', ',', $valor); ?></td>
				<?php if ($rs['EDICAO']) { ?>
				<td class="button"><a href="<?php echo $pagina; ?>?action=edit&idEvento=<?php echo $idEvento; ?>&data=<?php echo $data; ?>">Editar</a></td>
				<td class="button"><a href="<?php echo $pagina; ?>?action=delete&idEvento=<?php echo $idEvento; ?>&data=<?php echo $data; ?>">Apagar</a></td>
				<?php } else { ?>
				<td colspan="2">&nbsp;</td>
				<?php } ?>
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