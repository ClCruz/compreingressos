-- =============================================
-- Author:		Emerson Capreti
-- Create date: 08/10/10
-- Description:	Atualiza as apresentacoes no Middleway
-- =============================================

alter TRIGGER dbo.tr_atualiza_ingressos 
   ON  TABTIPBILHETE
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

					update ci_middleway..mw_apresentacao_bilhete
					set	in_ativo = 0
					from
					ci_middleway..mw_evento e
					inner join
					ci_middleway..mw_apresentacao a
					on	a.id_evento = e.id_evento
					inner join
					ci_middleway..mw_apresentacao_bilhete b
					on	b.id_apresentacao = a.id_apresentacao
					inner join
					inserted i
					on	i.codtipbilhete = b.codtipbilhete
					where e.id_base = @id_base



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
						tabpeca 
						inner join 
						tabapresentacao 
						on	tabapresentacao.codpeca = tabpeca.codpeca 
						inner join  
						tabvalbilhete
						on	tabpeca.codpeca = tabvalbilhete.codpeca 
						and tabapresentacao.datapresentacao between tabvalbilhete.datinidesconto and tabvalbilhete.datfindesconto
						inner join 
						inserted as tabtipbilhete 
						on	tabtipbilhete.codtipbilhete = tabvalbilhete.codtipbilhete 
						and	tabtipbilhete.statipbilhete = 'A'  
						and tabtipbilhete.in_venda_site = '1' 
						inner join
						ci_middleway..mw_evento as mw_evento
						on	mw_evento.id_base = @id_base
						and mw_evento.codpeca = tabpeca.codpeca
						inner join
						ci_middleway..mw_apresentacao as mw_apresentacao
						on	mw_apresentacao.id_evento = mw_evento.id_evento
						and mw_apresentacao.codapresentacao = tabapresentacao.codapresentacao
				end
			else
				begin
					update ci_middleway..mw_apresentacao_bilhete
					set	in_ativo = 0
					from
					ci_middleway..mw_evento e
					inner join
					ci_middleway..mw_apresentacao a
					on	a.id_evento = e.id_evento
					inner join
					ci_middleway..mw_apresentacao_bilhete b
					on	b.id_apresentacao = a.id_apresentacao
					inner join
					deleted i
					on	i.codtipbilhete = b.codtipbilhete
					where e.id_base = @id_base
				end
		end
END
GO
