GO
/****** Object:  StoredProcedure [dbo].[SP_PEC_CON009]    Script Date: 04/18/2011 14:47:23 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO


/*
'+=================================================================================================================+
'!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
'!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
'+========+=============+============+=================+===========================================================+
'!    01  !  1129       ! 28/07/2006 ! Domingo Matte   ! pesquisa para todas as apresentações, horarios            !
'!--------+-------------+------------+-----------------+-----------------------------------------------------------!
'!    02  !  1141       ! 17/08/2006 ! Domingo Matte   ! Listar pro um periodo de datas                            !
'!--------+-------------+------------+-----------------+-----------------------------------------------------------!
'!    03  !  1239       ! 05/03/2007 ! Marciano Carvalh! TSPWeb - Erro relatório Bordero de Vendas-Duplica registro!
'!--------+-------------+------------+-----------------+-----------------------------------------------------------!
'!    04  !  3101       ! 23/08/2007 ! Marciano C.S    ! Inner join para filtrar somente as peças que o login tem  !
'!        !             !            !                 ! acesso na procedure dbo.SP_PEC_CON009;4		 		   !
'!--------+-------------+------------+-----------------+-----------------------------------------------------------!
'!    05  !  1361       ! 28/01/2008 ! Alex Gobbato    ! Acrescido mais um join para filtrar as peças que o login  ! 
'!		  !             !			 !			       ! tem acessona procedure dbo.SP_PEC_CON009;4							   !
'!        !             !            !                 ! e acrescentado o parametro @NomBase                       !
'!--------+-------------+------------+-----------------+-----------------------------------------------------------!
'!    06  !  XXXX       ! 18/04/2011 ! Gabriel Monteiro! compatibilidade com o middleway                           !
'!--------+-------------+------------+-----------------+-----------------------------------------------------------!
'!    07  !  335        ! 20/09/2013 ! Gabriel Monteiro! só exibir o evento no combo do relat. de borderô qdo mw_evento.in_ver_no_bordero=1
'+========+=============+============+=================+===========================================================+
*/


/*

            carrega todas as pecas...

*/

ALTER procedure [dbo].[SP_PEC_CON009]

            @Login                        varchar(10),

            @CodPeca                   smallint = null

as

 

            select tbPc.CodPeca, tbPc.nomPeca

            from tabApresentacao tbAp (nolock)

            inner join tabPeca tbPc (nolock)

                        on        tbPc.CodPeca = tbAp.CodPeca

            inner join tspweb.dbo.tabItemAcessoConc iac (nolock)

                        on                    iac.CodPeca = tbAp.CodPeca

                                   and       Login = @Login

 

            where   (tbPc.CodPeca = @CodPeca or @CodPeca is null)

            group by tbPc.CodPeca, tbPc.NomPeca

            order by tbPc.NomPeca



GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_PEC_CON009];2    Script Date: 04/18/2011 14:47:24 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

/*

            carrega todas as datas...

*/

ALTER procedure [dbo].[SP_PEC_CON009];2

            @Login                        varchar(10),

            @CodPeca                   smallint = null,

            @DatApresentacao       varchar(10)        = null

as

 

            select tbAp.DatApresentacao

            from tabApresentacao tbAp (nolock)

            inner join tabPeca tbPc (nolock)

                        on        tbPc.CodPeca = tbAp.CodPeca

            inner join tspweb.dbo.tabItemAcessoConc iac (nolock)

                        on                    iac.CodPeca = tbAp.CodPeca

                                   and       Login = @Login

 

            where               (tbPc.CodPeca = @CodPeca or @CodPeca is null)

                        and       (convert(varchar(10), tbAp.DatApresentacao, 112) = @DatApresentacao or @DatApresentacao is null)

            group by tbAp.DatApresentacao

            order by tbAp.DatApresentacao


GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_PEC_CON009];3    Script Date: 04/18/2011 14:47:25 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

/*

            carrega todos os horarios...

*/

ALTER procedure [dbo].[SP_PEC_CON009];3

            @Login                        varchar(10),

            @CodPeca                   smallint = null,

            @DatApresentacao       varchar(10)        = null

