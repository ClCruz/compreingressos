SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (
		SELECT *
		FROM DBO.SYSOBJECTS
		WHERE ID = OBJECT_ID(N'[DBO].[SP_REL_BORDERO_COMPLETO]')
			AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1
		)
	DROP PROCEDURE [DBO].[SP_REL_BORDERO_COMPLETO]
GO

/*
+=================================================================================================================+'
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!   1    !     #336    ! 11/09/2013 ! Edicarlos S. B. ! Proc. Utilizada p/ Relatório Borderô de Vendas implemen-  !
!		 !			   !			!				  !	tado em PHP.											  !	
+========+=============+============+=================+===========================================================+'
!        !             !            !                 !                                              		      !
+=================================================================================================================+
*/
CREATE PROCEDURE [dbo].[SP_REL_BORDERO_COMPLETO] (
	@CodPeca INT
	,@CodApresentacao INT
	,@DatApresentacao VARCHAR(10)
	,@IngressosExcedentes INT
	,@ListaCodTipBilhete VARCHAR(MAX)
	)
AS
SET NOCOUNT ON

DECLARE @TotVenda MONEY
	,@ItensVendidos SMALLINT

SET NOCOUNT ON

-- CALCULA O VALOR DO TOTAL VENDIDO PARA A APRESENTAÇÃO ESPECIFICADA (VENDAS, DESPRESA SE OS ESTORNOS)
EXECUTE sp_sel_vlrtotal_bordero @CodApresentacao
	,@TotVenda OUTPUT

-- SOMA A QUANTIDADE DE INGRESSOS VENDIDOS
SELECT @ItensVendidos = isnull(COUNT(1), 0)
FROM tabLancamento
WHERE CodTipLancamento = 1
	AND CodApresentacao = @CodApresentacao

-- SUBTRAI A QUANTIDADE DE INGRESSOS ESTORNADOS
SELECT @ItensVendidos = @ItensVendidos - isnull(COUNT(1), 0)
FROM tabLancamento
WHERE CodTipLancamento = 2
	AND CodApresentacao = @CodApresentacao

-- CALCULA OS DÉBITOS BASEADOS EM PERCENTUAIS - P = PERCENTUAL.
SELECT tabTipDebBordero.CodTipDebBordero
	,tabTipDebBordero.DebBordero
	,isnull(tabTipDebBordero.VlMinimo, 0) AS VlMinimoDebBordero
	,Round(tabTipDebBordero.PerDesconto, 2) AS PerDesconto
	,COALESCE(ROUND(tabTipDebBordero.PerDesconto * @TotVenda / 100, 2), 0) AS Valor
	,tabTipDebBordero.TipValor
	,tabTipDebBordero.QtdLimiteIngrParaVenda
	,tabTipDebBordero.ValIngressoExcedente
	,tabTipDebBordero.CodTipBilhete
INTO #TEMP1
FROM tabDebBordero
INNER JOIN tabTipDebBordero ON tabDebBordero.CodTipDebBordero = tabTipDebBordero.CodTipDebBordero
WHERE (
		convert(VARCHAR(10), @DatApresentacao, 112) BETWEEN tabDebBordero.DatIniDebito
			AND tabDebBordero.DatFinDebito
		)
	AND (tabDebBordero.CodPeca = @CodPeca)
	AND tabTipDebBordero.TipValor = 'P'
	AND tabTipDebBordero.Ativo = 'A'
	AND COALESCE(tabTipDebBordero.CodTipBilhete,0) IN (SELECT CodTipBilhete FROM DBO.GETTIPBILHETES(@ListaCodTipBilhete, ','))
GROUP BY tabTipDebBordero.CodTipDebBordero
	,tabTipDebBordero.DebBordero
	,isnull(tabTipDebBordero.VlMinimo, 0)
	,tabTipDebBordero.PerDesconto
	,COALESCE(tabTipDebBordero.PerDesconto * @TotVenda / 100, 0)
	,tabTipDebBordero.TipValor
	,tabTipDebBordero.QtdLimiteIngrParaVenda
	,tabTipDebBordero.ValIngressoExcedente
	,tabTipDebBordero.CodTipBilhete

-- CALCULA OS DÉBITOS BASEADOS EM VALORES MONETÁRIOS - V = VALOR.
SELECT tabTipDebBordero.CodTipDebBordero
	,tabTipDebBordero.DebBordero
	,isnull(tabTipDebBordero.VlMinimo, 0) AS VlMinimoDebBordero
	,Round(tabTipDebBordero.PerDesconto, 2) AS PerDesconto
	,COALESCE(ROUND(tabTipDebBordero.PerDesconto * @ItensVendidos, 2), 0) AS Valor
	,tabTipDebBordero.TipValor
	,tabTipDebBordero.QtdLimiteIngrParaVenda
	,tabTipDebBordero.ValIngressoExcedente
	,tabTipDebBordero.CodTipBilhete
INTO #TEMP2
FROM tabDebBordero
INNER JOIN tabTipDebBordero ON tabDebBordero.CodTipDebBordero = tabTipDebBordero.CodTipDebBordero	
WHERE (
		convert(VARCHAR(10), @DatApresentacao, 112) BETWEEN tabDebBordero.DatIniDebito
			AND tabDebBordero.DatFinDebito
		)
	AND (tabDebBordero.CodPeca = @CodPeca)
	AND (tabTipDebBordero.TipValor = 'V')
	AND tabTipDebBordero.Ativo = 'A'
	AND COALESCE(tabTipDebBordero.CodTipBilhete,0) IN (SELECT CodTipBilhete FROM DBO.GETTIPBILHETES(@ListaCodTipBilhete, ','))
