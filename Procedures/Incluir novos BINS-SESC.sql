--23/07/2014 - Jefferson - script para importar bins, que na verdade são as matriculas dos socios do SESC.
--Este script foi criado para importar uma planilha de excel para mw_cartao_patrocinado
--Na planilha foram criadas as colunas de cod_patrocinador (11) e cod_bandeira (6) para facilitar o insert
--IMPORTANTE ESTE PROCEDIMENTO NÃO FOI IMPLANTADO, POIS O NÚMERO DE REGISTRO NA TABELA MW_EVENTO_PATROCINADO IRIA
--PASSAR DE 5 MILHÕES PARA 55 MILHÕES, O QUE INVIABILIZOU ESTA SOLUÇÃO.

--verificar se existe todas as bandeiras na tabela
select * from dbo.mw_bandeira_cartao
--se não existir incluir as bandeira que faltam - no caso do SESC não fopi necessário incluir nenhuma bandeira
--insert into mw_bandeira_cartao values ('ELO')
--insert into mw_bandeira_cartao values ('AMEX')

--verificar se existe o patrocinador na tabela
select * from mw_patrocinador
--no caso do SESC foi criado o patrocinador "SESCRJ"
--insert into mw_patrocinador values ('SESCRJ')

--insere na mw_cartao_patrocinado desde que não existe o bin cadastrado e conforme a importação do excel para a tebela temporario
INSERT INTO mw_cartao_patrocinado
       (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
(SELECT 11,  'Matricula SESC', matricula, 6
        FROM TempBinsSesc
where not exists (select 1 from mw_cartao_patrocinado c
					where matricula = cd_bin))