as

 

            select HorSessao

            from tabApresentacao tbAp (nolock)

            inner join tabPeca tbPc (nolock)

                        on        tbPc.CodPeca = tbAp.CodPeca

            inner join tspweb.dbo.tabItemAcessoConc iac (nolock)

                        on                    iac.CodPeca = tbAp.CodPeca

                                   and       Login = @Login

 

            where               (tbPc.CodPeca = @CodPeca or @CodPeca is null)

                        and       (convert(varchar(10), tbAp.DatApresentacao, 112) = @DatApresentacao or @DatApresentacao is null)

            group by tbAp.HorSessao

            order by tbAp.HorSessao

GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_PEC_CON009];4    Script Date: 04/18/2011 14:47:25 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO


ALTER procedure [dbo].[SP_PEC_CON009];4
	@Login			varchar(10),
	@CodPeca		int		= null,
	@DataIni		varchar(10)	= null,
	@DataFim		varchar(10)	= null,
	@HorSessao		char(5)		= null,
	@NomBase		Varchar(30)	= null

 AS

	select	tbAp.CodPeca,
		tbAp.CodApresentacao,
		tbAp.NumBordero,
		tbPc.NomPeca,
		tbSl.NomSala,
		tbPc.NomResPeca,
		tbAp.DatApresentacao,
		tbAp.HorSessao,
		(select coalesce(count(Indice), 0) from tabSalDetalhe tbSDet (nolock)
			where		tbSDet.CodSala = tbAp.CodSala
				and	tbSDet.TipObjeto <> 'I') as Lugares,
		(select coalesce(count(Indice), 0) from tabLugSala (nolock)
			where		CodApresentacao = tbAp.CodApresentacao
				AND	StaCadeira = 'V') as PubTotal,
		(select coalesce(count(tbLSl.Indice),0)
			from tabLugSala tbLSl (nolock)
			inner join tabTipBilhete (nolock)
				on	tbLSl.CodTipBilhete = tabTipBilhete.CodTipBilhete
			where		tabTipBilhete.PerDesconto < 100
				AND	tbLSl.CodApresentacao = tbAp.CodApresentacao
				AND	tbLSl.CodVenda IS NOT NULL) as Pagantes,
		(select round(Coalesce(sum(ValPagto), 0), 2)
			from tabLancamento (nolock)
			where CodApresentacao = tbAp.CodApresentacao) as ValVendas

	from tabApresentacao tbAp (nolock)

	inner join tabPeca tbPc (nolock)
		on	tbPc.CodPeca = tbAp.CodPeca

	inner join tabSala tbSl (nolock)
		on	tbSl.CodSala = tbAp.CodSala

	--Inner join acrescentado

	inner join tspweb.dbo.tabItemAcessoConc iac (nolock)
		on		iac.CodPeca = tbAp.CodPeca
			and	Login = @Login

	INNER JOIN tspweb.dbo.tabAcessoConcedido AC (NOLOCK) ON 
		IAC.LOGIN = AC.LOGIN 
		AND IAC.SENHA = AC.SENHA

	where		(tbAp.CodPeca = @CodPeca or @CodPeca is null)
		and	(convert(varchar(10), tbAp.DatApresentacao, 112) between  @DataIni and @DataFim or @DataIni is null)
		and	(tbAp.HorSessao = @HorSessao or @HorSessao is null)
		and 	(AC.NOMBASEDADOS = @NomBase or @NomBase is null)
	order by	tbAp.CodPeca,
			tbAp.DatApresentacao,
			tbAp.HorSessao


GO
/****** Object:  StoredProcedure [dbo].[SP_PEC_CON009];5    Script Date: 04/18/2011 14:47:23 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
/*
	equivalente a procedure ;1 para mo middleway
*/
ALTER procedure [dbo].[SP_PEC_CON009];5
            @idMW       varchar(10),
			@idBase		int,
            @CodPeca    smallint = null
