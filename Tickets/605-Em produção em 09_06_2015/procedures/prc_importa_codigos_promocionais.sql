USE [CI_MIDDLEWAY]
GO
/****** Object:  StoredProcedure [dbo].[prc_importa_codigos_promocionais]    Script Date: 06/02/2015 10:10:34 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER PROCEDURE [dbo].[prc_importa_codigos_promocionais]
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
		@del_file BIT,
		@query_string VARCHAR(2000),
		@copy_string VARCHAR(2000),
		@del_string VARCHAR(2000),
		@file_name VARCHAR(2000),
		@new_path VARCHAR(2000);
		
	set @del_file = 0;
	
	BEGIN TRY
	
		set @file_name = REVERSE(LEFT(REVERSE(@file_path),CHARINDEX('\', REVERSE(@file_path), 1) - 1));
		
		-- copia arquivo da rede para pasta local temporaria (solucao para o problema de permissao de acesso)
		exec xp_cmdshell 'net use w: \\10.0.37.2\csv "U5r1C09y" /user:COMPREING_DB\usrcopy', no_output;
		exec xp_cmdshell 'net use y: \\10.0.37.3\csv "U5r1C09y" /user:COMPREING_DB\usrcopy', no_output;

		set @copy_string = 'copy /Y "'+@file_path+'" "C:\windows\temp\'+@file_name+'"';
		set @del_string = 'del "C:\windows\temp\'+@file_name+'"';
		exec xp_cmdshell @copy_string, no_output;
		set @del_file = 1;
		
		exec xp_cmdshell 'net use w: /delete', no_output;
		exec xp_cmdshell 'net use y: /delete', no_output;
		-----------------------------------------------------------------------------------------------------
		
		set @new_path = 'C:\windows\temp\'+@file_name
	
		CREATE TABLE #TEMP_CSV (CODIGO VARCHAR(32), CPF VARCHAR(11));
		
		SET @query_string = 'BULK INSERT #TEMP_CSV
							FROM '+char(39)+@new_path+char(39)+'
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
	
	IF @del_file = 1
	BEGIN
		exec xp_cmdshell @del_string, no_output;
	END
	
	SET NOCOUNT OFF;
	
	SELECT @success as SUCCESS, @error as ERROR
	
END
