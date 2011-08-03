-- =============================================
-- Author:		Emerson Capreti
-- Create date: 08/10/10
-- Alteração em: 15/03/11 --Atualiza o campo in_vende_itau na mw_evento
-- Alteração em: 28/03/11 --Adicionado o campo id_local_evento. Edicarlos Barbosa
-- Alteração em: 03/08/11 --Adicionado o campo in_entrega_ingresso no insert. Edicarlos Barbosa
-- Description:	Atualiza os eventos no Middleway
-- =============================================
alter TRIGGER dbo.tr_atualiza_eventos 
   ON  TABPECA
   AFTER INSERT,DELETE,UPDATE
AS 
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	declare @codpeca smallint,
			@nompeca varchar(35),
			@stapeca char(1),
			@in_vende_site char(1),
			@id_base int,
            @in_bin_itau char(1),
			@id_local_evento int


	select @id_base = id_base from ci_middleway..mw_base where ds_nome_base_sql = DB_NAME()

	if @id_base is not null
		begin	
			if exists (Select 1 from inserted)
				begin
					select @codpeca = codpeca, @nompeca = nompeca, @stapeca = stapeca, 
					@in_vende_site = in_vende_site, @in_bin_itau = in_bin_itau, @id_local_evento = id_local_evento 
					from inserted

					if exists (Select 1 from ci_middleway..mw_evento where codpeca = @codpeca and id_base = @id_base)
						begin
							UPDATE ci_middleway..mw_evento 
							SET ds_evento = @nompeca,
							in_ativo = case @stapeca when 'I' then 0 else @in_vende_site end,
							in_vende_itau = case when @stapeca = 'A' and @in_bin_itau = 1 then 1 else 0 end,
							id_local_evento = @id_local_evento
							WHERE CodPeca = @codpeca AND id_base = @id_base
						end
					else
						begin
							insert into ci_middleway..mw_evento (ds_evento, codpeca, id_base,
							in_ativo, in_vende_itau, id_local_evento, in_entrega_ingresso)
							values (@nompeca, @codpeca, @id_base, case @stapeca when 'I' then 0					
									else @in_vende_site end, case when @stapeca = 'A' and @in_bin_itau = 1 then 1 else 0 end,
									@id_local_evento, 0)
						end
				end
			else
				begin
					UPDATE ci_middleway..mw_evento 
					SET ds_evento = @nompeca,
						in_ativo = 0, 
						in_vende_itau = 0,
						id_local_evento = @id_local_evento
					WHERE CodPeca = (select codpeca from deleted) AND id_base = @id_base
				end
		end
END
GO
