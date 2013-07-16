/*
+=================================================================================================================+'
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!   1    !     436     ! 24/03/2004 ! Emerson         ! Excluir da TabIngressoAgregados antes da TabIngresso      !
+=================================================================================================================+
!   2    !     1369    ! 12/02/2008 ! Marciano S.C    ! Modif. o tipo de dado  @CodTipBilhete tinyint p/ smallint !
+========+=============+============+=================+===========================================================+'
!   3    !     --      ! 02/10/2010 ! Emerson         ! Verifica se o ingresso nao foi utilizado, quer dizer, se o!
!		 !			   !			!				  ! cara passou na catraca. valido somente para bases que	  !	
!		 !			   !			!				  !	controlam o codigo de barras							  !
+========+=============+============+=================+===========================================================+'
!   4    !     233     ! 07/06/2013	! Edicarlos       ! Adicionado novo select para retornar o CodTipBilhete      !
+========+=============+============+=================+===========================================================+'
!   5    !     276     ! 16/07/2013	! Edicarlos       ! Adicionado parâmetro @CodUsuario na Procedure		      !
+========+=============+============+=================+===========================================================+'
!   6    !     276     ! 16/07/2013	! Edicarlos       ! Trocado o @DatMovimento p/ GETUPDATE no INS tabLancamento !	
+=================================================================================================================+
*/

ALTER PROCEDURE dbo.SP_LUG_DEL003 (
	@CodCaixa			tinyint,
	@DatMovimento		smalldatetime,
	@CodApresentacao	int,
	@Indice				int,
	@CodLog				int,
	@CodMovimento		int,
	@CodUsuario			int
)   
AS

DECLARE @NumLancamento  int
DECLARE @CodTipBilhete  smallint
DECLARE @CodCliente   	int
DECLARE @CodVenda		varchar(10)
DECLARE @Step			int

SET NOCOUNT ON
 
-- Seleciona o último lancamento para o a cadeira
  set @step = 1
  SELECT @NumLancamento = (SELECT MAX(NumLancamento) FROM tabLancamento WHERE CodApresentacao = @CodApresentacao AND Indice = @Indice and codtiplancamento not in (4,2))
  IF @@ERROR <> 0 GOTO ERRO

-- Grava Log de Operação
--  set @step = 2
--  INSERT INTO tabLogOpeDetalhe (IdLogOperacao, Indice, NumLancamento, TipLancamento)  (SELECT @CodLog, Indice, @NumLancamento, 2 FROM tabLugSala WHERE CodApresentacao = @CodApresentacao AND Indice = @Indice)
--  IF @@ERROR <> 0 GOTO ERRO

-- Seleciona o Código da Venda  
    SET @CodVenda = (SELECT CodVenda From tabLugsala WHERE Indice = @Indice AND CodApresentacao = @CodApresentacao) 


-- Verifica se o sistema está controlando o codigo de barras do ingresso. se sim, checa se o ingresso ja passou na catraca.
	if exists (select 1 from sysobjects where type = 'U' and name = 'tabControleSeqVenda')
		begin
			if exists (select 1 from tabControleSeqVenda where codapresentacao = @CodApresentacao and indice = @Indice and statusingresso = 'U')
				RAISERROR ('Ingresso não pode ser estornado pois já passou na catraca.', 16, 1 )
			else
				update tabControleSeqVenda set statusingresso = 'E' where codapresentacao = @CodApresentacao and indice = @Indice and statusingresso <> 'E'
		end


-- Deleta o registro em tabLugSala pelo indice da cadeira
  set @step = 3
  DELETE FROM tabLugSala WHERE Indice = @Indice AND CodApresentacao = @CodApresentacao
  IF @@ERROR <> 0 GOTO ERRO

  set @step = 4
  SELECT @CodTipBilhete = (SELECT TOP 1 CodTipBilhete FROM tabLancamento WHERE NumLancamento = @NumLancamento AND  Indice = @Indice AND CodApresentacao = @CodApresentacao)
  IF @@ERROR <> 0 GOTO ERRO  
  
    -- Insere na tabela de lancamento um lancamento negativo igual o ultimo lancamento para o índice
  set @step = 5
   INSERT INTO tabLancamento (NumLancamento, CodTipBilhete, CodTipLancamento, CodApresentacao, Indice,
      CodUsuario, CodForPagto, CodCaixa, DatMovimento, QtdBilhete, ValPagto, DatVenda, CodMovimento)
      SELECT NumLancamento, CodTipBilhete, 2, CodApresentacao, Indice, 
       @CodUsuario, CodForPagto, @CodCaixa, GETDATE(), -1, COALESCE(ValPagto,0)*-1, GETDATE(), @CodMovimento
       FROM tabLancamento 
       WHERE NumLancamento = @NumLancamento AND Indice = @Indice AND CodTipLancamento not in(4,2)
  
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

SET NOCOUNT OFF

RETURN


ERRO:
	select @STEP as passo
	SELECT @CodCliente As CodCliente,
		   @NumLancamento As NumLancamento,
		   @CodTipBilhete As CodTipBilhete, 
		   2, 
		   @CodApresentacao As CodApresentacao, 
		   @Indice As Indice
	SET NOCOUNT OFF
	RETURN
