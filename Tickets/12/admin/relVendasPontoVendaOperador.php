<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 31, true)) {

require_once('../settings/Paginator.php');

$pagina = basename(__FILE__);

if(isset($_GET["dt_inicial"]) && isset($_GET["dt_final"]) && isset($_GET["local"]) && isset($_GET["evento"])
	and acessoPermitidoEvento($_GET["local"], $_SESSION['admin'], $_GET["evento"])){

	$conn = getConnection($_GET["local"]);
	
	//relatório
	$strSql = "select
					cv.ds_canal_venda,
					isnull(c.descrcaixa, 'N&atilde;o Informado') descrcaixa,
					u.nomusuario,
					t.tipbilhete,
					sum(l.qtdbilhete) qtd,
					sum(l.valpagto) val
				from tablancamento l
				inner join tabcaixa c on l.codcaixa = c.codcaixa
				inner join ci_middleway..mw_canal_venda cv on c.id_canal_venda = cv.id_canal_venda
				inner join tabusuario u on l.codusuario = u.codusuario
				inner join tabtipbilhete t on l.codtipbilhete = t.codtipbilhete
				inner join tabapresentacao a on l.codapresentacao = a.codapresentacao
				where a.codpeca = ?
					and l.datvenda between convert(datetime, ? + ' 00:00:00', 103) and convert(datetime, ? + ' 23:59:59', 103)
					and not exists (select 1
									from tablancamento l2
									where l2.numlancamento = l.numlancamento
										and l2.codtipbilhete = l.codtipbilhete
										and l2.codapresentacao = l.codapresentacao
										and l2.indice = l.indice
										and l2.codtiplancamento = 2)
				group by
					cv.ds_canal_venda,
					c.descrcaixa,
					u.nomusuario,
					t.tipbilhete";
	$params = array($_GET['evento'], $_GET['dt_inicial'], $_GET['dt_final']);
	$result = executeSQL($conn, $strSql, $params);
	
	$query = "select
					sum(l.qtdbilhete) qtd,
					sum(l.valpagto) val
				from tablancamento l
				inner join tabapresentacao a on l.codapresentacao = a.codapresentacao
				where a.codpeca = ?
					and l.datvenda between convert(datetime, ? + ' 00:00:00', 103) and convert(datetime, ? + ' 23:59:59', 103)";
	$rs = executeSQL($conn, $query, $params, true);
	$total['TOTAL_PEDIDO'] = $rs['val'];
	$total['QUANTIDADE'] = $rs['qtd'];
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
		var data1 = $('#dt_inicial').val().split('/'),
			data2 = $('#dt_final').val().split('/');
		
		data1 = Number(data1[2] + data1[1] + data1[0]);
		data2 = Number(data2[2] + data2[1] + data2[0]);
		
		if (data1 > data2) {
			$.dialog({title:'Alerta...', text:'A data inicial não pode ser maior que a final.'});
			return false;
		}

		if (($('#local').val() == '' && $('#evento').val() == '')
		    ||
		    ($('#local').val() == '' && $('#evento').val() != '')) {
			$.dialog({title:'Alerta...', text:'Você deve selecionar um local e um evento antes de continuar.'});
			return false;
		}

		if ($('#evento').val() == '') {
		    document.location = '?p=' + pagina.replace('.php', '') +
							'&dt_inicial=' + $("#dt_inicial").val() +
							'&dt_final='+ $("#dt_final").val() +
							'&local='+ $("#local").val() +
							'&evento='+ $("#evento").val() +
							'&eventoNome='+ $("#evento option:selected").text();
		} else {
		    document.location = 'esperaProcesso.php?redirect=' + escape('./?p=' + pagina.replace('.php', '') +
							'&dt_inicial=' + $("#dt_inicial").val() +
							'&dt_final='+ $("#dt_final").val() +
							'&local='+ $("#local").val() +
							'&evento='+ $("#evento").val() +
							'&eventoNome='+ $("#evento option:selected").text());
		}
	});
	
	$('#local').change(function() {
	    if ($('#evento').val() != '') {
		$('#evento').val('');
	    }
	    $("#btnRelatorio").click();
	});

	$('#evento').change(function() {
	    if ($('#local').val() != '') {
		$("#btnRelatorio").click();
	    }
	});
	
	$('.excell').click(function(e) {
		e.preventDefault();
		
		document.location = 'xls<?php echo ucfirst($pagina); ?>?' + $.serializeUrlVars();
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
<h2>Relatório Canais de Venda (Por Data de Venda)</h2>

<p style="width:1000px;">Data Inicial da Venda <input type="text" value="<?php echo (isset($_GET["dt_inicial"])) ? $_GET["dt_inicial"] : date("d/m/Y") ?>" class="datepicker" id="dt_inicial" name="dt_inicial" />
&nbsp;&nbsp;Data Final da Venda <input type="text" class="datepicker" value="<?php echo (isset($_GET["dt_final"])) ? $_GET["dt_final"] : date("d/m/Y") ?>" id="dt_final" name="dt_final" />
&nbsp;&nbsp;<?php echo comboTeatroPorUsuario('local', $_SESSION['admin'], $_GET['local']); ?>
&nbsp;&nbsp;<?php echo comboEventoPorUsuario('evento', $_GET['local'], $_SESSION['admin'], $_GET['evento']); ?>
&nbsp;&nbsp;<input type="submit" class="button" id="btnRelatorio" value="Buscar" />
<?php if(isset($result) && hasRows($result)) { ?>
&nbsp;&nbsp;<a class="button excell" href="#">Exportar Excel</a>
<?php } ?>
</p>

<!-- Tabela de pedidos -->
<table class="ui-widget ui-widget-content" id="tabPedidos">
	<thead>
		<tr class="ui-widget-header">
			<th>Canal de venda</th>
            <th>Nome do Ponto de Venda (Descrição)</th>
			<th>Operador</th>
			<th>Tipo de Ingresso</th>
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
				$lastPonto = '';
				$somaTotalPonto = 0;
				$somaQuantPonto = 0;
				while($rs = fetchResult($result)) {
					if ($lastLocal != $rs['ds_canal_venda'] and $lastLocal != '') {
						?>
						<tr class="total">
							<td colspan="4" class="number">Sub-Total (ponto)</td>
							<td class="number"><?php echo $somaQuantPonto; ?></td>
							<td class="number"><?php echo number_format($somaTotalPonto, 2, ',', '.'); ?></td>
						</tr>
						<tr class="total">
							<td colspan="4" class="number">Sub-Total (canal)</td>
							<td class="number"><?php echo $somaQuant; ?></td>
							<td class="number"><?php echo number_format($somaTotal, 2, ',', '.'); ?></td>
						</tr>
						<?php
						$lastLocal = $rs['ds_canal_venda'];
						$somaTotal = $somaTotalPonto = $rs['val'];
						$somaQuant = $somaQuantPonto = $rs['qtd'];
						$lastPonto =  $rs['descrcaixa'];
						?>
						<tr>
							<td><?php echo utf8_encode($rs['ds_canal_venda']); ?></td>
							<td><?php echo utf8_encode($rs['descrcaixa']); ?></td>
							<td><?php echo utf8_encode($rs['nomusuario']); ?></td>
							<td><?php echo utf8_encode($rs['tipbilhete']); ?></td>
							<td class="number"><?php echo $rs['qtd']; ?></td>
							<td class="number"><?php echo number_format($rs['val'], 2, ',', '.'); ?></td>
						</tr>
						<?php
					} elseif ($lastLocal != $rs['ds_canal_venda']) {
						?>
						<tr>
							<td><?php echo utf8_encode($rs['ds_canal_venda']); ?></td>
							<td><?php echo utf8_encode($rs['descrcaixa']); ?></td>
							<td><?php echo utf8_encode($rs['nomusuario']); ?></td>
							<td><?php echo utf8_encode($rs['tipbilhete']); ?></td>
							<td class="number"><?php echo $rs['qtd']; ?></td>
							<td class="number"><?php echo number_format($rs['val'], 2, ',', '.'); ?></td>
						</tr>
						<?php
						$lastLocal = $rs['ds_canal_venda'];
						$somaTotal += $somaTotalPonto = $rs['val'];
						$somaQuant += $somaQuantPonto = $rs['qtd'];
						$lastPonto =  $rs['descrcaixa'];
					} else {
						if ($lastPonto != $rs['descrcaixa']) {
						?>
							<tr class="total">
								<td colspan="4" class="number">Sub-Total (ponto)</td>
								<td class="number"><?php echo $somaQuantPonto; ?></td>
								<td class="number"><?php echo number_format($somaTotalPonto, 2, ',', '.'); ?></td>
							</tr>
						<?php
							$somaTotalPonto = $rs['val'];
							$somaQuantPonto = $rs['qtd'];
							$lastPonto =  $rs['descrcaixa'];
						} else {
							$rs['descrcaixa'] = '&nbsp;';
							$somaTotalPonto += $rs['val'];
							$somaQuantPonto += $rs['qtd'];
						}
						?>
						<tr>
							<td>&nbsp;</td>
							<td><?php echo utf8_encode($rs['descrcaixa']); ?></td>
							<td><?php echo utf8_encode($rs['nomusuario']); ?></td>
							<td><?php echo utf8_encode($rs['tipbilhete']); ?></td>
							<td class="number"><?php echo $rs['qtd']; ?></td>
							<td class="number"><?php echo number_format($rs['val'], 2, ',', '.'); ?></td>
						</tr>
						<?php
						$somaTotal += $rs['val'];
						$somaQuant += $rs['qtd'];
					}
				}
		?>
		<tr class="total">
			<td colspan="4" class="number">Sub-Total (ponto)</td>
			<td class="number"><?php echo $somaQuantPonto; ?></td>
			<td class="number"><?php echo number_format($somaTotalPonto, 2, ',', '.'); ?></td>
		</tr>
		<tr class="total">
			<td colspan="4" class="number">Sub-Total (canal)</td>
			<td class="number"><?php echo $somaQuant; ?></td>
			<td class="number"><?php echo number_format($somaTotal, 2, ',', '.'); ?></td>
		</tr>
		<tr class="total">
			<td colspan="4" class="number">Total geral</td>
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