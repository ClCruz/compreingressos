SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

/*
prc_vender_pedido 396679
SELECT * FROM MW_PEDIDO_VENDA WHERE ID_PEDIDO_VENDA = 396676
DELETE MW_RESERVA
SELECT * FROM MW_RESERVA
SElECT * FROM tab_log_gabriel ORDER BY DATA DESC
SELECT * FROM CI_THEATRO_MUNICIPAL..TAB_LOG_EMERSON ORDER BY DATA_HORA DESC

SELECT * FROM CI_THEATRO_MUNICIPAL..TABLUGSALA WHERE INDICE = 11439 AND CODAPRESENTACAO = 253
SELECT * FROM CI_CORINTHIANS..TABLOGERRO ORDER BY DATERRO DESC
DECLARE @TEST VARCHAR(10)
SET @TEST = '123'
SELECT ''''+@TEST+''''

SELECT T.ID_SESSION, R.ID_SESSION
FROM MW_RESERVA R
INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
INNER JOIN CI_CORINTHIANS..TABLUGSALA T
	ON T.INDICE = R.ID_CADEIRA
	AND T.CODAPRESENTACAO = A.CODAPRESENTACAO
	AND T.CODTIPBILHETE = AB.CODTIPBILHETE
WHERE R.ID_PEDIDO_VENDA = 396955

EXEC('DECLARE @RETURNSTATUS INT; SELECT @RETURNSTATUS = 1')
SELECT @RETURNSTATUS

EXEC prc_vender_pedido 396863
*/
ALTER PROCEDURE [dbo].[prc_vender_pedido] (
	@ID_PEDIDO_VENDA	int,				-- codigo do pedido de venda no MW_PEDIDO_VENDA
	@CAIXA 				int = NULL
	)   AS


DECLARE @query						NVARCHAR(MAX),
		@params_definition			NVARCHAR(MAX),
		@RETURN_CODE				INT,
		@ERROR_MSG					VARCHAR(500),
		@ID_CLIENTE					INT,
		@ID_SESSION					VARCHAR(37),
		@ID_BASE					INT,
		@CD_MEIO_PAGAMENTO			INT,
		@CODAPRESENTACAO			INT,
		@DS_DDD_TELEFONE			VARCHAR(2),
		@DS_TELEFONE				VARCHAR(15),
		@DS_NOME_SOBRENOME			VARCHAR(50),
		@CD_CPF						VARCHAR(11),
		@CD_RG						VARCHAR(11),
		@ID_PEDIDO_IPAGARE			VARCHAR(50),
		@CD_NUMERO_AUTORIZACAO		VARCHAR(50),
		@CD_NUMERO_TRANSACAO		VARCHAR(50),
		@CD_BIN_CARTAO				VARCHAR(16),
		@DS_NOME_BASE_SQL			VARCHAR(50)

 
SET NOCOUNT ON

--PEGA DADOS DO CLIENTE
SELECT TOP 1
@ID_CLIENTE = C.ID_CLIENTE,
@DS_NOME_SOBRENOME = CONVERT(VARCHAR(50), ISNULL(C.DS_NOME, '') + ' ' + ISNULL(C.DS_SOBRENOME, '')),
@DS_DDD_TELEFONE = C.DS_DDD_TELEFONE,
@DS_TELEFONE = C.DS_TELEFONE,
@CD_CPF = C.CD_CPF,
@CD_RG = C.CD_RG,
@CD_BIN_CARTAO = ISNULL(PV.CD_BIN_CARTAO, ''),
@ID_SESSION = R.ID_SESSION,
@CD_MEIO_PAGAMENTO = ISNULL(M.CD_MEIO_PAGAMENTO, ''),
@CAIXA = CASE WHEN @CAIXA IS NOT NULL THEN @CAIXA
		 ELSE (CASE WHEN ID_USUARIO_CALLCENTER IS NOT NULL
			THEN (CASE WHEN IN_RETIRA_ENTREGA <> 'R' THEN 252 ELSE 254 END)
			ELSE (CASE WHEN IN_RETIRA_ENTREGA <> 'R' THEN 253 ELSE 255 END)
		END) END,
