/*
+=================================================================================================================+'
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!   1    !			   ! 08/10/2010 ! Emerson Capreti ! Criação inicial da Trigger p/ atualizar as apresentações  !
!		 !			   !			!				  !	no Middleway.											  !	
+========+=============+============+=================+===========================================================+'
!   2    !    #328     ! 13/09/2013 ! Edicarlos S. B. ! Alterado a forma utilizada p/ atualizar a tabela	      !
!        !             !            !                 ! mw_apresentacao_bilhete buscando o id_apresentacao_bilhete!
+=================================================================================================================+
*/

ALTER TRIGGER dbo.tr_atualiza_apresentacoes ON TABAPRESENTACAO
AFTER INSERT
	,DELETE
	,UPDATE
AS
BEGIN
	SET NOCOUNT ON;

	DECLARE @codpeca SMALLINT
		,@codapresentacao INT
		,@datapresentacao SMALLDATETIME
		,@horsessao CHAR(5)
		,@nomsala VARCHAR(30)
		,@id_base INT
		,@id_evento INT
		,@in_ativo_web BIT
		,@in_ativo_bilheteria BIT
		,@id_apresentacao_bilhete INT

	SELECT @id_base = id_base
	FROM ci_middleway..mw_base
	WHERE ds_nome_base_sql = DB_NAME()

	IF @id_base IS NOT NULL
	BEGIN
		IF EXISTS (
				SELECT 1
				FROM inserted
				)
		BEGIN
			DECLARE C1 CURSOR
			FOR
			SELECT a.CodPeca
				,a.CodApresentacao
				,a.DatApresentacao
				,CONVERT(CHAR(5), REPLACE(a.HorSessao, ':', 'h'))
				,s.nomsala
				,CASE (a.StaAtivoBilheteria)
					WHEN 'S'
						THEN 1
					ELSE 0
					END AS StaAtivoBilheteria
				,CASE (a.StaAtivoWeb)
					WHEN 'S'
						THEN 1
					ELSE 0
					END AS StaAtivoWeb
			FROM inserted A
			INNER JOIN tabSala S ON s.codsala = a.codsala

			OPEN C1

			FETCH NEXT
			FROM C1
			INTO @codpeca
				,@codapresentacao
				,@datapresentacao
				,@horsessao
				,@nomsala
				,@in_ativo_bilheteria
				,@in_ativo_web

			WHILE @@FETCH_STATUS = 0
			BEGIN				
				SELECT @id_evento = id_evento
				FROM ci_middleway..mw_evento
				WHERE id_base = @id_base
					AND codpeca = @codpeca

				IF EXISTS (
						SELECT 1
						FROM ci_middleway..mw_apresentacao
						WHERE id_evento = @id_evento
							AND codapresentacao = @codapresentacao
						)
				BEGIN					
					UPDATE ci_middleway..mw_apresentacao
					SET dt_apresentacao = @datapresentacao
						,hr_apresentacao = @horsessao
						,ds_piso = @nomsala						
						,in_ativo = @in_ativo_web
					WHERE id_evento = @id_evento
						AND codapresentacao = @codapresentacao												
					
					-- #328 Atualiza Apresentação Bilhete
					SELECT @id_apresentacao_bilhete = ab.id_apresentacao_bilhete
					FROM ci_middleway..mw_apresentacao_bilhete AS ab
					INNER JOIN 	ci_middleway..mw_evento AS mw_evento ON mw_evento.id_base = @id_base
						AND mw_evento.codpeca = @codpeca
					INNER JOIN ci_middleway..mw_apresentacao AS mw_apresentacao 
						ON mw_apresentacao.id_evento = mw_evento.id_evento
						AND mw_apresentacao.codapresentacao = @codapresentacao					
					WHERE ab.id_apresentacao = mw_apresentacao.id_apresentacao
					
					UPDATE ci_middleway..mw_apresentacao_bilhete
					SET in_ativo = 0
					WHERE id_apresentacao_bilhete = @id_apresentacao_bilhete
				END
				ELSE
				BEGIN
					INSERT INTO ci_middleway..mw_apresentacao (
						dt_apresentacao
						,codapresentacao
						,hr_apresentacao
						,id_evento
						,ds_piso
						,in_ativo						
						)
					VALUES (
						@datapresentacao
						,@codapresentacao
						,@horsessao
						,@id_evento
						,@nomsala
						,1						
						)
				END

				INSERT INTO ci_middleway..mw_apresentacao_bilhete (
					id_apresentacao
					,codtipbilhete
					,ds_tipo_bilhete
					,vl_preco_unitario
					,vl_desconto
					,vl_liquido_ingresso
					,in_ativo
					)
				SELECT mw_apresentacao.id_apresentacao
					,tabtipbilhete.codtipbilhete
					,isnull(tabtipbilhete.ds_nome_site, 'Não Informado')
					,CASE 
						WHEN isnull(tabtipbilhete.vl_preco_fixo, 0) = 0
							THEN tabApresentacao.ValPeca
						ELSE tabtipbilhete.vl_preco_fixo
						END
					,convert(NUMERIC(15, 2), (
							(
								CASE 
									WHEN isnull(tabtipbilhete.vl_preco_fixo, 0) = 0
										THEN tabApresentacao.ValPeca
									ELSE tabtipbilhete.vl_preco_fixo
									END
								) * tabtipbilhete.perdesconto / 100
							)) AS valdesconto
					,convert(NUMERIC(15, 2), (
							CASE 
								WHEN isnull(tabtipbilhete.vl_preco_fixo, 0) = 0
									THEN tabApresentacao.ValPeca
								ELSE tabtipbilhete.vl_preco_fixo
								END
							) - (
							(
								CASE 
									WHEN isnull(tabtipbilhete.vl_preco_fixo, 0) = 0
										THEN tabApresentacao.ValPeca
									ELSE tabtipbilhete.vl_preco_fixo
									END
								) * tabtipbilhete.perdesconto / 100
							)) AS valliquido
					,1
				FROM inserted AS tabapresentacao
				INNER JOIN tabvalbilhete ON tabapresentacao.codpeca = tabvalbilhete.codpeca
					AND tabapresentacao.datapresentacao BETWEEN tabvalbilhete.datinidesconto
						AND tabvalbilhete.datfindesconto
				INNER JOIN tabtipbilhete ON tabtipbilhete.codtipbilhete = tabvalbilhete.codtipbilhete
					AND tabtipbilhete.statipbilhete = 'A'
					AND tabtipbilhete.in_venda_site = '1'
				INNER JOIN ci_middleway..mw_evento AS mw_evento ON mw_evento.id_base = @id_base
					AND mw_evento.codpeca = tabapresentacao.codpeca
				INNER JOIN ci_middleway..mw_apresentacao AS mw_apresentacao ON mw_apresentacao.id_evento = mw_evento.id_evento
					AND mw_apresentacao.codapresentacao = tabapresentacao.codapresentacao				
				
				FETCH NEXT
				FROM C1
				INTO @codpeca
					,@codapresentacao
					,@datapresentacao
					,@horsessao
					,@nomsala
					,@in_ativo_bilheteria
					,@in_ativo_web
			END

			CLOSE C1

			DEALLOCATE C1
		END
		ELSE
		BEGIN
			UPDATE ci_middleway..mw_apresentacao
			SET in_ativo = 0
			FROM ci_middleway..mw_evento e
			INNER JOIN deleted d ON d.codpeca = e.codpeca
			INNER JOIN ci_middleway..mw_apresentacao a ON a.id_evento = e.id_evento
			WHERE id_base = @id_base
				AND a.codapresentacao = @codapresentacao
		END
	END
END
GO