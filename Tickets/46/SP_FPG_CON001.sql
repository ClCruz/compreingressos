SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS ON 
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[SP_FPG_CON001]') and OBJECTPROPERTY(id, N'IsProcedure') = 1)
drop procedure [dbo].[SP_FPG_CON001]
GO
/*
'+=================================================================================================================+'
'!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
'!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
'+========+=============+============+=================+===========================================================+'
'!  1     ! 542         ! 05/07/2004 ! Alex Gobbato    ! Retornar a tx de administração                            !
'+--------+-------------+------------+-----------------+-----------------------------------------------------------+
'!  2     ! 46          ! 01/06/2011 ! Jefferson Ferre ! Retornar o Prazo de Repasse em dias                       !
'+--------+-------------+------------+-----------------+-----------------------------------------------------------+
'!        !             !            !                 !                                                           !
'+--------+-------------+------------+-----------------+-----------------------------------------------------------+
'!        !             !            !                 !                                                           !
'+--------+-------------+------------+-----------------+-----------------------------------------------------------+
'!        !             !            !                 !                                                           !
'+--------+-------------+------------+-----------------+-----------------------------------------------------------+
'!        !             !            !                 !                                                           !
'+========+=============+============+=================+===========================================================+'
*/
CREATE PROCEDURE dbo.SP_FPG_CON001
 @StaForPagto char(1),
 @TipCaixa char(1)
As

if @TipCaixa is null
 SELECT tabForPagamento.CodForPagto, tabForPagamento.ForPagto, tabTipForPagamento.CodTipForPagto,
	tabTipForPagamento.TipForPagto, tabForPagamento.StaForPagto, tabForPagamento.TipCaixa, tabForPagamento.PcTxAdm,
	tabForPagamento.PrzRepasseDias
  FROM tabForPagamento 
  	INNER JOIN tabTipForPagamento 
		ON tabForPagamento.CodTipForPagto = tabTipForPagamento.CodTipForPagto
  	WHERE (StaForPagto = @StaForPagto) --AND (tabForPagamento.TipCaixa = @TipCaixa OR @TipCaixa is null )
  ORDER BY ForPagto

else

 SELECT tabForPagamento.CodForPagto, tabForPagamento.ForPagto, tabTipForPagamento.CodTipForPagto,
	tabTipForPagamento.TipForPagto, tabForPagamento.StaForPagto, tabForPagamento.TipCaixa, tabForPagamento.PcTxAdm,
	tabForPagamento.PrzRepasseDias
  FROM tabForPagamento 
  	INNER JOIN tabTipForPagamento 
		ON tabForPagamento.CodTipForPagto = tabTipForPagamento.CodTipForPagto
  	WHERE (StaForPagto = @StaForPagto) AND (tabForPagamento.TipCaixa = @TipCaixa or tabForPagamento.TipCaixa = 'A')
  ORDER BY ForPagto
GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS ON 
GO
