<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 33, true)) {

    require_once('../settings/Paginator.php');

    $pagina = basename(__FILE__);

    if (isset($_GET["dt_inicial"]) && isset($_GET["dt_final"]) && isset($_GET["local"]) && isset($_GET["evento"])
	    and acessoPermitidoEvento($_GET["local"], $_SESSION['admin'], $_GET["evento"])) {

	$conn = getConnection($_GET["local"]);

	//relatório
	$strSql = " declare @CodTipBilhete	int,
			    @TipBilhete		varchar(20),
			    @DatMovimento	datetime,
			    @NomSetor		varchar(20),
			    @Indice			int,
			    @Preco			money,
			    @VlrAgregados	money,
			    @OUTROSVALORES	money,
			    @ds_canal_venda	varchar(20),
			    @DtIniApr		varchar(8),
			    @DtFimApr		varchar(8),
			    @codPeca		int,
                            @codApresentacao    int

	    set @codPeca = ?
	    set @DtIniApr = ?
	    set @DtFimApr = ?

	    set nocount on

	    SELECT
		    tabLugSala.CodTipBilhete,
		    tabTipBilhete.TipBilhete,
		    tabLancamento.DatMovimento,
		    tabSetor.NomSetor,
		    tabforpagamento.tipcaixa,
		    tabLugSala.Indice,
                    tabLugSala.CodApresentacao,
		    tabLancamento.ValPagto as Preco2,
		    tabLancamento.ValPagto as Preco,
		    ci_middleway..mw_canal_venda.ds_canal_venda,
		    sum(isnull(tabIngressoAgregados.valor,0))  as VlrAgregados,
		    0 AS OUTROSVALORES,
		    1 AS CONTABILIZAR

	    INTO #TMP_RESUMO
	    FROM
		    tabLugSala
		    INNER JOIN
		    tabTipBilhete
			    ON  tabLugSala.CodTipBilhete 	    = tabTipBilhete.CodTipBilhete
		    INNER JOIN
		    tabSalDetalhe
			    ON  tabLugSala.Indice 		    = tabSalDetalhe.Indice
		    INNER JOIN
		    tabSetor
			    ON  tabSalDetalhe.CodSala           = tabSetor.CodSala
			    AND tabSalDetalhe.CodSetor 	    = tabSetor.CodSetor
		    INNER JOIN
		    tabApresentacao
			    ON  tabLugSala.CodApresentacao      = tabApresentacao.CodApresentacao
		    INNER JOIN
			    tabSala
			    ON tabApresentacao.CodSala		   = tabSala.CodSala
		    INNER JOIN
		    tabLancamento
			    ON  tabTipBilhete.CodTipBilhete     = tabLancamento.CodTipBilhete
			    AND tabSalDetalhe.Indice            = tabLancamento.Indice
			    AND tabApresentacao.CodApresentacao = tabLancamento.CodApresentacao
			    AND tabLancamento.CodTipLancamento  = 1
		    INNER JOIN
		    tabforpagamento
			    ON tabforpagamento.CodForPagto = tabLancamento.CodForPagto
		    LEFT JOIN
		    tabIngressoAgregados
			    ON  tabIngressoAgregados.codvenda   = tabLugSala.codvenda
			    and tabIngressoAgregados.indice     = tabLugSala.indice
		    INNER JOIN
			    tabCaixa
				    ON	tabLancamento.codCaixa	   = tabCaixa.codCaixa
		    LEFT JOIN
		    ci_middleway..mw_canal_venda
			    ON ci_middleway..mw_canal_venda.id_canal_venda = tabCaixa.id_canal_venda
		    INNER JOIN tabUsuario ON tabLancamento.codUsuario = tabUsuario.codUsuario
	    WHERE
		    (tabLugSala.CodVenda IS NOT NULL)
	    AND 	(convert(varchar(8), tabLancamento.DatVenda,112) between @DtIniApr and @DtFimApr)
	    and	(tabApresentacao.codpeca = convert(varchar(6),@codPeca) or convert(varchar(6),@codPeca) is null)
	    AND	not exists (Select 1 from tabLancamento bb
				    where tabLancamento.numlancamento = bb.numlancamento
				      and tabLancamento.codtipbilhete = bb.codtipbilhete
				      and bb.codtiplancamento = 2
				      and tabLancamento.codapresentacao = bb.codapresentacao
				      and tabLancamento.indice          = bb.indice)
	    and tabLancamento.ValPagto > 0
	    GROUP BY
		    tabLugSala.CodTipBilhete,
		    tabTipBilhete.TipBilhete,
		    tabLancamento.DatMovimento,
		    tabSetor.NomSetor,
		    tabLugSala.Indice,
                    tabLugSala.CodApresentacao,
		    tabLancamento.ValPagto,
		    tabforpagamento.tipcaixa,
		    ci_middleway..mw_canal_venda.ds_canal_venda
	
	
	INSERT INTO #TMP_RESUMO
	SELECT
		    tabLugSala.CodTipBilheteComplMeia as CodTipBilhete,
		    tabTipBilhete.TipBilhete,
		    tabLancamento.DatMovimento,
		    tabSetor.NomSetor,
		    tabforpagamento.tipcaixa,
		    tabLugSala.Indice,
                    tabLugSala.CodApresentacao,
		    tabLancamento.ValPagto as Preco2,
		    tabLancamento.ValPagto as Preco,
		    ci_middleway..mw_canal_venda.ds_canal_venda,
		    sum(isnull(tabIngressoAgregados.valor,0))  as VlrAgregados,
		    0 AS OUTROSVALORES,
		    0 AS CONTABILIZAR
	    FROM
		    tabLugSala
		    INNER JOIN
		    tabTipBilhete
			    ON  tabLugSala.CodTipBilheteComplMeia 	    = tabTipBilhete.CodTipBilhete
		    INNER JOIN
		    tabSalDetalhe
			    ON  tabLugSala.Indice 		    = tabSalDetalhe.Indice
		    INNER JOIN
		    tabSetor
			    ON  tabSalDetalhe.CodSala           = tabSetor.CodSala
			    AND tabSalDetalhe.CodSetor 	    = tabSetor.CodSetor
		    INNER JOIN
		    tabApresentacao
			    ON  tabLugSala.CodApresentacao      = tabApresentacao.CodApresentacao
		    INNER JOIN
			    tabSala
			    ON tabApresentacao.CodSala		   = tabSala.CodSala
		    INNER JOIN
		    tabLancamento
			    ON  tabTipBilhete.CodTipBilhete     = tabLancamento.CodTipBilhete
			    AND tabSalDetalhe.Indice            = tabLancamento.Indice
			    AND tabApresentacao.CodApresentacao = tabLancamento.CodApresentacao
			    AND tabLancamento.CodTipLancamento  = 4
		    INNER JOIN
		    tabforpagamento
			    ON tabforpagamento.CodForPagto = tabLancamento.CodForPagto
		    LEFT JOIN
		    tabIngressoAgregados
			    ON  tabIngressoAgregados.codvenda   = tabLugSala.codvenda
			    and tabIngressoAgregados.indice     = tabLugSala.indice
		    INNER JOIN
			    tabCaixa
				    ON	tabLancamento.codCaixa	   = tabCaixa.codCaixa
		    LEFT JOIN
		    ci_middleway..mw_canal_venda
			    ON ci_middleway..mw_canal_venda.id_canal_venda = tabCaixa.id_canal_venda
		    INNER JOIN tabUsuario ON tabLancamento.codUsuario = tabUsuario.codUsuario
	    WHERE
		    (tabLugSala.CodVenda IS NOT NULL)
	    AND 	(convert(varchar(8), tabLancamento.DatVenda,112) between @DtIniApr and @DtFimApr)
	    and	(tabApresentacao.codpeca = convert(varchar(6),@codPeca) or convert(varchar(6),@codPeca) is null)
	    AND	not exists (Select 1 from tabLancamento bb
				    where tabLancamento.numlancamento = bb.numlancamento
				      and tabLancamento.codtipbilhete = bb.codtipbilhete
				      and bb.codtiplancamento = 2
				      and tabLancamento.codapresentacao = bb.codapresentacao
				      and tabLancamento.indice          = bb.indice)
	    and tabLancamento.ValPagto > 0
	    GROUP BY
		    tabLugSala.CodTipBilheteComplMeia,
		    tabTipBilhete.TipBilhete,
		    tabLancamento.DatMovimento,
		    tabSetor.NomSetor,
		    tabLugSala.Indice,
                    tabLugSala.CodApresentacao,
		    tabLancamento.ValPagto,
		    tabforpagamento.tipcaixa,
		    ci_middleway..mw_canal_venda.ds_canal_venda


	    declare C1 cursor for
		    SELECT
			    CodTipBilhete,
			    TipBilhete,
			    DatMovimento,
			    NomSetor,
			    Indice,
                            CodApresentacao,
			    Preco,
			    VlrAgregados,
			    OUTROSVALORES,
			    ds_canal_venda

		    from #TMP_RESUMO


	    open C1

	    fetch next from C1 into
		    @CodTipBilhete,
		    @TipBilhete,
		    @DatMovimento,
		    @NomSetor,
		    @Indice,
                    @CodApresentacao,
		    @Preco,
		    @VlrAgregados,
		    @OUTROSVALORES,
		    @ds_canal_venda


	    while @@fetch_Status = 0
	    BEGIN
		    Select
			    @OutrosValores = (@Preco - @VlrAgregados) * case TTLB.icdebcre when 'D' then (isnull(TTBTL.valor,0)/100) else (isnull(TTBTL.valor,0)/100) * -1 end
		    FROM
			    tabTipBilhTipLcto	TTBTL
		    INNER JOIN
			    tabTipLanctoBilh	TTLB
			    ON  TTLB.codtiplct  = TTBTL.codtiplct
			    and TTLB.icpercvlr  = 'P'
			    and TTLB.icusolcto != 'C'
			    and TTLB.inativo    = 'A'
		    WHERE
			    TTBTL.codtipbilhete = @codtipbilhete
		    and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig)
					     from tabTipBilhTipLcto  TTBTL1,
						  tabTipLanctoBilh   TTLB1
					    where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
					      and TTBTL1.codtiplct     = TTBTL.codtiplct
					      and TTBTL1.dtinivig     <= @DatMovimento
					      and TTBTL1.inativo       = 'A'
					      and TTLB1.codtiplct     = TTBTL1.codtiplct
					      and TTLB1.IcPercVlr     = 'P'
					      and TTLB1.icusolcto    != 'C'
					      and TTLB1.inativo       = 'A')
		    and 	TTBTL.inativo        = 'A'


		    Select
			    @OutrosValores = @OutrosValores + (case TTLB.icdebcre when 'D' then isnull(TTBTL.valor,0) else isnull(TTBTL.valor,0) * -1 end)
		    FROM
			    tabTipBilhTipLcto	TTBTL
		    INNER JOIN
			    tabTipLanctoBilh	TTLB
			    ON  TTLB.codtiplct  = TTBTL.codtiplct
			    and TTLB.icpercvlr  = 'V'
			    and TTLB.icusolcto != 'C'
			    and TTLB.inativo    = 'A'
		    WHERE
			    TTBTL.codtipbilhete = @codtipbilhete
		    and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig)
					     from tabTipBilhTipLcto  TTBTL1,
						  tabTipLanctoBilh   TTLB1
					    where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
					      and TTBTL1.codtiplct     = TTBTL.codtiplct
					      and TTBTL1.dtinivig     <= @DatMovimento
					      and TTBTL1.inativo       = 'A'
					      and TTLB1.codtiplct     = TTBTL1.codtiplct
					      and TTLB1.IcPercVlr     = 'V'
					      and TTLB1.icusolcto    != 'C'
					      and TTLB1.inativo       = 'A')
		    and 	TTBTL.inativo        = 'A'


		    Update #TMP_RESUMO
		    Set	Preco = @Preco - @VlrAgregados + @OutrosValores
		    ,	OutrosValores = @OutrosValores

		    where	Indice = @Indice and
                                CodApresentacao = @CodApresentacao


		    fetch next from C1 into
			    @CodTipBilhete,
			    @TipBilhete,
			    @DatMovimento,
			    @NomSetor,
			    @Indice,
                            @CodApresentacao,
			    @Preco,
			    @VlrAgregados,
			    @OUTROSVALORES,
			    @ds_canal_venda
	    END

	    Close C1
	    Deallocate C1

	    Select
		    isnull(ds_canal_venda, 'Forma n&atilde;o cadastrada') ds_canal_venda,
		    tipbilhete,
		    count(1) as qtd,
		    sum(preco) as val,
		    contabilizar
	    from
		    #TMP_RESUMO
	    group by
		    isnull(ds_canal_venda, 'Forma n&atilde;o cadastrada'),
		    tipbilhete, contabilizar
	    order by ds_canal_venda, tipbilhete, qtd, val

	    DROP TABLE #TMP_RESUMO";

	$dtInicial = explode('/', $_GET['dt_inicial']);
	$dtInicial = $dtInicial[2] . $dtInicial[1] . $dtInicial[0];
	$dtFinal = explode('/', $_GET['dt_final']);
	$dtFinal = $dtFinal[2] . $dtFinal[1] . $dtFinal[0];

	$params = array($_GET['evento'], $dtInicial, $dtFinal);
	$result = executeSQL($conn, $strSql, $params);
    }
