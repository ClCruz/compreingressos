USE [ci_middleway]
GO

/****** Object:  StoredProcedure [dbo].[prc_insere_bilhete_promocao]    Script Date: 08/14/2015 10:29:15 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
-- [prc_insere_bilhete_promocao] 27
ALTER PROCEDURE [dbo].[prc_insere_bilhete_promocao] (@id_promocao_controle INT)
AS
DECLARE @query NVARCHAR(max),
		@CodTipBilhete SMALLINT,
		@id_db INT,
		@ds_db VARCHAR(50),
		@abrangencia VARCHAR(10),
		@caixa varchar(1);

SET NOCOUNT ON;

select @caixa = case codtippromocao when 4 then 'A' else 'T' end from mw_promocao_controle where id_promocao_controle = @id_promocao_controle

DECLARE C1 CURSOR
FOR
SELECT b.id_base,
	b.ds_nome_base_sql,
	'geral' AS abrangencia
FROM mw_base b
WHERE EXISTS (
		SELECT 1
		FROM mw_promocao_controle
		WHERE id_promocao_controle = @id_promocao_controle
			AND in_todos_eventos = 1 AND in_ativo = 1
		)
	AND ds_nome_base_sql IN (SELECT name FROM sys.databases)

UNION ALL

SELECT b.id_base,
	b.ds_nome_base_sql,
	'base' AS abrangencia
FROM mw_promocao_controle pc
INNER JOIN mw_base b ON b.id_base = pc.id_base
WHERE pc.id_promocao_controle = @id_promocao_controle and pc.in_ativo = 1
	AND ds_nome_base_sql IN (SELECT name FROM sys.databases)

UNION ALL

SELECT DISTINCT b.id_base,
	b.ds_nome_base_sql,
	'especifico' AS abrangencia
FROM mw_promocao_controle pc
INNER JOIN mw_controle_evento ce ON ce.id_promocao_controle = pc.id_promocao_controle
INNER JOIN mw_evento e ON e.id_evento = ce.id_evento
INNER JOIN mw_base b ON b.id_base = e.id_base
WHERE pc.id_promocao_controle = @id_promocao_controle and pc.in_ativo = 1
	AND ds_nome_base_sql IN (SELECT name FROM sys.databases)

OPEN C1;

FETCH NEXT
FROM C1
INTO @id_db,
	@ds_db,
	@abrangencia;

WHILE @@fetch_status = 0
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
					''A'', ''' + @caixa + ''', 1, 0, 1, pc.ds_nome_site, 0, 0, 0, 0, 0, 0, 0, pc.vl_preco_fixo, ''N'', ''N'', ''P'',
					0, ''N'', 0, pc.Imag1Promocao, pc.Imag2Promocao, pc.In_hot_site, pc.id_promocao_controle
				from mw_promocao_controle pc
				where pc.in_ativo = 1 and pc.id_promocao_controle = ' + convert(NVARCHAR, @id_promocao_controle) + N'
					and not exists (
						select 1 from ' + @ds_db + N'..tabTipBilhete
						where id_promocao_controle = ' + convert(NVARCHAR, @id_promocao_controle) + N')';

	--print @query;
	EXEC sp_executesql @query;

	-- insere datas de inicio e fim para o bilhete promocional nas respectivas pecas
	IF @abrangencia = 'especifico'
	BEGIN
		SET @query = N'INSERT INTO ' + @ds_db + N'..tabValBilhete ( CodPeca, CodTipBilhete, DatIniDesconto, DatFinDesconto )
					select e.codpeca, tb.codtipbilhete, pc.dt_inicio_promocao, pc.dt_fim_promocao
					from mw_promocao_controle pc
					inner join mw_controle_evento ce on ce.id_promocao_controle = pc.id_promocao_controle
					inner join mw_evento e on e.id_evento = ce.id_evento
					inner join ' + @ds_db + N'..tabtipbilhete tb on tb.id_promocao_controle = pc.id_promocao_controle
					where pc.in_ativo = 1 and pc.id_promocao_controle = ' + convert(NVARCHAR, @id_promocao_controle) + N'
						and e.id_base = ' + convert(NVARCHAR, @id_db) + N'
						and not exists (
							select 1 from ' + @ds_db + N'..tabValBilhete vb
							where vb.codpeca = e.codpeca
							and vb.codtipbilhete = tb.codtipbilhete
						)';
	END
	ELSE
	BEGIN
		SET @query = N'INSERT INTO ' + @ds_db + N'..tabValBilhete ( CodPeca, CodTipBilhete, DatIniDesconto, DatFinDesconto )
					select e.codpeca, tb.codtipbilhete, pc.dt_inicio_promocao, pc.dt_fim_promocao
					from mw_evento e, mw_promocao_controle pc
					inner join ' + @ds_db + N'..tabtipbilhete tb on tb.id_promocao_controle = pc.id_promocao_controle
					where pc.in_ativo = 1 and pc.id_promocao_controle = ' + convert(NVARCHAR, @id_promocao_controle) + N'
						and e.id_base = ' + convert(NVARCHAR, @id_db) + N'
						and not exists (
							select 1 from ' + @ds_db + N'..tabValBilhete vb
							where vb.codpeca = e.codpeca
							and vb.codtipbilhete = tb.codtipbilhete
						)';
	END

	--print @query;
	EXEC sp_executesql @query;

	-- atualiza datas de inicio e fim para o bilhete promocional
	SET @query = N'update vb
					set vb.DatIniDesconto = pc.dt_inicio_promocao,
						vb.DatFinDesconto = pc.dt_fim_promocao
					from ' + @ds_db + N'..tabValBilhete vb
					inner join ' + @ds_db + N'..tabtipbilhete tb on tb.codtipbilhete = vb.codtipbilhete
					inner join mw_promocao_controle pc on pc.id_promocao_controle = tb.id_promocao_controle
					where pc.in_ativo = 1 and pc.id_promocao_controle = ' + convert(NVARCHAR, @id_promocao_controle) + N'
						and (vb.DatIniDesconto <> pc.dt_inicio_promocao
						or vb.DatFinDesconto <> pc.dt_fim_promocao)';

	--print @query;
	EXEC sp_executesql @query;

	FETCH NEXT
	FROM C1
	INTO @id_db,
		@ds_db,
		@abrangencia;
END

CLOSE C1;

DEALLOCATE C1;

SET NOCOUNT OFF;