GROUP BY tabTipDebBordero.CodTipDebBordero
	,tabTipDebBordero.DebBordero
	,isnull(tabTipDebBordero.VlMinimo, 0)
	,tabTipDebBordero.PerDesconto
	,COALESCE(tabTipDebBordero.PerDesconto * @ItensVendidos, 0)
	,tabTipDebBordero.TipValor
	,tabTipDebBordero.QtdLimiteIngrParaVenda
	,tabTipDebBordero.ValIngressoExcedente
	,tabTipDebBordero.CodTipBilhete

-- CALCULA OS DÉBITOS BASEADOS EM VALORES MONETÁRIOS - F = VALOR FIXO.
SELECT tabTipDebBordero.CodTipDebBordero
	,tabTipDebBordero.DebBordero
	,isnull(tabTipDebBordero.VlMinimo, 0) AS VlMinimoDebBordero
	,Round(tabTipDebBordero.PerDesconto, 2) AS PerDesconto
	,tabTipDebBordero.PerDesconto AS valor
	,tabTipDebBordero.TipValor
	,tabTipDebBordero.QtdLimiteIngrParaVenda
	,tabTipDebBordero.ValIngressoExcedente
	,tabTipDebBordero.CodTipBilhete
INTO #TEMP3
FROM tabDebBordero
INNER JOIN tabTipDebBordero ON tabDebBordero.CodTipDebBordero = tabTipDebBordero.CodTipDebBordero
WHERE (
		convert(VARCHAR(10), @DatApresentacao, 112) BETWEEN tabDebBordero.DatIniDebito
			AND tabDebBordero.DatFinDebito
		)
	AND (tabDebBordero.CodPeca = @CodPeca)
	AND (tabTipDebBordero.TipValor = 'F')
	AND tabTipDebBordero.Ativo = 'A'
	AND COALESCE(tabTipDebBordero.CodTipBilhete,0) IN (SELECT CodTipBilhete FROM DBO.GETTIPBILHETES(@ListaCodTipBilhete, ','))
GROUP BY tabTipDebBordero.CodTipDebBordero
	,tabTipDebBordero.DebBordero
	,isnull(tabTipDebBordero.VlMinimo, 0)
	,tabTipDebBordero.PerDesconto
	,tabTipDebBordero.TipValor
	,tabTipDebBordero.QtdLimiteIngrParaVenda
	,tabTipDebBordero.ValIngressoExcedente
	,tabTipDebBordero.CodTipBilhete

SET NOCOUNT OFF

SELECT CodTipDebBordero
	,DebBordero
	,CASE 
		WHEN CodTipBilhete IS NULL
			THEN PerDesconto
		ELSE ValIngressoExcedente
		END AS PerDesconto
	,CASE 
		WHEN VlMinimoDebBordero > valor
			AND VlMinimoDebBordero > 0
			THEN VlMinimoDebBordero
		ELSE DBO.GETINGEXCEDENTES(CodTipBilhete, Valor, ValIngressoExcedente, QtdLimiteIngrParaVenda, @IngressosExcedentes)
		END AS Valor
	,TipValor
	,VlMinimoDebBordero
	,DBO.GETINGEXCEDENTES(CodTipBilhete, Valor, ValIngressoExcedente, QtdLimiteIngrParaVenda, @IngressosExcedentes) AS ValorReal
	,CASE WHEN @IngressosExcedentes > QtdLimiteIngrParaVenda THEN (@IngressosExcedentes - QtdLimiteIngrParaVenda) ELSE 0 END AS QtdeIngExcedidos
	,CodTipBilhete
FROM #TEMP1

UNION

SELECT CodTipDebBordero
	,DebBordero
	,CASE 
		WHEN CodTipBilhete IS NULL
			THEN PerDesconto
		ELSE ValIngressoExcedente
		END AS PerDesconto
	,CASE 
		WHEN VlMinimoDebBordero > valor
			AND VlMinimoDebBordero > 0
			THEN VlMinimoDebBordero
		ELSE DBO.GETINGEXCEDENTES(CodTipBilhete, Valor, ValIngressoExcedente, QtdLimiteIngrParaVenda, @IngressosExcedentes)
		END AS Valor
	,TipValor
	,VlMinimoDebBordero
	,DBO.GETINGEXCEDENTES(CodTipBilhete, Valor, ValIngressoExcedente, QtdLimiteIngrParaVenda, @IngressosExcedentes) AS ValorReal
	,CASE WHEN @IngressosExcedentes > QtdLimiteIngrParaVenda THEN (@IngressosExcedentes - QtdLimiteIngrParaVenda) ELSE 0 END AS QtdeIngExcedidos
	,CodTipBilhete
FROM #TEMP2

UNION

SELECT CodTipDebBordero
	,DebBordero
	,CASE 
		WHEN CodTipBilhete IS NULL
			THEN PerDesconto
		ELSE ValIngressoExcedente
		END AS PerDesconto
	,CASE 
		WHEN VlMinimoDebBordero > valor
			AND VlMinimoDebBordero > 0
			THEN VlMinimoDebBordero
		ELSE DBO.GETINGEXCEDENTES(CodTipBilhete, Valor, ValIngressoExcedente, QtdLimiteIngrParaVenda, @IngressosExcedentes)
		END AS Valor
	,TipValor
	,VlMinimoDebBordero
	,DBO.GETINGEXCEDENTES(CodTipBilhete, Valor, ValIngressoExcedente, QtdLimiteIngrParaVenda, @IngressosExcedentes) AS ValorReal
	,CASE WHEN @IngressosExcedentes > QtdLimiteIngrParaVenda THEN (@IngressosExcedentes - QtdLimiteIngrParaVenda) ELSE 0 END AS QtdeIngExcedidos
	,CodTipBilhete
FROM #TEMP3
ORDER BY DebBordero
GO