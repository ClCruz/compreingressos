<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 30, true)) {

require_once('../settings/Paginator.php');

$pagina = basename(__FILE__);

if(isset($_GET["dt_inicial"]) && isset($_GET["dt_final"])){
		
	$strSql = "SELECT
					UI.DS_NOME,
					ISNULL(LE.DS_LOCAL_EVENTO, 'Não informado no cadastro de evento') DS_LOCAL_EVENTO,
					SUM(IPV.QT_INGRESSOS) QT_INGRESSOS,
					SUM(IPV.QT_INGRESSOS * IPV.VL_UNITARIO) TOTAL_VENDA
					
				FROM MW_PEDIDO_VENDA PV
					INNER JOIN MW_USUARIO_ITAU UI
					ON UI.ID_USUARIO = PV.ID_USUARIO_ITAU

					LEFT JOIN MW_ITEM_PEDIDO_VENDA IPV
					ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA

					LEFT JOIN MW_APRESENTACAO A
					ON A.ID_APRESENTACAO = IPV.ID_APRESENTACAO
					
					LEFT JOIN MW_EVENTO E
					ON E.ID_EVENTO = A.ID_EVENTO
					
					LEFT JOIN MW_LOCAL_EVENTO LE
					ON LE.ID_LOCAL_EVENTO = E.ID_LOCAL_EVENTO

				WHERE DT_HORA_CANCELAMENTO IS NULL
				AND DT_PEDIDO_VENDA BETWEEN CONVERT(DATETIME, ? + ' 00:00:00', 103) AND CONVERT(DATETIME, ? + ' 23:59:59', 103)
				GROUP BY 
					UI.DS_NOME,
					LE.DS_LOCAL_EVENTO
				ORDER BY LE.DS_LOCAL_EVENTO, TOTAL_VENDA DESC, UI.DS_NOME";
	$params = array($_GET["dt_inicial"], $_GET["dt_final"]);
	$result = executeSQL($mainConnection, $strSql, $params);
	
	$query = "SELECT
					SUM(IPV.QT_INGRESSOS) QT_INGRESSOS,
					SUM(IPV.QT_INGRESSOS * IPV.VL_UNITARIO) TOTAL_VENDA
					
				FROM MW_PEDIDO_VENDA PV
					INNER JOIN MW_USUARIO_ITAU UI
					ON UI.ID_USUARIO = PV.ID_USUARIO_ITAU

					LEFT JOIN MW_ITEM_PEDIDO_VENDA IPV
					ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA

					LEFT JOIN MW_APRESENTACAO A
					ON A.ID_APRESENTACAO = IPV.ID_APRESENTACAO

				WHERE PV.DT_HORA_CANCELAMENTO IS NULL
				AND PV.DT_PEDIDO_VENDA BETWEEN CONVERT(DATETIME, ? + ' 00:00:00', 103) AND CONVERT(DATETIME, ? + ' 23:59:59', 103)";
	$rs = executeSQL($mainConnection, $query, $params, true);
	$total['TOTAL_PEDIDO'] = $rs['TOTAL_VENDA'];
	$total['QUANTIDADE'] = $rs['QT_INGRESSOS'];
}
?>
<script type="text/javascript" src="../javascripts/jquery.ui.datepicker-pt-BR.js"></script>
<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>'
	$('.button').button();
	$(".datepicker").datepicker();
		$('tr:not(.ui-widget-header)').hover(function() {
		$(this).addClass('ui-state-hover');
	}, function() {
		$(this).removeClass('ui-state-hover');
	});
	
	$("#btnRelatorio").click(function(){
		var data1 = Number($('#dt_inicial').val().replace(/\//ig, '')),
			data2 = Number($('#dt_final').val().replace(/\//ig, ''));
		
		if (data1 > data2) {
			$.dialog({title:'Alerta...', text:'A data inicial não pode ser maior que a final.'});
			return false;
		}
		
		document.location = '?p=' + pagina.replace('.php', '') + '&dt_inicial=' + $("#dt_inicial").val() + '&dt_final='+ $("#dt_final").val();
	});
});
</script>
<style type="text/css">
#paginacao{
	width: 100%;
	text-align: center;
	margin-top: 10px;	
}
.number {
	text-align: right;
}
.total {
	font-weight: bold;
}
</style>
<h2>Relatório de Vendas por Local/Usuário Itaú</h2>

<p style="width:1000px;">Data Inicial <input type="text" value="<?php echo (isset($_GET["dt_inicial"])) ? $_GET["dt_inicial"] : date("d/m/Y") ?>" class="datepicker" id="dt_inicial" name="dt_inicial" />
&nbsp;&nbsp;Data Final <input type="text" class="datepicker" value="<?php echo (isset($_GET["dt_final"])) ? $_GET["dt_final"] : date("d/m/Y") ?>" id="dt_final" name="dt_final" />
&nbsp;&nbsp;<input type="submit" class="button" id="btnRelatorio" value="Buscar" />
<?php /*if(isset($result) && hasRows($result)) { ?>
&nbsp;&nbsp;<a class="button" href="gerarExcel.php?dt_inicial=<?php echo $_GET["dt_inicial"]; ?>&dt_final=<?php echo $_GET["dt_final"]; ?>">Exportar Excel</a>
<?php }*/ ?>
</p>

<!-- Tabela de pedidos -->
<table class="ui-widget ui-widget-content" id="tabPedidos">
	<thead>
		<tr class="ui-widget-header">
			<th>Local</th>
            <th>Usuário</th>
			<th>Quantidade de Ingressos</th>
			<th>Total das Vendas</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			if(isset($result) ){
				$lastLocal = '';
				$somaTotal = 0;
				$somaQuant = 0;
				while($rs = fetchResult($result)) {
					if ($lastLocal != $rs['DS_LOCAL_EVENTO'] and $lastLocal != '') {
						?>
						<tr class="total">
							<td colspan="2" class="number">Sub-Total</td>
							<td class="number"><?php echo $somaQuant; ?></td>
							<td class="number"><?php echo number_format($somaTotal, 2, ',', '.'); ?></td>
						</tr>
						<?php
						$lastLocal = $rs['DS_LOCAL_EVENTO'];
						$somaTotal = $rs['TOTAL_VENDA'];
						$somaQuant = $rs['QT_INGRESSOS'];
						?>
						<tr>
							<td><?php echo $rs['DS_LOCAL_EVENTO']; ?></td>
							<td><?php echo $rs['DS_NOME'] ?></td>
							<td class="number"><?php echo $rs['QT_INGRESSOS']; ?></td>
							<td class="number"><?php echo number_format($rs['TOTAL_VENDA'], 2, ',', '.'); ?></td>
						</tr>
						<?php
					} elseif ($lastLocal != $rs['DS_LOCAL_EVENTO']) {
						?>
						<tr>
							<td><?php echo $rs['DS_LOCAL_EVENTO']; ?></td>
							<td><?php echo $rs['DS_NOME'] ?></td>
							<td class="number"><?php echo $rs['QT_INGRESSOS']; ?></td>
							<td class="number"><?php echo number_format($rs['TOTAL_VENDA'], 2, ',', '.'); ?></td>
						</tr>
						<?php
						$lastLocal = $rs['DS_LOCAL_EVENTO'];
						$somaTotal += $rs['TOTAL_VENDA'];
						$somaQuant += $rs['QT_INGRESSOS'];
					} else {
						?>
						<tr>
							<td>&nbsp;</td>
							<td><?php echo $rs['DS_NOME'] ?></td>
							<td class="number"><?php echo $rs['QT_INGRESSOS']; ?></td>
							<td class="number"><?php echo number_format($rs['TOTAL_VENDA'], 2, ',', '.'); ?></td>
						</tr>
						<?php
						$somaTotal += $rs['TOTAL_VENDA'];
						$somaQuant += $rs['QT_INGRESSOS'];
					}
				}
		?>
		<tr class="total">
			<td colspan="2" class="number">Sub-Total</td>
			<td class="number"><?php echo $somaQuant; ?></td>
			<td class="number"><?php echo number_format($somaTotal, 2, ',', '.'); ?></td>
		</tr>
		<tr class="total">
			<td colspan="2" class="number">Total geral</td>
			<td class="number"><?php echo $total['QUANTIDADE']; ?></td>
			<td class="number"><?php echo number_format($total['TOTAL_PEDIDO'], 2, ',', '.'); ?></td>
		</tr>
		<?php 
			}
		?>
	</tbody>
</table>

<?php
}
?>