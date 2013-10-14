IF EXISTS (
		SELECT *
		FROM dbo.sysobjects
		WHERE id = object_id(N'[dbo].[SP_REL_CONSOLIDADO_LIQUIDO]')
			AND OBJECTPROPERTY(id, N'IsProcedure') = 1
		)
	DROP PROCEDURE [dbo].[SP_REL_CONSOLIDADO_LIQUIDO]
GO

/*
+=================================================================================================================+'
!  N� de !   N� da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!    1   !    #321     ! 06/09/2013 ! Edicarlos S. B. ! Proc. utilizada p/ Relat�rio Consolidado L�quido implemen-!
!        !             !            !                 ! tado no Reporting Services.                               !
+--------+-------------+------------+-----------------+-----------------------------------------------------------+
!    2   !    #321     ! 14/10/2013 ! Edicarlos S. B. ! Adicionado DATAPRESENTACAO e HORSESSAO no select de		  !	
!        !             !            !                 ! descontos e removido compl. de meia dos descontos.        !
!        !             !            !                 ! Alterado regra p/ calculo de qtde de lugares.             !
+--------+-------------+------------+-----------------+-----------------------------------------------------------+
!        !             !            !                 !                                                           !
+========+=============+============+=================+===========================================================+'
*/
CREATE PROCEDURE DBO.SP_REL_CONSOLIDADO_LIQUIDO (
	@CODSALA SMALLINT
	,@CODPECA SMALLINT
	,@DATINICIO SMALLDATETIME
	,@DATFIM SMALLDATETIME
	)
AS
DECLARE @CODTIPBILHETE INT
	,@TIPBILHETE VARCHAR(20)
	,@DATMOVIMENTO DATETIME
	,@NOMSETOR VARCHAR(27)
	,@INDICE INT
	,@PRECO MONEY
	,@VLRAGREGADOS MONEY
	,@OUTROSVALORES MONEY
	,@CODSALAMIN SMALLINT
	,@CODSALAMAX SMALLINT
	,@QTDLIMITEINGRPARAVENDA INT
	,@VALINGRESSOEXCEDENTE NUMERIC(10, 2)
DECLARE @DATAPRESENTACAO VARCHAR(100)
	,@HORSESSAO VARCHAR(5)
	,@QTDE INT
	,@PAGTO MONEY
	,@CANAL_VENDA VARCHAR(200)
	,@OPERACAO VARCHAR(100)
	,@ORDEM INT
	,@CODTIPBILHETEDEB INT
	,@QTDEEXCEDENTES INT
/*==========================VARI�VEIS UTILIZADAS NO CURSOR================================*/
DECLARE @CodTipBilhete2 INT
	,@TipBilhete2 VARCHAR(20)
	,@DatMovimento2 DATETIME
	,@NomSetor2 VARCHAR(27)
	,@codapresentacao2 INT
	,@Indice2 INT
	,@Preco2 MONEY
	,@VlrAgregados2 MONEY
	,@OUTROSVALORES2 MONEY
	,@canalvenda2 VARCHAR(200)

SET NOCOUNT ON

IF @CODSALA = - 1
BEGIN
	SET @CODSALAMIN = (
			SELECT DISTINCT MIN(CODSALA)
			FROM TABAPRESENTACAO
			)
	SET @CODSALAMAX = (
			SELECT DISTINCT MAX(CODSALA)
			FROM TABAPRESENTACAO
			)
END
ELSE
BEGIN
	SET @CODSALAMIN = 254
	SET @CODSALAMAX = 0
END

