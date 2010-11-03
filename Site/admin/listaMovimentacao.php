<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 12, true)) {

require_once('../settings/Paginator.php');

$pagina = basename(__FILE__);

if(isset($_GET["dt_inicial"]) && isset($_GET["dt_final"]) && isset($_GET["situacao"])){
	if(isset($_GET["offset"]))
		$offset = $_GET["offset"];
	else
		$offset = 1;
	
	$where = "CONVERT(DATETIME,CONVERT(CHAR(8), PV.DT_PEDIDO_VENDA, 112)) BETWEEN CONVERT(DATETIME, ?, 103) AND CONVERT(DATETIME, ?, 103) AND PV.IN_SITUACAO = ?";

	$params = array($_GET["dt_inicial"], $_GET["dt_final"], $_GET["situacao"], $_GET["dt_inicial"], $_GET["dt_final"], $_GET["situacao"]);

	$tr = numRows($mainConnection, "SELECT PV.ID_PEDIDO_VENDA FROM MW_PEDIDO_VENDA PV WHERE ". $where, $params);
		
	$total_reg = ($_GET["controle"]) ? $_GET["controle"] : 10;
	$final = ($offset + $total_reg) - 1;
	
	$strSql = "WITH RESULTADO AS (
				  SELECT 
					  (CONVERT(VARCHAR(10), PV.DT_PEDIDO_VENDA, 103) + ' - ' + CONVERT(VARCHAR(8), PV.DT_PEDIDO_VENDA, 114)) AS DT_PEDIDO_VENDA,
					  PV.ID_PEDIDO_VENDA,
					  DS_NOME,
					  DS_SOBRENOME,
					  PV.VL_TOTAL_PEDIDO_VENDA,
					  PV.IN_SITUACAO,
					  ROW_NUMBER() OVER(ORDER BY DT_PEDIDO_VENDA) AS 'LINHA',
					  COUNT(1) AS QUANTIDADE
				  FROM 
					  MW_PEDIDO_VENDA PV 
					  INNER JOIN
					  MW_CLIENTE C
					  ON C.ID_CLIENTE = PV.ID_CLIENTE
					  INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
				  WHERE CONVERT(DATETIME,CONVERT(CHAR(8), PV.DT_PEDIDO_VENDA, 112)) BETWEEN CONVERT(DATETIME, ?, 103) AND CONVERT(DATETIME, ?, 103)
				  AND PV.IN_SITUACAO = ?
				  GROUP BY
  					  (CONVERT(VARCHAR(10), PV.DT_PEDIDO_VENDA, 103) + ' - ' + CONVERT(VARCHAR(8), PV.DT_PEDIDO_VENDA, 114)),
					  PV.ID_PEDIDO_VENDA,
					  DS_NOME,
					  DS_SOBRENOME,
					  PV.VL_TOTAL_PEDIDO_VENDA,
					  PV.IN_SITUACAO,
					  DT_PEDIDO_VENDA
				  
				  UNION ALL
				  
				  SELECT 
					  (CONVERT(VARCHAR(10), PV.DT_PEDIDO_VENDA, 103) + ' - ' + CONVERT(VARCHAR(8), PV.DT_PEDIDO_VENDA, 114)) AS DT_PEDIDO_VENDA,
					  PV.ID_PEDIDO_VENDA,
					  DS_NOME,
					  DS_SOBRENOME,
					  PV.VL_TOTAL_PEDIDO_VENDA,
					  PV.IN_SITUACAO,
					  ROW_NUMBER() OVER(ORDER BY DT_PEDIDO_VENDA) AS 'LINHA',
					  COUNT(1) AS QUANTIDADE
				  FROM 
					  MW_PEDIDO_VENDA PV 
					  INNER JOIN
					  MW_CLIENTE C
					  ON C.ID_CLIENTE = PV.ID_CLIENTE
					  INNER JOIN MW_ITEM_PEDIDO_VENDA_HIST IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
				  WHERE CONVERT(DATETIME,CONVERT(CHAR(8), PV.DT_PEDIDO_VENDA, 112)) BETWEEN CONVERT(DATETIME, ?, 103) AND CONVERT(DATETIME, ?, 103)
				  AND PV.IN_SITUACAO = ?
				  GROUP BY
  					  (CONVERT(VARCHAR(10), PV.DT_PEDIDO_VENDA, 103) + ' - ' + CONVERT(VARCHAR(8), PV.DT_PEDIDO_VENDA, 114)),
					  PV.ID_PEDIDO_VENDA,
					  DS_NOME,
					  DS_SOBRENOME,
					  PV.VL_TOTAL_PEDIDO_VENDA,
					  PV.IN_SITUACAO,
					  DT_PEDIDO_VENDA
				  )
				  SELECT * FROM RESULTADO WHERE LINHA BETWEEN ". $offset ." AND ". $final ." ORDER BY DT_PEDIDO_VENDA ASC";
	$result = executeSQL($mainConnection, $strSql, $params);
	
	$query = 'SELECT 
					  SUM (VL_TOTAL_PEDIDO_VENDA) AS TOTAL_PEDIDO
				  FROM 
					  MW_PEDIDO_VENDA PV 
				  WHERE CONVERT(DATETIME,CONVERT(CHAR(8), PV.DT_PEDIDO_VENDA, 112)) BETWEEN CONVERT(DATETIME, ?, 103) AND CONVERT(DATETIME, ?, 103)
				  AND PV.IN_SITUACAO = ?';
	$rs = executeSQL($mainConnection, $query, $params, true);
	$total['TOTAL_PEDIDO'] = $rs['TOTAL_PEDIDO'];
	
	$query = 'SELECT 
					  COUNT(1) AS QUANTIDADE
				  FROM 
					  MW_PEDIDO_VENDA PV 
					  INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
				  WHERE CONVERT(DATETIME,CONVERT(CHAR(8), PV.DT_PEDIDO_VENDA, 112)) BETWEEN CONVERT(DATETIME, ?, 103) AND CONVERT(DATETIME, ?, 103)
				  AND PV.IN_SITUACAO = ?
				  
				  UNION ALL
				  
				  SELECT 
					  COUNT(1) AS QUANTIDADE
				  FROM 
					  MW_PEDIDO_VENDA PV 
					  INNER JOIN MW_ITEM_PEDIDO_VENDA_HIST IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
				  WHERE CONVERT(DATETIME,CONVERT(CHAR(8), PV.DT_PEDIDO_VENDA, 112)) BETWEEN CONVERT(DATETIME, ?, 103) AND CONVERT(DATETIME, ?, 103)
				  AND PV.IN_SITUACAO = ?';
	$result2 = executeSQL($mainConnection, $query, $params);
	$total['QUANTIDADE'] = 0;
	while ($rs = fetchResult($result2)) {
		$total['QUANTIDADE'] += $rs['QUANTIDADE'];
	}
}
?>
<script type="text/javascript" src="../javascripts/jquery.ui.datepicker-pt-BR.js"></script>
<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>'
	$('.button').button();
	$(".datepicker").datepicker();
	$("#btnRelatorio").click(function(){
		if($('#cboSituacao').val() == "V"){
			$.dialog({title: 'Alerta...', text: 'Selecione a situação'});
		}else{
			document.location = '?p=' + pagina.replace('.php', '') + '&dt_inicial=' + $("#dt_inicial").val() + '&dt_final='+ $("#dt_final").val() + '&situacao=' + $("#cboSituacao").val() + '';	
		}
	});
	$("#controle").change(function(){
		document.location = '?p=' + pagina.replace('.php', '') + '&dt_inicial=' + $("#dt_inicial").val() + '&dt_final='+ $("#dt_final").val() + '&situacao=' + $("#cboSituacao").val() + '&controle=' + $("#controle").val() + '';		
	});
	$('tr:not(.ui-widget-header)').hover(function() {
		$(this).addClass('ui-state-hover');
	}, function() {
		$(this).removeClass('ui-state-hover');
	});	 
	
	$('tr:not(.ui-widget-header, .total)').click(function() {
		$('loadingIcon').fadeIn('fast');	
		var $this = $(this),
		url = $this.find('a').attr('destino');
		$.ajax({
			url: url,
			success: function(data) {
				$('#tabPedidos').find('.itensDoPedido').hide();
				$this.after('<tr class="itensDoPedido"><td colspan="6">' + data + '</td></tr>');
			},
			complete: function() {
				$('loadingIcon').fadeOut('slow');
			}
		});
	});
});
</script>
<style type="text/css">
#paginacao{
	width: 100%;
	text-align: center;
	margin-top: 10px;	
}
</style>
<h2>Relatório de Movimentação</h2>
<?php 
	$mes = date("m") - 1;
