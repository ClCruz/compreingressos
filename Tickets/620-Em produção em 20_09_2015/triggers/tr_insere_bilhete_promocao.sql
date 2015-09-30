USE [ci_middleway]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Gabriel Fernandes Monteiro
-- Create date: 14/08/13
-- Description:	Cria registros de promoção no "VB"
-- =============================================
ALTER TRIGGER [dbo].[tr_insere_bilhete_promocao]
   ON  [dbo].[mw_evento]
   AFTER INSERT
AS 
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;


	declare @codpeca smallint,
			@query NVARCHAR(max),
			@CodTipBilhete SMALLINT,
			@id_db INT,
			@ds_db VARCHAR(50),
			@id_promocao_controle INT

	DECLARE C1 CURSOR
	FOR
	SELECT i.codpeca, i.id_base, b.ds_nome_base_sql, pc.id_promocao_controle
	FROM inserted i
	inner join mw_base b on b.id_base = i.id_base
	inner join mw_promocao_controle pc on pc.id_base = i.id_base and pc.in_ativo = 1

	union all

	SELECT i.codpeca, i.id_base, b.ds_nome_base_sql, pc.id_promocao_controle
	FROM mw_promocao_controle pc, inserted i
	inner join mw_base b on b.id_base = i.id_base
	where pc.in_todos_eventos = 1 and pc.in_ativo = 1

	OPEN C1

	FETCH NEXT FROM C1 INTO	@codpeca,
							@id_db,
							@ds_db,
							@id_promocao_controle
	WHILE @@FETCH_STATUS = 0
	BEGIN

		-- obtem codtipbilhete
		SET @query = N'SELECT @CodTipBilhete = COALESCE( (SELECT MAX(CodTipBilhete) FROM ' + @ds_db + N'..tabTipBilhete (nolock)), 0) + 1';

		EXEC sp_executesql @query,
			N'@CodTipBilhete smallint output',
			@CodTipBilhete OUTPUT;

		-- insere tipbilhete
		SET @query = N'INSERT INTO ' + @ds_db + N'..tabTipBilhete (
													CodTipBilhete, 
													TipBilhete, 
													PerDesconto, 
													StaTipBilhete, 
													TipCaixa, 
													ImpVlIngresso, 
													ImpDSBilhDest,
													In_venda_site,
													ds_nome_site,
													in_dom,
													in_seg,
													in_ter,
													in_qua,
													in_qui,
													in_sex,
													in_sab,
													vl_preco_fixo,
													StaTipBilhMeia,
													StaTipBilhMeiaEstudante,
													StaCalculoMeiaEstudante,
													CotaMeiaEstudante,
													StaCalculoPorSala,
													QtdVendaPorLote,
													Img1Promocao,
													Img2Promocao,
													In_hot_site,
													id_promocao_controle
													)
					select ' + convert(NVARCHAR, @CodTipBilhete) + N', pc.ds_tipo_bilhete, pc.perc_desconto_vr_normal,
						''A'', ''T'', 1, 0, 1, pc.ds_nome_site, 0, 0, 0, 0, 0, 0, 0, pc.vl_preco_fixo, ''N'', ''N'', ''P'',
						0, ''N'', 0, pc.Imag1Promocao, pc.Imag2Promocao, pc.In_hot_site, pc.id_promocao_controle
					from mw_promocao_controle pc
					where pc.in_ativo = 1 and id_promocao_controle = ' + convert(NVARCHAR, @id_promocao_controle) + N'
						and not exists (
							select 1 from ' + @ds_db + N'..tabTipBilhete
							where id_promocao_controle = ' + convert(NVARCHAR, @id_promocao_controle) + N')';

		EXEC sp_executesql @query;
		
		-- insere datas de inicio e fim para o bilhete promocional nas respectivas pecas
		SET @query = N'INSERT INTO ' + @ds_db + N'..tabValBilhete ( CodPeca, CodTipBilhete, DatIniDesconto, DatFinDesconto )
					select e.codpeca, tb.codtipbilhete, pc.dt_inicio_promocao, pc.dt_fim_promocao
					from mw_evento e, mw_promocao_controle pc
					inner join ' + @ds_db + N'..tabtipbilhete tb on tb.id_promocao_controle = pc.id_promocao_controle
					where pc.in_ativo = 1 and e.codpeca = ' + convert(NVARCHAR, @codpeca) + N'
						and pc.id_promocao_controle = ' + convert(NVARCHAR, @id_promocao_controle) + N'
						and e.id_base = ' + convert(NVARCHAR, @id_db) + N'
						and not exists (
							select 1 from ' + @ds_db + N'..tabValBilhete vb
							where vb.codpeca = e.codpeca
							and vb.codtipbilhete = tb.codtipbilhete
						)';
						
		EXEC sp_executesql @query;

		FETCH NEXT FROM C1 INTO @codpeca,
								@id_db,
								@ds_db,
								@id_promocao_controle

	END
	CLOSE C1
	DEALLOCATE C1
				
END