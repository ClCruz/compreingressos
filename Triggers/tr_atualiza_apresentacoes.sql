-- =============================================
-- Author:		Emerson Capreti
-- Create date: 08/10/10
-- Description:	Atualiza as apresentacoes no Middleway
-- =============================================
alter TRIGGER dbo.tr_atualiza_apresentacoes 
   ON  TABAPRESENTACAO
   AFTER INSERT,DELETE,UPDATE
AS 
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;


	declare @codpeca smallint,
			@codapresentacao int,
			@datapresentacao smalldatetime,
			@horsessao char(5),
			@nomsala varchar(30),
			@id_base int,
			@id_evento int

	select @id_base = id_base from ci_middleway..mw_base where ds_nome_base_sql = DB_NAME()

	if @id_base is not null
		begin	
			if exists (Select 1 from inserted)
				begin
					DECLARE C1 CURSOR FOR 
					SELECT  a.CodPeca
					,		a.CodApresentacao
					,		a.DatApresentacao
					,		CONVERT(char(5), REPLACE(a.HorSessao, ':', 'h')) 
					,		s.nomsala 
					FROM inserted A inner join tabSala S on s.codsala = a.codsala

					OPEN C1

					FETCH NEXT FROM C1 INTO @codpeca
									,		@codapresentacao
									,		@datapresentacao
									,		@horsessao
									,		@nomsala
					WHILE @@FETCH_STATUS = 0
					BEGIN

						select @id_evento = id_evento from ci_middleway..mw_evento where id_base = @id_base and codpeca = @codpeca

						if exists (Select 1 from ci_middleway..mw_apresentacao 
									where id_evento = @id_evento
									  and codapresentacao = @codapresentacao)
							begin
								UPDATE ci_middleway..mw_apresentacao
								SET dt_apresentacao = @datapresentacao
								,	hr_apresentacao = @horsessao
								,	ds_piso			= @nomsala
								WHERE id_evento = @id_evento
								  and codapresentacao = @codapresentacao
	
								update ci_middleway..mw_apresentacao_bilhete
								set in_ativo = 0
								from
								inserted as tabapresentacao
								inner join 
								ci_middleway..mw_evento as mw_evento
								on	mw_evento.id_base = @id_base
								and mw_evento.codpeca = tabapresentacao.codpeca
								inner join
								ci_middleway..mw_apresentacao as mw_apresentacao
								on	mw_apresentacao.id_evento = mw_evento.id_evento
								and mw_apresentacao.codapresentacao = tabapresentacao.codapresentacao
								inner join
								ci_middleway..mw_apresentacao_bilhete ab
								on ab.id_apresentacao = mw_apresentacao.id_apresentacao
	
							end
						else
							begin
								insert into ci_middleway..mw_apresentacao (dt_apresentacao, codapresentacao, hr_apresentacao, id_evento, ds_piso, in_ativo)
								values (@datapresentacao, @codapresentacao, @horsessao, @id_evento, @nomsala, 1)
							end

						insert into ci_middleway..mw_apresentacao_bilhete
						(id_apresentacao, codtipbilhete, ds_tipo_bilhete, vl_preco_unitario, vl_desconto, vl_liquido_ingresso, in_ativo)
						SELECT 	
							mw_apresentacao.id_apresentacao,
							tabtipbilhete.codtipbilhete, 
							isnull(tabtipbilhete.ds_nome_site,'Não Informado'), 
							case when isnull(tabtipbilhete.vl_preco_fixo,0) = 0 then tabApresentacao.ValPeca else tabtipbilhete.vl_preco_fixo	end,
							convert(numeric(15,2), ((case when isnull(tabtipbilhete.vl_preco_fixo,0) = 0 then tabApresentacao.ValPeca else tabtipbilhete.vl_preco_fixo	end) * tabtipbilhete.perdesconto/100)) as valdesconto, 
							convert(numeric(15,2), (case when isnull(tabtipbilhete.vl_preco_fixo,0) = 0 then tabApresentacao.ValPeca else tabtipbilhete.vl_preco_fixo	end) - ((case when isnull(tabtipbilhete.vl_preco_fixo,0) = 0 then tabApresentacao.ValPeca else tabtipbilhete.vl_preco_fixo	end) * tabtipbilhete.perdesconto/100)) as valliquido,
							1
						FROM 
							inserted as tabapresentacao 
							inner join  
							tabvalbilhete
							on	tabapresentacao.codpeca = tabvalbilhete.codpeca 
							and tabapresentacao.datapresentacao between tabvalbilhete.datinidesconto and tabvalbilhete.datfindesconto
							inner join 
							tabtipbilhete 
							on	tabtipbilhete.codtipbilhete = tabvalbilhete.codtipbilhete 
							and	tabtipbilhete.statipbilhete = 'A'  
							and tabtipbilhete.in_venda_site = '1' 
							inner join
							ci_middleway..mw_evento as mw_evento
							on	mw_evento.id_base = @id_base
							and mw_evento.codpeca = tabapresentacao.codpeca
							inner join
							ci_middleway..mw_apresentacao as mw_apresentacao
							on	mw_apresentacao.id_evento = mw_evento.id_evento
							and mw_apresentacao.codapresentacao = tabapresentacao.codapresentacao
	
						FETCH NEXT FROM C1 INTO @codpeca
										,		@codapresentacao
										,		@datapresentacao
										,		@horsessao
										,		@nomsala

					END
					CLOSE C1
					DEALLOCATE C1
				end
			else
				begin
					UPDATE ci_middleway..mw_apresentacao 
					SET in_ativo = 0
					FROM
						ci_middleway..mw_evento e 
						inner join 
						deleted d 
						on d.codpeca = e.codpeca
						inner join 
						ci_middleway..mw_apresentacao a
						on	a.id_evento = e.id_evento
					WHERE id_base = @id_base
					  and a.codapresentacao = @codapresentacao
				end
		end
END
GO