?>
<p style="width:1000px;">Data Inicial <input type="text" value="<?php echo (isset($_GET["dt_inicial"])) ? $_GET["dt_inicial"] : date("d/m/Y") ?>" class="datepicker" id="dt_inicial" name="dt_inicial" />&nbsp;&nbsp;Data Final <input type="text" class="datepicker" value="<?php echo (isset($_GET["dt_final"])) ? $_GET["dt_final"] : date("d/m/Y") ?>" id="dt_final" name="dt_final" />&nbsp;&nbsp;Situação <?php echo (isset($_GET["situacao"])) ? combosituacao($_GET["situacao"]) : comboSituacao() ?>&nbsp;&nbsp;<input type="submit" class="button" id="btnRelatorio" value="Buscar" />
<?php if(isset($result) && hasRows($result)){ ?>
&nbsp;&nbsp;<a class="button" href="gerarExcel.php?dt_inicial=<?php echo $_GET["dt_inicial"]; ?>&dt_final=<?php echo $_GET["dt_final"]; ?>&situacao=<?php echo $_GET["situacao"]; ?>">Exportar Excel</a>
<?php } ?>
</p>

<!-- Tabela de pedidos -->
<table class="ui-widget ui-widget-content" id="tabPedidos">
	<thead>
		<tr class="ui-widget-header">
        	<th style="text-align: center;">Visualizar</th>
			<th>Número do Pedido</th>
            <th>Data</th>
			<th>Nome</th>
			<th>Valor total</th>
			<th>Quantidade de Ingressos</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			if(isset($result) ){
				while($rs = fetchResult($result)) { 
		?>
		<tr>
			<td style="text-align: center;"><a style="cursor: pointer;" destino="listaItens.php?pedido=<?php echo $rs['ID_PEDIDO_VENDA']; ?>">+</a></td>
            <td><?php echo $rs['ID_PEDIDO_VENDA']; ?></td>
            <td><?php echo $rs['DT_PEDIDO_VENDA'] ?></td>
			<td><?php echo utf8_encode($rs['DS_NOME']. " ".$rs['DS_SOBRENOME']); ?></td>
			<td><?php echo str_replace(".", ",", $rs['VL_TOTAL_PEDIDO_VENDA']); ?></td>
			<td><?php echo $rs['QUANTIDADE']; ?></td>
		</tr>
		<?php 
				}
		?>
		<tr class="total">
			<td colspan="4" align="right"><strong>Total geral</strong></td>
			<td><?php echo str_replace(".", ",", $total['TOTAL_PEDIDO']); ?></td>
			<td><?php echo $total['QUANTIDADE']; ?></td>
		</tr>
		<?php 
			}
		?>			
	</tbody>
</table>
<div id="paginacao">
<?php
	//paginacao($pc, $intervalo, $tp, true);
	$link = "?p=listaMovimentacao&dt_inicial=".$_GET["dt_inicial"]."&dt_final=".$_GET["dt_final"]."&situacao=".$_GET["situacao"]."&controle=". $total_reg ."&bar=2&baz=3&offset=";
	Paginator::paginate($offset, $tr, $total_reg, $link, true);
?>
</div>

<?php
}
?>