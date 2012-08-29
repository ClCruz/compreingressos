USE [tspweb]
GO
/****** Object:  StoredProcedure [dbo].[SP_REL_BORDERO_VENDAS]    Script Date: 08/28/2012 13:44:24 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER    procedure [dbo].[SP_REL_BORDERO_VENDAS]
	@Login			varchar(10),
	@CodPeca		int		= null,
	@CodSala		int		= null,
	@DataIni		varchar(10)	= null,
	@DataFim		varchar(10)	= null,
	@HorSessao		varchar(10)	= null,
	@NomBase		varchar(30)	= null

 AS

/*
declare @Login			varchar(10)
declare @CodPeca		int 		
declare @DataIni		varchar(10)	
declare @DataFim		varchar(10)	
declare @HorSessao		varchar(10)	
declare @NomBase		varchar(30)	

set @Login	= 'clcruz'
set @CodPeca	= 11
set @DataIni	= '20040318'
set @DataFim	= '20040318'
set @HorSessao	= '21:00'
set @NomBase	= 'TSP_TAUGUSTA'
*/

declare @query varchar(8000)
set @query = 

'
declare @horaAux varchar(10)
declare @hora varchar(10)

declare @DtIAux Datetime
declare @DtI varchar(10)

declare @DtFAux Datetime
declare @DtF varchar(10)


set @hora = ''' + @HorSessao + '''
set @horaAux = case when @hora <> ''null'' then @hora else null end

set @DtI = ''' + @DataIni + '''
set @DtIAux = case when @DtI <> ''null'' then @DtI else null end

set @DtF = ''' + @DataFim + '''
set @DtFAux = case when @DtF <> ''null'' then @DtF else null end

set nocount on
 select
		tbAp.CodPeca,
		tbAp.CodApresentacao,
		tbAp.NumBordero,
		tbPc.NomPeca,
		tbSl.NomSala,
		tbPc.NomResPeca,
		tbAp.DatApresentacao,
		tbAp.HorSessao,
		(select coalesce(count(Indice), 0) from ' + @NomBase + '..tabSalDetalhe tbSDet (nolock)
			where		tbSDet.CodSala = tbAp.CodSala
				and	tbSDet.TipObjeto <> ''I'') as Lugares,
		(select coalesce(count(Indice), 0) from ' + @NomBase + '..tabLugSala (nolock)
			where		CodApresentacao = tbAp.CodApresentacao
				AND	StaCadeira = ''V'') as PubTotal,
		(select coalesce(count(tbLSl.Indice),0)
			from ' + @NomBase + '..tabLugSala tbLSl (nolock)
			inner join ' + @NomBase + '..tabTipBilhete (nolock)
				on	tbLSl.CodTipBilhete = ' + @NomBase + '..tabTipBilhete.CodTipBilhete
			where		' + @NomBase + '..tabTipBilhete.PerDesconto < 100
				AND	tbLSl.CodApresentacao = tbAp.CodApresentacao
				AND	tbLSl.CodVenda IS NOT NULL) as Pagantes,
		(select round(Coalesce(sum(ValPagto), 0), 2)
			from ' + @NomBase + '..tabLancamento (nolock)
			where CodApresentacao = tbAp.CodApresentacao) as ValVendas

	from ' + @NomBase + '..tabApresentacao tbAp (nolock)

	inner join ' + @NomBase + '..tabPeca tbPc (nolock)
		on	tbPc.CodPeca = tbAp.CodPeca

	inner join ' + @NomBase + '..tabSala tbSl (nolock)
		on	tbSl.CodSala = tbAp.CodSala

	--Inner join acrescentado

	--inner join tspweb.dbo.tabItemAcessoConc iac (nolock)
		--on		iac.CodPeca = tbAp.CodPeca
			--and	Login = ''' + @Login + '''

	--INNER JOIN tspweb.dbo.tabAcessoConcedido AC (NOLOCK) ON 
	--	IAC.LOGIN = AC.LOGIN 
	--	AND IAC.SENHA = AC.SENHA

	where		(tbAp.CodPeca = ' + convert(varchar(10),@CodPeca) + ' or ' + convert(varchar(10),@CodPeca) + ' is null)
		and		(tbSl.CodSala = ' + convert(varchar(10),@CodSala) + ' or ' + convert(varchar(10),@CodSala) + ' is null)
--		and	(convert(varchar(10), tbAp.DatApresentacao, 112) between  ''' + @DataIni + ''' and ''' + @DataFim + ''' or ''' + @DataIni + ''' is null)
--		and	(tbAp.HorSessao = ''' + convert(varchar(5),@HorSessao) + ''' or ''' + convert(varchar(5),@HorSessao) + ''' is null)
		and 	(tbAp.DatApresentacao >= @DtIAux or @DtIAux is null)
		and 	(tbAp.DatApresentacao <= @DtFAux or @DtFAux is null)
		and	(tbAp.HorSessao = @horaAux or @horaAux is null)
--		and 	(AC.NOMBASEDADOS = ''' + @NomBase + ''' or ''' + @NomBase + ''' is null)
	order by	tbAp.CodPeca,
			tbAp.DatApresentacao,
			tbAp.HorSessao'
--print (@query)
exec (@query)






GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];2    Script Date: 08/28/2012 13:44:25 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER  PROCEDURE [dbo].[SP_REL_BORDERO_VENDAS];2
	@CodPeca  int,
	@CodApresentacao int,
	@DataBase2 varchar(30) = null
AS

set nocount on

