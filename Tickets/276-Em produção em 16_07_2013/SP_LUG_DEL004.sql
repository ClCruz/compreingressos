/*
+=================================================================================================================+'
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!   1    !     233     ! 06/06/2013 ! Edicarlos S. B. ! Criada a procedure para estornar ingressos do tipo de	  !
!		 !			   !			!				  !	venda "Complemento de Meia Entrada"					      !
+========+=============+============+=================+===========================================================+'
!   2    !     276     ! 16/07/2013	! Edicarlos       ! Adicionado parâmetro @CodUsuario na Procedure		      !
+=================================================================================================================+
*/

ALTER PROCEDURE dbo.SP_LUG_DEL004 (
	@CodCaixa			tinyint,
	@DatMovimento		smalldatetime,
	@CodApresentacao	int,
	@Indice				int,
	@CodLog				int,
	@CodMovimento		int,
	@CodUsuario			int
)   
AS

DECLARE @NumLancamento  int			--Número do Lançamento da Venda
DECLARE @CodTipBilhete  smallint	--Código do Tipo de Bilhete
DECLARE @CodCliente   	int			--Código do Cliente
DECLARE @CodVenda		varchar(10)	--Código da Venda
DECLARE @Step			int			--Passo de Execução da Rotina

SET NOCOUNT ON
 
-- Seleciona o último lancamento para o a cadeira
  set @step = 1
  SELECT @NumLancamento = (SELECT MAX(NumLancamento) FROM tabLancamento WHERE CodApresentacao = @CodApresentacao AND Indice = @Indice AND CodTipLancamento = 4)
  IF @@ERROR <> 0 GOTO ERRO

-- Grava Log de Operação
--set @step = 2
--INSERT INTO tabLogOpeDetalhe (IdLogOperacao, Indice, NumLancamento, TipLancamento)  (SELECT @CodLog, Indice, @NumLancamento, 2 FROM tabLugSala WHERE CodApresentacao = @CodApresentacao AND Indice = @Indice)
--IF @@ERROR <> 0 GOTO ERRO

-- Seleciona o Código da Venda Complemento de Meia Entrada
   SET @CodVenda = (SELECT CASE WHEN CodVendaComplMeia = '' THEN NULL ELSE CodVendaComplMeia END AS CodVendaComplMeia From tabLugsala WHERE Indice = @Indice AND CodApresentacao = @CodApresentacao) 
   
IF @CodVenda IS NOT NULL
BEGIN

-- Verifica se o sistema está controlando o codigo de barras do ingresso. se sim, checa se o ingresso ja passou na catraca.
	if exists (select 1 from sysobjects where type = 'U' and name = 'tabControleSeqVenda')
		begin
			if exists (select 1 from tabControleSeqVenda where codapresentacao = @CodApresentacao and indice = @Indice and statusingresso = 'U')
				RAISERROR ('Ingresso não pode ser estornado pois já passou na catraca.', 16, 1 )
			else
				update tabControleSeqVenda set statusingresso = 'E' where codapresentacao = @CodApresentacao and indice = @Indice and statusingresso <> 'E'
		end


-- Atualiza o Lugar na tabLugSala para NULL, pelo indice da cadeira e código da apresentação
-- Por se tratar de uma venda de Complemento de Meia Entrada o lugar não será apagado!
  set @step = 3
  UPDATE tabLugSala SET StaCadeiraComplMeia = NULL, CodVendaComplMeia = '', CodTipBilheteComplMeia = null WHERE Indice = @Indice AND CodApresentacao = @CodApresentacao  
  IF @@ERROR <> 0 GOTO ERRO

-- Obtém o Código do Tipo de Bilhete Vendido na tabLancamento através do Número de Lançamento da Venda
  set @step = 4
  SELECT @CodTipBilhete = (SELECT CodTipBilhete FROM tabLancamento WHERE NumLancamento = @NumLancamento AND  Indice = @Indice AND CodApresentacao = @CodApresentacao)
  IF @@ERROR <> 0 GOTO ERRO
  
-- Insere na tabela de lancamento um lancamento negativo igual o ultimo lancamento para o índice
  set @step = 5
   INSERT INTO tabLancamento (NumLancamento, CodTipBilhete, CodTipLancamento, CodApresentacao, Indice,
      CodUsuario, CodForPagto, CodCaixa, DatMovimento, QtdBilhete, ValPagto, DatVenda, CodMovimento)
      SELECT NumLancamento, CodTipBilhete, 2, CodApresentacao, Indice, 
       @CodUsuario, CodForPagto, @CodCaixa, @DatMovimento, -1, COALESCE(ValPagto,0)*-1, GETDATE(), @CodMovimento
       FROM tabLancamento 
       WHERE NumLancamento = @NumLancamento AND Indice = @Indice
  
    IF @@ERROR <> 0 GOTO ERRO


  -- Atualiza o Movimento do caixa
  set @step = 6
    UPDATE tabMovCaixa SET Saldo = COALESCE(Saldo- (SELECT TOP 1 ValPagto FROM tabLancamento WHERE NumLancamento = @NumLancamento AND Indice = @Indice),0)
     WHERE CodCaixa = @CodCaixa
     	AND CONVERT(varchar(10),DatMovimento,112) = CONVERT(varchar(10), @DatMovimento,112)
	AND StaMovimento = 'A'
  IF @@ERROR <> 0 GOTO ERRO


  -- Seleciona o codigo do cliente se houver historico
  set @step = 7
  SELECT @CodCliente =  (SELECT TOP 1 Codigo FROM tabHisCliente WHERE NumLancamento = @NumLancamento)
  IF @@ERROR <> 0 GOTO ERRO


  -- Insere o registro no histórico do cliente
  set @step = 8
  IF NOT @CodCliente IS NULL
   INSERT INTO tabHisCliente (Codigo, NumLancamento, CodTipBilhete, CodTipLancamento, CodApresentacao, Indice)
  VALUES (@CodCliente, @NumLancamento, @CodTipBilhete, 2, @CodApresentacao, @Indice)
  IF @@ERROR <> 0 GOTO ERRO


  set @step = 9
  DELETE FROM tabIngressoAgregados where (codvenda = @CodVenda) and (indice = @indice)
  IF @@ERROR <> 0 GOTO ERRO
 
  set @step = 10
  DELETE FROM tabIngresso where (codvenda = @CodVenda) and (indice = @indice)
  IF @@ERROR <> 0 GOTO ERRO
 
  IF not exists(Select CodVenda from tabIngresso Where CodVenda = @CodVenda)
     BEGIN	
        set @step = 11
        DELETE FROM tabcomprovante where codvenda = @CodVenda
         IF @@ERROR <> 0 GOTO ERRO
     END
END

SET NOCOUNT OFF

RETURN

ERRO:
	select @STEP as passo
	SET NOCOUNT OFF
	RETURN
