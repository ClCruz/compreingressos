SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (
		SELECT *
		FROM DBO.SYSOBJECTS
		WHERE ID = OBJECT_ID(N'[DBO].[SP_CON_INGRESSO_EXCEDIDO]')
			AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1
		)
	DROP PROCEDURE [DBO].[SP_CON_INGRESSO_EXCEDIDO]
GO

/*
+=================================================================================================================+'
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!   1    !     #336    ! 12/09/2013 ! Edicarlos S. B. ! Proc. Utilizada p/ Relatório Borderô de Vendas implemen-  !
!		 !			   !			!				  !	tado em PHP.											  !	
+========+=============+============+=================+===========================================================+'
!        !             !            !                 !                                              		      !
+=================================================================================================================+
*/
CREATE PROCEDURE [dbo].[SP_CON_INGRESSO_EXCEDIDO] (
	@CodTipBilhete INT
	,@DataBase VARCHAR(30)
	)
AS
DECLARE @Query VARCHAR(500)

SET @Query = 'SET NOCOUNT ON
		SELECT TOP 1 1 IsIngressoExcedido
		FROM ' + @DataBase + '..tabTipDebBordero 
		WHERE CodTipBilhete = ' + CONVERT(VARCHAR, @CodTipBilhete)

EXECUTE (@Query)
GO