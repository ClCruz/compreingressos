/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS_2];2    Script Date: 02/05/2013 11:16:48 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[DBO].[SP_REL_BORDERO02]') AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1)
DROP PROCEDURE [DBO].[SP_REL_BORDERO02]
GO

/* EQUIVALENTE AO ;5 (SOMENTE PARA TODOS OS SETORES) */
CREATE PROCEDURE [dbo].[SP_REL_BORDERO02]
	@DtIniApr varchar(8), 
	@DtFimApr varchar(8), 
	@codPeca int = null,
	@codSala varchar(5) = null, 
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
			' + @DataBase + '..tabforpagamento.ForPagto, pctxadm'

set @query2 ='
		INSERT INTO #TMP_RESUMO
		SELECT  
			' + @DataBase + '..tabLugSala.CodTipBilheteComplMeia As CodTipBilhete,
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
			' + @DataBase + '..tabLugSala.CodTipBilheteComplMeia,
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
					CodApresentacao = @codapresentacao AND CodTipBilhete = @CodTipBilhete
				
	
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
exec (@query+@query2)