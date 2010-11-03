<?php
header("Content-type: application/vnd.ms-excel");
header("Content-type: application/force-download");
header("Content-Disposition: attachment; filename=relatorio.xls");
header("Pragma: no-cache");
require_once('acessoLogadoDie.php');

require_once('../settings/functions.php');

$pagina = basename(__FILE__);
$mainConnection = mainConnection();

if(isset($_GET["dt_inicial"]) && isset($_GET["dt_final"]) && isset($_GET["situacao"])){
	$params = array($_GET["dt_inicial"], $_GET["dt_final"], $_GET["situacao"]);
	
	$result = executeSQL($mainConnection, "SELECT
		  CONVERT(CHAR(10), PV.DT_PEDIDO_VENDA,103) AS DT_PEDIDO_VENDA,
		  PV.ID_PEDIDO_VENDA,
		  DS_NOME,
		  DS_SOBRENOME,
		  PV.VL_TOTAL_PEDIDO_VENDA,
		  PV.IN_SITUACAO
	FROM
		  MW_PEDIDO_VENDA PV
		  INNER JOIN
		  MW_CLIENTE C
		  ON C.ID_CLIENTE = PV.ID_CLIENTE
	WHERE PV.DT_PEDIDO_VENDA BETWEEN CONVERT(DATETIME, ?, 103) AND CONVERT(DATETIME, ?, 103)
	  AND PV.IN_SITUACAO = ? 
	ORDER BY PV.DT_PEDIDO_VENDA", $params);
}
?>
<style type="text/css">
.moeda {
mso-number-format:"_\(\[$R$ -416\]* \#\,\#\#0\.00_\)\;_\(\[$R$ -416\]* \\\(\#\,\#\#0\.00\\\)\;_\(\[$R$ -416\]* \0022-\0022??_\)\;_\(\@_\)";
}
</style>
<table class="ui-widget ui-widget-content">
	<thead>
		<tr class="ui-widget-header">
			<th>Número do Pedido</th>
            <th>Data</th>
			<th>Nome</th>
			<th>Sobrenome</th>
			<th>Valor total</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			if(isset($result) && hasRows($result)){
				while($rs = fetchResult($result)) { ?>
		<tr>
			<td><?php echo $rs['ID_PEDIDO_VENDA']; ?></td>
            <td><?php echo $rs['DT_PEDIDO_VENDA'] ?></td>
			<td><?php echo utf8_encode($rs['DS_NOME']); ?></td>
			<td><?php echo utf8_encode($rs['DS_SOBRENOME']); ?></td>
			<td class="moeda"><?php echo str_replace(".", ",", $rs['VL_TOTAL_PEDIDO_VENDA']); ?></td>
		</tr>
		<?php 
				}
			}
		?>
	</tbody>
</table>
<?php print_r(sqlErrors()); ?>