SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (
		SELECT *
		FROM sys.objects
		WHERE object_id = OBJECT_ID(N'[dbo].[GETINGEXCEDENTES]')
			AND type IN (N'FN',N'IF',N'TF',N'FS',N'FT')
		)
	DROP FUNCTION [dbo].[GETINGEXCEDENTES]
GO

/*
+=================================================================================================================+'
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!   1    !     #336    ! 16/09/2013 ! Edicarlos S. B. ! Funcão Utilizada p/ Proc. SP_REL_BORDERO_COMPLETO.		  !
+========+=============+============+=================+===========================================================+'
!        !             !            !                 !                                              		      !
+=================================================================================================================+
*/


CREATE FUNCTION GETINGEXCEDENTES (
	@CodTipBilhete SMALLINT
	,@Valor FLOAT
	,@ValIngressoExcedente NUMERIC(10,2)
	,@QtdLimiteIngrParaVenda INT
	,@IngressosExcedidos INT
	)
RETURNS FLOAT
AS
BEGIN
	DECLARE @Result FLOAT
	
	IF @CodTipBilhete IS NULL
		BEGIN
		SET @Result = ROUND(@Valor,2)
		END		
	ELSE
		BEGIN					
		IF @IngressosExcedidos > @QtdLimiteIngrParaVenda
			BEGIN
			SET @Result = ((@IngressosExcedidos - @QtdLimiteIngrParaVenda) * ISNULL(@ValIngressoExcedente,0))		
			END
		ELSE
			SET @Result = 0				
		END 
	
	RETURN @Result
END