as
            select tbPc.CodPeca, tbPc.nomPeca
            from tabApresentacao tbAp (nolock)
            inner join tabPeca tbPc (nolock)
                        on       tbPc.CodPeca = tbAp.CodPeca
            inner join ci_middleway..mw_acesso_concedido iac (nolock)
                        on                    iac.id_base = @idBase
								and			  iac.id_usuario = @idMW
								and			  iac.CodPeca = tbAp.CodPeca
            where   (tbPc.CodPeca = @CodPeca or @CodPeca is null)
            group by tbPc.CodPeca, tbPc.NomPeca
            order by tbPc.NomPeca

GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_PEC_CON009];6    Script Date: 04/18/2011 14:47:24 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

/*
	equivalente a procedure ;2 para mo middleway
*/
ALTER procedure [dbo].[SP_PEC_CON009];6
            @idMW       varchar(10),
			@idBase		int,
            @CodPeca                   smallint = null,
            @DatApresentacao       varchar(10)        = null
as
            select tbAp.DatApresentacao
            from tabApresentacao tbAp (nolock)
            inner join tabPeca tbPc (nolock)
                        on        tbPc.CodPeca = tbAp.CodPeca
            inner join ci_middleway..mw_acesso_concedido iac (nolock)
                        on                    iac.id_base = @idBase
								and			  iac.id_usuario = @idMW
								and			  iac.CodPeca = tbAp.CodPeca
            where               (tbPc.CodPeca = @CodPeca or @CodPeca is null)
                        and       (convert(varchar(10), tbAp.DatApresentacao, 112) = @DatApresentacao or @DatApresentacao is null)
            group by tbAp.DatApresentacao
            order by tbAp.DatApresentacao


GO
/****** Object:  NumberedStoredProcedure [dbo].[SP_PEC_CON009];7    Script Date: 04/18/2011 14:47:25 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

/*
	equivalente a procedure ;3 para mo middleway
*/
ALTER procedure [dbo].[SP_PEC_CON009];7
            @idMW       varchar(10),
			@idBase		int,
            @CodPeca                   smallint = null,
            @DatApresentacao       varchar(10)        = null
as
            select HorSessao
            from tabApresentacao tbAp (nolock)
            inner join tabPeca tbPc (nolock)
                        on        tbPc.CodPeca = tbAp.CodPeca
            inner join ci_middleway..mw_acesso_concedido iac (nolock)
                        on                    iac.id_base = @idBase
								and			  iac.id_usuario = @idMW
								and			  iac.CodPeca = tbAp.CodPeca
            where               (tbPc.CodPeca = @CodPeca or @CodPeca is null)
                        and       (convert(varchar(10), tbAp.DatApresentacao, 112) = @DatApresentacao or @DatApresentacao is null)
            group by tbAp.HorSessao
            order by tbAp.HorSessao
            
GO          

/****** Object:  StoredProcedure [dbo].[SP_PEC_CON009];8    Script Date: 04/18/2011 14:47:23 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
/*
      equivalente a procedure ;5 para o middleway (+ filtro com o campo in_ver_no_bordero)
*/
create procedure [dbo].[SP_PEC_CON009];8
            @idMW       varchar(10),
                  @idBase           int,
            @CodPeca    smallint = null
as

            select tbPc.CodPeca, tbPc.nomPeca
            from tabApresentacao tbAp (nolock)
            inner join tabPeca tbPc (nolock)
                        on       tbPc.CodPeca = tbAp.CodPeca
            inner join ci_middleway..mw_acesso_concedido iac (nolock)
                        on                    iac.id_base = @idBase
                                                and                 iac.id_usuario = @idMW
                                                and                 iac.CodPeca = tbAp.CodPeca
                  inner join ci_middleway..mw_evento e (nolock)
                                    on                              e.id_base = @idBase
                                                and                 e.CodPeca = iac.CodPeca
                                                and                 e.in_ver_no_bordero = 1
            where   (tbPc.CodPeca = @CodPeca or @CodPeca is null)
            group by tbPc.CodPeca, tbPc.NomPeca
            order by tbPc.NomPeca

GO