@ID_PEDIDO_IPAGARE = PV.ID_PEDIDO_IPAGARE,
@CD_NUMERO_AUTORIZACAO = PV.CD_NUMERO_AUTORIZACAO,
@CD_NUMERO_TRANSACAO = PV.CD_NUMERO_TRANSACAO
FROM MW_CLIENTE C
INNER JOIN MW_PEDIDO_VENDA PV ON PV.ID_CLIENTE = C.ID_CLIENTE
INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
INNER JOIN MW_RESERVA R ON R.ID_RESERVA = IPV.ID_RESERVA
LEFT JOIN MW_MEIO_PAGAMENTO M ON M.ID_MEIO_PAGAMENTO = PV.ID_MEIO_PAGAMENTO
WHERE PV.ID_PEDIDO_VENDA = @ID_PEDIDO_VENDA


BEGIN TRANSACTION


begin try


DECLARE TEMP_cursor CURSOR LOCAL FOR 

--PEGA CODAPRESENTACAO/BASE DIFERENTES
SELECT DISTINCT A.CODAPRESENTACAO, B.ID_BASE, B.DS_NOME_BASE_SQL
FROM MW_BASE B
INNER JOIN MW_EVENTO E ON E.ID_BASE = B.ID_BASE
INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_APRESENTACAO = A.ID_APRESENTACAO
INNER JOIN MW_PEDIDO_VENDA P ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
WHERE P.ID_PEDIDO_VENDA = @ID_PEDIDO_VENDA

OPEN TEMP_cursor

FETCH NEXT FROM TEMP_cursor 
INTO @CODAPRESENTACAO, @ID_BASE, @DS_NOME_BASE_SQL
	
WHILE @@FETCH_STATUS = 0
BEGIN
	
	SET @query = N'EXEC '+CONVERT(NVARCHAR,@DS_NOME_BASE_SQL)+N'..SP_VEN_INS001_WEB @ID_SESSION_IN, @ID_BASE_IN, @CD_MEIO_PAGAMENTO_IN, @CODAPRESENTACAO_IN,
																					@DS_DDD_TELEFONE_IN, @DS_TELEFONE_IN, @DS_NOME_SOBRENOME_IN, @CD_CPF_IN,
																					@CD_RG_IN, @ID_PEDIDO_VENDA_IN, @ID_PEDIDO_IPAGARE_IN, @CD_NUMERO_TRANSACAO_IN,
																					@CD_NUMERO_AUTORIZACAO_IN, @CD_BIN_CARTAO_IN, @CAIXA_IN, @RETURN_CODE_OUT OUTPUT, @RETURN_ERROR_MSG_OUT OUTPUT'
	SET @params_definition = N'@ID_SESSION_IN				VARCHAR(37),
								@ID_BASE_IN					INT,
								@CD_MEIO_PAGAMENTO_IN		INT,
								@CODAPRESENTACAO_IN			INT,
								@DS_DDD_TELEFONE_IN			VARCHAR(2),
								@DS_TELEFONE_IN				VARCHAR(15),
								@DS_NOME_SOBRENOME_IN		VARCHAR(50),
								@CD_CPF_IN					VARCHAR(11),
								@CD_RG_IN					VARCHAR(11),
								@ID_PEDIDO_VENDA_IN			INT,
								@ID_PEDIDO_IPAGARE_IN		VARCHAR(50),
								@CD_NUMERO_TRANSACAO_IN		VARCHAR(50),
								@CD_NUMERO_AUTORIZACAO_IN	VARCHAR(50),
								@CD_BIN_CARTAO_IN			VARCHAR(16),
								@CAIXA_IN					INT,
								@RETURN_CODE_OUT			INT OUTPUT,
								@RETURN_ERROR_MSG_OUT		VARCHAR(500) OUTPUT'
	
	EXEC sp_executesql
			@query,
			@params_definition,
			--PARAMETRO DE ENTRADA
			@ID_SESSION_IN = @ID_SESSION,
			@ID_BASE_IN = @ID_BASE,
			@CD_MEIO_PAGAMENTO_IN = @CD_MEIO_PAGAMENTO,
			@CODAPRESENTACAO_IN = @CODAPRESENTACAO,
			@DS_DDD_TELEFONE_IN = @DS_DDD_TELEFONE,
			@DS_TELEFONE_IN = @DS_TELEFONE,
			@DS_NOME_SOBRENOME_IN = @DS_NOME_SOBRENOME,
			@CD_CPF_IN = @CD_CPF,
			@CD_RG_IN = @CD_RG,
			@ID_PEDIDO_VENDA_IN = @ID_PEDIDO_VENDA,
			@ID_PEDIDO_IPAGARE_IN = @ID_PEDIDO_IPAGARE,
			@CD_NUMERO_TRANSACAO_IN = @CD_NUMERO_TRANSACAO,
			@CD_NUMERO_AUTORIZACAO_IN = @CD_NUMERO_AUTORIZACAO,
			@CD_BIN_CARTAO_IN = @CD_BIN_CARTAO,
			@CAIXA_IN = @CAIXA,
			--PARAMETROS DE SAIDA
			@RETURN_CODE_OUT = @RETURN_CODE OUTPUT,
			@RETURN_ERROR_MSG_OUT = @ERROR_MSG OUTPUT
	
	IF @RETURN_CODE != 0
	BEGIN
		-- RAISERROR with severity 11-19 will cause execution to jump to the CATCH block.
		DECLARE @RAISERROR_MSG VARCHAR(200)
		SET @RAISERROR_MSG = 'ERRO NA EXECU��O DA PROCEDURE SP_VEN_INS001_WEB PARA O PEDIDO ' + CONVERT(VARCHAR,@ID_PEDIDO_VENDA)
		RAISERROR(@RAISERROR_MSG, 16, 1)
	END
	
	FETCH NEXT FROM TEMP_cursor 
    INTO @CODAPRESENTACAO, @ID_BASE, @DS_NOME_BASE_SQL

