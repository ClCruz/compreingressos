SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS ON 
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[SP_FPG_UPD001]') and OBJECTPROPERTY(id, N'IsProcedure') = 1)
drop procedure [dbo].[SP_FPG_UPD001]
GO

/*
'+=================================================================================================================+'
'!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
'!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
'+========+=============+============+=================+===========================================================+'
'!   1    ! 542         ! 05/07/2004 ! Alex Gobbato    ! Alterar a taxa de administração                           !
'+--------+-------------+------------+-----------------+-----------------------------------------------------------+
'!   2    ! 46          ! 01/06/2011 ! Jefferson Ferre ! Incluir campo de  Prazo de Repasse em Dias                !
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

CREATE PROCEDURE DBO.SP_FPG_UPD001
@CodForPagto smallint,
@CodTipForPagto smallint,
@ForPagto varchar(30),
@CodBanco varchar(3),
@StaForPagto varchar(1),
@TipCaixa char(1),
@TxAdministracao money,
@PrzRepasseDias int
As
 UPDATE tabForPagamento SET CodTipForPagto = @CodTipForPagto, ForPagto = @ForPagto,
      CodBanco = @CodBanco, StaForPagto = @StaForPagto, TipCaixa = @TipCaixa , PcTxAdm = @TxAdministracao,
      PrzRepasseDias = @PrzRepasseDias 
  WHERE CodForPagto = @CodForPagto

GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS ON 
GO