SELECT TABAPRESENTACAO.DATAPRESENTACAO
	,TABAPRESENTACAO.HORSESSAO
	,TABCAIXA.TIPCAIXA
	,TABLUGSALA.CODTIPBILHETE
	,TABTIPBILHETE.TIPBILHETE
	,TABLANCAMENTO.DATMOVIMENTO
	,TABSETOR.NOMSETOR
	,TABLUGSALA.INDICE
	,TABLANCAMENTO.VALPAGTO AS PRECO
	,CASE 
		WHEN CI_MIDDLEWAY..MW_CANAL_VENDA.ID_CANAL_VENDA = 1
			THEN 'Vendas ' + CI_MIDDLEWAY..MW_CANAL_VENDA.DS_CANAL_VENDA
		ELSE 'Vendas Externas'
		END AS DS_CANAL_VENDA
	,TTDB.DEBBORDERO
	,TTDB.CODTIPDEBBORDERO
	,ISNULL(TTDB.QTDLIMITEINGRPARAVENDA, 0) AS QTDLIMITEINGRPARAVENDA
	,ISNULL(TTDB.VALINGRESSOEXCEDENTE, 0) AS VALINGRESSOEXCEDENTE
	,ISNULL(TTDB.CODTIPBILHETE, 0) AS CODTIPBILHETEDEB
	,TABAPRESENTACAO.CODSALA
	,SUM(ISNULL(TABINGRESSOAGREGADOS.VALOR, 0)) AS VLRAGREGADOS
	,TABAPRESENTACAO.CODAPRESENTACAO
	,0 AS OUTROSVALORES
	,TABLANCAMENTO.CODTIPLANCAMENTO
INTO #TMP_RESUMO
FROM TABLUGSALA
INNER JOIN TABTIPBILHETE ON TABLUGSALA.CODTIPBILHETE = TABTIPBILHETE.CODTIPBILHETE
INNER JOIN TABSALDETALHE ON TABLUGSALA.INDICE = TABSALDETALHE.INDICE
INNER JOIN TABSETOR ON TABSALDETALHE.CODSALA = TABSETOR.CODSALA
	AND TABSALDETALHE.CODSETOR = TABSETOR.CODSETOR
INNER JOIN TABAPRESENTACAO ON TABLUGSALA.CODAPRESENTACAO = TABAPRESENTACAO.CODAPRESENTACAO
	AND (
		TABAPRESENTACAO.CODSALA = @CODSALA
		OR (
			TABAPRESENTACAO.CODSALA >= @CODSALAMIN
			AND TABAPRESENTACAO.CODSALA <= @CODSALAMAX
			)
		)
	AND TABAPRESENTACAO.CODPECA = @CODPECA
INNER JOIN TABLANCAMENTO ON TABTIPBILHETE.CODTIPBILHETE = TABLANCAMENTO.CODTIPBILHETE
	AND TABSALDETALHE.INDICE = TABLANCAMENTO.INDICE
	AND TABAPRESENTACAO.CODAPRESENTACAO = TABLANCAMENTO.CODAPRESENTACAO
	AND TABLANCAMENTO.CODTIPLANCAMENTO = 1
LEFT JOIN TABINGRESSOAGREGADOS ON TABINGRESSOAGREGADOS.CODVENDA = TABLUGSALA.CODVENDA
	AND TABINGRESSOAGREGADOS.INDICE = TABLUGSALA.INDICE
INNER JOIN TABCAIXA ON TABCAIXA.CODCAIXA = TABLANCAMENTO.CODCAIXA
INNER JOIN CI_MIDDLEWAY..MW_CANAL_VENDA ON CI_MIDDLEWAY..MW_CANAL_VENDA.ID_CANAL_VENDA = TABCAIXA.ID_CANAL_VENDA
LEFT JOIN TABDEBBORDERO TDB ON TDB.CODPECA = @CODPECA
LEFT JOIN TABTIPDEBBORDERO TTDB ON TTDB.CODTIPDEBBORDERO = TDB.CODTIPDEBBORDERO
	AND TTDB.ATIVO = 'A'
