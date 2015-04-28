SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[DBO].[SP_REL_DEMO_AVULSO_ASSINATURA]') AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1)
DROP PROCEDURE [DBO].[SP_REL_DEMO_AVULSO_ASSINATURA]
GO


CREATE PROCEDURE [dbo].[SP_REL_DEMO_AVULSO_ASSINATURA]
	@DtIniApr varchar(8),
	@DtFimApr varchar(8), 
	@CodPeca varchar(5) = null,	
	@horaSessao varchar(6),
	@DataBase varchar(30),
	@CodSala varchar(5) = null
AS


set nocount on
	
declare @query varchar(8000)
declare @query2 varchar(8000)
declare @hora varchar(1000)
declare @sala varchar(1000)
	

if @horaSessao = '' or @horaSessao = 'null' or @horaSessao is null
begin
	set @hora = ''
end
else
begin
	set @hora = 'and ta.HorSessao = ''' + @horaSessao + ''''
end

if @CodSala = '' or @CodSala = 'null' or @CodSala is null or @CodSala = 'TODOS'
begin
	set @sala = ''
end
else
begin
	set @sala = 'and ts.CodSala = ' + @CodSala
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
			ttb.StaTipBilhMeia,
			tl.DatMovimento,
			ts.NomSetor,
			tls.Indice,
			tl.ValPagto as Preco,
			tls.CodApresentacao,
			sum(isnull(tia.valor,0))  as VlrAgregados,
			0 AS OUTROSVALORES,
			csv.codbar,
			pv.id_pedido_pai
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
				
			LEFT JOIN ci_middleway..mw_item_pedido_venda ipv
				on tls.codvenda = ipv.codvenda collate SQL_Latin1_General_CP1_CI_AS
				and ipv.indice = tls.indice
			LEFT JOIN ci_middleway..mw_pedido_venda pv
				on pv.id_pedido_venda = ipv.id_pedido_venda
				and pv.id_pedido_pai is not null
			
		WHERE
			(tls.CodVenda IS NOT NULL) 
		AND 	ta.CodPeca = ' + convert(varchar(10), @CodPeca) + '
		AND 	(convert(varchar(8), ta.DatApresentacao,112) between ''' + @DtIniApr + ''' and ''' + @DtFimApr + ''')
		' + @hora + '
		' + @sala + '
		AND	not exists (Select 1 from ' + @DataBase + '..tabLancamento bb
					where tl.numlancamento = bb.numlancamento
					  and tl.codtipbilhete = bb.codtipbilhete
					  and bb.codtiplancamento = 2
					  and tl.codapresentacao = bb.codapresentacao
					  and tl.indice          = bb.indice)
		GROUP BY 
			tls.CodTipBilhete,
			ttb.TipBilhete,
			ttb.StaTipBilhMeia, 
			tl.DatMovimento,
			ts.NomSetor,
			tls.Indice,
			tl.ValPagto,
			tls.CodApresentacao,
			csv.codbar,
			pv.id_pedido_pai '

set @query2 ='
		INSERT INTO #TMP_RESUMO
		SELECT  
			tls.CodTipBilheteComplMeia As CodTipBilhete,
			ttb.TipBilhete,
			ttb.StaTipBilhMeia, 
			tl.DatMovimento,
			ts.NomSetor,
			tls.Indice,
			tl.ValPagto as Preco,
			tls.CodApresentacao,
			sum(isnull(tia.valor,0))  as VlrAgregados,
			0 AS OUTROSVALORES,
			csv.codbar,
			pv.id_pedido_pai
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
				
			LEFT JOIN ci_middleway..mw_item_pedido_venda ipv
				on tls.codvenda = ipv.codvenda collate SQL_Latin1_General_CP1_CI_AS
				and ipv.indice = tls.indice
			LEFT JOIN ci_middleway..mw_pedido_venda pv
				on pv.id_pedido_venda = ipv.id_pedido_venda
				and pv.id_pedido_pai is not null
				
		WHERE
			(tls.CodVenda IS NOT NULL) 
		AND 	ta.CodPeca = ' + convert(varchar(10), @CodPeca) + '
		AND 	(convert(varchar(8), ta.DatApresentacao,112) between ''' + @DtIniApr + ''' and ''' + @DtFimApr + ''')
		' + @hora + '
		' + @sala + '
		AND	not exists (Select 1 from ' + @DataBase + '..tabLancamento bb
					where tl.numlancamento = bb.numlancamento
					  and tl.codtipbilhete = bb.codtipbilhete
					  and bb.codtiplancamento = 2
					  and tl.codapresentacao = bb.codapresentacao
					  and tl.indice          = bb.indice)
		GROUP BY 
			tls.CodTipBilheteComplMeia,
			ttb.TipBilhete,
			ttb.StaTipBilhMeia, 
			tl.DatMovimento,
			ts.NomSetor,
			tls.Indice,
			tl.ValPagto,
			tls.CodApresentacao,
			csv.codbar,
			pv.id_pedido_pai


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
			StaTipBilhMeia,
			NomSetor,
			sum(QtdeVendidos) as QtdeVendidos,
			sum(Preco) as Preco,
			sum(Total) as Total,
			QtdeAcessos,
			in_from_pacote
		from (
			Select CodTipBilhete,
				TipBilhete,
				StaTipBilhMeia,
				NomSetor,
				count(1) as QtdeVendidos,
				round(Preco,2) as Preco,
				round(sum(Preco),2) as Total,
				count(codbar) as QtdeAcessos,
				case when id_pedido_pai is not null then 1 else 0 end as in_from_pacote
			from
				#TMP_RESUMO
			Group by
				CodTipBilhete,
				TipBilhete, 
				StaTipBilhMeia,
				NomSetor,
				Preco,
				case when id_pedido_pai is not null then 1 else 0 end
		) as resultado
		Group by
			CodTipBilhete,
			TipBilhete,
			StaTipBilhMeia,
			NomSetor,
			QtdeAcessos,
			in_from_pacote
		order by NomSetor, TipBilhete

		DROP TABLE #TMP_RESUMO


	END

ELSE

	SELECT 
		0 as CodTipBilhete,
		''Não houve vendas'' as tipBilhete,
		0 as StaTipBilhMeia,
		''Não houve vendas'' as NomSetor, 
		0 AS QtdeVendidos, 
		0 AS Preco, 
		0 AS Total'
--print @query
--print @query2
exec (@query+@query2)