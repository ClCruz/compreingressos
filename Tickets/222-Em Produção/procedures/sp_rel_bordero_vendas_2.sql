/*
continuacao da procedure SP_REL_BORDERO_VENDAS
aparentemente o numero maximo de opcoes é 14
*/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER  PROCEDURE [dbo].[SP_REL_BORDERO_VENDAS_2]
(
	@CodPeca   int, 
	@CodApresentacao int, 
	@DatApresentacao varchar(10), 
	@DataBase varchar(30)
) 
AS

--declare @CodPeca int
--declare @CodApresentacao int
--declare @DatApresentacao varchar(10)
--declare @DataBase varchar(30)

--set @CodPeca =4
--set @CodApresentacao = 119
--set @DatApresentacao = '20031205'
--set @DataBase = 'TSP_TESTES'



/*
https://portal.cc.com.br:8084/projetos/ticket/191

colunas VlMinimoDebBordero e Valor as ValorReal adicionadas 
as colunas de retorno devido a um problema com os calculos de
limite com multiplas apresentacoes representando uma mesma apresentacao, por exemplo:

0- O TOTAL DE VENDAS BRUTO foi de R$ 10.800,00.
 1- Foi criado uma apresentação em duas salas (do mesmo dia e hora), ou
 seja, foi gerado duas apresentações.
 2- Criou o valor mínimo do débito do borderô de R$ 750,00 no módulo
 Administrativo (só que na descrição do débito informada que o mínimo é de
 R$ 1.500,00).
 3- Em uma sala (apresentação) o valor da venda apurado foi de R$ 700,00,
 sendo assim o sistema assumiu o valor mínimo cadastrado de R$ 750,00.
 4- Na outra sala (apresentação) o valor apurado foi de R$ 1.460,00,
 superando o mínimo de R$ 750,00, portanto assumindo o R$ 1.460,00.
 5- Desta forma o sistema somou R$ 1.460,00 + R$ 750,00 = R$ 2.210,00.

 O sistema deverá calcular o valor do mínimo a ser cobrado do teatro depois
 que somar os valores das vendas de cada sala, ou seja, se o valor total
 das salas superar o mínimo deverá ser calculado o percentual cadastrado no
 sistema.
 No exemplo acima, o valor que deveria ser cobrado seria de R$ 2.160,00
 (1460,00+700,00) e não R$ 2.210,00 (1460,00+750,00).
*/



set nocount on

declare @query varchar(8000)
set @query =

'DECLARE @TotVenda 	money,
	@ItensVendidos  smallint

