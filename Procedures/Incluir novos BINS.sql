--Jefferson - 17/01/2012
--Processo para incluir novos BINS na base de acordo com relação enviada pela Compreingressos
--1o. Carregar a planilha Excel para a tabela TEMP

--2o. verificar se o patrocinador já está cadastrado
select * from mw_patrocinador 

--3o. verificar se a bandeira do cartão já está cadastrada
select * from mw_bandeira_cartao 
--insert into mw_bandeira_cartao values ('N/I')

--4o. incluir o novo registro de acordo com a relação recebida da Compreingressos
--Se os cadastros de bandeira e patrocinador estiverem OK, basta rodar o insert abaixo para atualizar o sistema e
--em seguida rodar o JOB "Cria BINS para novos eventos" para atualizar a mw_evento_patrocinado.
insert into mw_cartao_patrocinado
select 1, left([Descrição do Produto],50), [BIN Itaú], b.id_bandeira_cartao
from temp t
inner join
mw_bandeira_cartao b
on upper(b.ds_bandeira_cartao) = upper(ltrim(t.[bandeira]))
where not exists (select 1 from mw_cartao_patrocinado c
					where [BIN Itaú] =cd_bin)
					and [BIN Itaú] is not null

--exemplo: insert into mw_cartao_patrocinado 
--        (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
--         values (1,'TIM ITAUCARD GOLD MASTERCARD','526892',2)
--select * from mw_cartao_patrocinado

--5o. incluir o novo registro na mw_evento_patrocinado, relacionando o mw_evento e a mw_cartao_patrocinado
--será necessário rodar.
--Exemplo:insert into mw_evento_patrocinado 
--        (id_cartao_patrocinado, id_base, codpeca, dt_inicio, dt_fim)
--         select '651', id_base, codpeca, '2012-01-17 00:00', '2013-03-31 00:00' from mw_evento
--onde 651 = código do id_cartao_patrocinado criado no passo 3o.