WHERE (TABLUGSALA.CODVENDA IS NOT NULL)
	AND (CONVERT(SMALLDATETIME, CONVERT(VARCHAR(10), TABAPRESENTACAO.DATAPRESENTACAO, 112)) >= @DATINICIO)
	AND (
		CONVERT(SMALLDATETIME, CONVERT(VARCHAR(10), TABAPRESENTACAO.DATAPRESENTACAO, 112)) <= @DATFIM
		OR @DATFIM IS NULL
		)
	AND NOT EXISTS (
		SELECT 1
		FROM TABLANCAMENTO BB
		WHERE TABLANCAMENTO.NUMLANCAMENTO = BB.NUMLANCAMENTO
			AND TABLANCAMENTO.CODTIPBILHETE = BB.CODTIPBILHETE
			AND BB.CODTIPLANCAMENTO = 2
			AND TABLANCAMENTO.CODAPRESENTACAO = BB.CODAPRESENTACAO
			AND TABLANCAMENTO.INDICE = BB.INDICE
		)
GROUP BY TABAPRESENTACAO.DATAPRESENTACAO
	,TABAPRESENTACAO.HORSESSAO
	,TABCAIXA.TIPCAIXA
	,TABLUGSALA.CODTIPBILHETE
	,TABTIPBILHETE.TIPBILHETE
	,TABLANCAMENTO.DATMOVIMENTO
	,TABSETOR.NOMSETOR
	,TABLUGSALA.INDICE
	,TABLANCAMENTO.VALPAGTO
	,CASE 
		WHEN CI_MIDDLEWAY..MW_CANAL_VENDA.ID_CANAL_VENDA = 1
			THEN 'Vendas ' + CI_MIDDLEWAY..MW_CANAL_VENDA.DS_CANAL_VENDA
		ELSE 'Vendas Externas'
		END
	,TTDB.DEBBORDERO
	,TTDB.CODTIPDEBBORDERO
	,ISNULL(TTDB.QTDLIMITEINGRPARAVENDA, 0)
	,ISNULL(TTDB.VALINGRESSOEXCEDENTE, 0)
	,ISNULL(TTDB.CODTIPBILHETE, 0)
	,TABAPRESENTACAO.CODSALA
	,TABAPRESENTACAO.CODAPRESENTACAO
	,TABLANCAMENTO.CODTIPLANCAMENTO

-- SELECT PARA COMPLEMENTO DE MEIA ENTRADA
INSERT INTO #TMP_RESUMO
SELECT TABAPRESENTACAO.DATAPRESENTACAO
	,TABAPRESENTACAO.HORSESSAO
	,TABCAIXA.TIPCAIXA
	,TABLUGSALA.CODTIPBILHETECOMPLMEIA
	,TABTIPBILHETE.TIPBILHETE
	,TABLANCAMENTO.DATMOVIMENTO
	,TABSETOR.NOMSETOR
	,TABLUGSALA.INDICE
	,TABLANCAMENTO.VALPAGTO AS PRECO
	,CASE 
		WHEN CI_MIDDLEWAY..MW_CANAL_VENDA.ID_CANAL_VENDA = 1
			THEN 'Vendas ' + CI_MIDDLEWAY..MW_CANAL_VENDA.DS_CANAL_VENDA
		ELSE 'Vendas Externas'
		END AS DS_CANAL_VENDA
	,TTDB.DEBBORDERO
	,TTDB.CODTIPDEBBORDERO
	,ISNULL(TTDB.QTDLIMITEINGRPARAVENDA, 0) AS QTDLIMITEINGRPARAVENDA
	,ISNULL(TTDB.VALINGRESSOEXCEDENTE, 0) AS VALINGRESSOEXCEDENTE
	,ISNULL(TTDB.CODTIPBILHETE, 0) AS CODTIPBILHETEDEB
	,TABAPRESENTACAO.CODSALA
	,SUM(ISNULL(TABINGRESSOAGREGADOS.VALOR, 0)) AS VLRAGREGADOS
	,TABAPRESENTACAO.CODAPRESENTACAO
	,0 AS OUTROSVALORES
	,TABLANCAMENTO.CODTIPLANCAMENTO
