SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[DBO].[SP_REL_BORDERO06]') AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1)
DROP PROCEDURE [DBO].[SP_REL_BORDERO06]
GO

CREATE PROCEDURE [dbo].[SP_REL_BORDERO06]
(
	@CodPeca   int, 
	@CodApresentacao int, 
	@DatApresentacao varchar(10), 
	@DataBase varchar(30)
) 
AS


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