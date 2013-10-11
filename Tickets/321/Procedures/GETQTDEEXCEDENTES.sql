SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (
		SELECT *
		FROM sys.objects
		WHERE object_id = OBJECT_ID(N'[dbo].[GETQTDEEXCEDENTES]')
			AND type IN (N'FN',N'IF',N'TF',N'FS',N'FT')
		)
	DROP FUNCTION [dbo].[GETQTDEEXCEDENTES]
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

CREATE FUNCTION DBO.GETQTDEEXCEDENTES (
	@DATAPRESENTACAO DATETIME
	,@HORSESSAO VARCHAR(5)
	,@QTDLIMITEINGRPARAVENDA INT
	)
RETURNS INT
AS
BEGIN
	DECLARE @RESULT INT

	SELECT @RESULT = 0

	SELECT DISTINCT 
		@RESULT = SUM(QTDE) 
	FROM TMP_RESUMO_AUX T2 
	WHERE  
		T2.DATA_APRESENTACAO = @DATAPRESENTACAO 
		AND T2.HORSESSAO = @HORSESSAO 		
	GROUP BY T2.DATA_APRESENTACAO, 
		T2.HORSESSAO,
		T2.CANAL_VENDA	

	IF @RESULT > @QTDLIMITEINGRPARAVENDA
		SET @RESULT = @RESULT - @QTDLIMITEINGRPARAVENDA 

	RETURN @RESULT
END
