SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (
		SELECT *
		FROM sys.objects
		WHERE object_id = OBJECT_ID(N'[dbo].[GETLUGARES]')
			AND type IN (
				N'FN'
				,N'IF'
				,N'TF'
				,N'FS'
				,N'FT'
				)
		)
	DROP FUNCTION [dbo].[GETLUGARES]
GO

/*
+=================================================================================================================+'
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!   1    !     #336    ! 08/10/2013 ! Edicarlos S. B. ! Função Utilizada p/ Procedure SP_REL_CONSOLIDADO_LIQUIDO. !
+========+=============+============+=================+===========================================================+'
!        !             !            !                 !                                              		      !
+=================================================================================================================+
*/
CREATE FUNCTION DBO.GETLUGARES (
	@CODPECA SMALLINT
	,@DTIAUX SMALLDATETIME
	,@DTFAUX SMALLDATETIME
	,@HORAUX VARCHAR(5)
	,@CODSALA SMALLINT
	)
RETURNS INT
AS
BEGIN
	DECLARE @RESULT INT
		,@CODSALAMIN SMALLINT
		,@CODSALAMAX SMALLINT

	IF (@CODSALA IS NULL)
	BEGIN
		SET @CODSALAMIN = 254
		SET @CODSALAMAX = 0
	END

	SELECT @RESULT = COALESCE(COUNT(INDICE), 0)
	FROM TABSALDETALHE TBSDET
	INNER JOIN TABAPRESENTACAO TBA2 ON (
			TBSDET.CODSALA = TBA2.CODSALA
			OR (
				TBA2.CODSALA >= @CODSALAMIN
				AND TBA2.CODSALA <= @CODSALAMAX
				)
			)
		AND (
			TBA2.CODPECA = CONVERT(VARCHAR(10), @CODPECA)
			OR CONVERT(VARCHAR(10), @CODPECA) IS NULL
			)
		AND (
			TBA2.DATAPRESENTACAO >= @DTIAUX
			OR @DTIAUX IS NULL
			)
		AND (
			TBA2.DATAPRESENTACAO <= @DTFAUX
			OR @DTFAUX IS NULL
			)
		AND (
			TBA2.HORSESSAO = @HORAUX
			OR @HORAUX IS NULL
			)
	WHERE TBSDET.TIPOBJETO <> 'I'
		AND (
			TBSDET.CODSALA = @CODSALA
			OR @CODSALA IS NULL
			)

	RETURN @RESULT
END