END 
CLOSE TEMP_cursor;
DEALLOCATE TEMP_cursor;



COMMIT TRANSACTION



SET NOCOUNT OFF

SELECT 1 AS Resultado

end try
begin catch


	ROLLBACK TRANSACTION


	--ATUALIZA RESERVA MIDDLEWAY+VB:
		--ADICIONA 2 DIAS NA VALIDADE PARA CHECAGEM DO ERRO
		--MUDA ID_SESSION PARA QUE O USUARIO NAO VEJA/ALTERE A RESERVA
	/*	
	UPDATE MW_RESERVA SET
		DT_VALIDADE = GETDATE()+2,
		ID_SESSION = 'SESSION_' + CONVERT(VARCHAR, @ID_PEDIDO_VENDA)
	WHERE ID_PEDIDO_VENDA = @ID_PEDIDO_VENDA
	
	DECLARE TEMP_cursor2 CURSOR FOR 

	--PEGA BASE DIFERENTES
	SELECT DISTINCT B.DS_NOME_BASE_SQL
	FROM MW_BASE B
	INNER JOIN MW_EVENTO E ON E.ID_BASE = B.ID_BASE
	INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
	INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_APRESENTACAO = A.ID_APRESENTACAO
	INNER JOIN MW_PEDIDO_VENDA P ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
	WHERE P.ID_PEDIDO_VENDA = @ID_PEDIDO_VENDA

	OPEN TEMP_cursor2

	FETCH NEXT FROM TEMP_cursor2
	INTO @DS_NOME_BASE_SQL
		
	WHILE @@FETCH_STATUS = 0
	BEGIN
	
		SET @query = N'UPDATE T SET
							T.ID_SESSION = R.ID_SESSION
						FROM MW_RESERVA R
						INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
						INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
						INNER JOIN '+CONVERT(NVARCHAR,@DS_NOME_BASE_SQL)+N'..TABLUGSALA T
							ON T.INDICE = R.ID_CADEIRA
							AND T.CODAPRESENTACAO = A.CODAPRESENTACAO
							AND T.CODTIPBILHETE = AB.CODTIPBILHETE
						WHERE R.ID_PEDIDO_VENDA = @ID_PEDIDO_VENDA_IN'
		
		SET @params_definition = N'@ID_PEDIDO_VENDA_IN INT'
		
		EXEC sp_executesql @query, @params_definition, @ID_PEDIDO_VENDA_IN = @ID_PEDIDO_VENDA
	
		FETCH NEXT FROM TEMP_cursor2
		INTO @DS_NOME_BASE_SQL
	
	END
	CLOSE TEMP_cursor2;
	DEALLOCATE TEMP_cursor2;
	*/
	INSERT INTO tab_log_gabriel (data, passo, parametros) VALUES (GETDATE(), 'ERRO', left(ERROR_MESSAGE(), 500) + ' sql_error_msg: ' + @ERROR_MSG)

  	SET NOCOUNT OFF
	SELECT 0 AS Resultado, left(ERROR_MESSAGE(), 500) + ' sql_error_msg: ' + @ERROR_MSG AS Error_Msg
	
end catch