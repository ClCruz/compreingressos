--Jefferson - 17/01/2012
--Processo para incluir novos BINS na base de acordo com rela��o enviada pela Compreingressos
--1o. verificar se o patrocinador j� est� cadastrado
select * from mw_patrocinador 

--2o. verificar se a bandeira do cart�o j� est� cadastrada
select * from mw_bandeira_cartao 
insert into mw_bandeira_cartao values ('N/I')
--3o. incluir o novo registro de acordo com a rela��o recebida da Compreingressos

--exemplo: insert into mw_cartao_patrocinado 
--        (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
--         values (1,'TIM ITAUCARD GOLD MASTERCARD','526892',2)
select * from mw_cartao_patrocinado

commit
insert into mw_cartao_patrocinado
select 1, left([Descri��o do Produto],50), [BIN Ita�], b.id_bandeira_cartao
from temp t
inner join
mw_bandeira_cartao b
on upper(b.ds_bandeira_cartao) = upper(ltrim(t.[bandeira]))
where not exists (select 1 from mw_cartao_patrocinado c
					where [BIN Ita�] =cd_bin)
					and [BIN Ita�] is not null

--4o. incluir o novo registro na mw_evento_patrocinado, relacionando o mw_evento e a mw_cartao_patrocinado
--Exemplo:insert into mw_evento_patrocinado 
--        (id_cartao_patrocinado, id_base, codpeca, dt_inicio, dt_fim)
--         select '651', id_base, codpeca, '2012-01-17 00:00', '2013-03-31 00:00' from mw_evento
--onde 651 = c�digo do id_cartao_patrocinado criado no passo 3o.
