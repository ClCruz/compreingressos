<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 13, true)) {

$pagina = basename(__FILE__);

$result = executeSQL($mainConnection, 'SELECT E.ID_EVENTO, DS_EVENTO, E.IN_ATIVO, 
CONVERT(VARCHAR(10), MIN(DT_APRESENTACAO),103) AS DATA_INICIAL, 
CONVERT(VARCHAR(10), MAX(DT_APRESENTACAO),103) AS DATA_FINAL 
FROM MW_EVENTO  E LEFT JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
WHERE E.ID_BASE = ?
GROUP BY E.ID_EVENTO, DS_EVENTO, E.IN_ATIVO ORDER BY DS_EVENTO, E.ID_EVENTO', array($_GET['teatro']));

$resultTeatros = executeSQL($mainConnection, 'SELECT ID_BASE, DS_NOME_TEATRO FROM MW_BASE WHERE IN_ATIVO = \'1\'');
?>

<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>'

	$('#teatro').change(function() {
		document.location = '?p=' + pagina.replace('.php', '') + '&teatro=' + $(this).val();
	});
	
	$('tr:not(.ui-widget-header)').hover(function() {
		$(this).addClass('ui-state-hover');
	}, function() {
		$(this).removeClass('ui-state-hover');
	});
});
</script>
<h2>Lista de Eventos</h2>
<p style="width:200px;"><?php echo comboTeatro('teatro', $_GET['teatro']); ?></p>
<table class="ui-widget ui-widget-content">
	<thead>
		<tr class="ui-widget-header">
			<th>ID</th>
			<th>Evento</th>
			<th>Data de In&iacute;cio</th>
			<th>Data de T&eacute;rmino</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		<?php while($rs = fetchResult($result)) { ?>
		<tr>
			<td><?php echo $rs['ID_EVENTO']; ?></td>
			<td><?php echo utf8_encode($rs['DS_EVENTO']); ?></td>
			<td><?php echo $rs['DATA_INICIAL']; ?></td>
			<td><?php echo $rs['DATA_FINAL']; ?></td>
			<td><?php echo ($rs['IN_ATIVO'] ? 'Ativo' : 'Inativo'); ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
<?php
}
?>