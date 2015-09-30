ALTER PROCEDURE dbo.prc_existe_bilhete_pelo_nome (@tipbilhete VARCHAR(20))
AS
--prc_existe_bilhete_pelo_nome 'inteira1'
DECLARE @query NVARCHAR(max),
	@break TINYINT,
	@database VARCHAR(50);

SET NOCOUNT ON;

SELECT @break = isnull(id_promocao_controle, 0)
FROM MW_PROMOCAO_CONTROLE
WHERE ds_tipo_bilhete = @tipbilhete;

DECLARE C1 CURSOR
FOR
SELECT ds_nome_base_sql
FROM mw_base
WHERE ds_nome_base_sql IN (SELECT name FROM sys.databases);

OPEN C1;

FETCH NEXT
FROM C1
INTO @database;

WHILE @@fetch_status = 0
	AND @break = 0
BEGIN
	SET @query = N'select @break = isnull(codtipbilhete, 0) from ' + @database + N'..tabtipbilhete where tipbilhete = ''' + @tipbilhete + N'''';

	EXEC sp_executesql @query,
		N'@break int output',
		@break OUTPUT;

	FETCH NEXT
	FROM C1
	INTO @database;
END

CLOSE C1;

DEALLOCATE C1;

SET NOCOUNT OFF;

SELECT CASE 
		WHEN @break > 0
			THEN 1
		ELSE 0
		END AS in_existe;