declare	@query2	varchar(8000)
set @query2 =
'-- Variáveis de Saída
DECLARE  	@NomSala 		varchar(50),	--Nome da Sala	
  		@HorSessao 		char(5),	--Hora da Apresentacao
  		@NomResPeca 		varchar(50),	-- Nome Redusido da Peca
  		@DatApresentacao 	smalldatetime,	-- Data da Apresentacao
  		@Lugares  		int,     	-- Qtde de Cadeiras na Sala
  		@LugLivres  		int,     	-- Quantidade de Lugares livres que sobrou na sala
  		@PubTotal  		int,      	-- Quantidade de cadeiras vendidas
  		@Pagantes  		int,      	-- Quantidades de Bilhetes Inteiros (Desconto < 100%)
  		@NPagantes  		int,     	-- Quantidade de Bilhetes Convites e Cortesia (100% Desconto)
  		@ValVendas  		money,     	-- Total do valor das vendas  (Vendas de Bilhetes)
  		@ValDebitos  		money,     	-- Total do valor dos descontos (Débitos de Bordero)
  		@ValLiquido  		money,    	-- Valor Líquido da apresentacao @ValVendas - @ValDescontos
  		@NumBordero 		int,		-- Número do Borderô
		@Apresentacao	varchar(10)	-- Data da Apresentação formatada para o IIS.	

-- Variáveis Internas

DECLARE  @CodSala  int      -- Código da Sala da apresentacao
SET NOCOUNT ON
 
