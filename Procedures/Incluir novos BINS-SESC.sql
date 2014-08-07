--23/07/2014 - Jefferson - script para importar bins, que na verdade s�o as matriculas dos socios do SESC.
--Este script foi criado para importar uma planilha de excel para mw_cartao_patrocinado
--Na planilha foram criadas as colunas de cod_patrocinador (11) e cod_bandeira (6) para facilitar o insert
--IMPORTANTE ESTE PROCEDIMENTO N�O FOI IMPLANTADO, POIS O N�MERO DE REGISTRO NA TABELA MW_EVENTO_PATROCINADO IRIA
--PASSAR DE 5 MILH�ES PARA 55 MILH�ES, O QUE INVIABILIZOU ESTA SOLU��O.

--verificar se existe todas as bandeiras na tabela
select * from dbo.mw_bandeira_cartao
--se n�o existir incluir as bandeira que faltam - no caso do SESC n�o fopi necess�rio incluir nenhuma bandeira
--insert into mw_bandeira_cartao values ('ELO')
--insert into mw_bandeira_cartao values ('AMEX')

--verificar se existe o patrocinador na tabela
select * from mw_patrocinador
--no caso do SESC foi criado o patrocinador "SESCRJ"
--insert into mw_patrocinador values ('SESCRJ')

--insere na mw_cartao_patrocinado desde que n�o existe o bin cadastrado e conforme a importa��o do excel para a tebela temporario
INSERT INTO mw_cartao_patrocinado
       (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
(SELECT 11,  'Matricula SESC', matricula, 6
        FROM TempBinsSesc
where not exists (select 1 from mw_cartao_patrocinado c
					where matricula = cd_bin))

