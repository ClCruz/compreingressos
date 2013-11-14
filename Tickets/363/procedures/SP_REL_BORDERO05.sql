SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[DBO].[SP_REL_BORDERO05]') AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1)
DROP PROCEDURE [DBO].[SP_REL_BORDERO05]
GO

/* EQUIVALENTE AO ;3 (SOMENTE PARA TODOS OS SETORES E RESUMIDO) */
CREATE PROCEDURE [dbo].[SP_REL_BORDERO05]
	@DtIniApr varchar(8),
	@DtFimApr varchar(8), 
	@CodPeca varchar(5) = null,	
	@horaSessao varchar(6),
	@DataBase varchar(30)
AS

set nocount on
	
declare @query varchar(8000)
declare @query2 varchar(8000)
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
			0 AS OUTROSVALORES,
			csv.codbar
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
			LEFT JOIN
			' + @DataBase + '..tabControleSeqVenda AS csv
				on	ttb.CodTipBilhete = substring(csv.codbar, 15, 3)
				and tl.CodApresentacao = substring(csv.codbar, 1, 5)
				and tl.CodTipBilhete = substring(csv.codbar, 15, 3)
				and tl.indice = csv.indice
				and csv.statusIngresso = ''U''
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
			tls.CodApresentacao,
			csv.codbar '

set @query2 ='
		INSERT INTO #TMP_RESUMO
		SELECT  
			tls.CodTipBilheteComplMeia As CodTipBilhete,
			ttb.TipBilhete, 
			tl.DatMovimento,
			ts.NomSetor,
			tls.Indice,
			tl.ValPagto as Preco,
			tls.CodApresentacao,
			sum(isnull(tia.valor,0))  as VlrAgregados,
			0 AS OUTROSVALORES,
			csv.codbar		
		FROM       
			' + @DataBase + '..tabLugSala as tls
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete as ttb
				ON  tls.CodTipBilheteComplMeia 	    = ttb.CodTipBilhete 
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
				AND tl.CodTipLancamento  = 4
			LEFT JOIN
			' + @DataBase + '..tabIngressoAgregados as tia
				ON  tia.codvenda   = tls.codvenda
				and tia.indice     = tls.indice
			LEFT JOIN
			' + @DataBase + '..tabControleSeqVenda AS csv
				on	ttb.CodTipBilhete = substring(csv.codbar, 15, 3)
				and tl.CodApresentacao = substring(csv.codbar, 1, 5)
				and tl.CodTipBilhete = substring(csv.codbar, 15, 3)
				and tl.indice = csv.indice
				and csv.statusIngresso = ''U''
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
			tls.CodTipBilheteComplMeia,
			ttb.TipBilhete, 
			tl.DatMovimento,
			ts.NomSetor,
			tls.Indice,
			tl.ValPagto,
			tls.CodApresentacao,
			csv.codbar
			
			
			
			
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
			and  codapresentacao = @codapresentacao AND CodTipBilhete = @CodTipBilhete
				
	
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
			sum(Total) as Total,
			QtdeAcessos
		from (
			Select CodTipBilhete,
				TipBilhete, 
				NomSetor,
				NULL as QtdeEstornados,
				count(1) as QtdeVendidos,
				round(Preco,2) as Preco,
				round(sum(Preco),2) as Total,
				count(codbar) as QtdeAcessos
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
				NULL as Total,
				NULL as QtdeAcessos
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
			NomSetor,
			QtdeAcessos
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
--print @query2
exec (@query+@query2)