-- Seleciona a data da Apresentacao para o IIS.
SELECT @Apresentacao = (SELECT  convert(varchar(10),DatApresentacao,103) As DatApresentacao from ' + @DataBase2 + '..tabApresentacao WHERE CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + ')

-- Seleciona a data da Apresentaçao
 SELECT @DatApresentacao = (SELECT DatApresentacao FROM ' + @DataBase2 + '..tabApresentacao WHERE CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + ')
-- Seleciona código da sala da apresentacao
 SELECT @CodSala = (SELECT CodSala FROM ' + @DataBase2 + '..tabApresentacao WHERE CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + ')
-- Seleciona o Nome da Sala
 SELECT @NomSala = (SELECT NomSala FROM ' + @DataBase2 + '..tabSala WHERE CodSala = @CodSala)
-- Seleciona a hora da apresentacao
 SELECT @HorSessao = (SELECT HorSessao FROM ' + @DataBase2 + '..tabApresentacao Where CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + ')
-- Seleciona o nome do responsável da peça
 SELECT @NomResPeca = (SELECT NomResPeca FROM ' + @DataBase2 + '..tabPeca WHERE CodPeca = @CodPeca) 
-- Seleciona a quantidade de lugares da sala
 SELECT @Lugares = (SELECT COALESCE(COUNT(Indice),0) FROM ' + @DataBase2 + '..tabSalDetalhe WHERE TipObjeto <> ''I'' AND CodSala = @CodSala)
 -- Seleciona a quantidade de cadeiras vendidas para a apresentacao
 SELECT @PubTotal = (SELECT COALESCE(COUNT(Indice),0) FROM ' + @DataBase2 + '..tabLugSala WHERE CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + ' AND StaCadeira = ''V'')
 -- Carrega a variável @LugLivres
 SELECT @LugLivres = COALESCE(@Lugares - @PubTotal,0)
 -- Carrega a Variável @Pagantes
 SELECT @Pagantes = (SELECT COALESCE(COUNT(' + @DataBase2 + '..tabLugSala.Indice),0) 
     FROM ' + @DataBase2 + '..tabLugSala INNER JOIN ' + @DataBase2 + '..tabTipBilhete ON ' + @DataBase2 + '..tabLugSala.CodTipBilhete = ' + @DataBase2 + '..tabTipBilhete.CodTipBilhete
     WHERE (' + @DataBase2 + '..tabTipBilhete.PerDesconto < 100) AND (dbo.' + @DataBase2 + '..tabLugSala.CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + ') AND (' + @DataBase2 + '..tabLugSala.CodVenda IS NOT NULL))
 -- Carrega a variável de Nao Pagantes
 SELECT @NPagantes = COALESCE(@PubTotal - @Pagantes,0)
 -- Carrega a variável de valores de venda (Receita)
 SELECT 	@ValVendas = (SELECT ROUND(COALESCE (SUM(ValPagto), 0),2) FROM ' + @DataBase2 + '..tabLancamento WHERE (CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + '))
-- Carrega o Total de Débitos
 SET 		@ValDebitos = (SELECT ROUND(COALESCE (SUM(' + @DataBase2 + '..tabTipDebBordero.PerDesconto * @ValVendas / 100), 0),2) 
     FROM ' + @DataBase2 + '..tabDebBordero 
     INNER JOIN ' + @DataBase2 + '..tabTipDebBordero ON ' + @DataBase2 + '..tabDebBordero.CodTipDebBordero = ' + @DataBase2 + '..tabTipDebBordero.CodTipDebBordero
    WHERE (CONVERT(Varchar(10),@DatApresentacao,112) BETWEEN ' + @DataBase2 + '..tabDebBordero.DatIniDebito AND ' + @DataBase2 + '..tabDebBordero.DatFinDebito) 
     AND (' + @DataBase2 + '..tabDebBordero.CodPeca = ' + convert(varchar(10),@CodPeca) + ') AND (' + @DataBase2 + '..tabTipDebBordero.TipValor = ''P'' AND ' + @DataBase2 + '..tabTipDebBordero.Ativo = ''A''))
SET @ValDebitos = @ValDebitos + (SELECT ROUND(COALESCE (SUM(' + @DataBase2 + '..tabTipDebBordero.PerDesconto * @PubTotal), 0) ,2)
     FROM ' + @DataBase2 + '..tabDebBordero 
     INNER JOIN ' + @DataBase2+ '..tabTipDebBordero ON ' + @DataBase2 + '..tabDebBordero.CodTipDebBordero = ' + @DataBase2 + '..tabTipDebBordero.CodTipDebBordero
    WHERE (CONVERT(Varchar(10),@DatApresentacao,112) BETWEEN ' + @DataBase2 + '..tabDebBordero.DatIniDebito AND ' + @DataBase2 + '..tabDebBordero.DatFinDebito) 
     AND (' + @DataBase2 + '..tabDebBordero.CodPeca = ' + convert(varchar(10),@CodPeca) + ') AND (' + @DataBase2 + '..tabTipDebBordero.TipValor = ''V'' AND ' + @DataBase2 + '..tabTipDebBordero.Ativo = ''A''))
 --Carrega a variável de resultado @ValLiquido
 SELECT @ValLiquido = COALESCE(@ValVendaS - @ValDebitos,0)

-- Carregar o Numero do Bordero
SELECT @NumBordero = (SELECT NumBordero FROM ' + @DataBase2 + '..tabApresentacao WHERE CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + ')
SET NOCOUNT OFF
-- Seleciona os dados do borderô
SELECT NomPeca, @NomSala as NomSala, @NomResPeca as NomResPeca,  @DatApresentacao as DatApresentacao, @HorSessao as HorSessao, @LugLivres as LugLivres, @PubTotal as PubTotal, @Pagantes as Pagantes, 
 @NPagantes as NPagantes, @ValVendas as ValVendas, @ValDebitos as ValDebitos, @ValLiquido as ValLiquido, @NumBordero as NumBordero ,@CodSala as CodSala, tabImagem.Imagem as ImgSala, @Apresentacao as Apresentacao
 FROM ' + @DataBase2 + '..tabPeca ,' + @DataBase2 + '..tabLogoSala, ' + @DataBase2 + '..tabImagem
 Where (' + @DataBase2 + '..tabPeca.CodPeca = ' + convert(varchar(10),@CodPeca) + ') AND (' + @DataBase2 + '..tabImagem.CodImagem = ' + @DataBase2 + '..tabLogoSala.CodImagem) AND (' + @DataBase2 + '..tabLogoSala.CodSala = @CodSala)'
--print @query
exec (@query2)






GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];3    Script Date: 08/28/2012 13:44:27 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER  procedure [dbo].[SP_REL_BORDERO_VENDAS];3
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
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto as Preco,
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
			' + @DataBase + '..tabLancamento 
				ON  ' + @DataBase + '..tabTipBilhete.CodTipBilhete     = ' + @DataBase + '..tabLancamento.CodTipBilhete 
				AND ' + @DataBase + '..tabSalDetalhe.Indice            = ' + @DataBase + '..tabLancamento.Indice 
				AND ' + @DataBase + '..tabApresentacao.CodApresentacao = ' + @DataBase + '..tabLancamento.CodApresentacao
				AND ' + @DataBase + '..tabLancamento.CodTipLancamento  = 1
			LEFT JOIN
			' + @DataBase + '..tabIngressoAgregados
				ON  ' + @DataBase + '..tabIngressoAgregados.codvenda   = ' + @DataBase + '..tabLugSala.codvenda
				and ' + @DataBase + '..tabIngressoAgregados.indice     = ' + @DataBase + '..tabLugSala.indice
		WHERE
			(' + @DataBase + '..tabLugSala.CodVenda IS NOT NULL) 
		AND 	(' + @DataBase + '..tabApresentacao.CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + ')
		AND	not exists (Select 1 from ' + @DataBase + '..tabLancamento bb
					where ' + @DataBase + '..tabLancamento.numlancamento = bb.numlancamento
					  and ' + @DataBase + '..tabLancamento.codtipbilhete = bb.codtipbilhete
					  and bb.codtiplancamento = 2
					  and ' + @DataBase + '..tabLancamento.codapresentacao = bb.codapresentacao
					  and ' + @DataBase + '..tabLancamento.indice          = bb.indice)
		GROUP BY 
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto


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


		Select	CodTipBilhete,
			TipBilhete, 
			NomSetor,
			count(1) as Qtde,
			round(Preco,2) as Preco,
			round(sum(Preco),2) as Total
		from
			#TMP_RESUMO
		Group by
			CodTipBilhete,
			TipBilhete, 
			NomSetor,
			Preco
		order by TipBilhete


		DROP TABLE #TMP_RESUMO


	END

ELSE

	SELECT 
		0  as CodTipBilhete,
		''Não houve vendas'' as tipBilhete, 
		''Não houve vendas'' as NomSetor, 
		0 AS Qtde, 
		0 AS Preco, 
		0 AS Total'
--print @query
exec (@query)






GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];4    Script Date: 08/28/2012 13:44:27 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER  PROCEDURE [dbo].[SP_REL_BORDERO_VENDAS];4 
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

declare @query4 varchar(8000)
set @query4 =

'DECLARE @TotVenda 	money,
	@ItensVendidos  smallint

SET NOCOUNT ON

	-- CALCULA O VALOR DO TOTAL VENDIDO PARA A APRESENTAÇÃO ESPECIFICADA (VENDAS, DESPRESA SE OS ESTORNOS)
 	execute ' + @DataBase + '..sp_sel_vlrtotal_bordero ' + convert(varchar(10), @CodApresentacao) + ', @TotVenda output


	-- SOMA A QUANTIDADE DE INGRESSOS VENDIDOS
	SELECT  @ItensVendidos = isnull(COUNT(1),0)
	FROM 
		' + @DataBase + '..tabLancamento 
	WHERE 
		CodTipLancamento = 1
	AND 	CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + '

	-- SUBTRAI A QUANTIDADE DE INGRESSOS ESTORNADOS
	SELECT  @ItensVendidos = @ItensVendidos - isnull(COUNT(1),0)
	FROM 
		' + @DataBase + '..tabLancamento 
	WHERE 
		CodTipLancamento = 2
	AND 	CodApresentacao = ' + convert(varchar(10), @CodApresentacao) + '



 	-- CALCULA OS DÉBITOS BASEADOS EM PERCENTUAIS - P = PERCENTUAL.
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
 	GROUP BY	' + @DataBase + '..tabTipDebBordero.CodTipDebBordero,
			' + @DataBase + '..tabTipDebBordero.DebBordero,
			isnull(' + @DataBase + '..tabTipDebBordero.VlMinimo,0),
			' + @DataBase + '..tabTipDebBordero.PerDesconto,
			COALESCE (' + @DataBase + '..tabTipDebBordero.PerDesconto * @TotVenda / 100, 0),
			' + @DataBase + '..tabTipDebBordero.TipValor


	-- CALCULA OS DÉBITOS BASEADOS EM VALORES MONETÁRIOS - V = VALOR.
 	SELECT	' + @DataBase + '..tabTipDebBordero.CodTipDebBordero,
		' + @DataBase + '..tabTipDebBordero.DebBordero,
		isnull(' + @DataBase + '..tabTipDebBordero.VlMinimo,0) as VlMinimoDebBordero, 
		Round(' + @DataBase + '..tabTipDebBordero.PerDesconto,2) as PerDesconto,
		COALESCE (ROUND (' + @DataBase + '..tabTipDebBordero.PerDesconto * @ItensVendidos,2), 0)  AS Valor,
		' + @DataBase + '..tabTipDebBordero.TipValor INTO #TEMP2
 		FROM ' + @DataBase + '..tabDebBordero 
  			INNER JOIN ' + @DataBase + '..tabTipDebBordero ON ' + @DataBase + '..tabDebBordero.CodTipDebBordero = ' + @DataBase + '..tabTipDebBordero.CodTipDebBordero
  		WHERE (convert(varchar(10),''' + @DatApresentacao + ''',112) BETWEEN ' + @DataBase + '..tabDebBordero.DatIniDebito AND ' + @DataBase + '..tabDebBordero.DatFinDebito) AND (' + @DataBase + '..tabDebBordero.CodPeca = ' + convert(varchar(10), @CodPeca) + ') AND (' + @DataBase + '..tabTipDebBordero.TipValor = ''V'' ) AND ' + @DataBase + '..tabTipDebBordero.Ativo = ''A''
 	GROUP BY	' + @DataBase + '..tabTipDebBordero.CodTipDebBordero,
			' + @DataBase + '..tabTipDebBordero.DebBordero,
			isnull(' + @DataBase + '..tabTipDebBordero.VlMinimo,0),
			' + @DataBase + '..tabTipDebBordero.PerDesconto,
			COALESCE (' + @DataBase + '..tabTipDebBordero.PerDesconto * @ItensVendidos, 0),
			' + @DataBase + '..tabTipDebBordero.TipValor

	-- CALCULA OS DÉBITOS BASEADOS EM VALORES MONETÁRIOS - F = VALOR FIXO.
	SELECT	' + @DataBase + '..tabTipDebBordero.CodTipDebBordero,
		' + @DataBase + '..tabTipDebBordero.DebBordero,
		isnull(' + @DataBase + '..tabTipDebBordero.VlMinimo,0) as VlMinimoDebBordero, 
		Round(' + @DataBase + '..tabTipDebBordero.PerDesconto,2) as PerDesconto,
		' + @DataBase + '..tabTipDebBordero.PerDesconto as valor,
		' + @DataBase + '..tabTipDebBordero.TipValor INTO #TEMP3
 		FROM ' + @DataBase + '..tabDebBordero 
  			INNER JOIN ' + @DataBase + '..tabTipDebBordero ON ' + @DataBase + '..tabDebBordero.CodTipDebBordero = ' + @DataBase + '..tabTipDebBordero.CodTipDebBordero
  		WHERE (convert(varchar(10),''' + @DatApresentacao + ''',112) BETWEEN ' + @DataBase + '..tabDebBordero.DatIniDebito AND ' + @DataBase + '..tabDebBordero.DatFinDebito) AND (' + @DataBase + '..tabDebBordero.CodPeca = ' + convert(varchar(10), @CodPeca) + ') AND (' + @DataBase + '..tabTipDebBordero.TipValor = ''F'' ) AND ' + @DataBase + '..tabTipDebBordero.Ativo = ''A''
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
exec (@query4)






GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];5    Script Date: 08/28/2012 13:44:27 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER    PROCEDURE [dbo].[SP_REL_BORDERO_VENDAS];5 
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
	END
/*ELSE
	Select
			'''' as forpagto,
			0 as qtdbilh,
			0 as totfat*/'

--print @query
exec (@query)






GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];6    Script Date: 08/28/2012 13:44:27 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER  procedure [dbo].[SP_REL_BORDERO_VENDAS];6 	
	@DtIniApr varchar(8), 
	@DtFimApr varchar(8), 
	@codPeca int = null,
	@codSala int = null,	
	@hora varchar(6),
	@DataBase varchar(30)
as
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
			' + @DataBase + '..tabforpagamento.tipcaixa


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
		
		Select	
			case when tipcaixa = ''C'' then ''Bilheteria'' when tipcaixa = ''T'' then ''TeleMarkenting'' else ''Forma não cadastrada'' end as ''Venda'',
			count(1) as Quant,
			sum(preco) as Total
			
		from
			#TMP_RESUMO
		Group by
			tipcaixa		

		DROP TABLE #TMP_RESUMO
	END'
--print @query
exec (@query)






GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];7    Script Date: 08/28/2012 13:44:28 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER  procedure [dbo].[SP_REL_BORDERO_VENDAS];7	
	@data varchar(10) = null,
	@peca int = null,
	@hora varchar(5) = null,
	@Base varchar(20) = null
as
/*
declare @data varchar(10)
declare @peca int
declare @hora varchar(5)
declare @Base varchar(20)

set @Base = 'CI_COLISEU'
set @data = '2010-04-03'
set @peca = 30
set @hora = '20:30'*/

declare @query varchar(8000)
set @query =
'select 
		ts.codsala,
		ts.nomSala
from 
		' + @Base + '..tabapresentacao ta
join ' + @Base + '..tabsala ts on ts.codsala = ta.codsala
where
		ta.datapresentacao = ''' + convert(varchar, @data, 103) + '''
	and	ta.codpeca = ' + convert(varchar, @peca) + '
	and (convert(varchar(10), ta.horsessao, 112) = ''' + @hora + ''' or ''' + @hora + ''' is null)'
--print (@query)
exec (@query)






GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];8    Script Date: 08/28/2012 13:44:28 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER  procedure [dbo].[SP_REL_BORDERO_VENDAS];8
	@DtIniApr varchar(8), 
	@DtFimApr varchar(8), 
	@codPeca int = null,
	@codSala int = null,	
	@hora varchar(6),
	@DataBase varchar(30)
as
/*
declare @DtIniApr varchar(8) 
declare @DtFimApr varchar(8)
declare @codPeca int
declare @codSala int
declare @hora varchar(6)
declare @DataBase varchar(30)
declare @CodApresentacao int

set @CodApresentacao = 120
set @DtIniApr = '20110206'
set @DtFimApr = '20110206'
set @codPeca = 5
set @codSala = 4
set @hora = '19:00'
set @DataBase = 'CI_COLISEU'
*/
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
	@descrCaixa  varchar(50)

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
			sum(isnull(' + @DataBase + '..tabIngressoAgregados.valor,0))  as VlrAgregados,			
			0 AS OUTROSVALORES,
			' + @DataBase + '..tabCaixa.descrCaixa
			
		INTO #TMP_RESUMO
		FROM       
			' + @DataBase + '..tabLugSala 
			INNER JOIN 
			' + @DataBase + '..tabTipBilhete 	
				ON  ' + @DataBase + '..tabLugSala.CodTipBilhete 	   = ' + @DataBase + '..tabTipBilhete.CodTipBilhete 
		        INNER JOIN 
			' + @DataBase + '..tabSalDetalhe 	
				ON  ' + @DataBase + '..tabLugSala.Indice 			   = ' + @DataBase + '..tabSalDetalhe.Indice 
			INNER JOIN
		        ' + @DataBase + '..tabSetor 	
				ON  ' + @DataBase + '..tabSalDetalhe.CodSala           = ' + @DataBase + '..tabSetor.CodSala 
				AND ' + @DataBase + '..tabSalDetalhe.CodSetor 		   = ' + @DataBase + '..tabSetor.CodSetor 
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
				' + @DataBase + '..tabCaixa
					ON	' + @DataBase + '..tabLancamento.codCaixa	   = ' + @DataBase + '..tabCaixa.codCaixa 
			INNER JOIN 
			' + @DataBase + '..tabforpagamento 
				ON ' + @DataBase + '..tabforpagamento.CodForPagto	   = ' + @DataBase + '..tabLancamento.CodForPagto
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
		and ' + @DataBase + '..tabLancamento.ValPagto > 0
		GROUP BY 
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto,
			' + @DataBase + '..tabforpagamento.tipcaixa,
			' + @DataBase + '..tabCaixa.descrCaixa


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
				descrCaixa
				
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
			@descrCaixa
				

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
				@OUTROSVALORES,
				@descrCaixa
				

		END


		Close C1
		Deallocate C1
		
		Select	
			descrCaixa as ''Venda'',
			count(1) as Quant,
			sum(preco) as Total
			
		from
			#TMP_RESUMO
		Group by
			descrCaixa		

		DROP TABLE #TMP_RESUMO
	END'
--print @query
exec (@query)





GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];9    Script Date: 08/28/2012 13:44:28 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
--SP_REL_BORDERO_VENDAS;9 '20110120','20110120',1,1,'19:00','CI_COLISEU'
ALTER  procedure [dbo].[SP_REL_BORDERO_VENDAS];9 	
	@DtIniApr varchar(8), 
	@DtFimApr varchar(8), 
	@codPeca int = null,
	@codSala int = null,	
	@hora varchar(6),
	@DataBase varchar(30)
as
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
			
			where	Indice = @Indice
				
	
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
exec (@query)







GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];10    Script Date: 08/28/2012 13:44:29 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
/* EQUIVALENTE AO ;1 (SOMENTE PARA TODOS OS SETORES OU RESUMIDO) */
ALTER    procedure [dbo].[SP_REL_BORDERO_VENDAS];10
	@Login			varchar(10),
	@CodPeca		int		= null,
	@CodSala		varchar(5)	= null,
	@DataIni		varchar(10)	= null,
	@DataFim		varchar(10)	= null,
	@HorSessao		varchar(10)	= null,
	@NomBase		varchar(30)	= null

 AS

/*
declare @Login			varchar(10)
declare @CodPeca		int 		
declare @DataIni		varchar(10)	
declare @DataFim		varchar(10)	
declare @HorSessao		varchar(10)	
declare @NomBase		varchar(30)	

set @Login	= 'clcruz'
set @CodPeca	= 11
set @DataIni	= '20040318'
set @DataFim	= '20040318'
set @HorSessao	= '21:00'
set @NomBase	= 'TSP_TAUGUSTA'
*/

declare @query varchar(8000)
declare @hora varchar(1000)
declare @horaAux2 varchar(1000)
declare @horaAux3 varchar(1000)
declare @comment varchar(10)

if @HorSessao = '' or @HorSessao = 'null' or @HorSessao is null or @HorSessao = '--'
begin
	set @hora = ''
	set @horaAux2 = ''
	set @horaAux3 = ''
	set @comment = ''
	if @HorSessao = '--'
	begin
		set @comment = '''--''--'
	end
end
else
begin
	set @hora = 'and	(tbAp.HorSessao = ''' + convert(varchar(5),@HorSessao) + ''' or ''' + convert(varchar(5),@HorSessao) + ''' is null)'
	set @horaAux2 = 'and	(tbAp.HorSessao = @horaAux or @horaAux is null)'
	set @horaAux3 = 'and	(tbA2.HorSessao = @horaAux or @horaAux is null)'
	set @comment = ''
end
set @query = 

'
declare @horaAux varchar(10)
declare @hora varchar(10)

declare @DtIAux Datetime
declare @DtI varchar(10)

declare @DtFAux Datetime
declare @DtF varchar(10)


set @hora = ''' + @HorSessao + '''
set @horaAux = case when @hora <> ''null'' then @hora else null end

set @DtI = ''' + @DataIni + '''
set @DtIAux = case when @DtI <> ''null'' then @DtI else null end

set @DtF = ''' + @DataFim + '''
set @DtFAux = case when @DtF <> ''null'' then @DtF else null end

set nocount on;

with resultado as (
 select
		tbAp.CodPeca,
		--tbAp.CodApresentacao,
		'+@comment+'tbAp.NumBordero
			NumBordero,
		tbPc.NomPeca,
		''TODOS'' NomSala,
		tbPc.NomResPeca,
		'+@comment+'tbAp.DatApresentacao
			DatApresentacao,
		'+@comment+'tbAp.HorSessao
			HorSessao,
		(select coalesce(count(Indice), 0) from ' + @NomBase + '..tabSalDetalhe tbSDet (nolock)
			inner join ' + @NomBase + '..tabApresentacao tbA2 on
				tbSDet.CodSala = tbA2.CodSala and 
				(tbA2.CodPeca = ' + convert(varchar(10),@CodPeca) + ' or ' + convert(varchar(10),@CodPeca) + ' is null)
				and (tbA2.DatApresentacao >= @DtIAux or @DtIAux is null)
				and (tbA2.DatApresentacao <= @DtFAux or @DtFAux is null)
				' + @horaAux3 + '
			where tbSDet.TipObjeto <> ''I'') as Lugares,
		(select coalesce(count(Indice), 0) from ' + @NomBase + '..tabLugSala (nolock)
			where		CodApresentacao = tbAp.CodApresentacao
				AND	StaCadeira = ''V'') as PubTotal,
		(select coalesce(count(tbLSl.Indice),0)
			from ' + @NomBase + '..tabLugSala tbLSl (nolock)
			inner join ' + @NomBase + '..tabTipBilhete (nolock)
				on	tbLSl.CodTipBilhete = ' + @NomBase + '..tabTipBilhete.CodTipBilhete
			where		' + @NomBase + '..tabTipBilhete.PerDesconto < 100
				AND	tbLSl.CodApresentacao = tbAp.CodApresentacao
				AND	tbLSl.CodVenda IS NOT NULL) as Pagantes,
		(select round(Coalesce(sum(ValPagto), 0), 2)
			from ' + @NomBase + '..tabLancamento (nolock)
			where CodApresentacao = tbAp.CodApresentacao) as ValVendas

	from ' + @NomBase + '..tabApresentacao tbAp (nolock)

	inner join ' + @NomBase + '..tabPeca tbPc (nolock)
		on	tbPc.CodPeca = tbAp.CodPeca

	--inner join ' + @NomBase + '..tabSala tbSl (nolock)
		--on	tbSl.CodSala = tbAp.CodSala

	--Inner join acrescentado

	--inner join tspweb.dbo.tabItemAcessoConc iac (nolock)
		--on		iac.CodPeca = tbAp.CodPeca
			--and	Login = ''' + @Login + '''

	--INNER JOIN tspweb.dbo.tabAcessoConcedido AC (NOLOCK) ON 
	--	IAC.LOGIN = AC.LOGIN 
	--	AND IAC.SENHA = AC.SENHA

	where		(tbAp.CodPeca = ' + convert(varchar(10),@CodPeca) + ' or ' + convert(varchar(10),@CodPeca) + ' is null)
--		and		(tbSl.CodSala = ' + convert(varchar(10),@CodSala) + ' or ' + convert(varchar(10),@CodSala) + ' is null)
		and	(convert(varchar(10), tbAp.DatApresentacao, 112) between  ''' + @DataIni + ''' and ''' + @DataFim + ''' or ''' + @DataIni + ''' is null)
		' + @hora + '
		and 	(tbAp.DatApresentacao >= @DtIAux or @DtIAux is null)
		and 	(tbAp.DatApresentacao <= @DtFAux or @DtFAux is null)
		' + @horaAux2 + '
--		and 	(AC.NOMBASEDADOS = ''' + @NomBase + ''' or ''' + @NomBase + ''' is null)
)
select 
	CodPeca,'
if @CodSala = 'TODOS'
begin
	set @query = @query + '
	max(NumBordero) NumBordero,'
end
else
begin
	set @query = @query + '
	NumBordero,'
end
set @query = @query + '
	NomPeca,
	NomSala,
	NomResPeca,
	DatApresentacao,
	HorSessao,
	Lugares,
	sum(PubTotal) PubTotal,
	sum(Pagantes) Pagantes,
	sum(ValVendas) ValVendas
from resultado
group by
	CodPeca,'
if @CodSala <> 'TODOS'
begin
	set @query = @query + '
	NumBordero,'
end
set @query = @query + '
	NomPeca,
	NomSala,
	NomResPeca,
	DatApresentacao,
	HorSessao,
	Lugares
order by	CodPeca,
		DatApresentacao,
		HorSessao'
--print (@query)
exec (@query)






GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];11    Script Date: 08/28/2012 13:44:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
/* EQUIVALENTE AO ;5 (SOMENTE PARA TODOS OS SETORES) */
ALTER    PROCEDURE [dbo].[SP_REL_BORDERO_VENDAS];11
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
		--and	(' + @DataBase + '..tabSala.codsala = ' + convert(varchar(6),@codSala) + ' or ' + convert(varchar(6),@codSala) + ' is null)
		AND	not exists (Select 1 from ' + @DataBase + '..tabLancamento bb
					where ' + @DataBase + '..tabLancamento.numlancamento = bb.numlancamento
					  and ' + @DataBase + '..tabLancamento.codtipbilhete = bb.codtipbilhete
					  and bb.codtiplancamento = 2
					  and ' + @DataBase + '..tabLancamento.codapresentacao = bb.codapresentacao
					  and ' + @DataBase + '..tabLancamento.indice          = bb.indice)
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
	END
/*ELSE
	Select
			'''' as forpagto,
			0 as qtdbilh,
			0 as totfat*/'

--print @query
exec (@query)





GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];12    Script Date: 08/28/2012 13:44:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
/* EQUIVALENTE AO ;9 (SOMENTE PARA TODOS OS SETORES) */
--SP_REL_BORDERO_VENDAS;12 '20110120','20110120',1,TODOS,'19:00','CI_COLISEU'
ALTER  procedure [dbo].[SP_REL_BORDERO_VENDAS];12
	@DtIniApr varchar(8), 
	@DtFimApr varchar(8), 
	@codPeca int = null,
	@codSala varchar(5) = null,	
	@horaSessao varchar(6),
	@DataBase varchar(30)
as
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
	@ds_canal_venda	varchar(20),
	@CodApresentacao int	

	set nocount on
	BEGIN

		SELECT  
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabforpagamento.tipcaixa,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLugSala.CodApresentacao,
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
		' + @hora + '
		and	(' + @DataBase + '..tabApresentacao.codpeca = ' + convert(varchar(6),@codPeca) + ' or ' + convert(varchar(6),@codPeca) + ' is null)		
		--and	(' + @DataBase + '..tabSala.codsala = ' + convert(varchar(6),@codSala) + ' or ' + convert(varchar(6),@codSala) + ' is null)
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
			' + @DataBase + '..tabLugSala.CodApresentacao,
			' + @DataBase + '..tabLancamento.ValPagto,
			' + @DataBase + '..tabforpagamento.tipcaixa,
			ci_middleway..mw_canal_venda.ds_canal_venda


		declare C1 cursor for
			SELECT  
				CodTipBilhete,
				TipBilhete, 
				DatMovimento,
				NomSetor,
				CodApresentacao,
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
			@codapresentacao,
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
exec (@query)






GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];13    Script Date: 08/28/2012 13:44:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
/* EQUIVALENTE AO ;3 (SOMENTE PARA TODOS OS SETORES E RESUMIDO) */
ALTER  procedure [dbo].[SP_REL_BORDERO_VENDAS];13
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
	@codapresentacao int
IF EXISTS
	(SELECT 1 FROM
		' + @DataBase + '..tabLugSala 
		INNER JOIN 
		' + @DataBase + '..tabApresentacao 
			ON  ' + @DataBase + '..tabLugSala.CodApresentacao      = ' + @DataBase + '..tabApresentacao.CodApresentacao 
		WHERE   
			' + @DataBase + '..tabLugSala.CodVenda IS NOT NULL
		AND 	' + @DataBase + '..tabApresentacao.CodPeca = ' + convert(varchar(10), @CodPeca) + '
		AND 	(convert(varchar(8), ' + @DataBase + '..tabApresentacao.DatApresentacao,112) between ''' + @DtIniApr + ''' and ''' + @DtFimApr + ''')
		' + @hora + ')

	BEGIN

		SELECT  
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto as Preco,
			' + @DataBase + '..tabLugSala.CodApresentacao,
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
			' + @DataBase + '..tabLancamento 
				ON  ' + @DataBase + '..tabTipBilhete.CodTipBilhete     = ' + @DataBase + '..tabLancamento.CodTipBilhete 
				AND ' + @DataBase + '..tabSalDetalhe.Indice            = ' + @DataBase + '..tabLancamento.Indice 
				AND ' + @DataBase + '..tabApresentacao.CodApresentacao = ' + @DataBase + '..tabLancamento.CodApresentacao
				AND ' + @DataBase + '..tabLancamento.CodTipLancamento  = 1
			LEFT JOIN
			' + @DataBase + '..tabIngressoAgregados
				ON  ' + @DataBase + '..tabIngressoAgregados.codvenda   = ' + @DataBase + '..tabLugSala.codvenda
				and ' + @DataBase + '..tabIngressoAgregados.indice     = ' + @DataBase + '..tabLugSala.indice
		WHERE
			(' + @DataBase + '..tabLugSala.CodVenda IS NOT NULL) 
		AND 	' + @DataBase + '..tabApresentacao.CodPeca = ' + convert(varchar(10), @CodPeca) + '
		AND 	(convert(varchar(8), ' + @DataBase + '..tabApresentacao.DatApresentacao,112) between ''' + @DtIniApr + ''' and ''' + @DtFimApr + ''')
		' + @hora + '
		AND	not exists (Select 1 from ' + @DataBase + '..tabLancamento bb
					where ' + @DataBase + '..tabLancamento.numlancamento = bb.numlancamento
					  and ' + @DataBase + '..tabLancamento.codtipbilhete = bb.codtipbilhete
					  and bb.codtiplancamento = 2
					  and ' + @DataBase + '..tabLancamento.codapresentacao = bb.codapresentacao
					  and ' + @DataBase + '..tabLancamento.indice          = bb.indice)
		GROUP BY 
			' + @DataBase + '..tabLugSala.CodTipBilhete,
			' + @DataBase + '..tabTipBilhete.TipBilhete, 
			' + @DataBase + '..tabLancamento.DatMovimento,
			' + @DataBase + '..tabSetor.NomSetor,
			' + @DataBase + '..tabLugSala.Indice,
			' + @DataBase + '..tabLancamento.ValPagto,
			' + @DataBase + '..tabLugSala.CodApresentacao


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


		Select	CodTipBilhete,
			TipBilhete, 
			NomSetor,
			count(1) as Qtde,
			round(Preco,2) as Preco,
			round(sum(Preco),2) as Total
		from
			#TMP_RESUMO
		Group by
			CodTipBilhete,
			TipBilhete, 
			NomSetor,
			Preco
		order by TipBilhete


		DROP TABLE #TMP_RESUMO


	END

ELSE

	SELECT 
		0  as CodTipBilhete,
		''Não houve vendas'' as tipBilhete, 
		''Não houve vendas'' as NomSetor, 
		0 AS Qtde, 
		0 AS Preco, 
		0 AS Total'
--print @query
exec (@query)







GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];14    Script Date: 08/28/2012 13:44:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
/* EQUIVALENTE AO ;10 (SOMENTE PARA OBTER O NÚMERO DOS BORDERÔS) */
ALTER    procedure [dbo].[SP_REL_BORDERO_VENDAS];14
	@Login			varchar(10),
	@CodPeca		int		= null,
	@CodSala		varchar(5)	= null,
	@DataIni		varchar(10)	= null,
	@DataFim		varchar(10)	= null,
	@HorSessao		varchar(10)	= null,
	@NomBase		varchar(30)	= null

 AS