SET NOCOUNT ON

 	execute ' + @DataBase + '..sp_sel_vlrtotal_bordero ' + convert(varchar(10), @CodApresentacao) + ', @TotVenda output

	SELECT  @ItensVendidos = isnull(COUNT(1),0)
	FROM 
		' + @DataBase + '..tabLancamento 
	WHERE 
		CodTipLancamento = 1
	AND 	CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + '

	SELECT  @ItensVendidos = @ItensVendidos - isnull(COUNT(1),0)
	FROM 
		' + @DataBase + '..tabLancamento 
	WHERE 
		CodTipLancamento = 2
	AND 	CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + '

	SELECT	
		' + @DataBase + '..tabTipDebBordero.CodTipDebBordero,
		' + @DataBase + '..tabTipDebBordero.DebBordero,
		isnull(' + @DataBase + '..tabTipDebBordero.VlMinimo,0) as VlMinimoDebBordero, 
		Round(' + @DataBase + '..tabTipDebBordero.PerDesconto,2) as PerDesconto,
		COALESCE (ROUND(' + @DataBase + '..tabTipDebBordero.PerDesconto * @TotVenda / 100,2), 0)  AS Valor,
		' + @DataBase + '..tabTipDebBordero.TipValor
	INTO #TEMP1 FROM ' + @DataBase + '..tabDebBordero 
  			INNER JOIN ' + @DataBase + '..tabTipDebBordero ON ' + @DataBase + '..tabDebBordero.CodTipDebBordero = ' + @DataBase + '..tabTipDebBordero.CodTipDebBordero
  		WHERE (convert(varchar(10),''' + @DatApresentacao + ''',112) BETWEEN ' + @DataBase + '..tabDebBordero.DatIniDebito AND ' + @DataBase + '..tabDebBordero.DatFinDebito) AND (' + @DataBase + '..tabDebBordero.CodPeca = ' + convert(varchar(10), @CodPeca) + ') AND ' + @DataBase + '..tabTipDebBordero.TipValor = ''P'' AND ' + @DataBase + '..tabTipDebBordero.Ativo = ''A''
  		AND ' + @DataBase + '..tabTipDebBordero.StaDebBorderoLiq = ''A''
 	GROUP BY	' + @DataBase + '..tabTipDebBordero.CodTipDebBordero,
			' + @DataBase + '..tabTipDebBordero.DebBordero,
			isnull(' + @DataBase + '..tabTipDebBordero.VlMinimo,0),
			' + @DataBase + '..tabTipDebBordero.PerDesconto,
			COALESCE (' + @DataBase + '..tabTipDebBordero.PerDesconto * @TotVenda / 100, 0),
			' + @DataBase + '..tabTipDebBordero.TipValor

 	SELECT	' + @DataBase + '..tabTipDebBordero.CodTipDebBordero,
		' + @DataBase + '..tabTipDebBordero.DebBordero,
		isnull(' + @DataBase + '..tabTipDebBordero.VlMinimo,0) as VlMinimoDebBordero, 
		Round(' + @DataBase + '..tabTipDebBordero.PerDesconto,2) as PerDesconto,
		COALESCE (ROUND (' + @DataBase + '..tabTipDebBordero.PerDesconto * @ItensVendidos,2), 0)  AS Valor,
		' + @DataBase + '..tabTipDebBordero.TipValor INTO #TEMP2
 		FROM ' + @DataBase + '..tabDebBordero 
  			INNER JOIN ' + @DataBase + '..tabTipDebBordero ON ' + @DataBase + '..tabDebBordero.CodTipDebBordero = ' + @DataBase + '..tabTipDebBordero.CodTipDebBordero
  		WHERE (convert(varchar(10),''' + @DatApresentacao + ''',112) BETWEEN ' + @DataBase + '..tabDebBordero.DatIniDebito AND ' + @DataBase + '..tabDebBordero.DatFinDebito) AND (' + @DataBase + '..tabDebBordero.CodPeca = ' + convert(varchar(10), @CodPeca) + ') AND (' + @DataBase + '..tabTipDebBordero.TipValor = ''V'' ) AND ' + @DataBase + '..tabTipDebBordero.Ativo = ''A''
  		AND ' + @DataBase + '..tabTipDebBordero.StaDebBorderoLiq = ''A''
 	GROUP BY	' + @DataBase + '..tabTipDebBordero.CodTipDebBordero,
			' + @DataBase + '..tabTipDebBordero.DebBordero,
			isnull(' + @DataBase + '..tabTipDebBordero.VlMinimo,0),
			' + @DataBase + '..tabTipDebBordero.PerDesconto,
			COALESCE (' + @DataBase + '..tabTipDebBordero.PerDesconto * @ItensVendidos, 0),
			' + @DataBase + '..tabTipDebBordero.TipValor

	SELECT	' + @DataBase + '..tabTipDebBordero.CodTipDebBordero,
		' + @DataBase + '..tabTipDebBordero.DebBordero,
		isnull(' + @DataBase + '..tabTipDebBordero.VlMinimo,0) as VlMinimoDebBordero, 
		Round(' + @DataBase + '..tabTipDebBordero.PerDesconto,2) as PerDesconto,
		' + @DataBase + '..tabTipDebBordero.PerDesconto as valor,
		' + @DataBase + '..tabTipDebBordero.TipValor INTO #TEMP3
 		FROM ' + @DataBase + '..tabDebBordero 
  			INNER JOIN ' + @DataBase + '..tabTipDebBordero ON ' + @DataBase + '..tabDebBordero.CodTipDebBordero = ' + @DataBase + '..tabTipDebBordero.CodTipDebBordero
  		WHERE (convert(varchar(10),''' + @DatApresentacao + ''',112) BETWEEN ' + @DataBase + '..tabDebBordero.DatIniDebito AND ' + @DataBase + '..tabDebBordero.DatFinDebito) AND (' + @DataBase + '..tabDebBordero.CodPeca = ' + convert(varchar(10), @CodPeca) + ') AND (' + @DataBase + '..tabTipDebBordero.TipValor = ''F'' ) AND ' + @DataBase + '..tabTipDebBordero.Ativo = ''A''
  		AND ' + @DataBase + '..tabTipDebBordero.StaDebBorderoLiq = ''A''
 	GROUP BY	' + @DataBase + '..tabTipDebBordero.CodTipDebBordero,
			' + @DataBase + '..tabTipDebBordero.DebBordero,
			isnull(' + @DataBase + '..tabTipDebBordero.VlMinimo,0),
			' + @DataBase + '..tabTipDebBordero.PerDesconto,
			' + @DataBase + '..tabTipDebBordero.TipValor
	SET NOCOUNT OFF	
	
	SELECT	CodTipDebBordero,
	DebBordero,
	PerDesconto,
	case when VlMinimoDebBordero > valor and VlMinimoDebBordero > 0 then VlMinimoDebBordero else round(Valor,2) end as Valor,
	TipValor,
	VlMinimoDebBordero,
	Valor as ValorReal
	FROM #TEMP1 

	UNION 

	SELECT 
	CodTipDebBordero,
	DebBordero,
	PerDesconto,
	case when VlMinimoDebBordero > valor and VlMinimoDebBordero > 0 then VlMinimoDebBordero else round(Valor,2) end as Valor,
	TipValor,
	VlMinimoDebBordero,
	Valor as ValorReal
	FROM #TEMP2 

	UNION 

	SELECT CodTipDebBordero,
	DebBordero,
	PerDesconto,
	case when VlMinimoDebBordero > valor and VlMinimoDebBordero > 0 then VlMinimoDebBordero else round(Valor,2) end as Valor,
	TipValor,
	VlMinimoDebBordero,
	Valor as ValorReal
	FROM #TEMP3
	order by DebBordero'
--print @query
exec (@query)







GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS_2];2    Script Date: 02/05/2013 11:16:48 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
/* EQUIVALENTE AO ;5 (SOMENTE PARA TODOS OS SETORES) */
ALTER    PROCEDURE [dbo].[SP_REL_BORDERO_VENDAS_2];2
	@DtIniApr varchar(8), 
	@DtFimApr varchar(8), 
	@codPeca int = null,
	@codSala varchar(5) = null, 
	@horaSessao varchar(6),
	@DataBase varchar(30)/*,
	@CodApresentacao int*/
AS
/*
declare @DtIniApr varchar(8) 
declare @DtFimApr varchar(8)
declare @codPeca int
declare @hora varchar(6)
declare @DataBase varchar(30)
declare @CodApresentacao int
f
set @CodApresentacao = 120
set @DtIniApr = '20090815'
set @DtFimApr = '20090815'
set @codPeca = 3
set @hora = '21:00'
set @DataBase = 'CI_RAULCORTEZ'*/
set nocount on

declare @query varchar(8000)
declare @hora varchar(1000)

if @horaSessao = '' or @horaSessao = 'null' or @horaSessao is null
begin
	set @hora = ''
