SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[DBO].[SP_REL_BORDERO]') AND OBJECTPROPERTY(ID, N'ISPROCEDURE') = 1)
DROP PROCEDURE [DBO].[SP_REL_BORDERO]
GO

CREATE PROCEDURE [dbo].[SP_REL_BORDERO]
	@CodPeca  int,
	@CodApresentacao int,
	@DataBase2 varchar(30) = null
AS

set nocount on

DECLARE  	
		@NomSala 			varchar(50),	-- Nome da Sala	
  		@HorSessao 			char(5),		-- Hora da Apresentacao
  		@NomResPeca 		varchar(50),	-- Nome Redusido da Peca
  		@DatApresentacao 	smalldatetime,	-- Data da Apresentacao
  		@Lugares  			int,     		-- Qtde de Cadeiras na Sala
  		@LugLivres  		int,     		-- Quantidade de Lugares livres que sobrou na sala
  		@PubTotal  			int,      		-- Quantidade de cadeiras vendidas
  		@Pagantes  			int,      		-- Quantidades de Bilhetes Inteiros (Desconto < 100%)
  		@NPagantes  		int,     		-- Quantidade de Bilhetes Convites e Cortesia (100% Desconto)
  		@ValVendas  		money,     		-- Total do valor das vendas  (Vendas de Bilhetes)
  		@ValDebitos  		money,     		-- Total do valor dos descontos (Débitos de Bordero)
  		@ValLiquido  		money,    		-- Valor Líquido da apresentacao @ValVendas - @ValDescontos
  		@NumBordero 		int,			-- Número do Borderô
		@Apresentacao		varchar(10)		-- Data da Apresentação formatada para o IIS.	

-- Variáveis Internas
DECLARE  @CodSala  int  -- Código da Sala da apresentacao
SET NOCOUNT ON
 
-- Seleciona a data da Apresentacao para o IIS.
SELECT @Apresentacao = (SELECT  convert(varchar(10),DatApresentacao,103) As DatApresentacao from tabApresentacao WHERE CodApresentacao =  convert(varchar(10), @CodApresentacao) )

-- Seleciona a data da Apresentaçao
SELECT @DatApresentacao = (SELECT DatApresentacao FROM tabApresentacao WHERE CodApresentacao = convert(varchar(10), @CodApresentacao))
-- Seleciona código da sala da apresentacao
SELECT @CodSala = (SELECT CodSala FROM tabApresentacao WHERE CodApresentacao = convert(varchar(10), @CodApresentacao))
-- Seleciona o Nome da Sala
SELECT @NomSala = (SELECT NomSala FROM tabSala WHERE CodSala = @CodSala)
-- Seleciona a hora da apresentacao
SELECT @HorSessao = (SELECT HorSessao FROM tabApresentacao Where CodApresentacao = convert(varchar(10), @CodApresentacao))
-- Seleciona o nome do responsável da peça
SELECT @NomResPeca = (SELECT NomResPeca FROM tabPeca WHERE CodPeca = @CodPeca) 
-- Seleciona a quantidade de lugares da sala
SELECT @Lugares = (SELECT COALESCE(COUNT(Indice),0) FROM tabSalDetalhe WHERE TipObjeto <> 'I' AND CodSala = @CodSala)
-- Seleciona a quantidade de cadeiras vendidas para a apresentacao
SELECT @PubTotal = (SELECT COALESCE(COUNT(Indice),0) FROM tabLugSala WHERE CodApresentacao = convert(varchar(10), @CodApresentacao)  AND StaCadeira = 'V')
-- Carrega a variável @LugLivres
SELECT @LugLivres = COALESCE(@Lugares - @PubTotal,0)
-- Carrega a Variável @Pagantes
SELECT @Pagantes = (SELECT COALESCE(COUNT(tabLugSala.Indice),0) 
	FROM tabLugSala INNER JOIN tabTipBilhete ON tabLugSala.CodTipBilhete = tabTipBilhete.CodTipBilhete
	WHERE (tabTipBilhete.PerDesconto < 100) AND (dbo.tabLugSala.CodApresentacao = convert(varchar(10), @CodApresentacao) ) AND (tabLugSala.CodVenda IS NOT NULL))
-- Carrega a variável de Nao Pagantes
SELECT @NPagantes = COALESCE(@PubTotal - @Pagantes,0)
-- Carrega a variável de valores de venda (Receita)
SELECT	@ValVendas = (SELECT ROUND(COALESCE (SUM(ValPagto), 0),2) FROM tabLancamento WHERE (CodApresentacao =  convert(varchar(10), @CodApresentacao) ))
-- Carrega o Total de Débitos
SET @ValDebitos = (SELECT ROUND(COALESCE (SUM(tabTipDebBordero.PerDesconto * @ValVendas / 100), 0),2) 
	FROM tabDebBordero 
	INNER JOIN tabTipDebBordero ON tabDebBordero.CodTipDebBordero = tabTipDebBordero.CodTipDebBordero
	WHERE (CONVERT(Varchar(10),@DatApresentacao,112) BETWEEN tabDebBordero.DatIniDebito AND tabDebBordero.DatFinDebito) 
	AND (tabDebBordero.CodPeca = convert(varchar(10),@CodPeca) ) AND (tabTipDebBordero.TipValor = 'P' AND tabTipDebBordero.Ativo = 'A'))
SET @ValDebitos = @ValDebitos + (SELECT ROUND(COALESCE (SUM(tabTipDebBordero.PerDesconto * @PubTotal), 0) ,2)
    FROM tabDebBordero 
    INNER JOIN tabTipDebBordero ON tabDebBordero.CodTipDebBordero = tabTipDebBordero.CodTipDebBordero
    WHERE (CONVERT(Varchar(10),@DatApresentacao,112) BETWEEN tabDebBordero.DatIniDebito AND tabDebBordero.DatFinDebito) 
    AND (tabDebBordero.CodPeca =  convert(varchar(10),@CodPeca) ) AND (tabTipDebBordero.TipValor = 'V' AND tabTipDebBordero.Ativo = 'A'))
--Carrega a variável de resultado @ValLiquido
SELECT @ValLiquido = COALESCE(@ValVendaS - @ValDebitos,0)

-- Carregar o Numero do Bordero
SELECT @NumBordero = (SELECT NumBordero FROM tabApresentacao WHERE CodApresentacao = convert(varchar(10), @CodApresentacao) )
SET NOCOUNT OFF

-- Seleciona os dados do borderô
SELECT 
	NomPeca, 
	@NomSala as NomSala, 
	@NomResPeca as NomResPeca,  
	@DatApresentacao as DatApresentacao, 
	@HorSessao as HorSessao, 
	@LugLivres as LugLivres, 
	@PubTotal as PubTotal, 
	@Pagantes as Pagantes, 
	@NPagantes as NPagantes, 
	@ValVendas as ValVendas, 
	@ValDebitos as ValDebitos, 
	@ValLiquido as ValLiquido, 
	@NumBordero as NumBordero,
	@CodSala as CodSala, 
	tabImagem.Imagem as ImgSala, 
	@Apresentacao as Apresentacao
FROM 
	tabPeca ,tabLogoSala, tabImagem
Where 
	(tabPeca.CodPeca = convert(varchar(10),@CodPeca)) 
	AND (tabImagem.CodImagem = tabLogoSala.CodImagem) 
	AND (tabLogoSala.CodSala = @CodSala)