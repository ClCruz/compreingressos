-- =============================================
-- Author:		Emerson Capreti
-- Create date: 08/10/10
-- Description:	Atualiza os eventos no Middleway
-- =============================================
ALTER TRIGGER [dbo].[tr_atualiza_bilhetes] 
   ON  [dbo].[tabValBilhete]
   AFTER INSERT,DELETE,UPDATE
AS 
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	declare @codpeca smallint,
			@id_evento int,
			@id_base int

	select @id_base = id_base from ci_middleway..mw_base where ds_nome_base_sql = DB_NAME()

	if @id_base is not null
		begin	

			if exists (Select 1 from inserted)
				begin

					update ci_middleway..mw_apresentacao_bilhete
					set in_ativo = 0
					from
					inserted as tabvalbilhete 
					inner join 
					ci_middleway..mw_evento as mw_evento
					on	mw_evento.id_base = @id_base
					and mw_evento.codpeca = tabvalbilhete.codpeca
					inner join
					ci_middleway..mw_apresentacao as mw_apresentacao
					on	mw_apresentacao.id_evento = mw_evento.id_evento
					inner join
					ci_middleway..mw_apresentacao_bilhete ab
					on ab.id_apresentacao = mw_apresentacao.id_apresentacao
					and ab.codtipbilhete = tabvalbilhete.codtipbilhete



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
						inserted as tabvalbilhete 
						inner join 
						tabapresentacao 
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
						and mw_evento.codpeca = tabvalbilhete.codpeca
						inner join
						ci_middleway..mw_apresentacao as mw_apresentacao
						on	mw_apresentacao.id_evento = mw_evento.id_evento
						and mw_apresentacao.codapresentacao = tabapresentacao.codapresentacao

				end
			else
				begin 

					UPDATE ci_middleway..mw_apresentacao_bilhete 
					SET in_ativo = 0
					WHERE CodTipBilhete in (select CodTipBilhete from deleted)
					  and id_apresentacao in (
							select id_apresentacao
							from ci_middleway..mw_apresentacao
							where id_evento IN (
								select id_evento
								from ci_middleway..mw_evento 
								where id_base = @id_base
								and codpeca IN (
									select distinct codpeca from deleted
								)
							)
						)

				end
		end
END