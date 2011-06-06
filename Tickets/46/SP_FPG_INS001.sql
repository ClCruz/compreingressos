SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS ON 
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[SP_FPG_INS001]') and OBJECTPROPERTY(id, N'IsProcedure') = 1)
drop procedure [dbo].[SP_FPG_INS001]
GO

/*
'+=================================================================================================================+'
'!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
'!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
'+========+=============+============+=================+===========================================================+'
'!   1    ! 542         ! 05/07/2004 ! Alex Gobbato    ! Inserir a taxa de administração                           !
'+--------+-------------+------------+-----------------+-----------------------------------------------------------+
'!   2    ! 46          ! 01/06/2011 ! Jefferson Ferre ! Incluir campo de Prazo de Repasse em dias                !
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

CREATE PROCEDURE DBO.SP_FPG_INS001
@CodTipForPagto smallint,
@ForPagto varchar(30),
@CodBanco varchar(3),
@StaForPagto varchar(1),
@TipCaixa char(1),
@TxAdministracao money,
@PrzRepasseDias int
As
DECLARE @CodForPagto smallint
 SELECT @CodForPagto = COALESCE((SELECT MAX(CodForPagto) FROM tabForPagamento),0)+1
 INSERT INTO tabForPagamento (CodForPagto, CodTipForPagto, ForPagto, CodBanco, StaForPagto, TipCaixa, PcTxAdm, PrzRepasseDias)
  VALUES  (@CodForPagto, @CodTipForPagto, @ForPagto, @CodBanco, @StaForPagto, @TipCaixa, @TxAdministracao, @PrzRepasseDias)

GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS ON 
GO

