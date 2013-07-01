/****** Object:  NumberedStoredProcedure [dbo].[SP_REL_BORDERO_VENDAS];14    Script Date: 08/28/2012 13:44:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[DBO].[SP_REL_BORDERO14]') AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1)
DROP PROCEDURE [DBO].[SP_REL_BORDERO14]
GO

/* EQUIVALENTE AO ;10 (SOMENTE PARA OBTER O NÚMERO DOS BORDERÔS) */
CREATE    PROCEDURE [dbo].[SP_REL_BORDERO14]
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