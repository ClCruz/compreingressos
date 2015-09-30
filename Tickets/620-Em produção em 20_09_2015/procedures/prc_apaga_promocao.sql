ALTER PROCEDURE dbo.prc_apaga_promocao (@id_promocao_controle INT)
AS
--prc_apaga_promocao 20
DECLARE @query NVARCHAR(max),
	@database VARCHAR(50);

SET NOCOUNT ON;

DELETE MW_PROMOCAO WHERE ID_PROMOCAO_CONTROLE = @id_promocao_controle AND ID_PEDIDO_VENDA IS NULL AND ID_SESSION IS NULL;

UPDATE MW_PROMOCAO_CONTROLE SET IN_ATIVO = 0 WHERE ID_PROMOCAO_CONTROLE = @id_promocao_controle;

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
BEGIN
	SET @query = N'UPDATE TTB
					SET STATIPBILHETE = ''I''
					FROM ' + @database + N'..TABTIPBILHETE TTB
					INNER JOIN MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = TTB.ID_PROMOCAO_CONTROLE
					WHERE PC.ID_PROMOCAO_CONTROLE = ' + convert(NVARCHAR, @id_promocao_controle);

	EXEC sp_executesql @query;
	
	SET @query = N'DELETE TVB
					FROM ' + @database + N'..TABVALBILHETE TVB
					INNER JOIN ' + @database + N'..TABTIPBILHETE TTB ON TTB.CODTIPBILHETE = TVB.CODTIPBILHETE
					INNER JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = TTB.ID_PROMOCAO_CONTROLE
					WHERE PC.ID_PROMOCAO_CONTROLE = ' + convert(NVARCHAR, @id_promocao_controle);

	EXEC sp_executesql @query;

	FETCH NEXT
	FROM C1
	INTO @database;
END

CLOSE C1;

DEALLOCATE C1;

SET NOCOUNT OFF;

SELECT 1;