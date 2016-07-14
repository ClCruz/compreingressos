USE [CI_MIDDLEWAY];

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[DBO].[prc_dispara_url_assinatura]') AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1)
DROP PROCEDURE [DBO].[prc_dispara_url_assinatura]
GO

-- prc_dispara_url_assinatura 'http://local.compreingressos.com:8081/compreingressos2/comprar/recorrencia.php?id='

CREATE PROCEDURE prc_dispara_url_assinatura
	@url varchar(max)
AS

SET NOCOUNT ON;

declare @id int,
		@resp varchar(8000);

DECLARE ids_cursor CURSOR FOR
SELECT ID_ASSINATURA_CLIENTE
FROM MW_ASSINATURA_CLIENTE
WHERE DT_PROXIMO_PAGAMENTO = CAST(GETDATE() AS DATE) AND IN_ATIVO = 1;

OPEN ids_cursor

FETCH NEXT FROM ids_cursor   
INTO @id

WHILE @@FETCH_STATUS = 0  
BEGIN
	INSERT INTO MW_LOG_IPAGARE VALUES (GETDATE(), -1, 'INICIO REQUEST RECORRENCIA PEDIDO = '+CONVERT(varchar, @id));
	
    SELECT @resp = dbo.GetHttp(@url+CONVERT(varchar, @id));
    
	INSERT INTO MW_LOG_IPAGARE VALUES (GETDATE(), -1, 'FIM REQUEST RECORRENCIA PEDIDO = '+CONVERT(varchar, @id)+', RESULTADO = '+@resp);
END   
CLOSE ids_cursor;  
DEALLOCATE ids_cursor;