<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 260, true)) {
	
$pagina = basename(__FILE__);

if (isset($_GET['action'])) {
	
	require('actions/'.$pagina);
	
} else {
	
	$result = executeSQL($mainConnection, 'SELECT ID_MEIO_PAGAMENTO, DS_MEIO_PAGAMENTO, IN_ATIVO FROM MW_MEIO_PAGAMENTO ORDER BY IN_ATIVO DESC, DS_MEIO_PAGAMENTO');
	
?>

<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>';

	$('tr:not(.ui-widget-header)').hover(function() {
        $(this).addClass('ui-state-hover');
    }, function() {
        $(this).removeClass('ui-state-hover');
    });
	
	$('#app table').delegate('a', 'click', function(event) {
		event.preventDefault();
		
		var $this = $(this),
			href = $this.attr('href'),
			id = 'idMeioPagamento=' + $.getUrlVar('idMeioPagamento', href),
			tr = $this.closest('tr');
		
		if (href.indexOf('?action=add') != -1 || href.indexOf('?action=update') != -1) {
			$.ajax({
				url: href,
				type: 'post',
				data: $('#dados').serialize(),
				success: function(data) {
					if (data.substr(0, 4) == 'true') {
						var id = $.serializeUrlVars(data);
						
						tr.find('td:not(.button):eq(1)').html($('#in_ativo').is(':checked') ? 'Sim' : 'Não');
						
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
			
			tr.find('td:not(.button):eq(1)').html('<input id="in_ativo" name="in_ativo" type="checkbox" />');
			if (values[1] == 'Sim') $('#in_ativo').attr('checked', 'checked');
			
			$this.text('Salvar').attr('href', pagina + '?action=update&' + id);
		}
	});
});
</script>
<h2>Habilitar meio de pagamento para WEB</h2>
<form id="dados" name="dados" method="post">
	<table class="ui-widget ui-widget-content">
		<thead>
			<tr class="ui-widget-header ">
				<th>Meio de Pagamento</th>
				<th>Ativo</th>
				<th>A&ccedil;&otilde;es</th>
			</tr>
		</thead>
		<tbody>
			<?php
				while($rs = fetchResult($result)) {
			?>
			<tr>
				<td><?php echo utf8_encode($rs['DS_MEIO_PAGAMENTO']); ?></td>
				<td><?php echo $rs['IN_ATIVO'] ? 'Sim' : 'Não'; ?></td>
				<td class="button"><a href="<?php echo $pagina; ?>?action=edit&idMeioPagamento=<?php echo $rs['ID_MEIO_PAGAMENTO']; ?>">Editar</a></td>
			</tr>
			<?php
				}
			?>
		</tbody>
	</table>
</form>
<?php
}

}
?>