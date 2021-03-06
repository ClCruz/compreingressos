SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (
		SELECT *
		FROM sys.objects
		WHERE object_id = OBJECT_ID(N'[dbo].[GETDEBITO]')
			AND type IN (N'FN',N'IF',N'TF',N'FS',N'FT')
		)
	DROP FUNCTION [dbo].[GETDEBITO]
GO

/*
+=================================================================================================================+'
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!   1    !     #336    ! 18/09/2013 ! Edicarlos S. B. ! Função Utilizada p/ Procedure SP_REL_CONSOLIDADO_LIQUIDO. !
!        !             !            !                 ! Favor Não Alterar!!!									  !
+========+=============+============+=================+===========================================================+'
!        !             !            !                 !                                              		      !
+=================================================================================================================+
*/

CREATE FUNCTION [dbo].[GETDEBITO] (
	@CODTIPDEBBORDERO INT
	,@QTDEING FLOAT
	,@PRECO MONEY
	)
RETURNS FLOAT
AS
BEGIN
	DECLARE @RESULT FLOAT
		,@TIPOVALOR CHAR(1)
		,@VALOR FLOAT

	SELECT @TIPOVALOR = TTDB.TIPVALOR
		,@VALOR = TTDB.PERDESCONTO
	FROM TABTIPDEBBORDERO TTDB
	WHERE TTDB.CODTIPDEBBORDERO = @CODTIPDEBBORDERO

	IF @TIPOVALOR = 'P'
		SET @RESULT = COALESCE(@VALOR * @PRECO / 100, 0)

	IF @TIPOVALOR = 'V'
		SET @RESULT = COALESCE(@VALOR * @QTDEING, 0)

	IF @TIPOVALOR = 'F'
		SET @RESULT = @VALOR

	RETURN @RESULT
END
