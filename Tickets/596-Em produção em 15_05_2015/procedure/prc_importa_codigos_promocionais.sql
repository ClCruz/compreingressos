SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[prc_importa_codigos_promocionais]') and OBJECTPROPERTY(id, N'IsProcedure') = 1)
drop procedure [dbo].[prc_importa_codigos_promocionais]
GO

CREATE PROCEDURE prc_importa_codigos_promocionais
	@file_path VARCHAR(200),
	@id_evento INT,
	@codtippromocao SMALLINT,
	@ds_promocao VARCHAR(60)
AS
BEGIN

	SET NOCOUNT ON;
	
	DECLARE
		@success BIT,
		@error VARCHAR(2000),
		@query_string VARCHAR(2000);
	
	BEGIN TRY
	
		CREATE TABLE #TEMP_CSV (CODIGO VARCHAR(32), CPF VARCHAR(11));
		
		SET @query_string = 'BULK INSERT #TEMP_CSV
							FROM '+char(39)+@file_path+char(39)+'
							WITH (FIELDTERMINATOR = '+char(39)+';'+char(39)+', ROWTERMINATOR = '+char(39)+'\n'+char(39)+')';
		EXEC(@query_string);
		
		INSERT INTO MW_PROMOCAO (ID_EVENTO, CODTIPPROMOCAO, DS_PROMOCAO, CD_PROMOCIONAL, CD_CPF_PROMOCIONAL)
		SELECT @id_evento, @codtippromocao, @ds_promocao, CODIGO, CPF
		FROM #TEMP_CSV
		WHERE CPF != 'cpf' OR CPF IS NULL;
		
		SET @success = 1;
	
	END TRY
	
	BEGIN CATCH
	
		SET @success = 0;
		SET @error = ERROR_MESSAGE();
	
	END CATCH
	
	DROP TABLE #TEMP_CSV;
	
	SET NOCOUNT OFF;
	
	SELECT @success as SUCCESS, @error as ERROR
	
END
GO