end
else
begin
	set @hora = 'and 	' + @DataBase + '..tabApresentacao.HorSessao = ''' + @horaSessao + ''''
end
set @query =
'declare @CodTipBilhete 	int,
	@TipBilhete	varchar(20),
	@DatMovimento 	datetime,
	@NomSetor	varchar(20),
	@Indice		int,
	@Preco		money,
	@VlrAgregados	money,
	@OUTROSVALORES	money,
	@codapresentacao int,
	@PrzRepasseDias int
	
	set nocount on
	BEGIN

		SELECT  
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabforpagamento.ForPagto,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLugSala.CodApresentacao,
			' + @DataBase + '..tabLancamento.ValPagto as Preco2,
			' + @DataBase + '..tabLancamento.ValPagto as Preco,
			' + @DataBase + '..tabforpagamento.PrzRepasseDias,
			sum(isnull(' + @DataBase + '..tabIngressoAgregados.valor,0))  as VlrAgregados,			
			0 AS OUTROSVALORES,
			pctxadm
			
		INTO #TMP_RESUMO
		FROM       
			' + @DataBase + '..tabLugSala 
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete 	
				ON  ' + @DataBase + '..tabLugSala.CodTipBilhete 	    = ' + @DataBase + '..tabTipBilhete.CodTipBilhete 
		        INNER JOIN 
			' + @DataBase + '..tabSalDetalhe 	
				ON  ' + @DataBase + '..tabLugSala.Indice 		    = ' + @DataBase + '..tabSalDetalhe.Indice 
			INNER JOIN
		        ' + @DataBase + '..tabSetor 	
				ON  ' + @DataBase + '..tabSalDetalhe.CodSala           = ' + @DataBase + '..tabSetor.CodSala 
				AND ' + @DataBase + '..tabSalDetalhe.CodSetor 	    = ' + @DataBase + '..tabSetor.CodSetor 
			INNER JOIN
		        ' + @DataBase + '..tabApresentacao 
				ON  ' + @DataBase + '..tabLugSala.CodApresentacao      = ' + @DataBase + '..tabApresentacao.CodApresentacao 
			INNER JOIN
				' + @DataBase + '..tabSala
				ON ' + @DataBase + '..tabApresentacao.CodSala		   = ' + @DataBase + '..tabSala.CodSala
			INNER JOIN
			' + @DataBase + '..tabLancamento 
				ON  ' + @DataBase + '..tabTipBilhete.CodTipBilhete     = ' + @DataBase + '..tabLancamento.CodTipBilhete 
				AND ' + @DataBase + '..tabSalDetalhe.Indice            = ' + @DataBase + '..tabLancamento.Indice 
				AND ' + @DataBase + '..tabApresentacao.CodApresentacao = ' + @DataBase + '..tabLancamento.CodApresentacao
				AND ' + @DataBase + '..tabLancamento.CodTipLancamento  = 1
			INNER JOIN 
			' + @DataBase + '..tabforpagamento 
				ON ' + @DataBase + '..tabforpagamento.CodForPagto = ' + @DataBase + '..tabLancamento.CodForPagto
			LEFT JOIN
			' + @DataBase + '..tabIngressoAgregados
				ON  ' + @DataBase + '..tabIngressoAgregados.codvenda   = ' + @DataBase + '..tabLugSala.codvenda
				and ' + @DataBase + '..tabIngressoAgregados.indice     = ' + @DataBase + '..tabLugSala.indice
		WHERE
			(' + @DataBase + '..tabLugSala.CodVenda IS NOT NULL) 
		AND 	(convert(varchar(8), ' + @DataBase + '..tabApresentacao.DatApresentacao,112) between ''' + @DtIniApr + ''' and ''' + @DtFimApr + ''')
		' + @hora + '
		and	(' + @DataBase + '..tabApresentacao.codpeca = ' + convert(varchar(6),@codPeca) + ' or ' + convert(varchar(6),@codPeca) + ' is null)
		AND	not exists (Select 1 from ' + @DataBase + '..tabLancamento bb
					where ' + @DataBase + '..tabLancamento.numlancamento = bb.numlancamento
					  and ' + @DataBase + '..tabLancamento.codtipbilhete = bb.codtipbilhete
					  and bb.codtiplancamento = 2
					  and ' + @DataBase + '..tabLancamento.codapresentacao = bb.codapresentacao
					  and ' + @DataBase + '..tabLancamento.indice          = bb.indice)
		AND ' + @DataBase + '..tabforpagamento.StaDebBordLiq = ''S''
		GROUP BY 
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLugSala.CodApresentacao,
			' + @DataBase + '..tabLancamento.ValPagto,
			' + @DataBase + '..tabforpagamento.PrzRepasseDias,
			' + @DataBase + '..tabforpagamento.ForPagto, pctxadm


		declare C1 cursor for
			SELECT  
				CodTipBilhete,
				TipBilhete, 
				DatMovimento,
				NomSetor,
				CodApresentacao,
				Indice,
				Preco,
				PrzRepasseDias,
				VlrAgregados,
				OUTROSVALORES
				
			from #TMP_RESUMO

		
		open C1

		fetch next from C1 into
			@CodTipBilhete,
			@TipBilhete, 
			@DatMovimento,
			@NomSetor,
			@codapresentacao,
			@Indice,
			@Preco,
			@PrzRepasseDias,
			@VlrAgregados,
			@OUTROSVALORES
				

		while @@fetch_Status = 0
		BEGIN
			Select  
				@OutrosValores = (@Preco - @VlrAgregados) * case TTLB.icdebcre when ''D'' then (isnull(TTBTL.valor,0)/100) else (isnull(TTBTL.valor,0)/100) * -1 end
			FROM
				' + @DataBase + '..tabTipBilhTipLcto	TTBTL
			INNER JOIN
				' + @DataBase + '..tabTipLanctoBilh	TTLB
				ON  TTLB.codtiplct  = TTBTL.codtiplct
				and TTLB.icpercvlr  = ''P''
				and TTLB.icusolcto != ''C''
				and TTLB.inativo    = ''A''
			WHERE
				TTBTL.codtipbilhete = @codtipbilhete
			and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig) 
						 from ' + @DataBase + '..tabTipBilhTipLcto  TTBTL1,
						      ' + @DataBase + '..tabTipLanctoBilh   TTLB1
						where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
						  and TTBTL1.codtiplct     = TTBTL.codtiplct
						  and TTBTL1.dtinivig     <= @DatMovimento
						  and TTBTL1.inativo       = ''A''
						  and TTLB1.codtiplct     = TTBTL1.codtiplct
						  and TTLB1.IcPercVlr     = ''P''
						  and TTLB1.icusolcto    != ''C''
						  and TTLB1.inativo       = ''A'')
			and 	TTBTL.inativo        = ''A''


			Select  
				@OutrosValores = @OutrosValores + (case TTLB.icdebcre when ''D'' then isnull(TTBTL.valor,0) else isnull(TTBTL.valor,0) * -1 end)
			FROM
				' + @DataBase + '..tabTipBilhTipLcto	TTBTL
			INNER JOIN
				' + @DataBase + '..tabTipLanctoBilh	TTLB
				ON  TTLB.codtiplct  = TTBTL.codtiplct
				and TTLB.icpercvlr  = ''V''
				and TTLB.icusolcto != ''C''
				and TTLB.inativo    = ''A''
			WHERE
				TTBTL.codtipbilhete = @codtipbilhete
			and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig) 
						 from ' + @DataBase + '..tabTipBilhTipLcto  TTBTL1,
						      ' + @DataBase + '..tabTipLanctoBilh   TTLB1
						where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
						  and TTBTL1.codtiplct     = TTBTL.codtiplct
						  and TTBTL1.dtinivig     <= @DatMovimento
						  and TTBTL1.inativo       = ''A''
						  and TTLB1.codtiplct     = TTBTL1.codtiplct
						  and TTLB1.IcPercVlr     = ''V''
						  and TTLB1.icusolcto    != ''C''
						  and TTLB1.inativo       = ''A'')
			and 	TTBTL.inativo        = ''A''
	
	
			Update #TMP_RESUMO
			Set	Preco = @Preco - @VlrAgregados + @OutrosValores			
			,	OutrosValores = @OutrosValores
			where	Indice = @Indice and
					CodApresentacao = @codapresentacao
				
	
			fetch next from C1 into
				@CodTipBilhete,
				@TipBilhete, 
				@DatMovimento,
				@NomSetor,
				@codapresentacao,
				@Indice,
				@Preco,
				@PrzRepasseDias,
				@VlrAgregados,
				@OUTROSVALORES
				

		END


		Close C1
		Deallocate C1
		
		Select	
			forpagto,
			count(1) as qtdBilh,
			sum(preco) as totfat,
			sum(preco) * (pctxadm/100) as descontos,
			sum(preco) - (sum(preco) * (pctxadm/100)) as liquido,
			PrzRepasseDias			
		from
			#TMP_RESUMO
		Group by
			forpagto, pctxadm, PrzRepasseDias

		DROP TABLE #TMP_RESUMO
	END'

--print @query
exec (@query)







GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS_2];3    Script Date: 02/05/2013 11:16:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER    PROCEDURE [dbo].[SP_REL_BORDERO_VENDAS_2];3
	@DtIniApr varchar(8), 
	@DtFimApr varchar(8), 
	@codPeca int = null,
	@codSala int = null, 
	@hora varchar(6),
	@DataBase varchar(30)/*,
	@CodApresentacao int*/
AS
/*
declare @DtIniApr varchar(8) 
declare @DtFimApr varchar(8)
declare @codPeca int
declare @hora varchar(6)
declare @DataBase varchar(30)
declare @CodApresentacao int

set @CodApresentacao = 120
set @DtIniApr = '20090815'
set @DtFimApr = '20090815'
set @codPeca = 3
set @hora = '21:00'
set @DataBase = 'CI_RAULCORTEZ'*/
set nocount on

declare @query varchar(8000)
set @query =
'declare @CodTipBilhete 	int,
	@TipBilhete	varchar(20),
	@DatMovimento 	datetime,
	@NomSetor	varchar(20),
	@Indice		int,
	@Preco		money,
	@VlrAgregados	money,
	@OUTROSVALORES	money,
	@PrzRepasseDias int
	
	set nocount on
	BEGIN

		SELECT  
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabforpagamento.ForPagto,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto as Preco2,
			' + @DataBase + '..tabLancamento.ValPagto as Preco,
			' + @DataBase + '..tabforpagamento.PrzRepasseDias,
			sum(isnull(' + @DataBase + '..tabIngressoAgregados.valor,0))  as VlrAgregados,			
			0 AS OUTROSVALORES,
			pctxadm
			
		INTO #TMP_RESUMO
		FROM       
			' + @DataBase + '..tabLugSala 
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete 	
				ON  ' + @DataBase + '..tabLugSala.CodTipBilhete 	    = ' + @DataBase + '..tabTipBilhete.CodTipBilhete 
		        INNER JOIN 
			' + @DataBase + '..tabSalDetalhe 	
				ON  ' + @DataBase + '..tabLugSala.Indice 		    = ' + @DataBase + '..tabSalDetalhe.Indice 
			INNER JOIN
		        ' + @DataBase + '..tabSetor 	
				ON  ' + @DataBase + '..tabSalDetalhe.CodSala           = ' + @DataBase + '..tabSetor.CodSala 
				AND ' + @DataBase + '..tabSalDetalhe.CodSetor 	    = ' + @DataBase + '..tabSetor.CodSetor 
			INNER JOIN
		        ' + @DataBase + '..tabApresentacao 
				ON  ' + @DataBase + '..tabLugSala.CodApresentacao      = ' + @DataBase + '..tabApresentacao.CodApresentacao 
			INNER JOIN
				' + @DataBase + '..tabSala
				ON ' + @DataBase + '..tabApresentacao.CodSala		   = ' + @DataBase + '..tabSala.CodSala
			INNER JOIN
			' + @DataBase + '..tabLancamento 
				ON  ' + @DataBase + '..tabTipBilhete.CodTipBilhete     = ' + @DataBase + '..tabLancamento.CodTipBilhete 
				AND ' + @DataBase + '..tabSalDetalhe.Indice            = ' + @DataBase + '..tabLancamento.Indice 
				AND ' + @DataBase + '..tabApresentacao.CodApresentacao = ' + @DataBase + '..tabLancamento.CodApresentacao
				AND ' + @DataBase + '..tabLancamento.CodTipLancamento  = 1
			INNER JOIN 
			' + @DataBase + '..tabforpagamento 
				ON ' + @DataBase + '..tabforpagamento.CodForPagto = ' + @DataBase + '..tabLancamento.CodForPagto
			LEFT JOIN
			' + @DataBase + '..tabIngressoAgregados
				ON  ' + @DataBase + '..tabIngressoAgregados.codvenda   = ' + @DataBase + '..tabLugSala.codvenda
				and ' + @DataBase + '..tabIngressoAgregados.indice     = ' + @DataBase + '..tabLugSala.indice
		WHERE
			(' + @DataBase + '..tabLugSala.CodVenda IS NOT NULL) 
		AND 	(convert(varchar(8), ' + @DataBase + '..tabApresentacao.DatApresentacao,112) between ''' + @DtIniApr + ''' and ''' + @DtFimApr + ''')
		and 	' + @DataBase + '..tabApresentacao.HorSessao = ''' + @hora + '''
		and	(' + @DataBase + '..tabApresentacao.codpeca = ' + convert(varchar(6),@codPeca) + ' or ' + convert(varchar(6),@codPeca) + ' is null)
		and	(' + @DataBase + '..tabSala.codsala = ' + convert(varchar(6),@codSala) + ' or ' + convert(varchar(6),@codSala) + ' is null)
		AND	not exists (Select 1 from ' + @DataBase + '..tabLancamento bb
					where ' + @DataBase + '..tabLancamento.numlancamento = bb.numlancamento
					  and ' + @DataBase + '..tabLancamento.codtipbilhete = bb.codtipbilhete
					  and bb.codtiplancamento = 2
					  and ' + @DataBase + '..tabLancamento.codapresentacao = bb.codapresentacao
					  and ' + @DataBase + '..tabLancamento.indice          = bb.indice)
		AND ' + @DataBase + '..tabforpagamento.StaDebBordLiq = ''S''
		GROUP BY 
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto,
			' + @DataBase + '..tabforpagamento.PrzRepasseDias,
			' + @DataBase + '..tabforpagamento.ForPagto, pctxadm


		declare C1 cursor for
			SELECT  
				CodTipBilhete,
				TipBilhete, 
				DatMovimento,
				NomSetor,
				Indice,
				Preco,
				VlrAgregados,
				PrzRepasseDias,
				OUTROSVALORES
				
			from #TMP_RESUMO

		
		open C1

		fetch next from C1 into
			@CodTipBilhete,
			@TipBilhete, 
			@DatMovimento,
			@NomSetor,
			@Indice,
			@Preco,
			@VlrAgregados,
			@PrzRepasseDias,
			@OUTROSVALORES
				

		while @@fetch_Status = 0
		BEGIN
			Select  
				@OutrosValores = (@Preco - @VlrAgregados) * case TTLB.icdebcre when ''D'' then (isnull(TTBTL.valor,0)/100) else (isnull(TTBTL.valor,0)/100) * -1 end
			FROM
				' + @DataBase + '..tabTipBilhTipLcto	TTBTL
			INNER JOIN
				' + @DataBase + '..tabTipLanctoBilh	TTLB
				ON  TTLB.codtiplct  = TTBTL.codtiplct
				and TTLB.icpercvlr  = ''P''
				and TTLB.icusolcto != ''C''
				and TTLB.inativo    = ''A''
			WHERE
				TTBTL.codtipbilhete = @codtipbilhete
			and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig) 
						 from ' + @DataBase + '..tabTipBilhTipLcto  TTBTL1,
						      ' + @DataBase + '..tabTipLanctoBilh   TTLB1
						where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
						  and TTBTL1.codtiplct     = TTBTL.codtiplct
						  and TTBTL1.dtinivig     <= @DatMovimento
						  and TTBTL1.inativo       = ''A''
						  and TTLB1.codtiplct     = TTBTL1.codtiplct
						  and TTLB1.IcPercVlr     = ''P''
						  and TTLB1.icusolcto    != ''C''
						  and TTLB1.inativo       = ''A'')
			and 	TTBTL.inativo        = ''A''


			Select  
				@OutrosValores = @OutrosValores + (case TTLB.icdebcre when ''D'' then isnull(TTBTL.valor,0) else isnull(TTBTL.valor,0) * -1 end)
			FROM
				' + @DataBase + '..tabTipBilhTipLcto	TTBTL
			INNER JOIN
				' + @DataBase + '..tabTipLanctoBilh	TTLB
				ON  TTLB.codtiplct  = TTBTL.codtiplct
				and TTLB.icpercvlr  = ''V''
				and TTLB.icusolcto != ''C''
				and TTLB.inativo    = ''A''
			WHERE
				TTBTL.codtipbilhete = @codtipbilhete
			and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig) 
						 from ' + @DataBase + '..tabTipBilhTipLcto  TTBTL1,
						      ' + @DataBase + '..tabTipLanctoBilh   TTLB1
						where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
						  and TTBTL1.codtiplct     = TTBTL.codtiplct
						  and TTBTL1.dtinivig     <= @DatMovimento
						  and TTBTL1.inativo       = ''A''
						  and TTLB1.codtiplct     = TTBTL1.codtiplct
						  and TTLB1.IcPercVlr     = ''V''
						  and TTLB1.icusolcto    != ''C''
						  and TTLB1.inativo       = ''A'')
			and 	TTBTL.inativo        = ''A''
	
	
			Update #TMP_RESUMO
			Set	Preco = @Preco - @VlrAgregados + @OutrosValores			
			,	OutrosValores = @OutrosValores
			where	Indice = @Indice
				
	
			fetch next from C1 into
				@CodTipBilhete,
				@TipBilhete, 
				@DatMovimento,
				@NomSetor,
				@Indice,
				@Preco,
				@VlrAgregados,
				@PrzRepasseDias,
				@OUTROSVALORES
				

		END


		Close C1
		Deallocate C1
		
		Select	
			forpagto,
			count(1) as qtdBilh,
			sum(preco) as totfat,
			sum(preco) * (pctxadm/100) as descontos,
			sum(preco) - (sum(preco) * (pctxadm/100)) as liquido,
			PrzRepasseDias			
		from
			#TMP_RESUMO
		Group by
			forpagto, pctxadm, PrzRepasseDias

		DROP TABLE #TMP_RESUMO
	END'

--print @query
exec (@query)







GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS_2];4    Script Date: 02/05/2013 11:16:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER  procedure [dbo].[SP_REL_BORDERO_VENDAS_2];4
 	@CodApresentacao int,
	@DataBase varchar(30)
AS

set nocount on
	
declare @query varchar(8000)
set @query =
'declare @CodTipBilhete 	int,
	@TipBilhete	varchar(20),
	@DatMovimento 	datetime,
	@NomSetor	varchar(20),
	@Indice		int,
	@Preco		money,
	@VlrAgregados	money,
	@OUTROSVALORES	money
IF EXISTS
	(SELECT 1 FROM
		' + @DataBase + '..tabLugSala 
		INNER JOIN 
		' + @DataBase + '..tabApresentacao 
			ON  ' + @DataBase + '..tabLugSala.CodApresentacao      = ' + @DataBase + '..tabApresentacao.CodApresentacao 
		WHERE   
			' + @DataBase + '..tabLugSala.CodVenda IS NOT NULL
		AND 	' + @DataBase + '..tabApresentacao.CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + ')

	BEGIN

		SELECT  
			tls.CodTipBilhete,
			ttb.TipBilhete, 
			tl.DatMovimento,
			ts.NomSetor,
			tls.Indice,
			tl.ValPagto as Preco,
			sum(isnull(tia.valor,0))  as VlrAgregados,
			0 AS OUTROSVALORES
		INTO #TMP_RESUMO
		FROM       
			' + @DataBase + '..tabLugSala tls
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete ttb 	
				ON  tls.CodTipBilhete 	    = ttb.CodTipBilhete 
		        INNER JOIN 
			' + @DataBase + '..tabSalDetalhe tsd	
				ON  tls.Indice 		    = tsd.Indice 
			INNER JOIN
		        ' + @DataBase + '..tabSetor ts
				ON  tsd.CodSala           = ts.CodSala 
				AND tsd.CodSetor 	    = ts.CodSetor 
			INNER JOIN
		        ' + @DataBase + '..tabApresentacao ta
				ON  tls.CodApresentacao      = ta.CodApresentacao 
			INNER JOIN
			' + @DataBase + '..tabLancamento tl
				ON  ttb.CodTipBilhete     = tl.CodTipBilhete 
				AND tsd.Indice            = tl.Indice 
				AND ta.CodApresentacao = tl.CodApresentacao
				AND tl.CodTipLancamento  = 1
			LEFT JOIN
			' + @DataBase + '..tabIngressoAgregados tia
				ON  tia.codvenda   = tls.codvenda
				and tia.indice     = tls.indice
		WHERE
			(tls.CodVenda IS NOT NULL) 
		AND 	(ta.CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + ')
		AND	not exists (Select 1 from ' + @DataBase + '..tabLancamento bb
					where tl.numlancamento = bb.numlancamento
					  and tl.codtipbilhete = bb.codtipbilhete
					  and bb.codtiplancamento = 2
					  and tl.codapresentacao = bb.codapresentacao
					  and tl.indice          = bb.indice)
		GROUP BY 
			tls.CodTipBilhete,
			ttb.TipBilhete, 
			tl.DatMovimento,
			ts.NomSetor,
			tls.Indice,
			tl.ValPagto
			
			
			
		SELECT
			ts.NomSetor,
			tl.CodTipBilhete,
			ttb.TipBilhete,
			count(1) as QtdeEstornados
		INTO #TMP_RESUMO2
		FROM
			' + @DataBase + '..tabLancamento as tl
			INNER JOIN
			' + @DataBase + '..tabApresentacao as ta
				ON  tl.CodApresentacao      = ta.CodApresentacao 
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete as ttb
				ON  tl.CodTipBilhete 	    = ttb.CodTipBilhete
			INNER JOIN
			' + @DataBase + '..tabSalDetalhe as tsd
				ON	tl.Indice = tsd.Indice
			INNER JOIN
			' + @DataBase + '..tabSetor as ts
				ON	tsd.CodSala = ts.CodSala
				AND tsd.CodSetor = ts.CodSetor
		WHERE
			tl.CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + '
			AND tl.CodTipLancamento = 2
		GROUP BY
			ts.NomSetor,
			tl.CodTipBilhete,
			ttb.TipBilhete


		declare C1 cursor for
			SELECT  
				CodTipBilhete,
				TipBilhete, 
				DatMovimento,
				NomSetor,
				Indice,
				Preco,
				VlrAgregados,
				OUTROSVALORES
			from #TMP_RESUMO

		
		open C1

		fetch next from C1 into
			@CodTipBilhete,
			@TipBilhete, 
			@DatMovimento,
			@NomSetor,
			@Indice,
			@Preco,
			@VlrAgregados,
			@OUTROSVALORES

		while @@fetch_Status = 0
		BEGIN
			Select  
				@OutrosValores = (@Preco - @VlrAgregados) * case TTLB.icdebcre when ''D'' then (isnull(TTBTL.valor,0)/100) else (isnull(TTBTL.valor,0)/100) * -1 end
			FROM
				' + @DataBase + '..tabTipBilhTipLcto	TTBTL
			INNER JOIN
				' + @DataBase + '..tabTipLanctoBilh	TTLB
				ON  TTLB.codtiplct  = TTBTL.codtiplct
				and TTLB.icpercvlr  = ''P''
				and TTLB.icusolcto != ''C''
				and TTLB.inativo    = ''A''
			WHERE
				TTBTL.codtipbilhete = @codtipbilhete
			and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig) 
						 from ' + @DataBase + '..tabTipBilhTipLcto  TTBTL1,
						      ' + @DataBase + '..tabTipLanctoBilh   TTLB1
						where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
						  and TTBTL1.codtiplct     = TTBTL.codtiplct
						  and TTBTL1.dtinivig     <= @DatMovimento
						  and TTBTL1.inativo       = ''A''
						  and TTLB1.codtiplct     = TTBTL1.codtiplct
						  and TTLB1.IcPercVlr     = ''P''
						  and TTLB1.icusolcto    != ''C''
						  and TTLB1.inativo       = ''A'')
			and 	TTBTL.inativo        = ''A''


			Select  
				@OutrosValores = @OutrosValores + (case TTLB.icdebcre when ''D'' then isnull(TTBTL.valor,0) else isnull(TTBTL.valor,0) * -1 end)
			FROM
				' + @DataBase + '..tabTipBilhTipLcto	TTBTL
			INNER JOIN
				' + @DataBase + '..tabTipLanctoBilh	TTLB
				ON  TTLB.codtiplct  = TTBTL.codtiplct
				and TTLB.icpercvlr  = ''V''
				and TTLB.icusolcto != ''C''
				and TTLB.inativo    = ''A''
			WHERE
				TTBTL.codtipbilhete = @codtipbilhete
			and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig) 
						 from ' + @DataBase + '..tabTipBilhTipLcto  TTBTL1,
						      ' + @DataBase + '..tabTipLanctoBilh   TTLB1
						where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
						  and TTBTL1.codtiplct     = TTBTL.codtiplct
						  and TTBTL1.dtinivig     <= @DatMovimento
						  and TTBTL1.inativo       = ''A''
						  and TTLB1.codtiplct     = TTBTL1.codtiplct
						  and TTLB1.IcPercVlr     = ''V''
						  and TTLB1.icusolcto    != ''C''
						  and TTLB1.inativo       = ''A'')
			and 	TTBTL.inativo        = ''A''
	
	
			Update #TMP_RESUMO
			Set	Preco = @Preco - @VlrAgregados + @OutrosValores
			,	OutrosValores = @OutrosValores
			where	Indice = @Indice
				
	
			fetch next from C1 into
				@CodTipBilhete,
				@TipBilhete, 
				@DatMovimento,
				@NomSetor,
				@Indice,
				@Preco,
				@VlrAgregados,
				@OUTROSVALORES

		END


		Close C1
		Deallocate C1


		select 
			CodTipBilhete,
			TipBilhete,
			NomSetor,
			sum(QtdeEstornados) as QtdeEstornados,
			sum(QtdeVendidos) as QtdeVendidos,
			sum(Preco) as Preco,
			sum(Total) as Total
		from (
			Select CodTipBilhete,
				TipBilhete, 
				NomSetor,
				NULL as QtdeEstornados,
				count(1) as QtdeVendidos,
				round(Preco,2) as Preco,
				round(sum(Preco),2) as Total
			from
				#TMP_RESUMO
			Group by
				CodTipBilhete,
				TipBilhete, 
				NomSetor,
				Preco
			
			UNION
			
			Select CodTipBilhete,
				TipBilhete,
				NomSetor,
				QtdeEstornados,
				NULL as QtdeVendidos,
				NULL as Preco,
				NULL as Total
			from
				#TMP_RESUMO2
			Group by
				CodTipBilhete,
				TipBilhete,
				NomSetor,
				QtdeEstornados
		) as resultado
		Group by
			CodTipBilhete,
			TipBilhete,
			NomSetor
		order by TipBilhete

		DROP TABLE #TMP_RESUMO
		DROP TABLE #TMP_RESUMO2


	END

ELSE

	SELECT 
		0  as CodTipBilhete,
		''Não houve vendas'' as tipBilhete, 
		''Não houve vendas'' as NomSetor, 
		0 AS QtdeEstornados, 
		0 AS QtdeVendidos, 
		0 AS Preco, 
		0 AS Total'
--print @query
exec (@query)







GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS_2];5    Script Date: 02/05/2013 11:16:50 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
/* EQUIVALENTE AO ;3 (SOMENTE PARA TODOS OS SETORES E RESUMIDO) */
ALTER  procedure [dbo].[SP_REL_BORDERO_VENDAS_2];5
	@DtIniApr varchar(8),
	@DtFimApr varchar(8), 
	@CodPeca varchar(5) = null,	
	@horaSessao varchar(6),
	@DataBase varchar(30)
AS

set nocount on
	
declare @query varchar(8000)
declare @hora varchar(1000)

if @horaSessao = '' or @horaSessao = 'null' or @horaSessao is null
begin
	set @hora = ''
end
else
begin
	set @hora = 'and ta.HorSessao = ''' + @horaSessao + ''''
end
set @query =
'declare @CodTipBilhete 	int,
	@TipBilhete	varchar(20),
	@DatMovimento 	datetime,
	@NomSetor	varchar(20),
	@Indice		int,
	@Preco		money,
	@VlrAgregados	money,
	@OUTROSVALORES	money,
	@codapresentacao int
IF EXISTS
	(SELECT 1 FROM
		' + @DataBase + '..tabLugSala 
		INNER JOIN 
		' + @DataBase + '..tabApresentacao as ta
			ON  ' + @DataBase + '..tabLugSala.CodApresentacao      = ta.CodApresentacao 
		WHERE   
			' + @DataBase + '..tabLugSala.CodVenda IS NOT NULL
		AND 	ta.CodPeca = ' + convert(varchar(10), @CodPeca) + '
		AND 	(convert(varchar(8), ta.DatApresentacao,112) between ''' + @DtIniApr + ''' and ''' + @DtFimApr + ''')
		' + @hora + ')

	BEGIN

		SELECT  
			tls.CodTipBilhete,
			ttb.TipBilhete, 
			tl.DatMovimento,
			ts.NomSetor,
			tls.Indice,
			tl.ValPagto as Preco,
			tls.CodApresentacao,
			sum(isnull(tia.valor,0))  as VlrAgregados,
			0 AS OUTROSVALORES
		INTO #TMP_RESUMO
		FROM       
			' + @DataBase + '..tabLugSala as tls
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete as ttb
				ON  tls.CodTipBilhete 	    = ttb.CodTipBilhete 
		        INNER JOIN 
			' + @DataBase + '..tabSalDetalhe as tsd
				ON  tls.Indice 		    = tsd.Indice 
			INNER JOIN
		        ' + @DataBase + '..tabSetor as ts
				ON  tsd.CodSala           = ts.CodSala 
				AND tsd.CodSetor 	    = ts.CodSetor 
			INNER JOIN
		        ' + @DataBase + '..tabApresentacao as ta
				ON  tls.CodApresentacao      = ta.CodApresentacao 
			INNER JOIN
			' + @DataBase + '..tabLancamento as tl
				ON  ttb.CodTipBilhete     = tl.CodTipBilhete 
				AND tsd.Indice            = tl.Indice 
				AND ta.CodApresentacao = tl.CodApresentacao
				AND tl.CodTipLancamento  = 1
			LEFT JOIN
			' + @DataBase + '..tabIngressoAgregados as tia
				ON  tia.codvenda   = tls.codvenda
				and tia.indice     = tls.indice
		WHERE
			(tls.CodVenda IS NOT NULL) 
		AND 	ta.CodPeca = ' + convert(varchar(10), @CodPeca) + '
		AND 	(convert(varchar(8), ta.DatApresentacao,112) between ''' + @DtIniApr + ''' and ''' + @DtFimApr + ''')
		' + @hora + '
		AND	not exists (Select 1 from ' + @DataBase + '..tabLancamento bb
					where tl.numlancamento = bb.numlancamento
					  and tl.codtipbilhete = bb.codtipbilhete
					  and bb.codtiplancamento = 2
					  and tl.codapresentacao = bb.codapresentacao
					  and tl.indice          = bb.indice)
		GROUP BY 
			tls.CodTipBilhete,
			ttb.TipBilhete, 
			tl.DatMovimento,
			ts.NomSetor,
			tls.Indice,
			tl.ValPagto,
			tls.CodApresentacao
			
			
			
			
		SELECT
			ts.NomSetor,
			tl.CodTipBilhete,
			ttb.TipBilhete,
			count(1) as QtdeEstornados
		INTO #TMP_RESUMO2
		FROM
			' + @DataBase + '..tabLancamento as tl
			INNER JOIN
			' + @DataBase + '..tabApresentacao as ta
				ON  tl.CodApresentacao      = ta.CodApresentacao 
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete as ttb
				ON  tl.CodTipBilhete 	    = ttb.CodTipBilhete
			INNER JOIN
			' + @DataBase + '..tabSalDetalhe as tsd
				ON	tl.Indice = tsd.Indice
			INNER JOIN
			' + @DataBase + '..tabSetor as ts
				ON	tsd.CodSala = ts.CodSala
				AND tsd.CodSetor = ts.CodSetor
		WHERE
			ta.CodPeca = ' + convert(varchar(10), @CodPeca) + '
			AND tl.CodTipLancamento  = 2
			AND 	(convert(varchar(8), ta.DatApresentacao,112) between ''' + @DtIniApr + ''' and ''' + @DtFimApr + ''')
			' + @hora + '
		GROUP BY
			ts.NomSetor,
			tl.CodTipBilhete,
			ttb.TipBilhete


		declare C1 cursor for
			SELECT  
				CodTipBilhete,
				TipBilhete, 
				DatMovimento,
				NomSetor,
				codapresentacao,
				Indice,
				Preco,
				VlrAgregados,
				OUTROSVALORES
			from #TMP_RESUMO

		
		open C1

		fetch next from C1 into
			@CodTipBilhete,
			@TipBilhete, 
			@DatMovimento,
			@NomSetor,
			@codapresentacao,
			@Indice,
			@Preco,
			@VlrAgregados,
			@OUTROSVALORES

		while @@fetch_Status = 0
		BEGIN
			Select  
				@OutrosValores = (@Preco - @VlrAgregados) * case TTLB.icdebcre when ''D'' then (isnull(TTBTL.valor,0)/100) else (isnull(TTBTL.valor,0)/100) * -1 end
			FROM
				' + @DataBase + '..tabTipBilhTipLcto	TTBTL
			INNER JOIN
				' + @DataBase + '..tabTipLanctoBilh	TTLB
				ON  TTLB.codtiplct  = TTBTL.codtiplct
				and TTLB.icpercvlr  = ''P''
				and TTLB.icusolcto != ''C''
				and TTLB.inativo    = ''A''
			WHERE
				TTBTL.codtipbilhete = @codtipbilhete
			and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig) 
						 from ' + @DataBase + '..tabTipBilhTipLcto  TTBTL1,
						      ' + @DataBase + '..tabTipLanctoBilh   TTLB1
						where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
						  and TTBTL1.codtiplct     = TTBTL.codtiplct
						  and TTBTL1.dtinivig     <= @DatMovimento
						  and TTBTL1.inativo       = ''A''
						  and TTLB1.codtiplct     = TTBTL1.codtiplct
						  and TTLB1.IcPercVlr     = ''P''
						  and TTLB1.icusolcto    != ''C''
						  and TTLB1.inativo       = ''A'')
			and 	TTBTL.inativo        = ''A''


			Select  
				@OutrosValores = @OutrosValores + (case TTLB.icdebcre when ''D'' then isnull(TTBTL.valor,0) else isnull(TTBTL.valor,0) * -1 end)
			FROM
				' + @DataBase + '..tabTipBilhTipLcto	TTBTL
			INNER JOIN
				' + @DataBase + '..tabTipLanctoBilh	TTLB
				ON  TTLB.codtiplct  = TTBTL.codtiplct
				and TTLB.icpercvlr  = ''V''
				and TTLB.icusolcto != ''C''
				and TTLB.inativo    = ''A''
			WHERE
				TTBTL.codtipbilhete = @codtipbilhete
			and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig) 
						 from ' + @DataBase + '..tabTipBilhTipLcto  TTBTL1,
						      ' + @DataBase + '..tabTipLanctoBilh   TTLB1
						where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
						  and TTBTL1.codtiplct     = TTBTL.codtiplct
						  and TTBTL1.dtinivig     <= @DatMovimento
						  and TTBTL1.inativo       = ''A''
						  and TTLB1.codtiplct     = TTBTL1.codtiplct
						  and TTLB1.IcPercVlr     = ''V''
						  and TTLB1.icusolcto    != ''C''
						  and TTLB1.inativo       = ''A'')
			and 	TTBTL.inativo        = ''A''
	
	
			Update #TMP_RESUMO
			Set	Preco = @Preco - @VlrAgregados + @OutrosValores
			,	OutrosValores = @OutrosValores
			where	Indice = @Indice
			and  codapresentacao = @codapresentacao
				
	
			fetch next from C1 into
				@CodTipBilhete,
				@TipBilhete, 
				@DatMovimento,
				@NomSetor,
				@codapresentacao,
				@Indice,
				@Preco,
				@VlrAgregados,
				@OUTROSVALORES

		END


		Close C1
		Deallocate C1


		select 
			CodTipBilhete,
			TipBilhete,
			NomSetor,
			sum(QtdeEstornados) as QtdeEstornados,
			sum(QtdeVendidos) as QtdeVendidos,
			sum(Preco) as Preco,
			sum(Total) as Total
		from (
			Select CodTipBilhete,
				TipBilhete, 
				NomSetor,
				NULL as QtdeEstornados,
				count(1) as QtdeVendidos,
				round(Preco,2) as Preco,
				round(sum(Preco),2) as Total
			from
				#TMP_RESUMO
			Group by
				CodTipBilhete,
				TipBilhete, 
				NomSetor,
				Preco
			
			UNION
			
			Select CodTipBilhete,
				TipBilhete,
				NomSetor,
				QtdeEstornados,
				NULL as QtdeVendidos,
				NULL as Preco,
				NULL as Total
			from
				#TMP_RESUMO2
			Group by
				CodTipBilhete,
				TipBilhete,
				NomSetor,
				QtdeEstornados
		) as resultado
		Group by
			CodTipBilhete,
			TipBilhete,
			NomSetor
		order by TipBilhete

		DROP TABLE #TMP_RESUMO
		DROP TABLE #TMP_RESUMO2


	END

ELSE

	SELECT 
		0  as CodTipBilhete,
		''Não houve vendas'' as tipBilhete, 
		''Não houve vendas'' as NomSetor, 
		0 AS QtdeVendidos, 
		0 AS Preco, 
		0 AS Total'
--print @query
exec (@query)