declare @query varchar(8000)
declare @hora varchar(1000)
declare @horaAux2 varchar(1000)

if @HorSessao = '' or @HorSessao = 'null' or @HorSessao is null or @HorSessao = '--'
begin
	set @hora = ''
	set @horaAux2 = ''
end
else
begin
	set @hora = 'and	(tbAp.HorSessao = ''' + convert(varchar(5),@HorSessao) + ''' or ''' + convert(varchar(5),@HorSessao) + ''' is null)'
	set @horaAux2 = 'and	(tbAp.HorSessao = @horaAux or @horaAux is null)'
end
set @query = 

'
declare @horaAux varchar(10)
declare @hora varchar(10)

declare @DtIAux Datetime
declare @DtI varchar(10)

declare @DtFAux Datetime
declare @DtF varchar(10)


set @hora = ''' + @HorSessao + '''
set @horaAux = case when @hora <> ''null'' then @hora else null end

set @DtI = ''' + @DataIni + '''
set @DtIAux = case when @DtI <> ''null'' then @DtI else null end

set @DtF = ''' + @DataFim + '''
set @DtFAux = case when @DtF <> ''null'' then @DtF else null end

set nocount on;

select
		distinct tbAp.NumBordero
	from ' + @NomBase + '..tabApresentacao tbAp (nolock)

	inner join ' + @NomBase + '..tabPeca tbPc (nolock)
		on	tbPc.CodPeca = tbAp.CodPeca

	where		(tbAp.CodPeca = ' + convert(varchar(10),@CodPeca) + ' or ' + convert(varchar(10),@CodPeca) + ' is null)
		and	(convert(varchar(10), tbAp.DatApresentacao, 112) between  ''' + @DataIni + ''' and ''' + @DataFim + ''' or ''' + @DataIni + ''' is null)
		' + @hora + '
		and 	(tbAp.DatApresentacao >= @DtIAux or @DtIAux is null)
		and 	(tbAp.DatApresentacao <= @DtFAux or @DtFAux is null)
		' + @horaAux2 + '
order by NumBordero'
--print (@query)
exec (@query)