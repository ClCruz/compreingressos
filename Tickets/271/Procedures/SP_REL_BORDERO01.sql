/****** Object:  StoredProcedure [dbo].[SP_REL_BORDERO_VENDAS]    Script Date: 08/28/2012 13:44:24 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[DBO].[SP_REL_BORDERO01]') AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1)
DROP PROCEDURE [DBO].[SP_REL_BORDERO01]
GO

CREATE PROCEDURE [dbo].[SP_REL_BORDERO01]
	@Login			varchar(10),
	@CodPeca		int		= null,
	@CodSala		int		= null,
	@DataIni		varchar(10)	= null,
	@DataFim		varchar(10)	= null,
	@HorSessao		varchar(10)	= null,
	@NomBase		varchar(30)	= null

 AS

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
