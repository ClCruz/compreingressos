GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];5    Script Date: 08/28/2012 13:44:27 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[DBO].[SP_REL_BORDERO07]') AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1)
DROP PROCEDURE [DBO].[SP_REL_BORDERO07]
GO

CREATE PROCEDURE [dbo].[SP_REL_BORDERO07]
	@DtIniApr varchar(8), 
	@DtFimApr varchar(8), 
	@codPeca int = null,
	@codSala int = null, 
	@hora varchar(6),
	@DataBase varchar(30)
AS

set nocount on

declare @query varchar(8000)
declare @query2 varchar(8000)


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
	
/*IF EXISTS
	(SELECT 1 FROM
		' + @DataBase + '..tabLugSala 
		INNER JOIN 
		' + @DataBase + '..tabApresentacao 
			ON  ' + @DataBase + '..tabLugSala.CodApresentacao      = ' + @DataBase + '..tabApresentacao.CodApresentacao 
		WHERE   
			' + @DataBase + '..tabLugSala.CodVenda IS NOT NULL
		AND 	' + @DataBase + '..tabApresentacao.CodApresentacao = ' + /*convert(varchar(10), @CodApresentacao)*/ + ')*/
	set nocount on
	BEGIN

		SELECT  
			TLS.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabforpagamento.ForPagto,
			TLS.Indice,
			' + @DataBase + '..tabLancamento.ValPagto as Preco2,
			' + @DataBase + '..tabLancamento.ValPagto as Preco,
			' + @DataBase + '..tabforpagamento.PrzRepasseDias,
			sum(isnull(' + @DataBase + '..tabIngressoAgregados.valor,0))  as VlrAgregados,			
			0 AS OUTROSVALORES,
			pctxadm
			
		INTO #TMP_RESUMO
		FROM       
			' + @DataBase + '..tabLugSala TLS
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete 	
				ON  TLS.CodTipBilhete 	    = ' + @DataBase + '..tabTipBilhete.CodTipBilhete 
		        INNER JOIN 
			' + @DataBase + '..tabSalDetalhe 	
				ON  TLS.Indice 		    = ' + @DataBase + '..tabSalDetalhe.Indice 
			INNER JOIN
		        ' + @DataBase + '..tabSetor 	
				ON  ' + @DataBase + '..tabSalDetalhe.CodSala           = ' + @DataBase + '..tabSetor.CodSala 
				AND ' + @DataBase + '..tabSalDetalhe.CodSetor 	    = ' + @DataBase + '..tabSetor.CodSetor 
			INNER JOIN
		        ' + @DataBase + '..tabApresentacao 
				ON  TLS.CodApresentacao      = ' + @DataBase + '..tabApresentacao.CodApresentacao 
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
				ON  ' + @DataBase + '..tabIngressoAgregados.codvenda   = TLS.codvenda
				and ' + @DataBase + '..tabIngressoAgregados.indice     = TLS.indice
		WHERE
			(TLS.CodVenda IS NOT NULL) 
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
		GROUP BY 
			TLS.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			TLS.Indice,
			' + @DataBase + '..tabLancamento.ValPagto,
			' + @DataBase + '..tabforpagamento.PrzRepasseDias,
			' + @DataBase + '..tabforpagamento.ForPagto, pctxadm


		INSERT INTO #TMP_RESUMO
		SELECT  
			TLS.CodTipBilheteComplMeia as CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabforpagamento.ForPagto,
			TLS.Indice,
			' + @DataBase + '..tabLancamento.ValPagto as Preco2,
			' + @DataBase + '..tabLancamento.ValPagto as Preco,
			' + @DataBase + '..tabforpagamento.PrzRepasseDias,
			sum(isnull(' + @DataBase + '..tabIngressoAgregados.valor,0))  as VlrAgregados,			
			0 AS OUTROSVALORES,
			pctxadm				
		FROM       
			' + @DataBase + '..tabLugSala TLS 
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete 	
				ON  TLS.CodTipBilheteComplMeia 	    = ' + @DataBase + '..tabTipBilhete.CodTipBilhete 
		        INNER JOIN 
			' + @DataBase + '..tabSalDetalhe 	
				ON  TLS.Indice 		    = ' + @DataBase + '..tabSalDetalhe.Indice 
			INNER JOIN
		        ' + @DataBase + '..tabSetor 	
				ON  ' + @DataBase + '..tabSalDetalhe.CodSala           = ' + @DataBase + '..tabSetor.CodSala 
				AND ' + @DataBase + '..tabSalDetalhe.CodSetor 	    = ' + @DataBase + '..tabSetor.CodSetor 
			INNER JOIN
		        ' + @DataBase + '..tabApresentacao 
				ON  TLS.CodApresentacao      = ' + @DataBase + '..tabApresentacao.CodApresentacao 
			INNER JOIN
				' + @DataBase + '..tabSala
				ON ' + @DataBase + '..tabApresentacao.CodSala		   = ' + @DataBase + '..tabSala.CodSala
			INNER JOIN
			' + @DataBase + '..tabLancamento 
				ON  ' + @DataBase + '..tabTipBilhete.CodTipBilhete     = ' + @DataBase + '..tabLancamento.CodTipBilhete 
				AND ' + @DataBase + '..tabSalDetalhe.Indice            = ' + @DataBase + '..tabLancamento.Indice 
				AND ' + @DataBase + '..tabApresentacao.CodApresentacao = ' + @DataBase + '..tabLancamento.CodApresentacao
				AND ' + @DataBase + '..tabLancamento.CodTipLancamento  = 4
			INNER JOIN 
			' + @DataBase + '..tabforpagamento 
				ON ' + @DataBase + '..tabforpagamento.CodForPagto = ' + @DataBase + '..tabLancamento.CodForPagto
			LEFT JOIN
			' + @DataBase + '..tabIngressoAgregados
				ON  ' + @DataBase + '..tabIngressoAgregados.codvenda   = TLS.codvenda
				and ' + @DataBase + '..tabIngressoAgregados.indice     = TLS.indice
		WHERE
			(TLS.CodVendaComplMeia IS NOT NULL) 
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
		GROUP BY 
			TLS.CodTipBilheteComplMeia,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			TLS.Indice,
			' + @DataBase + '..tabLancamento.ValPagto,
			' + @DataBase + '..tabforpagamento.PrzRepasseDias,
			' + @DataBase + '..tabforpagamento.ForPagto, pctxadm'

set @query2 ='
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
			where	Indice = @Indice AND CodTipBilhete = @CodTipBilhete
				
	
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
			PrzRepasseDias,
			(pctxadm) as taxa
		from
			#TMP_RESUMO
		Group by
			forpagto, pctxadm, PrzRepasseDias

		DROP TABLE #TMP_RESUMO
	END
/*ELSE
	Select
			'''' as forpagto,
			0 as qtdbilh,
			0 as totfat*/'

--print @query
--print @query2
exec (@query+@query2)