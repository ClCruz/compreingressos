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
!   2    !    #321     ! 14/10/2013 ! Edicarlos S. B. ! Removido atribuição de @QTDE a @RESULT				      !
+=================================================================================================================+
*/

CREATE FUNCTION DBO.GETQTDEEXCEDENTES (
	@DATAPRESENTACAO DATETIME
	,@HORSESSAO VARCHAR(5)
	,@QTDLIMITEINGRPARAVENDA INT
	,@QTDE INT
	)
RETURNS INT
AS
BEGIN
	DECLARE @RESULT INT

	SELECT @RESULT = 0

	IF @QTDE > @QTDLIMITEINGRPARAVENDA
		SET @RESULT = @QTDE - @QTDLIMITEINGRPARAVENDA 

	RETURN @RESULT
END