FROM TABLUGSALA
INNER JOIN TABTIPBILHETE ON TABLUGSALA.CODTIPBILHETECOMPLMEIA = TABTIPBILHETE.CODTIPBILHETE
INNER JOIN TABSALDETALHE ON TABLUGSALA.INDICE = TABSALDETALHE.INDICE
INNER JOIN TABSETOR ON TABSALDETALHE.CODSALA = TABSETOR.CODSALA
	AND TABSALDETALHE.CODSETOR = TABSETOR.CODSETOR
INNER JOIN TABAPRESENTACAO ON TABLUGSALA.CODAPRESENTACAO = TABAPRESENTACAO.CODAPRESENTACAO
	AND (
		TABAPRESENTACAO.CODSALA = @CODSALA
		OR (
			TABAPRESENTACAO.CODSALA >= @CODSALAMIN
			AND TABAPRESENTACAO.CODSALA <= @CODSALAMAX
			)
		)
	AND TABAPRESENTACAO.CODPECA = @CODPECA
INNER JOIN TABLANCAMENTO ON TABTIPBILHETE.CODTIPBILHETE = TABLANCAMENTO.CODTIPBILHETE
	AND TABSALDETALHE.INDICE = TABLANCAMENTO.INDICE
	AND TABAPRESENTACAO.CODAPRESENTACAO = TABLANCAMENTO.CODAPRESENTACAO
	AND TABLANCAMENTO.CODTIPLANCAMENTO = 4
LEFT JOIN TABINGRESSOAGREGADOS ON TABINGRESSOAGREGADOS.CODVENDA = TABLUGSALA.CODVENDA
	AND TABINGRESSOAGREGADOS.INDICE = TABLUGSALA.INDICE
INNER JOIN TABCAIXA ON TABCAIXA.CODCAIXA = TABLANCAMENTO.CODCAIXA
INNER JOIN CI_MIDDLEWAY..MW_CANAL_VENDA ON CI_MIDDLEWAY..MW_CANAL_VENDA.ID_CANAL_VENDA = TABCAIXA.ID_CANAL_VENDA
LEFT JOIN TABDEBBORDERO TDB ON TDB.CODPECA = @CODPECA
LEFT JOIN TABTIPDEBBORDERO TTDB ON TTDB.CODTIPDEBBORDERO = TDB.CODTIPDEBBORDERO
	AND TTDB.ATIVO = 'A'
WHERE (TABLUGSALA.CODVENDA IS NOT NULL)
	AND (CONVERT(SMALLDATETIME, CONVERT(VARCHAR(10), TABAPRESENTACAO.DATAPRESENTACAO, 112)) >= @DATINICIO)
	AND (
		CONVERT(SMALLDATETIME, CONVERT(VARCHAR(10), TABAPRESENTACAO.DATAPRESENTACAO, 112)) <= @DATFIM
		OR @DATFIM IS NULL
		)
	AND NOT EXISTS (
		SELECT 1
		FROM TABLANCAMENTO BB
		WHERE TABLANCAMENTO.NUMLANCAMENTO = BB.NUMLANCAMENTO
			AND TABLANCAMENTO.CODTIPBILHETE = BB.CODTIPBILHETE
			AND BB.CODTIPLANCAMENTO = 2
			AND TABLANCAMENTO.CODAPRESENTACAO = BB.CODAPRESENTACAO
			AND TABLANCAMENTO.INDICE = BB.INDICE
		)
