SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER PROCEDURE prc_cons_assinaturas
	@STATUS_RESERVA VARCHAR(2), 
	@ANO_TEMPORADA INT,
	@BASE VARCHAR(100)
AS
BEGIN
	DECLARE @SQL VARCHAR(5000)
	
	SET NOCOUNT ON;
	
	SET @SQL = 'SELECT DISTINCT	C.DS_NOME +'' ''+ C.DS_SOBRENOME AS Assinante, C.CD_EMAIL_LOGIN AS Email'
			  +',E.DS_EVENTO COLLATE SQL_Latin1_General_CP1_CI_AS AS Assinatura, C.DS_DDD_TELEFONE +'' ''+ C.DS_TELEFONE AS Telefone '
			  +',C.DS_DDD_CELULAR +'' ''+ C.DS_CELULAR AS Celular, ISNULL(PR.IN_ANO_TEMPORADA,0) AS Temporada '
			  +',TS.NOMSETOR COLLATE SQL_Latin1_General_CP1_CI_AS AS Setor, TA.VALPECA AS ''Valor da Assinatura'' '
			  +',ISNULL(PR.DS_LOCALIZACAO,'''') AS DS_LOCALIZACAO, '
			  +'CASE PR.IN_STATUS_RESERVA '
              +'WHEN ''A'' THEN ''Aguardando a��o do Assinante'' '
              +'WHEN ''C'' THEN ''Assinatura cancelada'' '
              +'WHEN ''R'' THEN ''Assinatura renovada'' '
              +'WHEN ''S'' THEN ''Solicita��o de troca efetuada'' '
              +'WHEN ''T'' THEN ''Troca efetuada'' '
              +'END AS IN_STATUS_RESERVA '
			  +'FROM MW_PACOTE_RESERVA PR '
			  +'INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = PR.ID_CLIENTE '
			  +'INNER JOIN MW_PACOTE P ON P.ID_PACOTE = PR.ID_PACOTE ' 
			  +'INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = P.ID_APRESENTACAO '
			  +'INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO '
			  +'INNER JOIN MW_BASE B ON B.ID_BASE  = E.ID_BASE '
			  +'INNER JOIN '+ @BASE +'..TABSALDETALHE TSD ON TSD.INDICE = PR.ID_CADEIRA '
			  +'INNER JOIN '+ @BASE +'..TABSETOR TS ON TS.CODSALA = TSD.CODSALA AND TS.CODSETOR = TSD.CODSETOR '
			  +'INNER JOIN '+ @BASE +'..TABAPRESENTACAO TA ON TA.CODAPRESENTACAO = A.CODAPRESENTACAO '
			  +'WHERE '
			  IF(@STATUS_RESERVA != '-1')
			  BEGIN
			  SET @SQL = @SQL +' PR.IN_STATUS_RESERVA = '''+ @STATUS_RESERVA +''' AND '
			  END			  
			  SET @SQL = @SQL +' PR.IN_ANO_TEMPORADA = '+ CONVERT(VARCHAR, @ANO_TEMPORADA)
			  
	SET @SQL = @SQL + 'UNION ALL '
			  +'SELECT C.DS_NOME +'' ''+ C.DS_SOBRENOME AS Assinante, C.CD_EMAIL_LOGIN AS Email'
			  +',HA.DS_PACOTE AS Assinatura ,C.DS_DDD_TELEFONE +'' ''+ C.DS_TELEFONE AS Telefone'
			  +',C.DS_DDD_CELULAR + C.DS_CELULAR AS Celular,HA.ID_ANO_TEMPORADA AS Temporada'
			  +',HA.DS_SETOR AS Setor,HA.VL_PACOTE  AS ''Valor da Assinatura'' '
			  +',HA.DS_CADEIRA AS DS_LOCALIZACAO, '' '' AS IN_STATUS_RESERVA '
			  +'FROM MW_HIST_ASSINATURA HA '
			  +'INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = HA.ID_CLIENTE '
			  +'WHERE '
			  +' HA.ID_ANO_TEMPORADA = '+ CONVERT(VARCHAR, @ANO_TEMPORADA)
			  
			  SET @SQL = @SQL +' ORDER BY C.DS_NOME +'' ''+ C.DS_SOBRENOME'
	PRINT @SQL
	EXECUTE(@SQL)
END
GO