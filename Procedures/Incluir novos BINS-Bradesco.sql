--16/10/2013 - Jefferson - script para importar bins do Bradesco
--Este script foi criado para importar de 6 planilhas de excel diferentes os BINS para mw_cartao_patrocinado
--Nas planilhas foram criadas as colunad de cod_patrocinador e cod_bandeira para facilitar o insert

--verificar se existe todas as bandeiras na tabela
select * from dbo.mw_bandeira_cartao
--insert into mw_bandeira_cartao values ('ELO')
--insert into mw_bandeira_cartao values ('AMEX')

--verificar se existe todas o patrocinador na tabela
select * from mw_patrocinador
--insert into mw_patrocinador values ('BRADESCO')

--insere na mw_cartao_patrocinado desde que não existe o bin cadastrado e conforme a importação do excel para a tebela temporario
--INSERT INTO mw_cartao_patrocinado
--       (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
--(SELECT cod_patrocinador,  [complemento_(tamanho 45)], bin, cod_bandeira  
--        FROM TempBinBradesco 
--where not exists (select 1 from mw_cartao_patrocinado c
--					where bin = cd_bin))


--INSERT INTO mw_cartao_patrocinado
--       (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
--(SELECT cod_patrocinador,  left([Produto],50), bin, cod_bandeira  
--        FROM TempBinsAmex 
--where not exists (select 1 from mw_cartao_patrocinado c
--					where bin = cd_bin))

--INSERT INTO mw_cartao_patrocinado
--       (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
--(SELECT cod_patrocinador,  left([Produto],50), bin, cod_bandeira  
--        FROM TempBinsDEBITO 
--where not exists (select 1 from mw_cartao_patrocinado c
--					where bin = cd_bin))


--INSERT INTO mw_cartao_patrocinado
--       (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
--(SELECT cod_patrocinador,  left([DESCRIÇÃO],50), bins, cod_bandeira  
--        FROM TempBinsPRIVATE_LABEL 
--where not exists (select 1 from mw_cartao_patrocinado c
--					where bins = cd_bin))


--INSERT INTO mw_cartao_patrocinado
--       (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
--(SELECT cod_patrocinador,  left(PRODUTO,50), bin, cod_bandeira  
--        FROM TempBinsVME
--where not exists (select 1 from mw_cartao_patrocinado c
--					where bin = cd_bin))


--update TempBinsDeb set bin = '004237' where produto = 'Cartão Salário' 
--update TempBinsDeb set bin = '005237' where produto = 'Cartão Sem Bandeira'
--INSERT INTO mw_cartao_patrocinado
--       (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
--(SELECT cod_patronicador,  left(PRODUTO,50), bin, cod_bandeira  
--        FROM TempBinsDeb
--where not exists (select 1 from mw_cartao_patrocinado c
--					where bin = cd_bin))
				