?>
    <script type="text/javascript" src="../javascripts/jquery.ui.datepicker-pt-BR.js"></script>
    <script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
    <script>
        $(function() {
    	var pagina = '<?php echo $pagina; ?>'
    	$('.button').button();
    	//$(".datepicker").datepicker();
    	$('input.datepicker').datepicker({
    	    changeMonth: true,
    	    changeYear: true,
    	    onSelect: function(date, e) {
    		if ($(this).is('#dt_inicial')) {
    		    $('#dt_final').datepicker('option', 'minDate', $(this).datepicker('getDate'));
    		}
    	    }
    	}).datepicker('option', $.datepicker.regional['pt-BR']);
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
    <h2>Relatório Canais de Venda (Por Data de Venda) Resumido</h2>

    <p style="width:1000px;">Data Inicial da Venda <input type="text" value="<?php echo (isset($_GET["dt_inicial"])) ? $_GET["dt_inicial"] : date("d/m/Y") ?>" class="datepicker" id="dt_inicial" name="dt_inicial" />
        &nbsp;&nbsp;Data Final da Venda <input type="text" class="datepicker" value="<?php echo (isset($_GET["dt_final"])) ? $_GET["dt_final"] : date("d/m/Y") ?>" id="dt_final" name="dt_final" />
        &nbsp;&nbsp;<?php echo comboTeatroPorUsuario('local', $_SESSION['admin'], $_GET['local']); ?>
        &nbsp;&nbsp;<?php echo comboEventoPorUsuario('evento', $_GET['local'], $_SESSION['admin'], $_GET['evento']); ?>
        &nbsp;&nbsp;<input type="submit" class="button" id="btnRelatorio" value="Buscar" />
    <?php if (isset($result) && hasRows($result)) {
    ?>
        &nbsp;&nbsp;<a class="button excell" href="#">Exportar Excel</a>
    <?php } ?>
</p>

<!-- Tabela de pedidos -->
<table class="ui-widget ui-widget-content" id="tabPedidos">
    <thead>
	<tr class="ui-widget-header">
	    <th>Canal de venda</th>
	    <th>Tipo de Ingresso</th>
	    <th>Quantidade de Ingressos</th>
	    <th>Total das Vendas</th>
	</tr>
    </thead>
    <tbody>
	<?php
	if (isset($result)) {
	    $lastLocal = '';
	    $somaTotal = 0;
	    $somaQuant = 0;
	    $somaTotalCanal = 0;
	    $somaQuantCanal = 0;
	    while ($rs = fetchResult($result)) {
		$somaTotal += $rs['val'];
		$somaQuant += $rs['contabilizar'] ? $rs['qtd'] : 0;
		if ($lastLocal != $rs['ds_canal_venda'] and $lastLocal != '') {
	?>
	    	<tr class="total">
	    	    <td colspan="2" class="number">Sub-Total (canal)</td>
	    	    <td class="number"><?php echo $somaQuantCanal; ?></td>
	    	    <td class="number"><?php echo number_format($somaTotalCanal, 2, ',', '.'); ?></td>
	    	</tr>
	<?php
		    $lastLocal = $rs['ds_canal_venda'];
		    $somaTotalCanal = $rs['val'];
		    $somaQuantCanal = $rs['contabilizar'] ? $rs['qtd'] : 0;
	?>
	    	<tr>
	    	    <td><?php echo utf8_encode($rs['ds_canal_venda']); ?></td>
	    	    <td><?php echo utf8_encode($rs['tipbilhete']); ?></td>
	    	    <td class="number"><?php echo $rs['qtd']; ?></td>
	    	    <td class="number"><?php echo number_format($rs['val'], 2, ',', '.'); ?></td>
	    	</tr>
	<?php
		} elseif ($lastLocal != $rs['ds_canal_venda']) {
	?>
	    	<tr>
	    	    <td><?php echo utf8_encode($rs['ds_canal_venda']); ?></td>
	    	    <td><?php echo utf8_encode($rs['tipbilhete']); ?></td>
	    	    <td class="number"><?php echo $rs['qtd']; ?></td>
	    	    <td class="number"><?php echo number_format($rs['val'], 2, ',', '.'); ?></td>
	    	</tr>
	<?php
		    $lastLocal = $rs['ds_canal_venda'];
		    $somaTotalCanal += $rs['val'];
		    $somaQuantCanal += $rs['contabilizar'] ? $rs['qtd'] : 0;
		} else {
	?>
	    	<tr>
	    	    <td>&nbsp;</td>
	    	    <td><?php echo utf8_encode($rs['tipbilhete']); ?></td>
	    	    <td class="number"><?php echo $rs['qtd']; ?></td>
	    	    <td class="number"><?php echo number_format($rs['val'], 2, ',', '.'); ?></td>
	    	</tr>
	<?php
		    $somaTotalCanal += $rs['val'];
		    $somaQuantCanal += $rs['contabilizar'] ? $rs['qtd'] : 0;
		}
	    }
	?>
    	<tr class="total">
    	    <td colspan="2" class="number">Sub-Total (canal)</td>
    	    <td class="number"><?php echo $somaQuantCanal; ?></td>
    	    <td class="number"><?php echo number_format($somaTotalCanal, 2, ',', '.'); ?></td>
    	</tr>
    	<tr class="total">
    	    <td colspan="2" class="number">Total geral</td>
    	    <td class="number"><?php echo $somaQuant; ?></td>
    	    <td class="number"><?php echo number_format($somaTotal, 2, ',', '.'); ?></td>
    	</tr>
	<?php
	}
	?>
    </tbody>
</table>
<p style="text-align:right;">Importante: A qtde. de ingressos de "Complemento de Meia Entrada" não foram somados aos Totais de "Qtde. de Ingressos".</p>
<?php
    }
?>