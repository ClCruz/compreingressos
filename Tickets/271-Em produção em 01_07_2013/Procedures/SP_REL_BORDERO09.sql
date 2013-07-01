/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];9    Script Date: 08/28/2012 13:44:28 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[DBO].[SP_REL_BORDERO09]') AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1)
DROP PROCEDURE [DBO].[SP_REL_BORDERO09]
GO

CREATE PROCEDURE [dbo].[SP_REL_BORDERO09]
	@DtIniApr varchar(8), 
	@DtFimApr varchar(8), 
	@codPeca int = null,
	@codSala int = null,	
	@hora varchar(6),
	@DataBase varchar(30)
as
	
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
	@ds_canal_venda	varchar(20)	

	set nocount on
	BEGIN

		SELECT  
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabforpagamento.tipcaixa,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto as Preco2,
			' + @DataBase + '..tabLancamento.ValPagto as Preco,
			ci_middleway..mw_canal_venda.ds_canal_venda,
			sum(isnull(' + @DataBase + '..tabIngressoAgregados.valor,0))  as VlrAgregados,
			0 AS OUTROSVALORES
			
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
			INNER JOIN
				' + @DataBase + '..tabCaixa
					ON	' + @DataBase + '..tabLancamento.codCaixa	   = ' + @DataBase + '..tabCaixa.codCaixa 
			LEFT JOIN
			ci_middleway..mw_canal_venda
				ON ci_middleway..mw_canal_venda.id_canal_venda = ' + @DataBase + '..tabCaixa.id_canal_venda
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
		and ' + @DataBase + '..tabLancamento.ValPagto > 0
		GROUP BY 
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto,
			' + @DataBase + '..tabforpagamento.tipcaixa,
			ci_middleway..mw_canal_venda.ds_canal_venda'

set @query2 = '
		INSERT INTO #TMP_RESUMO
		SELECT  
			' + @DataBase + '..tabLugSala.CodTipBilheteComplMeia As CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabforpagamento.tipcaixa,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto as Preco2,
			' + @DataBase + '..tabLancamento.ValPagto as Preco,
			ci_middleway..mw_canal_venda.ds_canal_venda,
			sum(isnull(' + @DataBase + '..tabIngressoAgregados.valor,0))  as VlrAgregados,
			0 AS OUTROSVALORES				
		FROM       
			' + @DataBase + '..tabLugSala 
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete 	
				ON  ' + @DataBase + '..tabLugSala.CodTipBilheteComplMeia 	    = ' + @DataBase + '..tabTipBilhete.CodTipBilhete 
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
				AND ' + @DataBase + '..tabLancamento.CodTipLancamento  = 4
			INNER JOIN 
			' + @DataBase + '..tabforpagamento 
				ON ' + @DataBase + '..tabforpagamento.CodForPagto = ' + @DataBase + '..tabLancamento.CodForPagto
			LEFT JOIN
			' + @DataBase + '..tabIngressoAgregados
				ON  ' + @DataBase + '..tabIngressoAgregados.codvenda   = ' + @DataBase + '..tabLugSala.codvenda
				and ' + @DataBase + '..tabIngressoAgregados.indice     = ' + @DataBase + '..tabLugSala.indice
			INNER JOIN
				' + @DataBase + '..tabCaixa
					ON	' + @DataBase + '..tabLancamento.codCaixa	   = ' + @DataBase + '..tabCaixa.codCaixa 
			LEFT JOIN
			ci_middleway..mw_canal_venda
				ON ci_middleway..mw_canal_venda.id_canal_venda = ' + @DataBase + '..tabCaixa.id_canal_venda
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
		and ' + @DataBase + '..tabLancamento.ValPagto > 0
		GROUP BY 
			' + @DataBase + '..tabLugSala.CodTipBilheteComplMeia,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto,
			' + @DataBase + '..tabforpagamento.tipcaixa,
			ci_middleway..mw_canal_venda.ds_canal_venda


		declare C1 cursor for
			SELECT  
				CodTipBilhete,
				TipBilhete, 
				DatMovimento,
				NomSetor,
				Indice,
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
			@Preco,
			@VlrAgregados,
			@OUTROSVALORES,
			@ds_canal_venda
				

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
				@OUTROSVALORES,
				@ds_canal_venda
				

		END


		Close C1
		Deallocate C1
		
		Select	
			isnull(ds_canal_venda, ''Forma não cadastrada'') as ''Venda'',
			count(1) as Quant,
			sum(preco) as Total
			
		from
			#TMP_RESUMO
		Group by
			isnull(ds_canal_venda, ''Forma não cadastrada'')
		order by ''Venda''	

		DROP TABLE #TMP_RESUMO
	END'
--print @query
exec (@query+@query2)