GROUP BY TABAPRESENTACAO.DATAPRESENTACAO
	,TABAPRESENTACAO.HORSESSAO
	,TABCAIXA.TIPCAIXA
	,TABLUGSALA.CODTIPBILHETECOMPLMEIA
	,TABTIPBILHETE.TIPBILHETE
	,TABLANCAMENTO.DATMOVIMENTO
	,TABSETOR.NOMSETOR
	,TABLUGSALA.INDICE
	,TABLANCAMENTO.VALPAGTO
	,CASE 
		WHEN CI_MIDDLEWAY..MW_CANAL_VENDA.ID_CANAL_VENDA = 1
			THEN 'Vendas ' + CI_MIDDLEWAY..MW_CANAL_VENDA.DS_CANAL_VENDA
		ELSE 'Vendas Externas'
		END
	,TTDB.DEBBORDERO
	,TTDB.CODTIPDEBBORDERO
	,ISNULL(TTDB.QTDLIMITEINGRPARAVENDA, 0)
	,ISNULL(TTDB.VALINGRESSOEXCEDENTE, 0)
	,ISNULL(TTDB.CODTIPBILHETE, 0)
	,TABAPRESENTACAO.CODSALA
	,TABAPRESENTACAO.CODAPRESENTACAO
	,TABLANCAMENTO.CODTIPLANCAMENTO

/*==========================CALCULO DE VALORES AGREGADOS================================*/
DECLARE C1 CURSOR
FOR
SELECT CodTipBilhete
	,TipBilhete
	,DatMovimento
	,NomSetor
	,codapresentacao
	,Indice
	,Preco
	,VlrAgregados
	,OUTROSVALORES
	,ds_canal_venda
FROM #TMP_RESUMO

OPEN C1

FETCH NEXT
FROM C1
INTO @CodTipBilhete2
	,@TipBilhete2
	,@DatMovimento2
	,@NomSetor2
	,@codapresentacao2
	,@Indice2
	,@Preco2
	,@VlrAgregados2
	,@OUTROSVALORES2
	,@canalvenda2

WHILE @@fetch_Status = 0
BEGIN
	SELECT @OutrosValores2 = (@Preco2 - @VlrAgregados2) * CASE TTLB.icdebcre
			WHEN 'D'
				THEN (isnull(TTBTL.valor, 0) / 100)
			ELSE (isnull(TTBTL.valor, 0) / 100) * - 1
			END
	FROM tabTipBilhTipLcto TTBTL
	INNER JOIN tabTipLanctoBilh TTLB ON TTLB.codtiplct = TTBTL.codtiplct
		AND TTLB.icpercvlr = 'P'
		AND TTLB.icusolcto != 'C'
		AND TTLB.inativo = 'A'
	WHERE TTBTL.codtipbilhete = @codtipbilhete2
		AND TTBTL.dtinivig = (
			SELECT max(TTBTL1.dtinivig)
			FROM tabTipBilhTipLcto TTBTL1
				,tabTipLanctoBilh TTLB1
			WHERE TTBTL1.codtipbilhete = TTBTL.codtipbilhete
				AND TTBTL1.codtiplct = TTBTL.codtiplct
				AND TTBTL1.dtinivig <= @DatMovimento2
				AND TTBTL1.inativo = 'A'
				AND TTLB1.codtiplct = TTBTL1.codtiplct
				AND TTLB1.IcPercVlr = 'P'
				AND TTLB1.icusolcto != 'C'
				AND TTLB1.inativo = 'A'
			)
		AND TTBTL.inativo = 'A'

	SELECT @OutrosValores2 = @OutrosValores2 + (
			CASE TTLB.icdebcre
				WHEN 'D'
					THEN isnull(TTBTL.valor, 0)
				ELSE isnull(TTBTL.valor, 0) * - 1
				END
			)
	FROM tabTipBilhTipLcto TTBTL
	INNER JOIN tabTipLanctoBilh TTLB ON TTLB.codtiplct = TTBTL.codtiplct
		AND TTLB.icpercvlr = 'V'
		AND TTLB.icusolcto != 'C'
		AND TTLB.inativo = 'A'
	WHERE TTBTL.codtipbilhete = @codtipbilhete2
		AND TTBTL.dtinivig = (
			SELECT max(TTBTL1.dtinivig)
			FROM tabTipBilhTipLcto TTBTL1
				,tabTipLanctoBilh TTLB1
			WHERE TTBTL1.codtipbilhete = TTBTL.codtipbilhete
				AND TTBTL1.codtiplct = TTBTL.codtiplct
				AND TTBTL1.dtinivig <= @DatMovimento2
				AND TTBTL1.inativo = 'A'
				AND TTLB1.codtiplct = TTBTL1.codtiplct
				AND TTLB1.IcPercVlr = 'V'
				AND TTLB1.icusolcto != 'C'
				AND TTLB1.inativo = 'A'
			)
		AND TTBTL.inativo = 'A'

	UPDATE #TMP_RESUMO
	SET Preco = @Preco2 - @VlrAgregados2 + @OutrosValores2
		,OutrosValores = @OutrosValores2
	WHERE Indice = @Indice2
		AND codapresentacao = @codapresentacao2
		AND CodTipBilhete = @CodTipBilhete2

	FETCH NEXT
	FROM C1
	INTO @CodTipBilhete2
		,@TipBilhete2
		,@DatMovimento2
		,@NomSetor2
		,@codapresentacao2
		,@Indice2
		,@Preco2
		,@VlrAgregados2
		,@OUTROSVALORES2
		,@canalvenda2
