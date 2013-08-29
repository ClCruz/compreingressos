USE [ci_middleway]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Gabriel Fernandes Monteiro
-- Create date: 26/08/13
-- Description:	Atualiza as permissões no Middleway
-- =============================================
ALTER TRIGGER [dbo].[tr_atualiza_permissoes] 
   ON  [dbo].[mw_evento]
   AFTER INSERT,DELETE,UPDATE
AS 
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;


	declare @codpeca smallint,
			@id_base int


	if exists (Select 1 from inserted)
		begin
			DECLARE C1 CURSOR FOR 
			SELECT	codpeca,
					id_base
			FROM inserted

			OPEN C1

			FETCH NEXT FROM C1 INTO	@codpeca,
									@id_base
			WHILE @@FETCH_STATUS = 0
			BEGIN

				INSERT INTO MW_ACESSO_CONCEDIDO (ID_USUARIO, ID_BASE, CODPECA)
				SELECT R.ID_USUARIO, @id_base, @codpeca
				FROM MW_RESPONS_TEATRO R
				WHERE NOT EXISTS (SELECT * FROM MW_ACESSO_CONCEDIDO A WHERE A.ID_USUARIO = R.ID_USUARIO AND A.ID_BASE = @id_base AND A.CODPECA = @codpeca)
				AND R.ID_BASE = @id_base

				FETCH NEXT FROM C1 INTO @codpeca,
										@id_base

			END
			CLOSE C1
			DEALLOCATE C1
		end
	else
		begin
			DECLARE C1 CURSOR FOR 
			SELECT	codpeca,
					id_base
			FROM deleted

			OPEN C1

			FETCH NEXT FROM C1 INTO	@codpeca,
									@id_base
			WHILE @@FETCH_STATUS = 0
			BEGIN

				DELETE FROM MW_ACESSO_CONCEDIDO WHERE ID_BASE = @id_base AND CODPECA = @codpeca

				FETCH NEXT FROM C1 INTO @codpeca,
										@id_base

			END
			CLOSE C1
			DEALLOCATE C1
		end
				
END