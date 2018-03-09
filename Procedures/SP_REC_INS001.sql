USE [CI_MIDDLEWAY]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[SP_REC_INS001]
	@ds_razao_social VARCHAR(250),
	@cd_cpf_cnpj VARCHAR(14),
	@ds_nome VARCHAR(100),
	@cd_email VARCHAR(100),
	@ds_ddd_telefone VARCHAR(2),
	@ds_telefone VARCHAR(10),
	@ds_ddd_celular VARCHAR(2),
	@ds_celular VARCHAR(10),
	@cd_banco VARCHAR(5),
	@cd_agencia VARCHAR(6),
	@dv_agencia VARCHAR(2),
	@cd_conta_bancaria VARCHAR(15),
	@dv_conta_bancaria VARCHAR(2),
	@cd_tipo_conta CHAR(2),
	@id_produtor INT,
	@in_ativo BIT
AS
	BEGIN TRANSACTION
	SET NOCOUNT ON

	INSERT INTO [dbo].[mw_recebedor]
           ([ds_razao_social]
           ,[cd_cpf_cnpj]
           ,[ds_nome]
           ,[cd_email]
           ,[ds_ddd_telefone]
           ,[ds_telefone]
           ,[ds_ddd_celular]
           ,[ds_celular]
           ,[cd_banco]
           ,[cd_agencia]
           ,[dv_agencia]
           ,[cd_conta_bancaria]
           ,[dv_conta_bancaria]
           ,[cd_tipo_conta]
           ,[id_produtor]
           ,[in_ativo])
     VALUES
           (@ds_razao_social
           ,@cd_cpf_cnpj
           ,@ds_nome
           ,@cd_email
           ,@ds_ddd_telefone
           ,@ds_telefone
           ,@ds_ddd_celular
           ,@ds_celular
           ,@cd_banco
           ,@cd_agencia
           ,@dv_agencia
           ,@cd_conta_bancaria
           ,@dv_conta_bancaria
           ,@cd_tipo_conta
           ,@id_produtor
           ,@in_ativo)

	SELECT @@IDENTITY AS id

	SET NOCOUNT OFF
	COMMIT TRANSACTION
GO