END

CLOSE C1

DEALLOCATE C1

/*==========================INGRESSOS EXCEDENTES================================*/
SELECT @VALINGRESSOEXCEDENTE = MAX(VALINGRESSOEXCEDENTE)
FROM #TMP_RESUMO

SELECT @QTDLIMITEINGRPARAVENDA = MAX(QTDLIMITEINGRPARAVENDA)
FROM #TMP_RESUMO

IF EXISTS (
		SELECT *
		FROM dbo.sysobjects
		WHERE id = object_id(N'[dbo].[TMP_RESUMO_AUX]')
		)
	DROP TABLE TMP_RESUMO_AUX

CREATE TABLE TMP_RESUMO_AUX (
	DATA_APRESENTACAO VARCHAR(100) COLLATE Latin1_General_CI_AS
	,HORSESSAO VARCHAR(5) COLLATE Latin1_General_CI_AS
	,QTDE INT
	,PAGTO MONEY
	,CANAL_VENDA VARCHAR(200) COLLATE Latin1_General_CI_AS
	,OPERACAO VARCHAR(100) COLLATE Latin1_General_CI_AS
	,ORDEM INT
	,CODTIPBILHETEDEB INT
	,LUGARES INT
	)


SELECT DATAPRESENTACAO
	,HORSESSAO 
	,ROW_NUMBER() OVER(ORDER BY DATAPRESENTACAO DESC) AS LINHA
INTO #RESULTADO
FROM #TMP_RESUMO TI 	
GROUP BY DATAPRESENTACAO, HORSESSAO 
HAVING COUNT(1) > 1

INSERT INTO TMP_RESUMO_AUX
SELECT DISTINCT CONVERT(VARCHAR, DATAPRESENTACAO, 112) AS DATA_APRESENTACAO
	,HORSESSAO
	,COUNT(1) AS QTDE
	,SUM(PRECO) AS PAGTO
	,'CONVITE' AS CANAL_VENDA
	,'Convites' AS OPERACAO
	,1 AS ORDEM
	,CODTIPBILHETEDEB
	,0 AS LUGARES
FROM #TMP_RESUMO
WHERE PRECO = 0
GROUP BY CONVERT(VARCHAR, DATAPRESENTACAO, 112)
	,HORSESSAO
	,DS_CANAL_VENDA
	,DEBBORDERO
	,CODTIPBILHETEDEB

SELECT DISTINCT CODTIPDEBBORDERO
	,CODTIPBILHETEDEB
	,COUNT(1) AS QTDE
	,T1.DATAPRESENTACAO
	,T1.HORSESSAO
INTO #TMP_EXECEDENTES
FROM #TMP_RESUMO T1
INNER JOIN TABLANCAMENTO T2 ON T2.CODAPRESENTACAO = T1.CODAPRESENTACAO
	AND T2.CodTipBilhete = T1.CODTIPBILHETEDEB
	AND T2.CodTipLancamento = 1
	AND T2.Indice = T1.INDICE
WHERE T1.PRECO = 0
	AND ISNULL(T1.CODTIPBILHETEDEB, 0) <> 0
	AND NOT EXISTS (
		SELECT 1
		FROM TABLANCAMENTO BB
		WHERE T2.NUMLANCAMENTO = BB.NUMLANCAMENTO
			AND T2.CODTIPBILHETE = BB.CODTIPBILHETE
			AND BB.CODTIPLANCAMENTO = 2
			AND T2.CODAPRESENTACAO = BB.CODAPRESENTACAO
			AND T2.INDICE = BB.INDICE
		)
GROUP BY T1.CODTIPDEBBORDERO
	,T1.CODTIPBILHETEDEB
	,T1.DATAPRESENTACAO
	,T1.HORSESSAO

SELECT DISTINCT CONVERT(VARCHAR, TR.DATAPRESENTACAO, 112) AS DATA_APRESENTACAO
	,TR.HORSESSAO
	,DBO.GETQTDEEXCEDENTES(TR.DATAPRESENTACAO, TR.HORSESSAO, TR.QTDLIMITEINGRPARAVENDA, T1.QTDE) AS QTDE
	,(DBO.GETQTDEEXCEDENTES(TR.DATAPRESENTACAO, TR.HORSESSAO, TR.QTDLIMITEINGRPARAVENDA, T1.QTDE) * TR.VALINGRESSOEXCEDENTE) * - 1 AS PAGTO
	,TR.DEBBORDERO COLLATE Latin1_General_CI_AS AS CANAL_VENDA
	,'Descontos' AS OPERACAO
	,3 AS ORDEM
	,TR.CODTIPBILHETEDEB
	,1 AS CONVITE_EXCEDENTE
	,0 AS QTDE_REAL
	,0 AS PAGTO_REAL
	,0 AS LUGARES
FROM #TMP_RESUMO TR
INNER JOIN #TMP_EXECEDENTES T1 ON T1.CODTIPBILHETEDEB = TR.CODTIPBILHETEDEB
	AND T1.CODTIPDEBBORDERO = TR.CODTIPDEBBORDERO
	AND T1.DATAPRESENTACAO = TR.DATAPRESENTACAO
	AND T1.HORSESSAO = TR.HORSESSAO
WHERE ISNULL(TR.CODTIPBILHETEDEB, 0) <> 0
GROUP BY CONVERT(VARCHAR, TR.DATAPRESENTACAO, 112)
	,TR.DATAPRESENTACAO
	,TR.HORSESSAO
	,TR.DEBBORDERO
	,TR.CODTIPBILHETEDEB
	,TR.QTDLIMITEINGRPARAVENDA
	,TR.VALINGRESSOEXCEDENTE
	,T1.QTDE

UNION ALL

SELECT CONVERT(VARCHAR, DATAPRESENTACAO, 112) AS DATA_APRESENTACAO
	,HORSESSAO
	,COUNT(1) AS QTDE
	,DBO.GETDEBITO(CODTIPDEBBORDERO, COUNT(1), SUM(PRECO)) * - 1 AS PAGTO
	,DEBBORDERO COLLATE Latin1_General_CI_AS AS CANAL_VENDA
	,'Descontos' AS OPERACAO
	,3 AS ORDEM
	,CODTIPBILHETEDEB
	,0 AS CONVITE_EXCEDENTE
	,COUNT(1) AS QTDE_REAL
	,DBO.GETDEBITO(CODTIPDEBBORDERO, COUNT(1), SUM(PRECO)) AS PAGTO_REAL
	,0 AS LUGARES
FROM #TMP_RESUMO
WHERE ISNULL(CODTIPBILHETEDEB, 0) = 0
	AND #TMP_RESUMO.CODTIPLANCAMENTO <> 4
GROUP BY CONVERT(VARCHAR, DATAPRESENTACAO, 112)
	,HORSESSAO
	,DEBBORDERO
	,CODTIPDEBBORDERO
	,CODTIPBILHETEDEB

UNION ALL

SELECT DISTINCT CONVERT(VARCHAR, DATAPRESENTACAO, 112) AS DATA_APRESENTACAO
	,HORSESSAO
	,COUNT(1) AS QTDE
	,SUM(PRECO) AS PAGTO
	,UPPER(DS_CANAL_VENDA) COLLATE Latin1_General_CI_AS AS CANAL_VENDA
	,'Receitas' AS OPERACAO
	,2 AS ORDEM
	,NULL
	,0 AS CONVITE_EXCEDENTE
	,COUNT(1) AS QTDE_REAL
	,SUM(PRECO) AS PAGTO_REAL
	,0 AS LUGARES
FROM #TMP_RESUMO
WHERE PRECO <> 0
GROUP BY CONVERT(VARCHAR, DATAPRESENTACAO, 112)
	,HORSESSAO
	,DS_CANAL_VENDA
	,DEBBORDERO

UNION ALL

SELECT DISTINCT CONVERT(VARCHAR, DATAPRESENTACAO, 112) AS DATA_APRESENTACAO
	,HORSESSAO
	,COUNT(1) AS QTDE
	,SUM(PRECO) AS PAGTO
	,'CONVITE' AS CANAL_VENDA
	,'Convites' AS OPERACAO
	,1 AS ORDEM
	,NULL
	,0 AS CONVITE_EXCEDENTE
	,COUNT(1) AS QTDE_REAL
	,SUM(PRECO) AS PAGTO_REAL
	,0 AS LUGARES
FROM #TMP_RESUMO
WHERE PRECO = 0
GROUP BY CONVERT(VARCHAR, DATAPRESENTACAO, 112)
	,HORSESSAO
	--,DS_CANAL_VENDA
	,DEBBORDERO

UNION ALL

SELECT DISTINCT CONVERT(VARCHAR, DATAPRESENTACAO, 112) AS DATA_APRESENTACAO
	,HORSESSAO
	,0 AS QTDE
	,0 AS PAGTO
	,'Lugares' AS CANAL_VENDA
	,'Lugares' AS OPERACAO
	,1 AS ORDEM
	,NULL
	,0 AS CONVITE_EXCEDENTE
	,0 AS QTDE_REAL
	,0 AS PAGTO_REAL
	,DBO.GETLUGARES(@CODPECA, DATAPRESENTACAO, DATAPRESENTACAO, CASE 
			WHEN @CODSALA = - 1
				THEN CASE WHEN (SELECT COUNT(LINHA) FROM #RESULTADO R WHERE R.DATAPRESENTACAO = DATAPRESENTACAO) > 1
							THEN HORSESSAO
							ELSE NULL
						END
			ELSE HORSESSAO
			END, 
			CASE 
			WHEN @CODSALA = - 1
				THEN NULL
			ELSE CODSALA
			END) AS LUGARES
FROM #TMP_RESUMO
GROUP BY DATAPRESENTACAO
	,HORSESSAO
	,CODSALA
ORDER BY 1
	,2
	,5
	,6

DROP TABLE #TMP_RESUMO

DROP TABLE #TMP_EXECEDENTES

DROP TABLE TMP_RESUMO_AUX

SET NOCOUNT OFF
